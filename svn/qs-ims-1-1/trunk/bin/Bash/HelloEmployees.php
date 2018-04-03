<?php
class Qss_Bin_Bash_HelloEmployees extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		// increase Emp no by 1
		$model = new Qss_Model_Extra_Employees();
		$last = $model->getLastEmployee();
		$newEmpCode = 'NV00001';
		if($last->MaNhanVien)
		{	
			$oldCode = (int)'1'.preg_replace('/[^0-9]/','',$last->MaNhanVien);
			$tmpNewCode = $oldCode + 1;
			if(substr($tmpNewCode,0,1)=='1')
			{
				$newCode = 'NV'.substr($tmpNewCode,1);
			}
			else
			{
				$newCode = 'NV1'.substr($tmpNewCode,1);
			}
		}

		
		// create employee
		$aKyNang = array();
		$kyNang = $model->getKyNang($this->_params->IFID_M332);
		foreach($kyNang as $val)
		{
			$aKyNang[$val->IOID] = array(
								'KyNang'=>$val->KyNang,
								'SoNanKinhNghiem'=>$val->SoNamKinhNghiem				
			);
		}
		
		$employee = array(
						'ODanhSachNhanVien'=>array(
                                                    $this->_params->IOID=>array(
                                                           'MaNhanVien'=>$newCode
                                                           ,'TenNhanVien'=> $this->_params->TenUngVien
                                                           ,'NgaySinh' => Qss_Lib_Date::mysqltodisplay($this->_params->NgaySinh)
                                                           ,'GioiTinh' => $this->_params->GioiTinh
                                                           ,'DiaChiThuongChu' => $this->_params->DiaChi
                                                           ,'DienThoai' => $this->_params->DienThoai
                                                           ,'Email'=> $this->_params->Email
                                                           ,'ChucDanh' =>$this->_params->ChucDanh
                                                    )
						/*,'OKyNang'=>$aKyNang*/
						)	
		);
		/*echo "<pre>";
		print_r($employee);*/
		
		if(!$this->_form->getIOIDLink($this->_params->IOID)){
			$service = $this->services->Form->Manual('M316',0,$employee,false);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
			//$this->_form->saveIOIDLink ($this->_params->IFID_M401,$service->getData());
		}
		else 
		{
			$this->setError();
			$this->setMessage("{$this->_translate(1)}");/*Ứng viên đã được tạo từ trước .*/
		}
	}
	
}