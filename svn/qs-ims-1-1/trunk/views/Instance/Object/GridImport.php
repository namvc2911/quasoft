<?php
class Qss_View_Instance_Object_GridImport extends Qss_View_Abstract
{

	public function __doExecute ($sql, Qss_Model_Form $form, Qss_Model_Object $object, $currentpage = 1, $limit = 20, $orderfield = 0, $ordertype = 'ASC',$groupby=0)
	{
		$this->html->user = Qss_Register::get('userinfo');
		$this->html->objects = $object->a_fGetIOIDBySQL($sql, $currentpage, $limit);
		$this->html->fields = $object->loadFields();
		$this->html->o_Object = $object;
		$this->html->gridFieldCount = $object->getGridFieldCount() + 1;
	}
}

?>