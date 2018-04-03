<?php

class Qss_Service_Currencies_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$model = new Qss_Model_Currency();
		$id = $model->save($params);
		Qss_Cookie::set('grid_selected', $id);
	}

}
?>