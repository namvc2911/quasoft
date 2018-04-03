<?php
class Qss_Bin_Trigger_OKhuVucThietBi extends Qss_Lib_Trigger
{
	public function onUpdated($object)
	{
		parent::init();
		$this->updateArea($object);
	}
	public function onInserted($object)
	{
		parent::init();
		$this->updateArea($object);
	}
	private function updateArea($object)
	{
		$list  = $this->_params->OKhuVucThietBi;
		$date  = '0000-00-00';
		$vitri = 0;
		foreach ($list as $val)
		{
			if(Qss_Lib_Date::i_fMysql2Time($date) < Qss_Lib_Date::i_fMysql2Time($val->Ngay))
			{
				$date  = $val->Ngay;
				$vitri = $val->ViTri;
			}
		}
		if($vitri !== 0)
		{
			$params     = array('OMayMocThietBi'=>array(array('ViTri'=>$vitri, 'ioid'=>$this->_params->IOID)));
			$service    = $this->services->Form->Manual('M705',$this->_params->IFID_M705,$params,false);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
		}
	}
}