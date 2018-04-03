<?php
class Qss_Bin_Bash_M321_Download extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$model = new Qss_Model_M321_Main();
		if(!$model->updateTimesheet($this->_params))		
		{
			$this->setError();
			$this->setMessage('Cannot download logs!');
		}
	}
}
?>
