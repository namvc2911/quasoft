<?php

class Qss_Service_Admin_Web_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params,Qss_Model_UserInfo $user)
	{
		$cms_id = $params['cms_id'];
		$cms=  new Qss_Model_Admin_CMS();
		if(!$cms_id)
		$cms_id=-1;
		$cms->init($cms_id);
		$cms->doUpdate($params, $user);
	}

}
?>