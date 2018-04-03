<?php

class Qss_Bin_Bash_CreateGoodIssueFromWO extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$sql = sprintf('select * from OXuatKho where Ref_PhieuBaoTri = %1$d',$this->_params->IOID);
		$dataSQL = $this->_db->fetchOne($sql);
		$popup = $this->requests->getParam('popup',0);
		if(!$dataSQL)
		{
			$sql = sprintf('select *
				FROM OLoaiXuatKho
				WHERE Loai = %1$s',$this->_db->quote(Qss_Lib_Extra_Const::OUTPUT_TYPE_MAINTAIN));
			$dataLoaiXuatKho = $this->_db->fetchOne($sql);

			$sql = sprintf('select ODanhSachKho.*
					, IF( IFNULL(ODonViSanXuat.Ref_KhoVatTu, 0) != 0 , IFNULL(ODonViSanXuat.Ref_KhoVatTu, 0), ODanhSachKho.IOID ) AS IOID
				FROM ODanhSachKho
				LEFT JOIN ODonViSanXuat ON ODonViSanXuat.Ref_KhoVatTu = ODanhSachKho.IOID and ODonViSanXuat.IOID = %1$d
				WHERE LoaiKho=%2$s
				ORDER BY ODonViSanXuat.IOID desc
				limit 1'
				, $this->_params->Ref_MaDVBT
				, $this->_db->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_MATERIAL));
				$dataKho = $this->_db->fetchOne($sql);

				$insert = array();


                if(!$dataKho)
                {
                    $this->setError();
                    $this->setMessage('Cần có ít nhất một kho vật tư để tạo phiếu xuất kho!');
                    return;
                }

				// Main Obj
				$insert['OXuatKho'][0]['Kho']            = (int) $dataKho->IOID;
				$insert['OXuatKho'][0]['LoaiXuatKho'] 	 = (int) $dataLoaiXuatKho->IOID;
				$insert['OXuatKho'][0]['NgayChungTu']    = $this->_params->NgayBatDauDuKien;
				$insert['OXuatKho'][0]['NgayChuyenHang'] = $this->_params->NgayBatDauDuKien;
				$insert['OXuatKho'][0]['PhieuBaoTri']  	 = (int)$this->_params->IOID;
				$insert['OXuatKho'][0]['DonViThucHien']  = (int)$this->_params->Ref_MaDVBT;



				$i = 0;
				foreach ($this->_params->OVatTuPBT as $item)
				{
					if($item->HinhThuc == 0 || $item->HinhThuc == 1 || $item->HinhThuc == 4)
					{
						$insert['ODanhSachXuatKho'][$i]['MaSP']       = (int) $item->Ref_MaVatTu;
						$insert['ODanhSachXuatKho'][$i]['DonViTinh']  = (int) $item->Ref_DonViTinh;
						$insert['ODanhSachXuatKho'][$i]['SoLuong']    = ($item->SoLuongDuKien != 0)?$item->SoLuongDuKien:$item->SoLuong;
						$i++;
					}
				}
				$service = $this->services->Form->Manual('M506' ,0,$insert,false);
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
				else
				{
					if($popup)
					{
						Qss_Service_Abstract::$_redirect = '/user/form/popup?ifid='.$service->getData().'&deptid=1&popup=1';
					}
					else 
					{
						Qss_Service_Abstract::$_redirect = '/user/form/edit?ifid='.$service->getData().'&deptid=1';
					}
				}
		}
		else
		{
			if($popup)
			{
				Qss_Service_Abstract::$_redirect = '/user/form/popup?ifid='.$dataSQL->IFID_M506.'&deptid=1&popup=1';
			}
			else 
			{
				Qss_Service_Abstract::$_redirect = '/user/form/edit?ifid='.$dataSQL->IFID_M506.'&deptid=1';
			}
		}
	}
}
?>
