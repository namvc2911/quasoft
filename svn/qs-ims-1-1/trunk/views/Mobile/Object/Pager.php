<?php
class Qss_View_Mobile_Object_Pager extends Qss_View_Abstract
{

	public function __doExecute ($sql, $object, $currentpage = 1, $limit = 20,$groupby = 0)
	{
		$this->html->fields = $object->loadFields();
		$this->html->pagecount = $object->i_fGetPageCount($sql, $currentpage, $limit);
	}
}
?>