<?php

class Qss_Model_Extra_Warehouse extends Qss_Model_Abstract
{

	//-----------------------------------------------------------------------
	/**
	 * Build constructor
	 * '
	 * @return void
	 */
	public $vattuObject;

	public function __construct()
	{
		parent::__construct();
		// Trong hoa phat doi tuong vattu dung chung giua pbt m759 va ke hoach m724
	}


	public function getRecommendMaterials()
	{
		$sql = sprintf('
                    select sp.*, ifnull(sp.DonViTinh, \'\') as DonViTinh
                    , ifnull(sp.SLToiThieu,0) as MinQty
                    , ifnull(k1.SoLuong, 0) as Inventory
                    from OSanPham as sp
                    left join
                    (
                        select 
                                sum(case when ifnull(dvtsp.MacDinh,0) = 1
                                    then
                                        k.SoLuongHC
                                    else
                                        k.SoLuongHC * dvtsp.HeSoQuyDoi
                                    end)
                                 as SoLuong
                        , k.Ref_MaSP
                        , dvtsp.IOID as Ref_DonViTinh
                        from OKho as k
                        left join ODonViTinhSP as dvtsp on k.Ref_DonViTinh = dvtsp.IOID
                        group by k.Ref_MaSP
                    ) as k1
                    on sp.IOID = k1.Ref_MaSP
                    limit 10000
                ');
		return $this->_o_DB->fetchAll($sql);

	}

	/* Current qty of item (with stock status) */

	public function getCurrentDetailInventoryStatus($refItem, $refWarehouse, $refAttribute
	, $lot, $serial, $refBin)
	{
		$sql = sprintf('
			SELECT ttlt.*
			, (ifnull(ttlt.SoLuong, 0) * ifnull(dvt.HeSoQuyDoi,0)) as SoLuongQuyDoi
			FROM OThuocTinhChiTiet AS ttlt
			LEFT JOIN OSanPham AS sp on ttlt.Ref_MaSanPham = sp.IOID
			LEFT JOIN ODonViTinhSP AS dvt on sp.IFID_M113 = dvt.IFID_M113
			WHERE ifnull(ttlt.IFID_M602, 0) != 0
			AND ttlt.Ref_MaSanPham = %1$d
			AND ttlt.Ref_Kho = %2$d ', $refItem, $refWarehouse);
		
		if(Qss_Lib_System::fieldActive('OThuocTinhChiTiet', 'MaThuocTinh'))
		{
			$sql .= ($refAttribute) ? ' AND ttlt.Ref_MaThuocTinh = ' . $this->_o_DB->quote($refAttribute)
				 : ' AND ifnull(ttlt.Ref_MaThuocTinh, 0) = 0 ';
		}
		$sql .= ($lot) ? ' AND ttlt.SoLo = ' . $this->_o_DB->quote($lot) : '';
		$sql .= ($serial) ? ' AND ttlt.SoSerial = ' . $this->_o_DB->quote($serial) : '';
		$sql .= ($refBin) ? ' AND ttlt.Ref_Bin = ' . $refBin : '';
		return $this->_o_DB->fetchOne($sql);

	}
	
	

	/* Tim so luong san pham dua tren base uom  */
	// @todo: sua lai kiem tra so luong toi thieu, toi da cua san pham
	public function getItemQuantity($itemIOID)
	{
		$where = '';
		if($itemIOID)
		{
			$where .= sprintf(' AND k.`Ref_MaSP` = %1$d', $itemIOID);
		}
		
		$sql = sprintf('
						SELECT sum(
							ifnull(k.SoLuongHC, 0) * ifnull(dvt.`HeSoQuyDoi`, 0)
						) as SoLuongHC , sp.SLToiThieu
						FROM OKho AS k
						LEFT JOIN ODanhSachKho AS dsk ON dsk.IOID = k.Ref_Kho 
						LEFT JOIN OSanPham AS sp ON sp.IOID = k.Ref_MaSP
						LEFT JOIN ODonViTinhSP AS dvt ON dvt.`IFID_M113` = sp.`IFID_M113`
							AND k.`Ref_DonViTinh` = dvt.`IOID`
						WHERE ifnull(dsk.`LoaiKho`, \'\') != %1$s
							%2$s 
						GROUP BY sp.IOID
						ORDER BY sp.IOID
						LIMIT 8000
						'
					,$this->_o_DB->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_DRAFT)
					,$where);
		return $this->_o_DB->fetchOne($sql);

	}
	
	/* Tim so luong san pham dua tren base uom  */
	// @todo: sua lai kiem tra so luong toi thieu, toi da cua san pham
	public function getItemHasQtyLessThanMin($itemIOID = 0)
	{
		$where = '';
		if($itemIOID)
		{
			$where .= sprintf(' AND k.`Ref_MaSP` = %1$d', $itemIOID);
		}
		
		// lay so luong mot san pham
		$sql = sprintf('
			SELECT  *
			FROM 
			(
			SELECT 
			sp1.*,
			sum(
				ifnull(k.SoLuongHC, 0) * ifnull(dvt.`HeSoQuyDoi`, 0)
			) as SoLuongHC , ifnull(sp1.SLToiThieu, 0) AS MinQty
			FROM OSanPham AS sp1 
			LEFT JOIN OKho AS k ON k.Ref_MaSP = sp1.IOID
			LEFT JOIN ODanhSachKho AS dsk ON dsk.IOID = k.Ref_Kho 
			LEFT JOIN OSanPham AS sp ON sp.IOID = k.Ref_MaSP
			LEFT JOIN ODonViTinhSP AS dvt ON dvt.`IFID_M113` = sp.`IFID_M113`
				AND k.`Ref_DonViTinh` = dvt.`IOID`
			WHERE ifnull(dsk.`LoaiKho`, \'\') != %1$s
				%2$s 
			GROUP BY sp1.IOID
			ORDER BY sp1.IOID
			LIMIT 8000
			) AS t
			WHERE t.SoLuongHC < t.MinQty
			'
			, $this->_o_DB->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_DRAFT)
			,$where);
		return $this->_o_DB->fetchAll($sql);

	}	

	/**
	 * 
	 * @param number $ifid 
	 * @param string $module
	 * @return total qty of items has lot, serial or bin
	 */
	public function getTotalReceiveHasAttr($ifid, $module, $getDataObject = '')
	{
		$sanPham = 'MaSP';
		$kho = 'Kho';
		$soLuong = 'SoLuong';
		$joinMain = '';

		switch ($module)
		{
			case 'M402':
				$table = 'ODanhSachNhapKho';
				$joinMain = 'ONhapKho';
				$sanPham = 'MaSanPham';
				$kho = 'Kho';
				break;
			case 'M506':
				$table = 'ODanhSachXuatKho';
				$joinMain = 'OXuatKho';
				break;
			case 'M603':
				$table = 'ODanhSachCK';
				$kho = 'KhoLH';
				break;
			case 'M717':
				$table = 'OThongKeSanLuong';
				break;
			case 'M718':
				$table = 'ONVLDauVao';
				break;
			default:
				$sanPham = 'MaSP';
				$kho = 'Kho';
				$soLuong = 'SoLuong';
				break;
		}



		if ($module == 'M603')
		{
			$kho = sprintf(' inner join OChuyenKho as ck on ck.IFID_M603 = ds.IFID_M603  
				   			 INNER JOIN ODanhSachKho AS dsk ON ds.Ref_%1$s = dsk.IOID', $kho);
		} elseif ($module == 'M706')
		{
			$kho = sprintf(' inner join OPhieuBTDK as btdk on btdk.IFID_M706 = ds.IFID_M706  
                                         INNER JOIN ODonViSanXuat AS dvsx ON btdk.Ref_MaDVBT = dvsx.IOID
                                         INNER JOIN ODanhSachKho AS dsk ON dvsx.Ref_%1$s = dsk.IOID', $kho);
		} elseif ($module == 'M759')
		{
			$kho = sprintf(' inner join OPhieuBaoTri as btdk on btdk.IFID_M759 = ds.IFID_M759  
                                         INNER JOIN ODonViSanXuat AS dvsx ON btdk.Ref_MaDVBT = dvsx.IOID
                                         INNER JOIN ODanhSachKho AS dsk ON dvsx.Ref_%1$s = dsk.IOID', $kho);
		} else
		{
			$kho = ($joinMain) ?
				sprintf('
				   			INNER JOIN %2$s as dh on dh.IFID_%3$s = ds.IFID_%3$s
				   			INNER JOIN ODanhSachKho AS dsk ON dh.Ref_%1$s = dsk.IOID'
					, $kho, $joinMain, $module) :
				sprintf('INNER JOIN ODanhSachKho AS dsk ON ds.Ref_%1$s = dsk.IOID', $kho);
		}

		//#LEFT JOIN OZone AS z ON dsk.IFID_M601 = z.IFID_M601
		//#nd z.IOID = (SELECT IOID FROM OZone WHERE IFID_M601 = dsk.IFID_M601 LIMIT 1 )
		$sql = sprintf('SELECT sum(ifnull(ds.%1$s,0)) as SoLuong
                                FROM %2$s AS ds
                                INNER JOIN OSanPham AS sp ON sp.IOID = ds.Ref_%5$s
                                %6$s
                                LEFT JOIN OBin AS b ON dsk.IFID_M601 = b.IFID_M601
                                and b.IOID = (SELECT IOID FROM OBin WHERE IFID_M601 = dsk.IFID_M601 LIMIT 1 )
                                WHERE ds.IFID_%3$s =%4$d
                                AND (
                                sp.QuanLyTheoMa =1
                                OR sp.QuanLyTheoLo =1
                                OR ( b.IOID = (SELECT IOID FROM OBin WHERE IFID_M601 = dsk.IFID_M601 LIMIT 1  ))
                                )
                                ', $soLuong, $table, $module, $ifid, $sanPham, $kho);
		$dataSql = $this->_o_DB->fetchOne($sql);
		return $dataSql ? $dataSql->SoLuong : 0;

	}

	// @todo: Kiem tra suc chua thieu kiem tra bin co chua mat hang hay khongc
	// va chua quy doi ra don vi tinh co so
	public function checkCapacity($module, $ifid)
	{
		$kho = 'Kho';
		//$zone = 'Zone';
		$bin = 'Bin';
		$stockStatusTable = 'OThuocTinhChiTiet';

		switch ($module)
		{
			case 'M603':
				$kho = 'KhoNhanHang';
				//$zone = 'DenZone';
				$bin = 'DenBin';
				$stockStatusTable = 'OTrangThaiLuuTruCK';
				break;			
			
			case 'M706':
			case 'M758':
			case 'M759':
				$stockStatusTable = 'OTrangThaiLuuTruPLBT';
				break;

			default:
				$kho = 'Kho';
				//$zone = 'Zone';
				$bin = 'Bin';
				$stockStatusTable = 'OThuocTinhChiTiet';
				break;
		}
		$zone = '';
		$sql = sprintf('SELECT
			TTCT.MaSanPham as SSItemCode,
			IFNULL(TTCT.Ref_MaSanPham,0) as SSRefItemCode,
			TTCT.%1$s as SSWarehouse,
			TTCT.%2$s as SSBinCode,
			TTCT.SoSerial as Serial,
			TTCT.SoLo as Lot,
			B.MaSP as BinItemCode,
			IFNULL(B.Ref_MaSP,0) as BinRefItemCode,
			IFNULL(B.SucChua,0) as BinCapacity,
			B.DonViTinh as BinUOM,
			B.MaBin as BinCode,
			DSK.MaKho as Warehouse,
			IFNULL(TTCT.SoLuong,0) as NewQty,
			IFNULL(KHO.SoLuong,0)  as HasQty,
			(CASE 
			WHEN (B.MaSP is not null )  THEN  1
			WHEN (B.DonViTinh is not null ) THEN 2
			ELSE  0	END) as `Condition`
			FROM  %3$s as TTCT
			INNER JOIN ODanhSachKho as DSK
			ON  TTCT.Ref_%1$s = DSK.IOID
			LEFT JOIN OBin as B
			ON B.IFID_M601 = DSK.IFID_M601 AND B.IOID = TTCT.Ref_%2$s
			LEFT JOIN OThuocTinhChiTiet as KHO
			ON KHO.IFID_M602 is not null 
			AND KHO.Ref_Kho = DSK.IOID 
			AND 
			CASE 
			WHEN (B.MaSP is not null )  THEN KHO.Ref_MaSanPham = B.Ref_MaSP
			WHEN (B.DonViTinh is not null )  THEN KHO.DonViTinh = B.DonViTinh
			ELSE 1=0
			END
			AND (TTCT.Ref_MaThuocTinh is null or TTCT.Ref_MaThuocTinh = KHO.Ref_MaThuocTinh)
			AND (B.IOID is null OR B.IOID = KHO.Ref_Bin)
			WHERE TTCT.IFID_%4$s = %5$d'
			, $kho, $bin, $stockStatusTable, $module, $ifid);
		return $this->_o_DB->fetchAll($sql);

	}
	
	public function checkCapacityForSortWarehouseModule($ifid, $refWarehouse)
	{
		$sql = sprintf('
			SELECT
			IFNULL(TTCT.Ref_MaSP,0) as SSRefItemCode,
			TTCT.DenBin as SSBinCode,
			TTCT.Serial as Serial,
			TTCT.Lot as Lot,
			B.MaSP as BinItemCode,
			IFNULL(B.Ref_MaSP,0) as BinRefItemCode,
			IFNULL(B.SucChua,0) as BinCapacity,
			B.DonViTinh as BinUOM,
			B.MaBin as BinCode,
			DSK.MaKho as Warehouse,
			(IFNULL(TTCT.SoLuong,0) * IFNULL(dvt1.HeSoQuyDoi,0)) as NewQty,
			(IFNULL(KHO.SoLuong,0)* IFNULL(dvt2.HeSoQuyDoi,0))  as HasQty,
			(CASE 
			WHEN (B.MaSP is not null )  THEN  1
			WHEN (B.DonViTinh is not null ) THEN 2
			ELSE  0	END) as `Condition`
			FROM  OChiTietSapXepKho as TTCT
			LEFT JOIN OSanPham AS sp1 On sp1.IOID = TTCT.Ref_MaSP
			LEFT JOIN ODonViTinhSP AS dvt1 On dvt1.IFID_M113 = sp1.IFID_M113
			INNER JOIN ODanhSachKho as DSK
			ON   DSK.IOID = %1$d /* IOID Cua kho */
			LEFT JOIN OBin as B
			ON B.IFID_M601 = DSK.IFID_M601 AND B.IOID = TTCT.Ref_DenBin
			LEFT JOIN OThuocTinhChiTiet as KHO
			LEFT JOIN OSanPham AS sp2 On sp2.IOID = KHO.Ref_MaSanPham
			LEFT JOIN ODonViTinhSP AS dvt2 On dvt2.IFID_M113 = sp2.IFID_M113			
			ON KHO.IFID_M602 is not null 
			AND KHO.Ref_Kho = DSK.IOID 
			AND 
			CASE 
			WHEN (B.MaSP is not null )  THEN KHO.Ref_MaSanPham = B.Ref_MaSP
			WHEN (B.DonViTinh is not null )  THEN KHO.DonViTinh = B.DonViTinh
			ELSE 1=0
			END
			/*AND (TTCT.Ref_MaThuocTinh is null or TTCT.Ref_MaThuocTinh = KHO.Ref_MaThuocTinh)*/
			AND (B.IOID is null OR B.IOID = KHO.Ref_Bin)
			WHERE TTCT.IFID_M616 = %2$d'
			, $refWarehouse, $ifid);
		return $this->_o_DB->fetchAll($sql);

	}

	/*
	 * Stock volume report
	 * @Remove
	 */

	public function getAllItems($group = 0, $itemType = array())
	{
		$sql = 'select * from OSanPham where 1=1';
		//$sql .= ($group)?sprintf(' and Ref_NhomSanPham = %1$d ', $group):'';
		foreach ($itemType as $column => $val)
		{
			$sql .= ($val == 1) ? sprintf(' and %1$s = %2$d ', $column, $val) : '';
		}
		$sql .= ' order by  MaSanPham ';
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);

	}

	/* */
	public function getInventoryByDate($start, $warehouse, $item,$attribute = 0)
	{
		$sql = sprintf('
						select sum(ifnull(SoLuong,0)) as NhapKho
						from ODanhSachNhapKho as dsnk
						inner join ONhapKho as nk on nk.IFID_M402 = dsnk.IFID_M402
						inner join qsiforms on qsiforms.IFID =  nk.IFID_M402
						where nk.NgayChungTu < %1$s 
						and Ref_Kho = %2$d
						and Ref_MaSanPham = %3$d'
						, $this->_o_DB->quote($start)
						, $warehouse
						, $item);
		$nhapSQL = $this->_o_DB->fetchOne($sql);
		
		$sql = sprintf('
						select sum(ifnull(SoLuong,0)) as XuatKho
						from ODanhSachXuatKho as dsxk
						inner join OXuatKho as xk on xk.IFID_M506 = dsxk.IFID_M506
						inner join qsiforms on qsiforms.IFID =  xk.IFID_M506
						where xk.NgayChungTu < %1$s 
						and Ref_Kho = %2$d
						and Ref_MaSP = %3$d'
						, $this->_o_DB->quote($start)
						, $warehouse
						, $item);
		$xuatSQL = $this->_o_DB->fetchOne($sql);
		return (int)@$nhapSQL->NhapKho -(int)@$xuatSQL->XuatKho;
	}
	/*
	 * End in/out report
	 */
    
    /**
     * Lấy tồn kho ban đầu theo cài đặt
     */
    public function getFirstInventory($warehouse, $items)
    {
        $temp = '';
		foreach ($items as $it)
		{
			$temp .= $temp ? ' or ' : '';
			$temp .= sprintf(' kho.Ref_MaSP = %1$d ', $it);
		}
		$temp = $temp ? sprintf(' and (%1$s) ', $temp) : '';

		$warehouse = $warehouse ? sprintf(' and kho.Ref_Kho = %1$d ', $warehouse) : '';
        
        //OpeningStockVal
		$sql = sprintf('
						select 
                            kho.Ref_MaSP AS RefItem
                            , sum( ifnull(kho.`SoLuongKhoiTao` , 0) * ifnull(donvitinh.`HeSoQuyDoi` , 0) ) AS OpeningStock
                            , sum( ifnull(kho.`SoLuongKhoiTao` , 0) * ifnull(donvitinh.`HeSoQuyDoi` , 0) * sanpham.`GiaMua` ) AS OpeningStockVal
						from OKho as kho
                        INNER JOIN OSanPham AS sanpham ON kho.`Ref_MaSP` = sanpham.IOID
                        INNER JOIN ODonViTinhSP AS donvitinh ON sanpham.`IFID_M113` = donvitinh.`IFID_M113` 
                            AND kho.`Ref_DonViTinh` = donvitinh.IOID
						where 1=1
						%1$s
						group by kho.Ref_MaSP
						order by kho.Ref_MaSP
						', $temp);
		return $this->_o_DB->fetchAll($sql);        
    }
	
	//getInventoryHistory
	/* Report: Inventory History */
	public function getMaxMinInventoryByPeriod($start, $end, $period, $item, $warehouse = 0, $attribute = 0)
	{
		$fieldQty = 'TongHC';
		if ($warehouse && $attribute)
		{
			// Su dung kho hien tai thuoc tinh hien co
			$fieldQty = 'HienCo';
		} elseif ($warehouse && !$attribute)
		{
			// Su dung kho hien tai hien co
			$fieldQty = 'KhoHienTaiHC';
		} elseif (!$warehouse && $attribute)
		{
			// Su dung tong thuoc tinh hien co
			$fieldQty = 'TongThuocTinhHC';
		} elseif (!$warehouse && !$attribute)
		{
			// Su dung tong hien co
			$fieldQty = 'TongHC';
		}

		$subGroupBy = '';
		if ($attribute)
		{
			$subGroupBy = ', gdk.Ref_ThuocTinh ';
		}

		// Dieu kien loc 
		$where = sprintf(' where (gdk.Ngay between %1$s and %2$s)
						   and gdk.Ref_MaSanPham = %3$d '
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end)
			, $item);
		$where .= ($warehouse) ? sprintf(' and gdk.Ref_Kho = %1$d', $warehouse) : '';
		$where .= ($attribute) ? sprintf(' and gdk.Ref_ThuocTinh = %1$d', $attribute) : '';

		//Group by  & Order theo ky 
		$groupBy = '';
		$orderBy = '';
		$select = '';
		switch ($period)
		{
			case 'D':
				$groupBy = ' group by gdk.Ngay, gdk.Ref_MaSanPham ' . $subGroupBy;
				$orderBy = ' order by gdk.Ngay, gdk.Ref_MaSanPham ' . $subGroupBy;
				$select = ' , gdk.Ngay as Khoa';
				break;
			case 'W':
				$groupBy = ' group by week(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$orderBy = ' order by week(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$select = ' , week(gdk.Ngay) as Tuan, year(gdk.Ngay) as Nam, CONCAT_WS(\'.\',week(gdk.Ngay),year(gdk.Ngay)) as Khoa ';
				break;
			case 'M':
				$groupBy = ' group by month(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$orderBy = ' order by month(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$select = ' , month(gdk.Ngay) as Thang, year(gdk.Ngay) as Nam, CONCAT_WS(\'.\',month(gdk.Ngay),year(gdk.Ngay)) as Khoa ';
				break;
			case 'Q':
				$groupBy = ' group by quarter(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$orderBy = ' order by quarter(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$select = ' , quarter(gdk.Ngay) as Quy, year(gdk.Ngay) as Nam, CONCAT_WS(\'.\',quarter(gdk.Ngay),year(gdk.Ngay)) as Khoa  ';
				break;
			case 'Y':
				$groupBy = ' group by year(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$orderBy = ' order by year(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$select = ' , year(gdk.Ngay) as Nam, year(gdk.Ngay) as Khoa ';
				break;
		}

		$sql = sprintf('select 
						gdk.MaSanPham,
						gdk.SanPham, 
						gdk.Ngay,
						max(case when IFNULL(gdk.NhapXuat,0) = 1 then ifnull(gdk.%5$s,0) + gdk.SoLuong else ifnull(gdk.%5$s,0) - gdk.SoLuong end) as MaxTonKho, 
						min(case when IFNULL(gdk.NhapXuat,0) = 1 then ifnull(gdk.%5$s,0) + gdk.SoLuong else ifnull(gdk.%5$s,0) - gdk.SoLuong end) as MinTonKho 
						%1$s
						from OGiaoDichKho as gdk 
						%2$s %3$s %4$s
						', $select, $where, $groupBy, $orderBy, $fieldQty);
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);

	}

	/* Report: Inventory History */

	public function getLastestInventoryByPeriod($start, $end, $period, $item, $warehouse = 0, $attribute = 0)
	{
		$fieldQty = 'TongHC';
		if ($warehouse && $attribute)
		{
			// Su dung kho hien tai thuoc tinh hien co
			$fieldQty = 'HienCo';
		} elseif ($warehouse && !$attribute)
		{
			// Su dung kho hien tai hien co
			$fieldQty = 'KhoHienTaiHC';
		} elseif (!$warehouse && $attribute)
		{
			// Su dung tong thuoc tinh hien co
			$fieldQty = 'TongThuocTinhHC';
		} elseif (!$warehouse && !$attribute)
		{
			// Su dung tong hien co
			$fieldQty = 'TongHC';
		}

		$subGroupBy = '';
		if ($attribute)
		{
			$subGroupBy = ', gdk.Ref_ThuocTinh ';
		}

		// Dieu kien loc 
		$where = sprintf(' where (gdk.Ngay between %1$s and %2$s)
						   and gdk.Ref_MaSanPham = %3$d '
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end)
			, $item);
		$where .= ($warehouse) ? sprintf(' and gdk.Ref_Kho = %1$d', $warehouse) : '';
		$where .= ($attribute) ? sprintf(' and gdk.Ref_ThuocTinh = %1$d', $attribute) : '';

		// Group by  & Order theo ky 
		$groupBy = '';
		$orderBy = '';
		$select = '';
		switch ($period)
		{
			case 'D':
				$groupBy = ' group by gdk.Ngay, gdk.Ref_MaSanPham' . $subGroupBy;
				$orderBy = ' order by gdk.Ngay, gdk.Ref_MaSanPham' . $subGroupBy;
				$select = ' , gdk.Ngay as Khoa';
				break;
			case 'W':
				$groupBy = ' group by week(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$orderBy = ' order by week(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$select = ' , week(gdk.Ngay) as Tuan, year(gdk.Ngay) as Nam, CONCAT_WS(\'.\',week(gdk.Ngay),year(gdk.Ngay)) as Khoa ';
				break;
			case 'M':
				$groupBy = ' group by month(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$orderBy = ' order by month(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$select = ' , month(gdk.Ngay) as Thang, year(gdk.Ngay) as Nam, CONCAT_WS(\'.\',month(gdk.Ngay),year(gdk.Ngay)) as Khoa ';
				break;
			case 'Q':
				$groupBy = ' group by quarter(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$orderBy = ' order by quarter(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$select = ' , quarter(gdk.Ngay) as Quy, year(gdk.Ngay) as Nam, CONCAT_WS(\'.\',quarter(gdk.Ngay),year(gdk.Ngay)) as Khoa  ';
				break;
			case 'Y':
				$groupBy = ' group by year(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$orderBy = ' order by year(gdk.Ngay), gdk.Ref_MaSanPham ' . $subGroupBy;
				$select = ' , year(gdk.Ngay) as Nam, year(gdk.Ngay) as Khoa ';
				break;
		}

		$sql = sprintf('select
						case when IFNULL(gdk.NhapXuat,0) = 1 
						then ifnull(gdk.%5$s, 0) + gdk.SoLuong 
						else ifnull(gdk.%5$s, 0) - gdk.SoLuong 
						end as CuoiNgay
						%1$s 
						from OGiaoDichKho as gdk
						where IOID in (
							select max(IOID) as IOID from OGiaoDichKho as gdk
							%2$s %3$s %4$s
						)', $select, $where, $groupBy, $orderBy, $fieldQty);
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);

	}


	// Dung cho tao ke hoach nhan chuyen tu dong
	public function getIncomingOutgoing($select, $object, $joinObject, $module, $ifid, $hasPlan = true, $hasReferenceObject = '')
	{
		/* u
		 * 0 - Plan item code * 1 - plan Item attribute * 2 = plan Item qty * 3 - plan Item date
		 * 4 - Item price* 5 - Item code *  6 - Item attribute 7 - warehouse 8 - docNo
		 * 9 - Item uom
		 */

		// Convert de su dung ca kieu so lan kieu chu lam key
		$select[0] = isset($select[0]) ? $select[0] : @(string) $select['PlanItemCode'];
		$select[1] = isset($select[1]) ? $select[1] : @(string) $select['PlanItemAttribute'];
		$select[2] = isset($select[2]) ? $select[2] : @(string) $select['PlanItemQty'];
		$select[3] = isset($select[3]) ? $select[3] : @(string) $select['PlanItemDate'];
		$select[4] = isset($select[4]) ? $select[4] : @(string) $select['ItemPrice'];
		$select[5] = isset($select[5]) ? $select[5] : @(string) $select['ItemCode'];
		$select[6] = isset($select[6]) ? $select[6] : @(string) $select['ItemAttribute'];
		$select[7] = isset($select[7]) ? $select[7] : @(string) $select['Warehouse'];
		$select[8] = isset($select[8]) ? $select[8] : @(string) $select['DocNo'];
		$select[9] = isset($select[9]) ? $select[9] : @(string) $select['ItemUOM'];

		if ($hasPlan)
		{
			$sql = sprintf('select
                                        kh.IOID as IOID,
                                        kh.%1$s as ItemCode,
                                        kh.%2$s as ItemAttribute,
                                        kh.%3$s as ItemQty,
                                        kh.%4$s as ItemDate,
                                        kh.%14$s as ItemUOM,
                                        ds.%5$s as ItemPrice,
                                        ro.%13$s as DocNo
                                        from %6$s as ds 
                                        inner join %7$s as kh on kh.IFID_%8$s = ds.IFID_%8$s
                                        and ds.Ref_%9$s = kh.Ref_%1$s
                                        and ifnull(ds.Ref_%10$s,0) = ifnull(kh.Ref_%2$s,0)
                                        inner join %12$s as ro on kh.IFID_%8$s = ro.IFID_%8$s
                                        where ds.IFID_%8$s = %11$d'
				, $select[0], $select[1], $select[2], $select[3], $select[4], $object, $joinObject, $module, $select[5], $select[6], $ifid, $hasReferenceObject, $select[8], $select[9]
			);
		} else
		{

			// xu ly ngoai le

			$selectPlus = '';
			$joinCondition = '';
			if ($object == 'ODanhSachCK')
			{
				$selectPlus = ', ds.Ref_KhoLH as RefFromWarehouse, ds.Ref_KhoCD as RefToWarehouse';
			}

			$unitPrice = (isset($select[4]) && $select[4]) ? sprintf('ds.%1$s', $select[4]) : 'null';
			$warehouse = (isset($select[7]) && $select[7]) ? sprintf('ds.%1$s', $select[7]) : 'null';
			$refWarehouse = (isset($select[7]) && $select[7]) ? sprintf('ds.Ref_%1$s', $select[7]) : '0';
			$sql = sprintf('select 
                                                        ds.IOID as IOID,
                                                        ds.%1$s as ItemCode,
                                                        ds.Ref_%1$s as RefItem,
                                                        ds.%2$s as ItemAttribute,
                                                        ds.%14$s as ItemUOM,
                                                        ifnull(ds.Ref_%2$s,0) as RefAttribute,
                                                        ds.%3$s as ItemQty,
                                                        dh.%4$s as ItemDate,
                                                        %9$s as ItemPrice,
                                                        %10$s as ItemWarehouse,
                                                        %12$s as RefWarehouse,
                                                        dh.%11$s as DocNo
                                                        %13$s
                                                        from %5$s as ds
                                                        inner join %6$s as dh on ds.IFID_%7$s = dh.IFID_%7$s
                                                        where ds.IFID_%7$s = %8$d
                                                        ', $select[0], $select[1], $select[2], $select[3], $object, $joinObject, $module, $ifid, $unitPrice, $warehouse
				, $select[8], $refWarehouse, $selectPlus, $select[9]);

			// @todo: Xu ly tam thoi can viet lai ham nay  getInsertDataToWarehouseStack();
			if ($module == 'M710') // Lenh san xuat
			{
				$sql = sprintf('SELECT 
                                                        nvl.IOID as IOID,
                                                        nvl.MaThanhPhan as ItemCode,
                                                        nvl.Ref_MaThanhPhan as RefItem,
                                                        nvl.ThuocTinh  as ItemAttribute,
                                                        nvl.DonViTinh as ItemUOM,
                                                        ifnull(nvl.Ref_ThuocTinh,0) as RefAttribute,
                                                        nvl.SoLuong as ItemQty,
                                                        sx.DenNgay as ItemDate,
                                                        null as ItemPrice,
                                                        null as ItemWarehouse,
                                                        0 as RefWarehouse,
                                                        sx.MaLenhSX as DocNo
                                                     FROM OSanXuat AS sx
                                                     INNER JOIN OCauThanhSanPham AS ctsp on sx.Ref_ThietKe = ctsp.IOID
                                                     INNER JOIN OThanhPhanSanPham AS nvl on ctsp.IFID_M114 = nvl.IFID_M114
                                                     WHERE sx.IFID_M710 = %1$d
                                                ', $ifid);
			}
		}
		return $this->_o_DB->fetchAll($sql);

	}

	// Dung cho tao don nhan chuyen tu hang doi, dem so ban ghi doi nhap hoac doi xuat
	public function countComingShipment($comingObject, $partner, $warehouse, $fromDate, $toDate, $filterDateField = 'Ngay')
	{
		// Loc theo nha cung cap
		$where = '';
		$where .= is_numeric($partner) ? sprintf(' ifnull(Ref_MaDoiTac,0) = %1$d', $partner) : '';
		// Them dieu kien kho
		$where .= is_numeric($warehouse) ? sprintf(' and ifnull(Ref_Kho,0) = %1$d ', $warehouse) : '';
		// Them dieu kien ngay bat dau ket thuc, gioi han trong 30 ngay
		if ($fromDate && $toDate)
		{
			$toDate = Qss_Lib_Extra::getEndDate($fromDate, $toDate, 'D', array('D' => 30));
			$where .= $where ? ' and ' : '';
			$where .= sprintf(' ( %3$s between %1$s and %2$s ) ', $this->_o_DB->quote($fromDate)
				, $this->_o_DB->quote($toDate), $filterDateField);
		}
		$where = $where ? " where {$where}" : '';

		$sql = sprintf(' Select count(1) as SoLuong 
						 from %1$s
						 %2$s ', $comingObject, $where);
		$dataSql = $this->_o_DB->fetchOne($sql);
		return $dataSql ? $dataSql->SoLuong : 0;

	}

	/*
	 * $comingObject, $transactionObject, $partner, $warehouse, 
	  $fromDate, $toDate, $page, $perpage, $filterDateField = 'Ngay'

	 */

	/*
	 * @var: $objectArr[ alias=>_alias, object=>_object, module=>_module_code]
	 * @var: $filterCodeArr[ filed=>_code_number {0,n}]
	 * @var: $filterTextArr[ field=>_text {0,n}]
	 * @var: $filterDateArr[field=>_field, form=>_date, to=>_date]
	 * @var: $filterStepArr[_number {0,n}]
	 */
	/* Remove if can */

	public function replace_getComingShipments($objectArr, $filterCodeArr, $filterTextArr, $filterDateArr, $filterStepArr, $removeIfLink, $page = '', $perpage = '')
	{
		$where = ''; // filter condition
		$tmp = ''; // temp var
		$alias = isset($objectArr['alias']) ? $objectArr['alias'] . '.' : '';

		// ***
		// filter by field with code value
		foreach ($filterCodeArr as $field => $code)
		{
			$tmp .= is_numeric($code) ? sprintf(' and ifnull(%1$s%2$s,0) = %3$d ', $alias, $field, $code) : '';
		}
		$where .= $tmp; // match "where" with temp var
		$tmp = '';   // reset temp var to empty
		// ***
		// filter by field with text value
		foreach ($filterTextArr as $field => $text)
		{
			$tmp .= $text ? sprintf(' and %1$s%2$s = %3$d ', $alias, $field, $text) : '';
		}
		$where .= $tmp; // match "where" with temp var
		$tmp = '';   // reset temp var to empty
		// ***
		// filter by date
		$fromDate = (isset($filterDateArr['from']) && $filterDateArr['from']) ? $filterDateArr['from'] : '';
		$toDate = (isset($filterDateArr['to']) && $filterDateArr['to']) ? $filterDateArr['to'] : '';


		if ($fromDate && $toDate)
		{
			// between two date 
			$tmp .= sprintf(' ( %1$s%2$s between %3$s and %4$s )', $alias, $filterDateArr['field'], $this->_o_DB->quote($fromDate)
				, $this->_o_DB->quote($toDate));
		} else if ($fromDate)
		{
			// greater than or equal "form date"
			$tmp .= sprintf(' ( %1$s%2$s >= %3$s )', $alias, $filterDateArr['field'], $this->_o_DB->quote($fromDate));
		} else if ($toDate)
		{
			// less than or equal "to date"
			$tmp .= sprintf(' ( %1$s%2$s <= %3$s )', $alias, $filterDateArr['field'], $this->_o_DB->quote($toDate));
		}
		$where .= $tmp ? sprintf(' and %1$s', $tmp) : '';
		$tmp = '';   // reset temp var to empty
		// ***
		// filter by step
		$where .= sprintf(' and steps.Status in (%1$s) ', implode(',', $filterStepArr));
		// @Note : steps => qsiforms table
		// ***
		// Remove if has ToIOID 
		if ($removeIfLink)
		{
			$where .= $where ? ' and ifnull(linkWithInsertObj.ToIOID,0) = 0 ' : '';
		}

		$where = $where ? sprintf(' %1$s', $where) : '';

		if ($page)
		{
			// search 
			$startPosition = ($page - 1) * $perpage;
			$sql = sprintf(' select %1$s.*, ifnull(link.FromIOID, 0) as OrderIOID, 
							 ifnull(linkWithInsertObj.ToIOID,0) as TranIOID 
							 from %2$s as %1$s
							 inner join qsiforms as steps on steps.IFID = %1$s.IFID_%7$s
							 left join qsioidlink as link on %1$s.IOID = link.ToIOID AND %1$s.IFID_%7$s = link.ToIFID
							 left join qsioidlink as linkWithInsertObj on %1$s.IOID = linkWithInsertObj.FromIOID AND %1$s.IFID_%7$s = linkWithInsertObj.FromIFID
							 where 1=1 %3$s
							 group by %1$s.IOID 
							 order by %1$s.%4$s ASC 
							 limit %5$d, %6$d'
				, $objectArr['alias'], $objectArr['object'], $where, $filterDateArr['field'], $startPosition, $perpage
				, $objectArr['module']);
			//echo $sql; die;
			// @Note: group by phai xem lai
			return $this->_o_DB->fetchAll($sql);
		} else
		{
			// count
			$sql = sprintf('
							select count(1) as Qty
							from( 
							select yc.IOID as Qty
							 from %2$s as %1$s
							 inner join qsiforms as steps on steps.IFID = %1$s.IFID_%4$s
							 left join qsioidlink as link on %1$s.IOID = link.ToIOID AND %1$s.IFID_%4$s = link.ToIFID
							 left join qsioidlink as linkWithInsertObj on %1$s.IOID = linkWithInsertObj.FromIOID AND %1$s.IFID_%4$s = linkWithInsertObj.FromIFID
							 where 1=1 %3$s
							  group by %1$s.IOID 
							  ) as ycTable', $objectArr['alias'], $objectArr['object'], $where, $objectArr['module']);
			$dataSql = $this->_o_DB->fetchOne($sql);
			return $dataSql ? $dataSql->Qty : 0;
		}

		/*
		  $where = '';

		  // Loc theo doi tac
		  $where .= is_numeric($partner)?sprintf(' ifnull(hd.Ref_MaDoiTac,0) = %1$d', $partner):'';
		  // Them dieu kien kho
		  $where .= is_numeric($warehouse)?sprintf(' and ifnull(hd.Ref_Kho,0) = %1$d ', $warehouse):'';
		  // Them dieu kien ngay bat dau ket thuc, gioi han trong 30 ngay
		  if($fromDate && $toDate)
		  {
		  $toDate = Qss_Lib_Extra::getEndDate($fromDate, $toDate, 'D', array('D'=>30));
		  $where  .= sprintf(' and hd.%3$s between %1$s and %2$s ', $this->_o_DB->quote($fromDate)
		  , $this->_o_DB->quote($toDate), $filterDateField);
		  }

		  $where = $where?" where {$where}":'';
		  // Tinh ban ghi bat dau
		  $start     = ($page - 1) * $perpage;

		  $sql = sprintf('select hd.*, link.FromIOID as OrderIOID, link2.ToIOID as TranIOID
		  from %1$s as hd
		  left join qsioidlink as link on hd.IOID = link.ToIOID
		  left join qsioidlink as link2 on hd.IOID = link2.FromIOID
		  %2$s and link2.ToIOID is null
		  order by hd.Ngay ASC
		  limit %3$d, %4$d
		  ', $comingObject, $where, $start, $perpage);
		  return  $this->_o_DB->fetchAll($sql);
		 */

	}

	// Dung cho tao don nhan chuyen tu hang doi, hang doi nhap hoạc doi xuat
	public function getComingShipments($comingObject, $transactionObject, $partner, $warehouse, $fromDate, $toDate, $page, $perpage, $filterDateField = 'Ngay')
	{
		$where = '';

		// Loc theo doi tac
		$where .= is_numeric($partner) ? sprintf(' ifnull(hd.Ref_MaDoiTac,0) = %1$d', $partner) : '';
		// Them dieu kien kho
		$where .= is_numeric($warehouse) ? sprintf(' and ifnull(hd.Ref_Kho,0) = %1$d ', $warehouse) : '';
		// Them dieu kien ngay bat dau ket thuc, gioi han trong 30 ngay
		if ($fromDate && $toDate)
		{
			$toDate = Qss_Lib_Extra::getEndDate($fromDate, $toDate, 'D', array('D' => 30));
			$where .= sprintf(' and hd.%3$s between %1$s and %2$s ', $this->_o_DB->quote($fromDate)
				, $this->_o_DB->quote($toDate), $filterDateField);
		}

		$where = $where ? " where {$where}" : '';
		// Tinh ban ghi bat dau
		$start = ($page - 1) * $perpage;

		// Hang doi join voi link den don hang, join voi link 2 den nhan, chuyen hang
		$module = '';
		$ifid = '0';
		if ($comingObject == 'OHangDoiNhap')
		{
			$module = 'M610';
			$ifid = 'hd.IFID_M610';
		} elseif ($comingObject == 'OHangDoiXuat')
		{
			$module = 'M611';
			$ifid = 'hd.IFID_M611';
		}

		$sql = sprintf('select hd.*, %5$s as IFID, link.FromIOID as OrderIOID, link2.ToIOID as TranIOID
						from %1$s as hd
						left join qsioidlink as link on hd.IOID = link.ToIOID AND %5$s = link.ToIFID
						left join qsioidlink as link2 on hd.IOID = link2.FromIOID AND %5$s = link2.FromIFID
						%2$s and link2.ToIOID is null
						order by hd.Ngay ASC
						limit %3$d, %4$d
						', $comingObject, $where, $start, $perpage, $ifid);
		return $this->_o_DB->fetchAll($sql);

	}

	public function getOrderToComingInfo($module, $orderIOID)
	{
		switch ($module)
		{
			case 'M401':
				$fromObject = 'OKeHoachNhanHang';
				$joinObject = 'ODonMuaHang';
				$documentNoField = 'SoDonHang';
				$compareField = 'IOID';
				break;

			case 'M403':
				$fromObject = 'ODanhSachTraHang';
				$joinObject = 'OTraHang';
				$documentNoField = 'SoChungTu';
				$compareField = 'IOID';
				break;

			case 'M505':
				$fromObject = 'OKeHoachGiaoHang';
				$joinObject = 'ODonBanHang';
				$documentNoField = 'SoDonHang';
				$compareField = 'IOID';
				break;

			case 'M507':
				$fromObject = 'ODanhSachHangTL';
				$joinObject = 'ONhanHangTL';
				$documentNoField = 'SoChungTu';
				$compareField = 'IOID';
				break;

			case 'M603':
				$fromObject = 'ODanhSachCK';
				$joinObject = 'OChuyenKho';
				$documentNoField = 'SoChungTu';
				$compareField = 'IOID';
				break;

			case 'M605':
				$fromObject = 'ODanhSachXuatKho';
				$joinObject = 'OXuatKhoBaoTri';
				$documentNoField = 'SoPhieu';
				$compareField = 'IOID';
				break;

			case 'M606':
				$fromObject = 'ODanhSachNhapKho';
				$joinObject = 'ONhapKhoBaoTri';
				$documentNoField = 'SoPhieu';
				$compareField = 'IOID';
				break;
		}

		if ($module)
		{
			$sql = sprintf('select 
							jo.%5$s as docNo,
							jo.%6$s as compareField
							from %1$s as fo
							inner join %2$s as jo on fo.IFID_%3$s = jo.IFID_%3$s
							where fo.IOID = %4$d
							', $fromObject, $joinObject, $module, $orderIOID, $documentNoField, $compareField);

			return $this->_o_DB->fetchOne($sql);
		} else
		{
			return null;
		}

	}

	public function getInventoryStatitics($refWarehouse, $afterEndDate)
	{

        $sql = sprintf('
            select 
                k.ThuocTinh as Attribute
                , ifnull(k.Ref_ThuocTinh, 0) as RefAttribute
                , k.Ref_Kho as RefWarehouse
                , k.SoLuongHC as WarehouseQty
                , k.Ref_MaSP as RefItem
                , k.MaSP as ItemCode
                , k.TenSP as ItemName
                , k.DonViTinh as ItemUOM
                , ttct.SoLo as Lot
                , ttct.SoSerial as Serial
                , ttct.Bin as Bin
                , ifnull(ttct.SoLuong, k.SoLuongHC) as Qty
            from OKho as k
            left join OThuocTinhChiTiet as ttct on k.IFID_M602 = ttct.IFID_M602
            where k.Ref_Kho = %1$d 
        ', $refWarehouse);
        return $this->_o_DB->fetchAll($sql);

	}

	//$stackInfo = array('module'=>'M611', 'object'=>'OHangDoiXuat', 'stockStatusObject'=>'OThuocTinhChiTiet');
	public function getStockStatusOfStack($ioidStackArr, $stackInfo, $echo = false)
	{
		if (is_array($ioidStackArr))
		{
			$ioidStackArr[] = 0;
		} else
		{
			$temp[] = (int) $ioidStackArr;
			$ioidStackArr = $temp;
		}

		// Lay hang doi join voi trang thai luu tru cua hang doi
		// 
		$sql = sprintf('select ttlt.*, ttlt.IFID_%3$s as IFID from %1$s as hd
						inner join %2$s as ttlt on hd.IFID_%3$s = ttlt.IFID_%3$s
						where hd.IOID in (%4$s)', $stackInfo['object'], $stackInfo['stockStatusObject']
			, $stackInfo['module'], implode(',', $ioidStackArr));
		if ($echo)
		{
			echo $sql;
			die;
		}
		return $this->_o_DB->fetchAll($sql);

	}

	// @Function: getInventoryForItemAndAttribute(), tra ve ton kho cua thanh phan yeu cau
	// @Parameter: 
	//		- $itemAttrArr: Cac thanh phan yeu cau, array(RefItem=>,RefAttribute=>)
	// @Return: so luong ton kho cua cac thanh phan yeu cau
	public function getInventoryForItemAndAttribute($itemAttrArr)
	{
		$tempSql = '';
		foreach ($itemAttrArr as $item)
		{
			$tempSql .= $tempSql ? ' or ' : '';
			$tempSql .= sprintf('  (Ref_MaSP = %1$d and ifnull(Ref_ThuocTinh, 0) = %2$d)  ', $item['RefItem'], $item['RefAttribute']);
		}
		$tempSql = $tempSql ? sprintf(' (%1$s) ', $tempSql) : '';

		$sql = sprintf('select
						concat(Ref_MaSP, \'_\', ifnull(Ref_ThuocTinh, 0))  as ItemKey
						, Ref_MaSP as RefItem
						, MaSP as ItemCode
						, TenSP as ItemName
						, sum(ifnull(SoLuongHC, 0)) as ItemQty
						, ifnull(Ref_ThuocTinh, 0) as RefAttribute
						, ThuocTinh as Attribute
						from OKho
						where %1$s 
						group by Ref_MaSP, Ref_ThuocTinh', $tempSql);
		return $this->_o_DB->fetchAll($sql);

	}

	// End getInventoryForItemAndAttribute()
	// @Function: getInfoByBarocde(), lay thong tin barcode
	// @Parameter: 
	// 		- $filter, array('Barcode'=>code)
	// @Return: Thong tin theo barcode
	public function getInfoByBarocde($filter)
	{
		$sql = sprintf('select sp.*, dvt.*, sp.IFID_M113, sp.GiaMua as GiaVon
                                from OSanPham as sp
                                inner join ODonViTinhSP as dvt on sp.IFID_M113 = dvt.IFID_M113
                                where sp.Barcode = %1$s
                                order by sp.IFID_M113', $this->_o_DB->quote($filter['Barcode']));
		return $this->_o_DB->fetchAll($sql);
		;

	}

	// End getInfoByBarocde()


	public function getStockStatusSortedForUpdateWarehouse($formConfig)
	{
//            $alias = array();
//            // default, if has exception use switch statement
//            switch ($formConfig['module'])
//            {
//                default :        
//                    $alias['RefWarehouse']  = 'Ref_MaSanPham';
//                    $alias['wRefWarehouse'] = 'Ref_MaSanPham';
//                    $alias['RefWarehouse']  = 'MaSanPham';
//                    $alias['wRefWarehouse'] = 'MaSanPham';
//                    $alias['RefWarehouse']  = 'TenSanPham';
//                    $alias['wRefWarehouse'] = 'TenSanPham';
//                    $alias['RefWarehouse']  = 'SoLo';
//                    $alias['wRefWarehouse'] = 'SoLo';
//                    $alias['RefWarehouse']  = 'SoSerial';
//                    $alias['wRefWarehouse'] = 'SoSerial';
//                    $alias['RefWarehouse']  = 'MaThuocTinh';
//                    $alias['wRefWarehouse'] = 'MaThuocTinh';
//                    $alias['RefWarehouse']  = 'DonViTinh';
//                    $alias['wRefWarehouse'] = 'DonViTinh';
//                    $alias['RefWarehouse']  = 'SoLuong';
//                    $alias['wRefWarehouse'] = 'SoLuong';
//                    $alias['RefWarehouse']  = 'Kho';
//                    $alias['wRefWarehouse'] = 'Kho';
//                    $alias['RefWarehouse']  = 'Bin';
//                    $alias['wRefWarehouse'] = 'Bin';
//                    $alias['RefWarehouse']  = 'NgayNhan';
//                    $alias['wRefWarehouse'] = 'NgayNhan';
//                    $alias['RefWarehouse']  = 'NgaySX';
//                    $alias['wRefWarehouse'] = 'NgaySX';
//                    $alias['RefWarehouse']  = 'NgayHan';
//                    $alias['wRefWarehouse'] = 'NgayHan';                    
//                break;
//            }

		$sql = sprintf('select ss1.*, ifnull(ss2.SoLuong,0) as Inventory 
                            from %1$s as ss1
                            left join %2$s as ss2 on 
                                ss2.IFID_M602 is not null
                                and ss1.Ref_MaSanPham = ss2.Ref_MaSanPham
                                and ifnull(ss1.SoLo,\'\') = ifnull(ss2.SoLo,\'\')
                                and ifnull(ss1.SoSerial,\'\') = ifnull(ss2.SoSerial,\'\')
                                and ifnull(ss1.Ref_MaThuocTinh,0) = ifnull(ss2.Ref_MaThuocTinh,0)
                                and ss1.Ref_DonViTinh = ss2.Ref_DonViTinh
                                and ss1.Ref_Kho = ss2.Ref_Kho
                                and ifnull(ss1.Bin,\'\') = ifnull(ss2.Bin,\'\')
                             where ss1.IFID_%3$s = %4$d
                            
                            ', $formConfig['stockObj'], $formConfig['wStockObj']
			, $formConfig['module'], $formConfig['ifid']);
		return $this->_o_DB->fetchAll($sql);

	}

	public function getTransactionForUpdateWarehouse($formConfig)
	{
//            $alias = array();
//            switch($formConfig['module'])
//            {
//                case 'M402':
//                    $alias['UOM'] = 'DonViTinh';
//                break;
//            }
		$sql = sprintf(' select  
                                itemObj.*,
                                (ifnull(uomObj.HeSoQuyDoi,0) * ifnull(itemObj.SoLuong,0)) as ItemQty
                                /*
                                case when ifnull(attrObj.SoLuong,0) = 0
                                    then
                                        uomObj.HeSoQuyDoi * itemObj.SoLuong
                                    else
                                        attrObj.SoLuong * itemObj.SoLuong
                                    end
                                 as ItemQty
                                 */
                             from %1$s as itemObj
                             left join ODonViTinhSP as uomObj
                                on itemObj.Ref_DonViTinh = uomObj.IOID
                             /*left join OBangThuocTinh as attrObj
                                on attrObj.IOID = itemObj.Ref_ThuocTinh*/
                             where IFID_%2$s = %3$d
                            '
			, $formConfig['itemObj']
			, $formConfig['module']
			, $formConfig['ifid']);
		return $this->_o_DB->fetchAll($sql);

	}
    

	/* Current qty of item (with specific attributes) */

	public function getCurrentInventoryStatus($refItem, $refWarehouse, $refAttribute)
	{
		$refAttribute = @(int) $refAttribute;
		//$attribute =  ($refAttribute)?sprintf(' and Ref_ThuocTinh = %1$d', $refAttribute):sprintf(' and (Ref_ThuocTinh is null or Ref_ThuocTinh = 0) ');
		$attribute = sprintf(' and ifnull(k.Ref_ThuocTinh,0) = %1$d', $refAttribute);


		$sql = sprintf('select 
                                sum(
                                    case when k.Ref_MaSP = %1$d 
                                        then ifnull(k.SoLuongHC,0) *  
                                            case when ifnull(attrObj.SoLuong,0) = 0
                                                then
                                                    (uomObj.HeSoQuyDoi )
                                                else
                                                    (attrObj.SoLuong )
                                                end
                                            
                                        else 0 end ) 
                                    as TongHC,
                                sum(
                                    case when k.Ref_MaSP = %1$d %2$s 
                                        then ifnull(k.SoLuongHC,0) *  
                                            case when ifnull(attrObj.SoLuong,0) = 0
                                                then
                                                    (uomObj.HeSoQuyDoi )
                                                else
                                                    (attrObj.SoLuong )
                                                end
                                            
                                        else 0 end ) 
                                    as TongThuocTinhHC,
                                sum(
                                    case when k.Ref_MaSP = %1$d and k.Ref_Kho = %3$d 
                                        then ifnull(k.SoLuongHC,0) *  
                                            case when ifnull(attrObj.SoLuong,0) = 0
                                                then
                                                    (uomObj.HeSoQuyDoi )
                                                else
                                                    (attrObj.SoLuong )
                                                end
                                            
                                        else 0 end) 
                                    as KhoHienTaiHC,
                                sum(
                                    case when k.Ref_MaSP = %1$d %2$s and k.Ref_Kho = %3$d 
                                        then ifnull(k.SoLuongHC,0) *  
                                            case when ifnull(attrObj.SoLuong,0) = 0
                                                then
                                                    (uomObj.HeSoQuyDoi )
                                                else
                                                    (attrObj.SoLuong )
                                                end
                                            
                                        else 0 end) 
                                    as HienCo
                            from OKho as k
                            
                            left join ODonViTinhSP as uomObj
                                on uomObj.IOID = k.Ref_DonViTinh
                            left join OBangThuocTinh as attrObj
                                on attrObj.IOID = k.Ref_ThuocTinh
                            where k.Ref_MaSP = %1$d
                            group by k.Ref_MaSP', $refItem, $attribute, $refWarehouse);
		return $this->_o_DB->fetchOne($sql);

	}

	/**
	 * Ham lay ton kho hien tai 
	 * @path: http://pm/report/inventory/bin
	 */
	public function getCurrentInventoryForBinReport($warehouse_ifid = 0, $bin_ioid = 0, $item_ioid = 0)
	{
		// Lay dieu kien
		$whereArr = array();
		$where = '';

		// kho
		if ($warehouse_ifid)
		{
			$whereArr[] = sprintf(' dskho.IFID_M601 = %1$d ', $warehouse_ifid);
		}

		// bin
		if ($bin_ioid)
		{
			$whereArr[] = sprintf(' luutru.Ref_Bin = %1$d ', $bin_ioid);
		}

		// mat hang
		if ($item_ioid)
		{
			$whereArr[] = sprintf(' kho.Ref_MaSP = %1$d ', $item_ioid);
		}

		// match where
		if (count($whereArr))
		{
			$where = sprintf(' where %1$s ', implode(' and ', $whereArr));
		}


		$sql = sprintf('
						SELECT
						kho.`IFID_M602` AS WIFID,
						dskho.`IOID` AS WIOID,
						dskho.`MaKho` AS WCode,
						dskho.`TenKho` AS WName,
						ifnull(bin.`IOID`, 0) AS BIOID,
						ifnull(bin.`MaBin`, \'\') AS BCode,
						ifnull(bin.`TenBin`, \'\') AS BName,
						sanpham.`MaSanPham` AS ICode,
						sanpham.`TenSanPham` AS IName,
						donvi.`DonViTinh` AS UOM,
						CASE WHEN luutru.`SoLuong` is null THEN
							ifnull(
								(
									kho.`SoLuongHC` * 
									ifnull(dvtsanpham.`HeSoQuyDoi`, ifnull(dvtsanpham0.HeSoQuyDoi, 0) ) 
								)
							, 0)
						ELSE
							luutru.`SoLuong` * 
							ifnull( 
								ifnull(dvtsanpham.`HeSoQuyDoi`
								, ifnull(dvtsanpham0.HeSoQuyDoi, 0)  ) 
							, 0) 
						END AS Qty
						FROM OKho AS kho
						LEFT JOIN OSanPham AS sanpham ON kho.`Ref_MaSP` = sanpham.`IOID`
						LEFT JOIN ODonViTinh AS donvi ON donvi.`IOID` = sanpham.`Ref_DonViTinh`
						LEFT JOIN ODonViTinhSP AS dvtsanpham0 
							ON sanpham.`IFID_M113` = dvtsanpham0.`IFID_M113` 
							AND kho.Ref_DonViTinh = dvtsanpham0.IOID 
						LEFT JOIN ODanhSachKho AS dskho ON dskho.`IOID` = kho.`Ref_Kho`
						LEFT JOIN OThuocTinhChiTiet AS luutru ON kho.`IFID_M602` = luutru.IFID_M602
						LEFT JOIN ODanhSachKho AS dskho2 ON dskho2.`IOID` = luutru.`Ref_Kho`
						LEFT JOIN OBin AS bin On dskho2.`IFID_M601` = bin.`IFID_M601` 
							AND luutru.`Ref_Bin` = bin.`IOID`
						LEFT JOIN OSanPham AS sanpham2 ON sanpham2.`IOID` = luutru.`Ref_MaSanPham`
						LEFT JOIN ODonViTinhSP AS dvtsanpham 
							ON sanpham2.`IFID_M113` = dvtsanpham.`IFID_M113`
							AND luutru.`Ref_DonViTinh` = dvtsanpham.`IOID`
						%1$s
						ORDER BY kho.`IFID_M602`
				', $where);
		return $this->_o_DB->fetchAll($sql);

	}

	public function getInputForInventoryCardReport($start, $end, $warehouseIOID, $itemIOID)
	{
		$warehouseFilter = $warehouseIOID ? "and nh.`Ref_Kho` = {$warehouseIOID}" : '';
		$sql = sprintf('select nh.`SoChungTu`, nh.`NgayChungTu`
								, nh.`NgayChuyenHang`, (ds.`SoLuong` * ifnull(dvtsp.`HeSoQuyDoi`,0)) as SoLuong
							from ONhapKho as nh
							INNER JOIN ODanhSachNhapKho as ds on ds.`IFID_M402` = nh.`IFID_M402`
							INNER JOIN OSanPham as sp on ds.`Ref_MaSanPham` = sp.`IOID`
							LEFT JOIN ODonViTinhSP as dvtsp on sp.`IFID_M113` = dvtsp.`IFID_M113` 
								and ds.`Ref_DonViTinh` = dvtsp.`IOID`
							where 
							(nh.`NgayChuyenHang` between %1$s and %2$s)
							and ds.`Ref_MaSanPham` = %4$s
							%3$s
							group by nh.`IFID_M402`
							order by nh.`NgayChuyenHang`', $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end), $warehouseFilter, $itemIOID);
		return $this->_o_DB->fetchAll($sql);

	}

	public function getOutputForInventoryCardReport($start, $end, $warehouseIOID, $itemIOID)
	{
		$warehouseFilter = $warehouseIOID ? "and nh.`Ref_Kho` = {$warehouseIOID}" : '';
		$sql = sprintf('select nh.`SoChungTu`, nh.`NgayChungTu`, nh.`NgayChuyenHang`
								, (ds.`SoLuong` * ifnull(dvtsp.`HeSoQuyDoi`,0)) as SoLuong
							from OXuatKho as nh
							INNER JOIN ODanhSachXuatKho as ds on ds.`IFID_M506` = nh.`IFID_M506`
							INNER JOIN OSanPham as sp on ds.`Ref_MaSP` = sp.`IOID`
							LEFT JOIN ODonViTinhSP as dvtsp on sp.`IFID_M113` = dvtsp.`IFID_M113` 
								and ds.`Ref_DonViTinh` = dvtsp.`IOID`
							where 
							(nh.`NgayChuyenHang` between %1$s and %2$s)
							and ds.`Ref_MaSP` = %4$s
							%3$s
							group by nh.`IFID_M506`
							order by nh.`NgayChuyenHang`', $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end), $warehouseFilter, $itemIOID);
		return $this->_o_DB->fetchAll($sql);

	}
	
//	/**
//	 * @path: /report/inventory/recognize
//	 * @todo: Can loc theo nhung ban ghi da ket thuc (step cuoi)
//	 *
//	 */
//	public function getInputForRecognizeReport($start, $end, $loc, $EqGroup, $EqType, $EqID)
//	{
//		$common = new Qss_Model_Extra_Extra();
//		$where = '';
//
//		// Loc theo khu vuc
//		if($loc)
//		{
//			$locConf = $common->getDataset(array('module'=>'OKhuVuc'
//												, 'where'=>array('IOID'=>$loc)
//												, 'return'=>1));
//
//			if($locConf)
//			{
//				$where .= sprintf('
//					AND dstb.`Ref_MaKhuVuc` in (
//					SELECT IOID FROM OKhuVuc WHERE lft >= %1$d and rgt <= %2$d)'
//				, $locConf->lft, $locConf->rgt);
//			}
//		}
//
//
//		// Loc theo nhom thiet bi
//		if ($EqGroup)
//		{
//			$where .= sprintf(' AND dstb.Ref_NhomThietBi = %1$d ', $EqGroup);
//		}
//
//		// Loc theo loai thiet bi
//		if ($EqType)
//		{
//			$where .= sprintf(' AND dstb.Ref_LoaiThietBi = %1$d ', $EqType);
//		}
//
//		// Loc theo thiet bi
//		if ($EqID)
//		{
//			$where .= sprintf(' AND dstb.IOID = %1$d ', $EqID);
//		}
//
//		$sql = sprintf('
//			/* Gop so luong theo thiet bi, bo phan va mat hang (Thuoc tinh neu co)*/
//			/* Tinh so luong theo don vi tinh chinh*/
//			SELECT
//			dstb.`IOID` AS EID
//			, dstb.`MaThietBi` AS ECode
//			, ltb.`TenLoai` AS EType
//			, cttb.`IOID` AS ComponentID
//			, cttb.`BoPhan` AS Component
//			, cttb.`ViTri` AS Position
//			, sp.`IOID` AS IID
//			, sp.`MaSanPham` AS ICode
//			, sp.`TenSanPham` AS IName
//			, dvt.`DonViTinh` AS UOM /*Don Vi Tinh Chinh*/
//			, sum( ifnull(dsnk.`SoLuong`, 0) * ifnull(dvtsp.`HeSoQuyDoi`,0) ) AS Qty
//			/*, sum( ifnull(dsnk.`SoLuongMat`, 0) * ifnull(dvtsp.`HeSoQuyDoi`,0) ) AS `Lost`*/
//			, ifnull(dvtsp.`HeSoQuyDoi`,0) AS `Rate`
//			FROM ONhapKho AS nk
//			INNER JOIN ODanhSachNhapKho AS dsnk ON nk.`IFID_M402` = dsnk.`IFID_M402`
//			/* Lay thong tin san pham*/
//			INNER JOIN OSanPham AS sp  ON dsnk.`Ref_MaSanPham` = sp.`IOID`
//			INNER JOIN ODonViTinh AS dvt On dvt.`IOID` = sp.`Ref_DonViTinh`
//			INNER JOIN ODonViTinhSP AS dvtsp On dvtsp.`IOID` = dsnk.`Ref_DonViTinh`
//			/* Lay thong tin thiet bi*/
//			INNER JOIN ODanhSachThietBi AS dstb ON dstb.`IOID` = dsnk.`Ref_MaThietBi`
//			INNER JOIN OLoaiThietBi AS ltb ON dstb.`Ref_LoaiThietBi` = ltb.`IOID`
//			INNER JOIN OCauTrucThietBi AS cttb ON cttb.`IFID_M705` = dstb.`IFID_M705`
//				AND cttb.`IOID` = dsnk.`Ref_BoPhan`
//			WHERE (nk.`NgayChuyenHang` BETWEEN %1$s AND %2$s)
//				%3$s
//			GROUP BY dsnk.`Ref_MaThietBi`, dsnk.`Ref_BoPhan`, dsnk.`Ref_MaSanPham` /*, dsnk.`Ref_ThuocTinh`*/
//			ORDER BY dsnk.`Ref_MaThietBi`, dsnk.`Ref_BoPhan`, dsnk.`Ref_MaSanPham` /*, dsnk.`Ref_ThuocTinh`*/
//		'
//		, $this->_o_DB->quote($start)
//		, $this->_o_DB->quote($end)
//		, $where);
//		//echo $sql; die;
//		return $this->_o_DB->fetchAll($sql);
//	}
	
//	/**
//	 * @path: /report/inventory/recognize
//	 * @todo: Can loc theo nhung ban ghi da ket thuc (step cuoi)
//	 */
//	public function getOutputForRecognizeReport($start, $end, $loc, $EqGroup, $EqType, $EqID)
//	{
//		$common = new Qss_Model_Extra_Extra();
//		$where = '';
//
//		// Loc theo khu vuc
//		if($loc)
//		{
//			$locConf = $common->getDataset(array('module'=>'OKhuVuc'
//												, 'where'=>array('IOID'=>$loc)
//												, 'return'=>1));
//
//			if($locConf)
//			{
//				$where .= sprintf('
//					AND dstb.`Ref_MaKhuVuc` in (
//					SELECT IOID FROM OKhuVuc WHERE lft >= %1$d and rgt <= %2$d)'
//				, $locConf->lft, $locConf->rgt);
//			}
//		}
//
//
//		// Loc theo nhom thiet bi
//		if ($EqGroup)
//		{
//			$where .= sprintf(' AND dstb.Ref_NhomThietBi = %1$d ', $EqGroup);
//		}
//
//		// Loc theo loai thiet bi
//		if ($EqType)
//		{
//			$where .= sprintf(' AND dstb.Ref_LoaiThietBi = %1$d ', $EqType);
//		}
//
//		// Loc theo thiet bi
//		if ($EqID)
//		{
//			$where .= sprintf(' AND dstb.IOID = %1$d ', $EqID);
//		}
//
//		$sql = sprintf('
//			/* Gop so luong theo thiet bi, bo phan va mat hang (Thuoc tinh neu co)*/
//			/* Tinh so luong theo don vi tinh chinh */
//			SELECT
//			dstb.`IOID` AS EID
//			, dstb.`MaThietBi` AS ECode
//			, ltb.`TenLoai` AS EType
//			, cttb.`IOID` AS ComponentID
//			, cttb.`BoPhan` AS Component
//			, cttb.`ViTri` AS Position
//			, sp.`IOID` AS IID
//			, sp.`MaSanPham` AS ICode
//			, sp.`TenSanPham` AS IName
//			, dvt.`DonViTinh` AS UOM /*Don Vi Tinh Chinh*/
//			, sum( ifnull(dsxk.`SoLuong`, 0) * ifnull(dvtsp.`HeSoQuyDoi`,0) ) AS Qty
//			FROM OXuatKho AS xk
//			INNER JOIN ODanhSachXuatKho AS dsxk ON xk.`IFID_M506` = dsxk.`IFID_M506`
//			/* Lay thong tin san pham*/
//			INNER JOIN OSanPham AS sp  ON dsxk.`Ref_MaSP` = sp.`IOID`
//			INNER JOIN ODonViTinh AS dvt On dvt.`IOID` = sp.`Ref_DonViTinh`
//			INNER JOIN ODonViTinhSP AS dvtsp On dvtsp.`IOID` = dsxk.`Ref_DonViTinh`
//			/* Lay thong tin thiet bi*/
//			INNER JOIN ODanhSachThietBi AS dstb ON dstb.`IOID` = dsxk.`Ref_MaThietBi`
//			INNER JOIN OLoaiThietBi AS ltb ON dstb.`Ref_LoaiThietBi` = ltb.`IOID`
//			INNER JOIN OCauTrucThietBi AS cttb ON cttb.`IFID_M705` = dstb.`IFID_M705`
//				AND cttb.`IOID` = dsxk.`Ref_BoPhan`
//			WHERE (xk.`NgayChuyenHang` BETWEEN %1$s AND %2$s)
//				%3$s
//			GROUP BY dsxk.`Ref_MaThietBi`, dsxk.`Ref_BoPhan`, dsxk.`Ref_MaSP` /*, dsxk.`Ref_ThuocTinh`*/
//			ORDER BY dsxk.`Ref_MaThietBi`, dsxk.`Ref_BoPhan`, dsxk.`Ref_MaSP` /*, dsxk.`Ref_ThuocTinh`*/
//		'
//		, $this->_o_DB->quote($start)
//		, $this->_o_DB->quote($end)
//		, $where);
//		//echo $sql; die;
//		return $this->_o_DB->fetchAll($sql);
//	}
	
//	/**
//	 * @path: /report/inventory/recognize
//	 * @todo: Can loc theo nhung ban ghi da ket thuc (step cuoi)
//	 */
//	public function getMaintainOrderForRecognizeReport($start, $end, $loc, $EqGroup, $EqType, $EqID)
//	{
//		$common = new Qss_Model_Extra_Extra();
//		$where = '';
//
//		// Loc theo khu vuc
//		if($loc)
//		{
//			$locConf = $common->getDataset(array('module'=>'OKhuVuc'
//												, 'where'=>array('IOID'=>$loc)
//												, 'return'=>1));
//
//			if($locConf)
//			{
//				$where .= sprintf('
//					AND dstb.`Ref_MaKhuVuc` in (
//					SELECT IOID FROM OKhuVuc WHERE lft >= %1$d and rgt <= %2$d)'
//				, $locConf->lft, $locConf->rgt);
//			}
//		}
//
//
//		// Loc theo nhom thiet bi
//		if ($EqGroup)
//		{
//			$where .= sprintf(' AND dstb.Ref_NhomThietBi = %1$d ', $EqGroup);
//		}
//
//		// Loc theo loai thiet bi
//		if ($EqType)
//		{
//			$where .= sprintf(' AND dstb.Ref_LoaiThietBi = %1$d ', $EqType);
//		}
//
//		// Loc theo thiet bi
//		if ($EqID)
//		{
//			$where .= sprintf(' AND dstb.IOID = %1$d ', $EqID);
//		}
//
//		$sql = sprintf('
//			/* Gop so luong theo thiet bi, bo phan va mat hang (Thuoc tinh neu co)*/
//			/* Tinh so luong theo don vi tinh chinh*/
//			SELECT
//			dstb.`IOID` AS EID
//			, dstb.`MaThietBi` AS ECode
//			, ltb.`TenLoai` AS EType
//			, cttb.`IOID` AS ComponentID
//			, cttb.`BoPhan` AS Component
//			, cttb.`ViTri` AS Position
//			, sp.`IOID` AS IID
//			, sp.`MaSanPham` AS ICode
//			, sp.`TenSanPham` AS IName
//			, dvt.`DonViTinh` AS UOM /*Don Vi Tinh Chinh*/
//			, sum( ifnull(vt.`SoLuong`, 0) * ifnull(dvtsp.`HeSoQuyDoi`,0) ) AS `Use`
//			, sum( ifnull(vt.`SoLuongTraLai`, 0) * ifnull(dvtsp.`HeSoQuyDoi`,0) ) AS `Return`
//			, sum( ifnull(vt.`SoLuongMat`, 0) * ifnull(dvtsp.`HeSoQuyDoi`,0) ) AS `Lost`
//			FROM OPhieuBaoTri AS pbt
//			INNER JOIN OVatTuPBT AS vt ON pbt.`IFID_M759` = vt.`IFID_M759`
//			/* Lay thong tin phu tung vat tu*/
//			INNER JOIN OSanPham AS sp  ON vt.`Ref_MaVatTu` = sp.`IOID`
//			INNER JOIN ODonViTinh AS dvt On dvt.`IOID` = sp.`Ref_DonViTinh`
//			INNER JOIN ODonViTinhSP AS dvtsp On dvtsp.`IOID` = vt.`Ref_DonViTinh`
//			/* Lay thong tin thiet bi*/
//			INNER JOIN ODanhSachThietBi AS dstb ON dstb.`IOID` = pbt.`Ref_MaThietBi`
//			INNER JOIN OLoaiThietBi AS ltb ON dstb.`Ref_LoaiThietBi` = ltb.`IOID`
//			INNER JOIN OCauTrucThietBi AS cttb ON cttb.`IFID_M705` = dstb.`IFID_M705`
//				AND cttb.`IOID` = vt.`Ref_BoPhan`
//			WHERE (pbt.`Ngay` BETWEEN %1$s AND %2$s)
//				%3$s
//			GROUP BY pbt.`Ref_MaThietBi`, vt.`Ref_BoPhan`, vt.`Ref_MaVatTu` /*, vt.`Ref_ThuocTinh`*/
//			ORDER BY pbt.`Ref_MaThietBi`, vt.`Ref_BoPhan`, vt.`Ref_MaVatTu` /*, vt.`Ref_ThuocTinh`*/
//		'
//		, $this->_o_DB->quote($start)
//		, $this->_o_DB->quote($end)
//		, $where);
//		//echo $sql; die;
//		return $this->_o_DB->fetchAll($sql);
//	}

	
	// BEGIN REFACTOR
	public function getWarehouseStatisticsNotComplete($refWarehouse)
	{
		if(Qss_Lib_System::fieldActive('ODanhSachKho', 'TrucThuoc'))
		{
			$sql = sprintf('
			SELECT kkk.*
			FROM OKiemKeKho AS kkk
			INNER JOIN qsiforms AS ifo ON kkk.IFID_M612 = ifo.IFID
			INNER JOIN ODanhSachKho AS kho1 ON kkk.Ref_Kho = kho1.IOID
			LEFT JOIN ODanhSachKho AS kho2 ON kho1.lft <= kho2.lft AND kho1.rgt >= kho2.rgt
			WHERE kkk.Ref_Kho = %1$d OR kho2.IOID = %1$d
			AND ifnull(ifo.Status, 0) = 1
		', $refWarehouse);
		}
		else
		{
			$sql = sprintf('
			SELECT kkk.*
			FROM OKiemKeKho AS kkk
			INNER JOIN qsiforms AS ifo ON kkk.IFID_M612 = ifo.IFID
			WHERE kkk.Ref_Kho = %1$d
			AND ifnull(ifo.Status, 0) = 1
		', $refWarehouse);
		}

		return $this->_o_DB->fetchAll($sql);
	}
	public function getWarehouseByUser($userid)
	{
		$sql = sprintf('SELECT ODanhSachKho.* 
			FROM
			ODanhSachKho
			INNER JOIN ONhanVien ON ONhanVien.IFID_M601 = ODanhSachKho.IFID_M601
			INNER JOIN ODanhSachNhanVien ON ODanhSachNhanVien.IOID = ONhanVien.Ref_MaNV
			INNER JOIN qsusers ON qsusers.UID = ODanhSachNhanVien.Ref_TenTruyCap
			where qsusers.UID = %1$d', $userid);
		return $this->_o_DB->fetchAll($sql);
	}
    
    /**
     * Lấy danh sách xuất kho theo dự án
     * @param type $projectIOID
     */
    public function sumOutItemByProject($start, $end, $projectIOID)
    {
        $sql = sprintf('
            SELECT 
                SUM(ifnull(danhsachxuat.SoLuong, 0) * ifnull(donvi.HeSoQuyDoi,0)) AS SoLuongXuatKho,
                mathang.MaSanPham ,
                mathang.TenSanPham ,
                mathang.IOID AS IIOID,
                mathang.DonViTinh ,
                mathang.DacTinhKyThuat, 
                ifnull(mathang.VatTuTieuHao, 0) AS VatTuTieuHao
            FROM OXuatKho AS xuatkho
            INNER JOIN ODanhSachXuatKho AS danhsachxuat ON xuatkho.IFID_M506 = danhsachxuat.IFID_M506
            INNER JOIN OSanPham AS mathang ON danhsachxuat.Ref_MaSP = mathang.IOID
            INNER JOIN ODonViTinhSP AS donvi ON mathang.IFID_M113 = donvi.IFID_M113 AND danhsachxuat.Ref_DonViTinh = donvi.IOID
            WHERE (xuatkho.NgayChuyenHang BETWEEN %1$s AND %2$s)
            AND xuatkho.Ref_DuAn = %3$s 
            GROUP BY danhsachxuat.Ref_MaSP
            ORDER BY VatTuTieuHao'
        , $this->_o_DB->quote($start)
        , $this->_o_DB->quote($end)
        , $projectIOID);
        return $this->_o_DB->fetchAll($sql);
    }   
    
    /**
     * Lấy danh sách nhập kho theo dự án
     * @param type $start
     * @param type $end
     * @param type $projectIOID
     * @return type
     */
    public function sumInItemByProject($start, $end, $projectIOID)
    {
        $sql = sprintf('
            SELECT 
                SUM(ifnull(danhsachnhap.SoLuong, 0) * ifnull(donvi.HeSoQuyDoi,0)) AS SoLuongNhapKho,
                mathang.MaSanPham ,
                mathang.TenSanPham ,
                mathang.IOID AS IIOID,
                mathang.DonViTinh ,
                mathang.DacTinhKyThuat, 
                ifnull(mathang.VatTuTieuHao, 0) AS VatTuTieuHao
            FROM ONhapKho AS nhapkho
            INNER JOIN ODanhSachNhapKho AS danhsachnhap ON nhapkho.IFID_M402 = danhsachnhap.IFID_M402
            INNER JOIN OSanPham AS mathang ON danhsachnhap.Ref_MaSanPham = mathang.IOID
            INNER JOIN ODonViTinhSP AS donvi ON mathang.IFID_M113 = donvi.IFID_M113 AND danhsachnhap.Ref_DonViTinh = donvi.IOID
            WHERE (nhapkho.NgayChuyenHang BETWEEN %1$s AND %2$s)
            AND nhapkho.Ref_DuAn = %3$s 
            GROUP BY danhsachnhap.Ref_MaSanPham
            ORDER BY VatTuTieuHao'
        , $this->_o_DB->quote($start)
        , $this->_o_DB->quote($end)
        , $projectIOID);
        return $this->_o_DB->fetchAll($sql);
    }    
    
    /**
     * Lấy danh sách yêu cầu vật tư theo dự án
     * @param type $start
     * @param type $end
     * @param type $projectIOID
     * @return type
     */
    public function sumReqItemByProject($start, $end, $projectIOID)
    {
        $sql = sprintf('
            SELECT 
                SUM(ifnull(danhsachnhucau.SoLuong, 0) * ifnull(donvi.HeSoQuyDoi,0)) AS SoLuongYeuCau,
                mathang.MaSanPham ,
                mathang.TenSanPham ,
                mathang.IOID AS IIOID,
                mathang.DonViTinh ,
                mathang.DacTinhKyThuat, 
                ifnull(mathang.VatTuTieuHao, 0) AS VatTuTieuHao
            FROM ONhuCauVatTu AS nhucau
            INNER JOIN ODSNhuCauVatTu AS danhsachnhucau ON nhucau.IFID_M709 = danhsachnhucau.IFID_M709
            INNER JOIN OSanPham AS mathang ON danhsachnhucau.Ref_MaSP = mathang.IOID
            INNER JOIN ODonViTinhSP AS donvi ON mathang.IFID_M113 = donvi.IFID_M113 AND danhsachnhucau.Ref_DonViTinh = donvi.IOID
            WHERE (nhucau.Ngay BETWEEN %1$s AND %2$s)
            AND nhucau.Ref_DuAn = %3$s 
            GROUP BY danhsachnhucau.Ref_MaSP
            ORDER BY VatTuTieuHao'
        , $this->_o_DB->quote($start)
        , $this->_o_DB->quote($end)
        , $projectIOID);
        return $this->_o_DB->fetchAll($sql);            
    }
    
    /*==========================================================================*/
    /**
     * Xuất nhập kho
     */
    /*==========================================================================*/
    
    /**
     * Lấy giao dịch kho từ ioid link của dòng nhận hàng (nhập kho: FromIOID)
     * @param type $inputIFID
     * @return type
     */
    public function getTransactionsOfInput($inputIFID)
    {
        $sql = sprintf('
            SELECT giaodichkho.*, giaodichkho.IFID_M607 AS WIFID
            FROM ODanhSachNhapKho AS nhapkho
            INNER JOIN qsioidlink AS link ON nhapkho.IOID = link.FromIOID AND nhapkho.IFID_M402 = link.FromIFID
            INNER JOIN OGiaoDichKho AS giaodichkho ON link.ToIOID = giaodichkho.IOID AND link.ToIFID = giaodichkho.IFID_M607
            WHERE IFID_M402 = %1$d 
        ', $inputIFID);
        return $this->_o_DB->fetchAll($sql);    
    }
    
    /**
     * Lấy giao dịch kho từ ioid link của dòng xuất kho (xuất kho: FromIOID)
     * @param type $outputIFID
     * @return type
     */
    public function getTransactionsOfOutput($outputIFID)
    {
        $sql = sprintf('
            SELECT giaodichkho.*, giaodichkho.IFID_M607 AS WIFID
            FROM ODanhSachXuatKho AS xuatkho
            INNER JOIN qsioidlink AS link ON xuatkho.IOID = link.FromIOID AND xuatkho.IFID_M506 = link.FromIFID
            INNER JOIN OGiaoDichKho AS giaodichkho ON link.ToIOID = giaodichkho.IOID AND link.ToIFID = giaodichkho.IFID_M607
            WHERE IFID_M506 = %1$d 
        ', $outputIFID);
        return $this->_o_DB->fetchAll($sql);    
    }    
    
    /**
     * Lấy kho hiện tại theo dòng nhập kho 
     * @param type $inputIFID
     * @return type
     */
    public function getCurrentStockByInputLines($inputIFID)
    {
        // @todo: Sau can cho them thuoc tinh
        $sql = sprintf('
            SELECT 
                danhsach.*
                , kho.SoLuongHC AS SoLuongHC
                , nhapkho.Kho
                , nhapkho.NgayChungTu as NgayGiaoDich
                , ifnull(kho.IOID, 0) AS WIOID
                , ifnull(kho.IFID_M602, 0) AS WIFID
            FROM ONhapKho AS nhapkho
            INNER JOIN ODanhSachNhapKho AS danhsach ON nhapkho.`IFID_M402` = danhsach.`IFID_M402`
            LEFT JOIN OKho AS kho ON 
                danhsach.`Ref_MaSanPham` = kho.`Ref_MaSP`
                AND danhsach.`Ref_DonViTinh` = kho.`Ref_DonViTinh`
                AND nhapkho.`Ref_Kho` = kho.`Ref_Kho`
                AND ifnull(danhsach.`Ref_ThuocTinh`, 0) = ifnull(kho.`Ref_ThuocTinh`, 0)
            WHERE nhapkho.IFID_M402 = %1$d 
        ', $inputIFID);
        return $this->_o_DB->fetchAll($sql);            
    }
    
    /**
     * Lấy kho hiện tại theo dòng xuất kho
     * @param type $ouputIFID
     * @return type
     */
    public function getCurrentStockByOutputLines($ouputIFID)
    {
        // @todo: Sau can cho them thuoc tinh
        $sql = sprintf('
            SELECT 
                danhsach.*
                , kho.SoLuongHC AS SoLuongHC
                , xuatkho.Kho
                , xuatkho.NgayChungTu as NgayGiaoDich
                , ifnull(kho.IOID, 0) AS WIOID
                , ifnull(kho.IFID_M602, 0) AS WIFID
            FROM OXuatKho AS xuatkho
            INNER JOIN ODanhSachXuatKho AS danhsach ON xuatkho.`IFID_M506` = danhsach.`IFID_M506`
            LEFT JOIN OKho AS kho ON 
                danhsach.`Ref_MaSP` = kho.`Ref_MaSP`
                AND danhsach.`Ref_DonViTinh` = kho.`Ref_DonViTinh`
                AND xuatkho.`Ref_Kho` = kho.`Ref_Kho`
                AND ifnull(danhsach.`Ref_ThuocTinh`, 0) = ifnull(kho.`Ref_ThuocTinh`, 0)
            WHERE xuatkho.IFID_M506 = %1$d 
        ', $ouputIFID);
        return $this->_o_DB->fetchAll($sql);            
    }    
    
    public function getStockStatusOfWarehouseFormStockStatusOfInput($inputIFID)
    {
        // @todo: Sau can cho them thuoc tinh
        $sql = sprintf('
            SELECT 
                TrangThaiNhap.*
                , TrangThaiKho.SoLuong AS SoLuongKho
                , TrangThaiKho.IOID AS WIOID
                , TrangThaiKho.IFID_M602 AS WIFID
                , bin1.MaSP AS BinItem
                , ifnull(bin1.Ref_MaSP, 0) AS RefBinItem
                , ifnull(bin1.DonViTinh, \'\') AS BinUOM
                , bin1.Ref_DonViTinh AS RefBinUOM
                , bin1.SucChua AS BinCapacity
            FROM
            (
            SELECT *
            FROM OThuocTinhChiTiet 
            WHERE IFID_M402 = %1$d
            ) AS TrangThaiNhap
            LEFT JOIN OThuocTinhChiTiet AS TrangThaiKho
                ON ifnull(TrangThaiKho.IFID_M602, 0) != 0
                AND ifnull(TrangThaiNhap.Ref_Kho, 0) = ifnull(TrangThaiKho.Ref_Kho, 0)
                AND ifnull(TrangThaiNhap.Ref_Bin, 0) = ifnull(TrangThaiKho.Ref_Bin, 0)
                AND ifnull(TrangThaiNhap.Ref_MaSanPham, 0) = ifnull(TrangThaiKho.Ref_MaSanPham, 0)
                AND ifnull(TrangThaiNhap.Ref_MaThuocTinh, 0) = ifnull(TrangThaiKho.Ref_MaThuocTinh, 0)
                AND ifnull(TrangThaiNhap.SoLo, \'\') = ifnull(TrangThaiNhap.SoLo, \'\')
                AND ifnull(TrangThaiNhap.SoSerial, \'\') = ifnull(TrangThaiNhap.SoSerial, \'\')
                AND ifnull(TrangThaiNhap.Ref_DonViTinh, 0) = ifnull(TrangThaiNhap.Ref_DonViTinh, 0)
			LEFT JOIN OBin AS bin1 ON ifnull(TrangThaiNhap.Ref_Bin, 0) = bin1.IOID
        ', $inputIFID);
        return $this->_o_DB->fetchAll($sql);            
    }
    
    public function getStockStatusOfWarehouseFormStockStatusOfOutput($inputIFID)
    {
        // @todo: Sau can cho them thuoc tinh
        $sql = sprintf('
            SELECT 
                TrangThaiNhap.*
                , TrangThaiKho.SoLuong AS SoLuongKho        
                , TrangThaiKho.IOID AS WIOID
                , TrangThaiKho.IFID_M602 AS WIFID
            FROM
            (
            SELECT *
            FROM OThuocTinhChiTiet 
            WHERE IFID_M506 = %1$d
            ) AS TrangThaiNhap
            LEFT JOIN OThuocTinhChiTiet AS TrangThaiKho
                ON ifnull(TrangThaiKho.IFID_M602, 0) != 0
                AND ifnull(TrangThaiNhap.Ref_Kho, 0) = ifnull(TrangThaiKho.Ref_Kho, 0)
                AND ifnull(TrangThaiNhap.Ref_Bin, 0) = ifnull(TrangThaiKho.Ref_Bin, 0)
                AND ifnull(TrangThaiNhap.Ref_MaSanPham, 0) = ifnull(TrangThaiKho.Ref_MaSanPham, 0)
                AND ifnull(TrangThaiNhap.Ref_MaThuocTinh, 0) = ifnull(TrangThaiKho.Ref_MaThuocTinh, 0)
                AND ifnull(TrangThaiNhap.SoLo, \'\') = ifnull(TrangThaiNhap.SoLo, \'\')
                AND ifnull(TrangThaiNhap.SoSerial, \'\') = ifnull(TrangThaiNhap.SoSerial, \'\')
                AND ifnull(TrangThaiNhap.Ref_DonViTinh, 0) = ifnull(TrangThaiNhap.Ref_DonViTinh, 0)
        ', $inputIFID);
        return $this->_o_DB->fetchAll($sql);            
    }    
}

?>