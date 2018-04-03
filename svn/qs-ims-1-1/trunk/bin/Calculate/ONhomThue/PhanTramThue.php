<?php
class Qss_Bin_Calculate_ONhomThue_PhanTramThue extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$retval = 0;
		$sql    = sprintf('SELECT * 
						FROM ODanhMucThue AS dmt
						INNER JOIN qsrecforms AS qsrf ON dmt.IOID = qsrf.IOID
						WHERE IFID = %1$d ', $this->_object->i_IFID);
		
		$arr    = $this->_db->fetchAll($sql);
		foreach ($arr as $item)
		{
			$retval += $item->PhanTramChiuThue;
		}
		return  $retval;
	}
}
?>