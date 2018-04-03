<?php
/**
 * Save form service
 *
 * @author HuyBD
 *
 */
class Qss_Service_Report_Save extends Qss_Lib_Service
{

	public function __doExecute ($userid,$params)
	{
		/* The validation should be here. This will add to message property that
		 * we can access in caller e.g: $this->services->form->save->message 
		 */
		
		if(!$params['params']) return;
		
		$dashboardModel = new Qss_Model_Dashboad();
		$report         = new Qss_Model_Report();
		$quickReport    = $dashboardModel->getByPath($params['params']);
		
		if($quickReport && ($params['urid'] != $quickReport->URID))
		{
			$this->setError();
			$this->setMessage($this->_translate(1));
		}
		else 
		{
			$report->save($params);
		}
	}
}
?>