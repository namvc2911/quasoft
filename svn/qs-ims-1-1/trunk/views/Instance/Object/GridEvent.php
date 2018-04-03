<?php
class Qss_View_Instance_Object_GridEvent extends Qss_View_Abstract
{

	public function __doExecute ($eventid, $fid, $objid)
	{
		$model = new Qss_Model_Event();
		$form = new Qss_Model_Form();
		$form->v_fInit($fid, Qss_Register::get('userinfo')->user_dept_id,Qss_Register::get('userinfo')->user_id);
		$object = new Qss_Model_Object();
		$object->v_fInit($objid, $fid);
		$this->html->user = Qss_Register::get('userinfo');
		$this->html->objects = $model->getReferByFID($eventid, $fid);
		$this->html->fields = $object->loadFields();
		$this->html->object = $object;
	}
}

?>