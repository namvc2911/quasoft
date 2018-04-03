<?php
class Qss_Bin_Bash_CreateSanluong extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		
		if($this->_form->getIOIDLink($this->_params->IOID)){
			$this->setError();
			$this->setMessage("{$this->_translate(1)}");/*Thống kê sản lượng mặc định đã được tạo từ trước.*/
		}
		else 
		{
			$model 		 	= new Qss_Model_Extra_Production();
			$sanPhamKhac 	= $model->getSanPhamDauRa($this->_params->MaSP, $this->_params->Ref_CauThanhSP);
			
			$aSanLuong  = array();
			$tmp        = array();
			$tmp[$this->_params->IOID] = array(
								'MaLenhSX'=>$this->_params->MaLenhSX,
								'Ngay'=>Qss_Lib_Date::mysqltodisplay($this->_params->NgayYeuCau),
								'MaSP'=>$this->_params->MaSP,
                                                                'DonViTinh'=>$this->_params->DonViTinh,
								'ThuocTinh'=>$this->_params->ThuocTinh,
								'SoLuong'=>$this->_params->SoLuong,
								'DayChuyen'=>$this->_params->DayChuyen,
								'Ca'=>$this->_params->CaSX,
								'Kho'=>$this->_params->KhoChuyenDen,
								'CongDoan'=>$this->_params->CongDoan				
			);
			$aSanLuong = array('OThongKeSanLuong'=>$tmp);
			$service   = $this->services->Form->Manual('M717',0,$aSanLuong,false);
			
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
		
			foreach($sanPhamKhac as $val)
			{
				$aSanPhamKhac  = array();
				$tmp           = array();
				$tmp[$this->_params->IOID] = array(
									'MaLenhSX'=>$this->_params->MaLenhSX,
									'Ngay'=>Qss_Lib_Date::mysqltodisplay($this->_params->NgayYeuCau),
									'MaSP'=>$val->MaSP,
                                                                        'DonViTinh'=>$val->DonViTinh,
									'ThuocTinh'=>$val->ThuocTinh,
									'SoLuong'=>($this->_params->SoLuong * $val->SoLuong),
									'DayChuyen'=>$this->_params->DayChuyen,
									'Ca'=>$this->_params->CaSX,
									'Kho'=>$this->_params->KhoChuyenDen,
									'CongDoan'=>$val->CongDoan				
				);
				$aSanPhamKhac = array('OThongKeSanLuong'=>$tmp);
				$service      = $this->services->Form->Manual('M717',0,$aSanPhamKhac,false);
				
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
		}
	}
	
}