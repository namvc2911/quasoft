<?php

class Qss_Service_Bash_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$bash = new Qss_Model_Bash();
		$bash->save($params);
	}

}
?>