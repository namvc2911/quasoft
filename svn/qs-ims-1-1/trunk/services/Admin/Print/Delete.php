<?php

class Qss_Service_Admin_Print_Delete extends Qss_Service_Abstract
{

	public function __doExecute ($designid)
	{
		$design = new Qss_Model_Admin_Print();
		$design->delete($designid);
	}

}
?>