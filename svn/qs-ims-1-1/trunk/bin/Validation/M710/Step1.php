<?php

class Qss_Bin_Validation_M710_Step1 extends Qss_Lib_Warehouse_WValidation
{
	public function onAlert()
	{
		parent::init();
		$EnoughStages = Qss_Lib_Production_Common::checkEnoughStages($this->_params->Ref_DayChuyen, $this->_params->Ref_ThietKe);
		if(count($EnoughStages))
		{
			$this->setError();
			$this->setMessage($EnoughStages['error']);
		}
	}
}