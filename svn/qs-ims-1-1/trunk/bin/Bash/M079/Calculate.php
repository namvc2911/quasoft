<?php
class Qss_Bin_Bash_M079_Calculate extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$service  = new Qss_Service();
		$service->Button->M317->Calculate($this->_params->Ref_KyCong,$this->_params->Ref_PhongBan);
	}
}
?>
