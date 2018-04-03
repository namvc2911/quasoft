<?php
class Qss_Service_Admin_User_Delete extends Qss_Service_Abstract
{

	public function __doExecute ($userid)
	{
		$user = new Qss_Model_Admin_User();
		$ret = $user->checkUserDataExists($userid);
		if(!count($ret))
		{
			$user->delete($userid);
		}
		else 
		{
			$this->setError();
			$this->setMessage($this->_translate(1));	
		}
	}

}
?>