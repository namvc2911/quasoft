<?php
class Qss_View_Common_Header extends Qss_View_Abstract
{

	public function __doExecute (Qss_Model_UserInfo $o_User,$title = '')
	{
		$quickmenu = Qss_Session::get('quickmenu');
		if ( !$quickmenu)
		{
			$quickmenu = $o_User->a_fGetAllModule();
		}
		Qss_Session::set('quickmenu', $quickmenu);
		$this->html->quickmenu = $quickmenu;
		$this->html->notifycount = $o_User->user_notify;
		$this->html->messagecount = $o_User->user_message;
		$this->html->eventcount = $o_User->user_event;
	}
}
?>