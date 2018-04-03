<?php
class Qss_View_Common_Quick extends Qss_View_Abstract
{

	public function __doExecute (Qss_Model_UserInfo $o_User)
	{
		$lastActive = Qss_Cookie::get('lastActiveModule');
		$lastActive = $lastActive?$lastActive:'M000';
		$this->html->menuid = Qss_Cookie::get('lastMenuID');
		$this->html->blocklist = $o_User->getParentMenu(0);
	}
}
?>