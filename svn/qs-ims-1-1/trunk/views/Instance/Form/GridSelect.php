<?php
class Qss_View_Instance_Form_GridSelect extends Qss_View_Abstract
{

	public function __doExecute ($sql, Qss_Model_Form $form, $currentpage = 1, $limit = 20, $orderfield = 0, $ordertype = 'ASC',$groupby=0,$fieldid = 0,$object = null)
	{
		//$object = $form->o_fGetMainObject();
		$this->html->user = Qss_Register::get('userinfo');
		$this->html->objects = $object->a_fGetIOIDBySQL($sql, $currentpage, $limit);
		$this->html->gridFieldCount = $object->getGridFieldCount() + 1;
		$this->html->fields = $object->loadFields();
		$this->html->o_Object = $object;
	}
}

?>