<?php
class Qss_View_Bash_Grid extends Qss_View_Abstract
{

	public function __doExecute ($user, $currentpage = 1, $limit = 20, $orderfield = 0, $ordertype = 'ASC',$groupby=0,$filter)
	{
		$document    = new Qss_Model_Bash();
        $documents   = $document->getAll($user->user_id,$currentpage, $limit, $orderfield, $ordertype,$groupby,$filter);
        $recordcount = $document->countAll($user->user_id,$currentpage, $limit, $orderfield, $ordertype,$groupby,$filter);
        $totalPage   = ceil($recordcount/$limit);

        if($currentpage > $totalPage)
        {
            $currentpage = 1;
            $documents   = $document->getAll($user->user_id,$currentpage, $limit, $orderfield, $ordertype,$groupby,$filter);
            $recordcount = $document->countAll($user->user_id,$currentpage, $limit, $orderfield, $ordertype,$groupby,$filter);
            $totalPage   = ceil($recordcount/$limit);
        }

		$this->html->documents   = $documents;
		$this->html->recordcount = $recordcount;
        $this->html->totalPage   = $totalPage;
	}
}

?>