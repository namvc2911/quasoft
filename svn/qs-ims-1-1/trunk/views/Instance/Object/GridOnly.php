<?php
class Qss_View_Instance_Object_GridOnly extends Qss_View_Abstract
{

	public function __doExecute ($sql, Qss_Model_Form $form, Qss_Model_Object $object, $currentpage = 1, $limit = 20, $orderfield = 0, $ordertype = 'ASC',$groupby=0,$params=array(),$fieldid=0)
	{
		$this->html->user = Qss_Register::get('userinfo');
		$this->html->objects = $object->a_fGetIOIDBySQL($sql, $currentpage, $limit);
		$this->html->fields = $object->loadFields();
		$this->html->gridFieldCount = $object->getGridFieldCount() + 1;
		$this->html->o_Object = $object;
		$this->html->isSub = $form->o_fGetObjectByCode($object->ObjectCode)?1:0;
	}
}

?>