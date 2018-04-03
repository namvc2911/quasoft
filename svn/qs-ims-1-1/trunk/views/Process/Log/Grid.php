<?php
class Qss_View_Process_Log_Grid extends Qss_View_Abstract
{

	public function __doExecute ($ifid, $currentpage = 1, $limit = 20, $orderfield = 0, $ordertype = 'ASC',$groupby=0,$filter)
	{
		$process = new Qss_Model_Process();
		$this->html->logs = $process->getLogs($ifid,$currentpage, $limit, $orderfield, $ordertype,$groupby,$filter);
		$this->html->recordcount = $process->countLogs($ifid,$currentpage, $limit, $orderfield, $ordertype,$groupby,$filter);
	}
}

?>