<?php
class Qss_View_Instance_Form_GridDocument extends Qss_View_Abstract
{

	public function __doExecute ($id, $fid)
	{
		$model = new Qss_Model_Document();
		$form = new Qss_Model_Form();
		$form->init($fid, Qss_Register::get('userinfo')->user_dept_id,Qss_Register::get('userinfo')->user_id);
		$object = $form->o_fGetMainObject();
		$this->html->user = Qss_Register::get('userinfo');
		$this->html->objects = $model->getByFID($id, $fid,$object->ObjectCode);
		$this->html->fields = $object->loadFields();
		$this->html->object = $object;
	}
}

?>