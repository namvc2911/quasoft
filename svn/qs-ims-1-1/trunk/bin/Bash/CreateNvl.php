<?php
class Qss_Bin_Bash_CreateNvl extends Qss_Lib_Bin
{
	public function __doExecute()
	{

		
		if($this->_form->getIOIDLink($this->_params->IOID)){
				$this->setError();
				$this->setMessage("{$this->_translate(1)}");/*Phiếu sử dụng nguyên vật liệu mặc định đã được tạo từ trước.*/
		}
		else 
		{
			$model = new Qss_Model_Extra_Production();
			$nvl   = $model->getNVL($this->_params->MaSP, $this->_params->Ref_CauThanhSP);
			
			foreach($nvl as $val)
			{
				$aNVL  = array();
				$tmp   = array();
				$tmp[$this->_params->IOID] = array(
									'MaLenhSX'=>$this->_params->MaLenhSX,
									'Ngay'=>Qss_Lib_Date::mysqltodisplay($this->_params->NgayYeuCau),
									'MaSP'=>$val->MaThanhPhan,
                                                                        'DonViTinh'=>$val->DonViTinh,
									'ThuocTinh'=>$val->ThuocTinh,
									'SoLuong'=>($this->_params->SoLuong * $val->SoLuong),
									'DayChuyen'=>$this->_params->DayChuyen,
									'Ca'=>$this->_params->CaSX,
									'Kho'=>$this->_params->KhoChuyenDen,
									'CongDoan'=>$val->CongDoan				
				);
				$aNVL = array('ONVLDauVao'=>$tmp);
				$service = $this->services->Form->Manual('M718',0,$aNVL,false);
				
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
		}
					
	}
	
}