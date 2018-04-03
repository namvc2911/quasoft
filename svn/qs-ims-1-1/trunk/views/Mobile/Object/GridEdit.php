<?php
class Qss_View_Mobile_Object_GridEdit extends Qss_View_Abstract
{

	public function __doExecute ($sql, Qss_Model_Form $form, Qss_Model_Object $object, $currentpage = 1, $limit = 20, $orderfield = 0, $groupby=0)
	{
		$this->html->user = Qss_Register::get('userinfo');
		if($object->i_PageCount && $currentpage > $object->i_PageCount)
		{
			$currentpage = 1;
		}
		$this->html->currentpage = $currentpage;
		$this->html->objects = $object->a_fGetIOIDBySQL($sql, $currentpage, $limit);
		//echo '<pre>';
		//print_r($this->html->objects);die;
		$this->html->gridFieldCount = $object->getGridFieldCount() + 1;
		$this->html->fields = $object->loadFields();
		$this->html->o_Object = $object;
	}
}

?>