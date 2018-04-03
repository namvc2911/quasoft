<?php
class Qss_View_Instance_Form_PagerSelect extends Qss_View_Abstract
{

	public function __doExecute ($sql, Qss_Model_Form $form, $currentpage = 1, $limit = 20,$groupby = 0,Qss_Model_Object $object = null)
	{
		$mainobject = $form->o_fGetMainObject();
		$this->html->isMain = ($mainobject->ObjectCode == $object->ObjectCode);
		$this->html->fields = $object->loadFields();
		$this->html->pagecount = $object->i_fGetPageCount($sql, $currentpage, $limit);
	}
}

?>