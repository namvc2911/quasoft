<?php
class Qss_Bin_Notify_Validate_M412_Sync extends Qss_Lib_Notify_Validate
{
	const TITLE ='Đồng bộ yêu cầu mua hàng với Bravo.';

	const TYPE ='SUBSCRIBE';

	public function __doExecute()
	{
		try{
			$msDB = Qss_Db::getAdapter('mssql');
			$msDB->open();
			$sqlNhapKho  = sprintf('{call usp_BangKePhieuNhap(?,?,?)}');
			//$params = array(date('Y-m-d'),date('Y-m-d'));
			$params = array(array(date('Y-m-d'),SQLSRV_PARAM_IN),
			array(date('Y-m-d'), SQLSRV_PARAM_IN),
			array('', SQLSRV_PARAM_IN));
			$nhapkho = $msDB->fetchAll($sqlNhapKho,$params);

			$sql = sprintf('SELECT *
							FROM  ONhapKho where NgayChungTu = %1$s', $this->_db->quote(date('Y-m-d')));
			$arrNhapKho = array();
			$dataSQL = $this->_db->fetchAll($sql);
			foreach ($dataSQL as $item)
			{
				$arrNhapKho[$item->SoChungTu] = $item;
			}

			$import = new Qss_Model_Import_Form('M402');
			$arrDanhSach = array();
			$count = 0;
			$i = 0;
			$arrData = array();
			foreach ($nhapkho as $item)
			{
				$phieunhap = $item->So_Ct;
				$kho = $item->Ma_Kho;
				if(isset($arrNhapKho[$phieunhap]))
				{
					continue;
				}
				if(substr( strtoupper($kho), 0, 3 ) === 'CC0')
				{
					if(!isset($arrData[$phieunhap]))
					{
						$arrData[$phieunhap] = array();
					}
					$arrData[$phieunhap][] = $item;
				}
			}
			foreach ($arrData as $key=>$values)
			{
				$item = $values[0];
				$phieunhap = $item->So_Ct;
				$kho = $item->Ma_Kho;
				$ngayct = $item->Ngay_Ct->format('Y-m-d');
				$ngaynhap = $item->Ngay_Ct->format('Y-m-d');
				$arrDanhSach = array();
				foreach($values as $item)
				{
					$masp = $item->Ma_Vt;
					$tensp = $item->Ten_Vt;
					$donvitinh = $item->Dvt;
					$soluong = $item->So_Luong;
					$dongia = $item->Don_Gia*1000;
					$thanhtien = $item->Thanh_Tien*1000;
					$soyccc = $item->OrderNo;
					$arrDanhSach[] = array('SoYeuCau'=>strtoupper($soyccc)
					,'MaSanPham'=>$masp
					,'TenSanPham'=>$tensp
					,'DonViTinh'=>$donvitinh
					,'SoLuong'=>$soluong
					,'DonGia'=>$dongia
					,'ThanhTien'=>$thanhtien
					);
				}
				if(count($arrDanhSach))
				{
					$arrInsert = array('ONhapKho'=>array($i=>array(
										'SoChungTu'=>$phieunhap
					,'Kho'=>strtoupper($kho)
					,'NgayChungTu'=>$ngayct
					,'NgayChuyenHang'=>$ngaynhap
					,'status'=>2)),
									'ODanhSachNhapKho'=>$arrDanhSach);
					$import->setData($arrInsert);
				}
			}
			$import->generateSQL();
			$import->cleanTemp();
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}

		//echo $count;
	}
}
?>