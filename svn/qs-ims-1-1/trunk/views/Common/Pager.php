<?php
class Qss_View_Common_Pager extends Qss_View_Abstract
{

	public function __doExecute ($model, $data, $recordcount,$currentpage = 1, $limit = 20,$groupby = 0)
	{
		$this->html->recordcount = $recordcount?$recordcount:1;
	}
}

?>