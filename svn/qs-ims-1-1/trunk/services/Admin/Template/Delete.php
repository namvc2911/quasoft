<?php

class Qss_Service_Admin_Template_Delete extends Qss_Service_Abstract
{

	public function __doExecute ($designid,$type =Qss_Lib_Const::FORM_DESIGN_FORM)
	{
		$userinfo = Qss_Register::get('userinfo');
		$design = new Qss_Model_Admin_Design($userinfo->user_dept_id,$type);
		$design->delete($designid);
	}

}
?>