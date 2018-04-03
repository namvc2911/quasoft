<?php

class Qss_Service_Maintenance_WorkOrder_Cost_Update extends Qss_Service_Abstract
{
/**/
	public function __doExecute ($id,$chiphithemgio=0,$chiphiphatsinh = 0,$ghichu = '')
	{
		$model = new Qss_Model_Maintenance_Workorder();
		$model->updateCost($id,$chiphithemgio,$chiphiphatsinh,$ghichu); 
	}

}
?>