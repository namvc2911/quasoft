<?php
class Qss_Bin_Bash_CreateMaterialConsumption extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$insert = array();
		$extra  = new Qss_Model_Extra_Extra();
		$manuLineInfo = $extra->getTable(array('*'), 'ODayChuyen', array('IOID'=>$this->_params->Ref_DayChuyen),array(), 1, 1);
		
		foreach ($this->_params->ONVLDauVao as $item)
		{
			$insert['OHangDoiXuat'][0]['Ngay']      = $this->_params->Ngay;
			$insert['OHangDoiXuat'][0]['MaSP']      = $item->MaSP;
			$insert['OHangDoiXuat'][0]['ThuocTinh'] = $item->ThuocTinh;
                        $insert['OHangDoiXuat'][0]['DonViTinh'] = $item->D;
			$insert['OHangDoiXuat'][0]['SoLuong']   = $item->SoLuong;
			$insert['OHangDoiXuat'][0]['Module']    = 'M717';
			$insert['OHangDoiXuat'][0]['Kho']       = $manuLineInfo?$manuLineInfo->KhoSanXuat:'';
			$insert['OHangDoiXuat'][0]['MoTa']      = $this->_translate(1);
			//echo '<pre'; print_r($insert); die;
			
			$service = $this->services->Form->Manual('M611' ,0,$insert,false);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
		}
	}
}