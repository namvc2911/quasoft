<?php

class Qss_Service_Mail_Import extends Qss_Service_Abstract
{

	public function __doExecute ($id, $arrIOID, $arrFID)
	{
		$user = Qss_Register::get('userinfo');
		$model = new Qss_Model_Mail();
		foreach ($arrIOID as $key=>$val)
		{
			$model->saveRefer($id,$arrFID[$key],$val);
		}
	}

}
?>