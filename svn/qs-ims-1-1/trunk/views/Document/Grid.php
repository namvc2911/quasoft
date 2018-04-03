<?php
class Qss_View_Document_Grid extends Qss_View_Abstract
{

	public function __doExecute ($user, $currentpage = 1, $limit = 20, $orderfield = 0, $ordertype = 'ASC',$groupby=0,$filter)
	{
		$document = new Qss_Model_Document();
		$this->html->documents = $document->getAll($user->user_id,$currentpage, $limit, $orderfield, $ordertype,$groupby,$filter);
		$this->html->recordcount = $document->countAll($user->user_id,$currentpage, $limit, $orderfield, $ordertype,$groupby,$filter);
	}
}

?>