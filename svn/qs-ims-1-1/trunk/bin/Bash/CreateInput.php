<?php
class Qss_Bin_Bash_CreateInput extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$insert = array();
		$extra  = new Qss_Model_Extra_Extra();
		$manuLineInfo = $extra->getTable(array('*'), 'ODayChuyen',
										 array('IOID'=>$this->_params->Ref_DayChuyen), 
										 array() , 1 , 1);
		
		foreach ($this->_params->OSanLuong as $item)
		{
			$insert['OHangDoiNhap'][0]['Ngay']      = $this->_params->Ngay;
			$insert['OHangDoiNhap'][0]['MaSP']      = $item->MaSP;
                        $insert['OHangDoiNhap'][0]['DonViTinh'] = $item->DonViTinh;
			$insert['OHangDoiNhap'][0]['ThuocTinh'] = $item->ThuocTinh;
			$insert['OHangDoiNhap'][0]['SoLuong']   = $item->SoLuong;
			$insert['OHangDoiNhap'][0]['Module']    = 'M717';
			$insert['OHangDoiNhap'][0]['Kho']       = $manuLineInfo?$manuLineInfo->KhoSanXuat:'';
			$insert['OHangDoiNhap'][0]['MoTa']      = $this->_params->MaLenhSX;

			$service = $this->services->Form->Manual('M610' ,0,$insert,false);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
		}
	}
}