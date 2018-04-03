<?php
class Qss_View_Document_Select extends Qss_View_Abstract
{

	public function __doExecute ($form, $currentpage = 1, $limit = 20, $orderfield = 0, $ordertype = 'ASC',$groupby=0)
	{
		$document = new Qss_Model_Document();
		$this->html->documents = $document->getAttach($form->i_IFID,$currentpage, $limit, $orderfield, $ordertype,$groupby,array());
		$this->html->recordcount = $document->countAttach($form->i_IFID,$currentpage, $limit, $orderfield, $ordertype,$groupby,array());
	}
}

?>