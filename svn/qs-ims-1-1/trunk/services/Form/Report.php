<?php

class Qss_Service_Form_Report extends Qss_Service_Abstract
{

	public function __doExecute ($params = array())
	{
		$ifid = $params['ifid'];
		$deptid = $params['deptid'];
		$form = new Qss_Model_Form();
		if ( $form->initData($ifid, $deptid) )
		{
			$form->report(Qss_Register::get('userinfo')->user_id,$params['content'],$params['date']);
		}
	}

}
?>