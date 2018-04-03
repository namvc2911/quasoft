
<?php
class Qss_Bin_Calculate_OThueThuNhapCaNhan_MucToiThieu extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$SYSTEM_PARAMS  = new Qss_Model_System_Param;
		$SYSTEM_PARAMS  = $SYSTEM_PARAMS->getById('GTGC');
		return $SYSTEM_PARAMS->Value;
	}
}
?>
