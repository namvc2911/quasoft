<?php
/**
 * Save form service
 *
 * @author HuyBD
 *
 */
class Qss_Service_Report_Delete extends Qss_Lib_Service
{

	public function __doExecute ($userid,$params)
	{
		/* The validation should be here. This will add to message property that
		 * we can access in caller e.g: $this->services->form->save->message */
		$report = new Qss_Model_Report();
		$report->delete($params['urid']);
	}
}
?>