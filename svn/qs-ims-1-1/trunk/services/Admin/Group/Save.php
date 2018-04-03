<?php

class Qss_Service_Admin_Group_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$group = new Qss_Model_Admin_Group();
		$groupid = (int) $params['groupid'];
		$group->init($groupid);
		$group->save($params);
	}

}
?>