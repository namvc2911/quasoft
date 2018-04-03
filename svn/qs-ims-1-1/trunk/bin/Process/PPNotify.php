<?php
class Qss_Bin_Process_PPNotify extends Qss_Lib_Bin
{
	// @Description:
	// Thuc hien tao lenh san xuat cho ky hien tai hoac ky tiep theo dua vao ke hoach san xuat cua
	// xu ly yeu cau. #de
	public function __doExecute()
	{

		$sql = sprintf('select * from OKy where IOID = %1$d',$this->_params->Ref_Ky);
		$periodSQL = $this->_db->fetchOne($sql);

		if($periodSQL)
		{
			$period = $periodSQL->MaKy;
			$next   = $this->_params->KyKeTiep;
			switch ($period)
			{
				case 'D':
					$date = date_create();
					if($next)
					{
						$date = Qss_Lib_Date::add_date($date, 1);
					}
					
					$sql   = sprintf('select OKeHoachSanXuat.* from OKeHoachSanXuat
									inner join qsiforms on qsiforms.IFID = OKeHoachSanXuat.IFID_M901
									left join qsioidlink on qsioidlink.FromIOID = OKeHoachSanXuat.IOID  AND qsioidlink.FromIFID = OKeHoachSanXuat.IFID_M901
									where 
									TuNgay = %1$s 
									and qsiforms.Status <> -1
									and qsioidlink.ToIOID is null
						',$this->_db->quote($date->format('Y-m-d')));
					$dataSQL = $this->_db->fetchAll($sql);
					$params  = array();
					foreach($dataSQL as $item)
					{
						//manual to notification
						$thietke = $item->Ref_ThietKe;
							
						$params['OSanXuat'][0]['DayChuyen'] = $item->DayChuyen;
						$params['OSanXuat'][0]['TuNgay']    = $item->TuNgay;
						$params['OSanXuat'][0]['DenNgay']   = $item->DenNgay;
						$params['OSanXuat'][0]['ThoiGian']  = $item->ThoiGian;
						$params['OSanXuat'][0]['SanXuatSuaChua'] = 1;
						$params['OSanXuat'][0]['MaSP']      = $item->MaSP;
                                                $params['OSanXuat'][0]['DonViTinh'] = $item->DonViTinh;
						$params['OSanXuat'][0]['ThuocTinh'] = $item->ThuocTinh;
						$params['OSanXuat'][0]['ThietKe']   = $item->ThietKe;
						$params['OSanXuat'][0]['SoLuong']   = $item->SoLuong;
						$params['OSanXuat'][0]['ioidlink']  = $item->IOID;
						
						$materialsSql  = sprintf('select tp.*, ct.SoLuong as MainQty from OThanhPhanSanPham as tp
								inner join OCauThanhSanPham as ct on ct.IFID_M114=tp.IFID_M114
								where ct.IOID = %1$d and  BanTP != 1',
								$thietke);
						$materials     = $this->_db->fetchAll($materialsSql);
						
						$toolsetSql 	= sprintf('select tp.*, ct.SoLuong as MainQty from OCongCuCauThanh  as tp
								inner join OCauThanhSanPham as ct on ct.IFID_M114=tp.IFID_M114
								where ct.IOID = %1$d',
								$thietke);
						$toolset 		= $this->_db->fetchAll($toolsetSql);
						
						$n = 0;
						foreach ($materials as $mat)
						{
							$params['ONguyenVatLieuSX'][$n]['CongDoan']  = $mat->CongDoan;
							$params['ONguyenVatLieuSX'][$n]['MaSP']      = $mat->MaThanhPhan;
                                                        $params['ONguyenVatLieuSX'][$n]['DonViTinh'] = $mat->DonViTinh;
							$params['ONguyenVatLieuSX'][$n]['ThuocTinh'] = $mat->ThuocTinh;
							$params['ONguyenVatLieuSX'][$n]['SoLuong']   = ($mat->SoLuong * $item->SoLuong) / $mat->MainQty;
							$n++;
						}
						
						$o = 0;
						foreach ($toolset as $ta)
						{
							$params['OCongCuSX'][$o]['CongDoan']  = $ta->CongDoan;
							$params['OCongCuSX'][$o]['MaSP']      = $ta->MaSP;
                                                        $params['OCongCuSX'][$o]['DonViTinh'] = $ta->DonViTinh;
							$params['OCongCuSX'][$o]['ThuocTinh'] = $ta->MoTa;
							$params['OCongCuSX'][$o]['SoLuong']   = ($ta->SoLuong * $item->SoLuong) / $ta->MainQty;
							$o++;
						}
						
						
						$service = $this->services->Form->Manual('M710',0,$params,true,false);
						
						if($service->isError())
						{
							$this->setError();
							$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
						}
						
					}
				break;
			}
		}
	}
}
?>