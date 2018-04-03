<?php
class Qss_Bin_Bash_M046_Calculate extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$service  = new Qss_Service();
		$service->Button->M026->Calculate($this->_params->Ref_KyCong,$this->_params->Ref_PhongBan);
	}
}
?>
