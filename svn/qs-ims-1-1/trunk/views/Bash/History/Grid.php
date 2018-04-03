<?php
class Qss_View_Bash_History_Grid extends Qss_View_Abstract
{

	public function __doExecute ($id, $currentpage = 1, $limit = 20,  $ordertype = 'ASC')
	{
		$bash = new Qss_Model_Bash();
		$this->html->history = $bash->getAllHistory($id,$currentpage, $limit,$ordertype);
		$this->html->recordcount = $bash->countAllHistory($id);
	}
}

?>