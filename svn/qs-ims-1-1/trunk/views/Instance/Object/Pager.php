<?php
class Qss_View_Instance_Object_Pager extends Qss_View_Abstract
{

	public function __doExecute ($sql, $object, $currentpage = 1, $limit = Qss_Lib_Const_Display::OBJECT_LIMIT_DEFAULT,$groupby = 0)
	{
		$this->html->fields = $object->loadFields();
		$pagecount = $object->i_fGetPageCount($sql, $currentpage, $limit);
		$this->html->recordcount = $object->i_RecordCount;
		if($currentpage > $pagecount)
		{
			$this->html->currentpage = 1;
			Qss_Params::getInstance()->cookies->set('object_' . $object->ObjectCode . '_currentpage', 1);
		}
		$this->html->pagecount = $pagecount;
	}
}
?>