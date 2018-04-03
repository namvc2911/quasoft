<?php
class Qss_View_Extra_Project extends Qss_View_Abstract
{

	public function __doExecute ($form,$selected)
	{
		$user= Qss_Register::get('userinfo');
		$model = new Qss_Model_Maintenance_Project();
		$this->html->duan = $model->getProjectByUser($user);
	}
}
?>