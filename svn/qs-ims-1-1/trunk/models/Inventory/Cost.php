<?php

class Qss_Model_Inventory_Cost extends Qss_Model_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getAll()
	{
		$retval = array();
		$sql = sprintf('show tables like \'tblcost%%\'');
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$item = (array) $item;
			$item = array_values($item);
			$name = $item[0];
			$period = substr($name, 7,strlen($name));
			if(strlen($period) == 6)
			{
				$month = substr($period, 0,2);
				$year = substr($period, 2,4);
				$new = new stdClass();
				$new->Name = $name;
				$new->Month = $month;
				$new->Year = $year;
				$retval[] = $new;
			}
		}
		return $retval;
	}
	public function dropTable($month,$year)
	{
		$sql = sprintf('DROP TABLE `tblcost%1$s%2$s`',
				str_pad($month,2,'0',STR_PAD_LEFT),
				str_pad($year,4,'0',STR_PAD_LEFT));
		$this->_o_DB->execute($sql);
	}
	public function createTable($month,$year)
	{
		$sql = sprintf('CREATE TABLE `tblcost%1$s%2$s` (
					   	`Ref_MaSanPham` int NOT NULL,
                        `Ref_ThuocTinh` int NOT NULL DEFAULT 0,
                        `ThuocTinh` varchar(255) NULL,
						`Ref_Kho` int NOT NULL,
						`Gia` BIGINT NOT NULL DEFAULT 0,
						`TonKhoDK` double NOT NULL DEFAULT 0,
						`Nhap` double NOT NULL DEFAULT 0,
						`Xuat` double NOT NULL DEFAULT 0,
						`TonKhoCK` double NOT NULL DEFAULT 0,
						PRIMARY KEY (`Ref_MaSanPham`, `Ref_ThuocTinh`, `Ref_Kho`)
					) ENGINE=InnoDB',
				str_pad($month,2,'0',STR_PAD_LEFT),
				str_pad($year,4,'0',STR_PAD_LEFT));
		$this->_o_DB->execute($sql);
	}
	public function calculate($month,$year)
	{
		$last = '';
		if($month==1)
		{
			$last = sprintf('tblcost12%1$s',str_pad($year-1, 4,'0',STR_PAD_LEFT));
		}
		else
		{
			$last = sprintf('tblcost%1$s%2$s',str_pad($month-1, 2,'0',STR_PAD_LEFT),str_pad($year, 4,'0',STR_PAD_LEFT));
		}
		$table = sprintf('tblcost%1$s%2$s',str_pad($month, 2,'0',STR_PAD_LEFT),str_pad($year, 4,'0',STR_PAD_LEFT));
		if($this->_o_DB->tableExists($last))
		{
			//lấy đầu kỳ trước
			$sql = sprintf('select sp.IOID, kho.Ref_Kho,sum(gdnhap.Nhap) AS Nhap
                            ,sum(gdxuat.Xuat) AS Xuat,sum(gdnhap.NhapTien) AS NhapTien, sum(gdxuat.XuatTien) AS XuatTien,
							ifnull(last.TonKhoCK,kho.SoLuongKhoiTao) as TonDK,last.Gia,
							ifnull(last.TonKhoCK,kho.SoLuongKhoiTao)*ifnull(last.Gia,sp.GiaMua) as TonTien,
							if(last.TonKhoCK is null,GiaTriKhoiTao,0) as TonTien1
							from OSanPham as sp
							inner join OKho as kho on kho.Ref_MaSP = sp.IOID
							left join %1$s as last on last.Ref_MaSanPham = sp.IOID and last.Ref_Kho = kho.Ref_Kho and ifnull(last.Ref_ThuocTinh,0) = ifnull(kho.Ref_ThuocTinh,0)
							left join (select Ref_MaSanPham, Ref_Kho, Ref_ThuocTinh,
								sum(ifnull(SoLuong,0)) as Nhap,
								sum(ifnull(SoLuong,0)*ifnull(DonGia,0)) as NhapTien
								from ODanhSachNhapKho 
								inner join ONhapKho on ONhapKho.IFID_M402 = ODanhSachNhapKho.IFID_M402
								inner join qsiforms on qsiforms.IFID =  ONhapKho.IFID_M402
								where qsiforms.Status = 2 and 
								month(NgayChungTu)=%2$d and year(NgayChungTu)=%3$d 
								group by Ref_MaSanPham, ifnull(Ref_ThuocTinh,0),Ref_Kho)
								as gdnhap on gdnhap.Ref_MaSanPham = sp.IOID and gdnhap.Ref_Kho = kho.Ref_Kho
								and ifnull(gdnhap.Ref_ThuocTinh,0) = ifnull(kho.Ref_ThuocTinh,0)
							left join (select Ref_MaSP as Ref_MaSanPham, Ref_Kho, Ref_ThuocTinh,
								sum(ifnull(SoLuong,0)) as Xuat,
								sum(ifnull(SoLuong,0)*ifnull(DonGia,0)) as XuatTien
								from ODanhSachXuatKho
								inner join OXuatKho on OXuatKho.IFID_M506 = ODanhSachXuatKho.IFID_M506
								inner join qsiforms on qsiforms.IFID =  OXuatKho.IFID_M506
								where qsiforms.Status = 2 and 
								month(NgayChungTu)=%2$d and year(NgayChungTu)=%3$d 
								group by Ref_MaSP, ifnull(Ref_ThuocTinh,0),Ref_Kho)
								as gdxuat on gdxuat.Ref_MaSanPham = sp.IOID and gdxuat.Ref_Kho = kho.Ref_Kho
								and ifnull(gdxuat.Ref_ThuocTinh,0) = ifnull(kho.Ref_ThuocTinh,0)
                            GROUP BY sp.IOID, ifnull(kho.Ref_ThuocTinh, 0), kho.Ref_Kho
							',
							$last,
							$month,
							$year);
		}
		else 
		{
			//Lấy trong kho tôn lúc đầu
			$sql = sprintf('select sp.IOID,kho.Ref_Kho,sum(gdnhap.Nhap) AS Nhap
                            ,sum(gdxuat.Xuat) AS Xuat,sum(gdnhap.NhapTien) AS NhapTien, sum(gdxuat.XuatTien) AS XuatTien,
							kho.SoLuongKhoiTao as TonDK,sp.GiaMua as Gia,
							kho.SoLuongKhoiTao * sp.GiaMua as TonTien,
							kho.GiaTriKhoiTao as TonTien1
							from OSanPham as sp
							inner join OKho as kho on kho.Ref_MaSP = sp.IOID
							left join (select Ref_MaSanPham, Ref_Kho, Ref_ThuocTinh,
								sum(ifnull(SoLuong,0)) as Nhap,
								sum(ifnull(SoLuong,0)*ifnull(DonGia,0)) as NhapTien
								from ODanhSachNhapKho 
								inner join ONhapKho on ONhapKho.IFID_M402 = ODanhSachNhapKho.IFID_M402
								inner join qsiforms on qsiforms.IFID =  ONhapKho.IFID_M402
								where qsiforms.Status = 2 and 
								month(NgayChungTu)=%2$d and year(NgayChungTu)=%3$d 
								group by Ref_MaSanPham, ifnull(Ref_ThuocTinh,0),Ref_Kho)
								as gdnhap on gdnhap.Ref_MaSanPham = sp.IOID and gdnhap.Ref_Kho = kho.Ref_Kho
								and ifnull(gdnhap.Ref_ThuocTinh,0) = ifnull(kho.Ref_ThuocTinh,0)
							left join (select Ref_MaSP as Ref_MaSanPham, Ref_Kho, Ref_ThuocTinh,
								sum(ifnull(SoLuong,0)) as Xuat,
								sum(ifnull(SoLuong,0)*ifnull(DonGia,0)) as XuatTien
								from ODanhSachXuatKho
								inner join OXuatKho on OXuatKho.IFID_M506 = ODanhSachXuatKho.IFID_M506
								inner join qsiforms on qsiforms.IFID =  OXuatKho.IFID_M506
								where qsiforms.Status = 2 and 
								month(NgayChungTu)=%2$d and year(NgayChungTu)=%3$d 
								group by Ref_MaSP, ifnull(Ref_ThuocTinh,0),Ref_Kho)
								as gdxuat on gdxuat.Ref_MaSanPham = sp.IOID and gdxuat.Ref_Kho = kho.Ref_Kho
								and ifnull(gdxuat.Ref_ThuocTinh,0) = ifnull(kho.Ref_ThuocTinh,0)
                            GROUP BY sp.IOID, ifnull(kho.Ref_ThuocTinh, 0), kho.Ref_Kho
							',
							$last,
							$month,
							$year);
			
		}
		//echo $sql;
		$dataSQL =  $this->_o_DB->fetchAll($sql);
		$sqlInsert = sprintf('insert into %1$s(Ref_MaSanPham,Ref_Kho,Gia,TonKhoDK,Nhap,Xuat,TonKhoCK)',$table);

		$sqlValues = '';
		foreach ($dataSQL as $item)
		{
			if($sqlValues != '')
			{
				$sqlValues .= ',';
			}
			if($item->TonDK + $item->Nhap)
			{
				$item->TonTien = $item->TonTien1?$item->TonTien1:$item->TonTien;
				$gia = round(($item->TonTien + $item->NhapTien )/($item->TonDK + $item->Nhap),2);//- $item->XuatTien  - $item->Xuat  - $item->Xuat
			}
			else 
			{
				$gia = $item->Gia;
			}
			if(!$gia)
			{
				$gia = 0;
			}
			$sqlValues .= sprintf('(%1$d,%2$d,%3$s,%4$g,%5$g,%6$g,%7$g)',
								$item->IOID,
								$item->Ref_Kho,
								$gia,
								$item->TonDK,
								$item->Nhap,
								$item->Xuat,
								$item->TonDK - $item->Xuat + $item->Nhap);
		}
		if($sqlValues != '')
		{
			if($this->_o_DB->tableExists($table))
			{
				$this->dropTable($month,$year);
			}
			$this->createTable($month,$year);
			$sql = $sqlInsert . ' values' . $sqlValues;
            //echo '<pre>'; print_r($sql); die;
			$this->_o_DB->execute($sql);
		}
		//Tinhs tat cac kho
		
	}
	public function update($masp,$month,$year,$kho)
	{
		$last = '';
		if($month==1)
		{
			$last = sprintf('tblcost12%1$s',str_pad($year-1, 4,'0',STR_PAD_LEFT));
		}
		else
		{
			$last = sprintf('tblcost%1$s%2$s',str_pad($month-1, 2,'0',STR_PAD_LEFT),str_pad($year, 4,'0',STR_PAD_LEFT));
		}
		$table = sprintf('tblcost%1$s%2$s',str_pad($month, 2,'0',STR_PAD_LEFT),str_pad($year, 4,'0',STR_PAD_LEFT));
		if(!$this->_o_DB->tableExists($table))
		{
			$this->calculate($month,$year);
		}
		else 
		{
			if($this->_o_DB->tableExists($last))
			{
				//lấy đầu kỳ trước
				$sql = sprintf('select sp.IOID,kho.Ref_Kho,sum(gdnhap.Nhap) AS Nhap
                                ,sum(gdxuat.Xuat) AS Xuat,sum(gdnhap.NhapTien) AS NhapTien, sum(gdxuat.XuatTien) AS XuatTien,
								ifnull(last.TonKhoCK,kho.SoLuongKhoiTao) as TonDK,last.Gia,
								ifnull(last.TonKhoCK,kho.SoLuongKhoiTao)*ifnull(last.Gia,sp.GiaMua) as TonTien,
								if(last.TonKhoCK is null,GiaTriKhoiTao,0) as TonTien1
								from OSanPham as sp
								inner join OKho as kho on kho.Ref_MaSP = sp.IOID
								left join %1$s as last on last.Ref_MaSanPham = sp.IOID and last.Ref_Kho = kho.Ref_Kho and ifnull(last.Ref_ThuocTinh,0) = ifnull(kho.Ref_ThuocTinh,0)
								left join (select Ref_MaSanPham, Ref_Kho, Ref_ThuocTinh,
									sum(ifnull(SoLuong,0)) as Nhap,
									sum(ifnull(SoLuong,0)*ifnull(DonGia,0)) as NhapTien
									from ODanhSachNhapKho 
									inner join ONhapKho on ONhapKho.IFID_M402 = ODanhSachNhapKho.IFID_M402
									inner join qsiforms on qsiforms.IFID =  ONhapKho.IFID_M402
									where qsiforms.Status = 2 and 
									month(NgayChungTu)=%2$d and year(NgayChungTu)=%3$d 
									group by Ref_MaSanPham, ifnull(Ref_ThuocTinh,0),Ref_Kho)
									as gdnhap on gdnhap.Ref_MaSanPham = sp.IOID and gdnhap.Ref_Kho = kho.Ref_Kho
									and ifnull(gdnhap.Ref_ThuocTinh,0) = ifnull(kho.Ref_ThuocTinh,0)
								left join (select Ref_MaSP as Ref_MaSanPham, Ref_Kho, Ref_ThuocTinh,
									sum(ifnull(SoLuong,0)) as Xuat,
									sum(ifnull(SoLuong,0)*ifnull(DonGia,0)) as XuatTien
									from ODanhSachXuatKho
									inner join OXuatKho on OXuatKho.IFID_M506 = ODanhSachXuatKho.IFID_M506
									inner join qsiforms on qsiforms.IFID =  OXuatKho.IFID_M506
									where qsiforms.Status = 2 and 
									month(NgayChungTu)=%2$d and year(NgayChungTu)=%3$d 
									group by Ref_MaSP, ifnull(Ref_ThuocTinh,0),Ref_Kho)
									as gdxuat on gdxuat.Ref_MaSanPham = sp.IOID and gdxuat.Ref_Kho = kho.Ref_Kho
									and ifnull(gdxuat.Ref_ThuocTinh,0) = ifnull(kho.Ref_ThuocTinh,0)
								where sp.MaSanPham=%4$s and kho.Kho=%5$s
								GROUP BY sp.IOID, ifnull(kho.Ref_ThuocTinh, 0), kho.Ref_Kho
								',
								$last,
								$month,
								$year,
								$this->_o_DB->quote($masp),
								$this->_o_DB->quote($kho));
			}
			else 
			{
				//Lấy trong kho tôn lúc đầu
				$sql = sprintf('select sp.IOID,kho.Ref_Kho,sum(gdnhap.Nhap) AS Nhap
                                ,sum(gdxuat.Xuat) AS Xuat,sum(gdnhap.NhapTien) AS NhapTien, sum(gdxuat.XuatTien) AS XuatTien,
								kho.SoLuongKhoiTao as TonDK,sp.GiaMua as Gia,
								kho.SoLuongKhoiTao * sp.GiaMua as TonTien,
								GiaTriKhoiTao as TonTien1
								from OSanPham as sp
								inner join OKho as kho on kho.Ref_MaSP = sp.IOID
								left join (select Ref_MaSanPham, Ref_Kho, Ref_ThuocTinh,
									sum(ifnull(SoLuong,0)) as Nhap,
									sum(ifnull(SoLuong,0)*ifnull(DonGia,0)) as NhapTien
									from ODanhSachNhapKho 
									inner join ONhapKho on ONhapKho.IFID_M402 = ODanhSachNhapKho.IFID_M402
									inner join qsiforms on qsiforms.IFID =  ONhapKho.IFID_M402
									where qsiforms.Status = 2 and 
									month(NgayChungTu)=%2$d and year(NgayChungTu)=%3$d 
									group by Ref_MaSanPham, ifnull(Ref_ThuocTinh,0),Ref_Kho)
									as gdnhap on gdnhap.Ref_MaSanPham = sp.IOID and gdnhap.Ref_Kho = kho.Ref_Kho
									and ifnull(gdnhap.Ref_ThuocTinh,0) = ifnull(kho.Ref_ThuocTinh,0)
								left join (select Ref_MaSP as Ref_MaSanPham, Ref_Kho, Ref_ThuocTinh,
									sum(ifnull(SoLuong,0)) as Xuat,
									sum(ifnull(SoLuong,0)*ifnull(DonGia,0)) as XuatTien
									from ODanhSachXuatKho
									inner join OXuatKho on OXuatKho.IFID_M506 = ODanhSachXuatKho.IFID_M506
									inner join qsiforms on qsiforms.IFID =  OXuatKho.IFID_M506
									where qsiforms.Status = 2 and 
									month(NgayChungTu)=%2$d and year(NgayChungTu)=%3$d 
									group by Ref_MaSP, ifnull(Ref_ThuocTinh,0),Ref_Kho)
									as gdxuat on gdxuat.Ref_MaSanPham = sp.IOID and gdxuat.Ref_Kho = kho.Ref_Kho
									and ifnull(gdxuat.Ref_ThuocTinh,0) = ifnull(kho.Ref_ThuocTinh,0)
								where sp.MaSanPham=%4$s and kho.Kho=%5$s
								GROUP BY sp.IOID, ifnull(kho.Ref_ThuocTinh, 0), kho.Ref_Kho
								',
								$last,
								$month,
								$year,
								$this->_o_DB->quote($masp),
								$this->_o_DB->quote($kho));
				
			}
			$item =  $this->_o_DB->fetchOne($sql);
			if($item)
			{
				if($item->TonDK + $item->Nhap - $item->Xuat)
				{
					$item->TonTien = $item->TonTien1?$item->TonTien1:$item->TonTien;
					$gia = round(($item->TonTien + $item->NhapTien - $item->XuatTien)/($item->TonDK + $item->Nhap - $item->Xuat),2);
				}
				else 
				{
					$gia = $item->Gia;
				}
				if(!$gia)
				{
					$gia = 0;
				}
				$sqlValues = sprintf('replace into %8$s(Ref_MaSanPham,Ref_Kho,Gia,TonKhoDK,Nhap,Xuat,TonKhoCK) values(%1$d,%2$d,%3$s,%4$g,%5$g,%6$g,%7$g)',
									$item->IOID,
									$item->Ref_Kho,
									$gia,
									$item->TonDK,
									$item->Nhap,
									$item->Xuat,
									$item->TonDK - $item->Xuat + $item->Nhap,
									$table); 
				$this->_o_DB->execute($sqlValues);
			}
		}
	}
}