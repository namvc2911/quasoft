<?php
class Qss_Bin_Process_Clean extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$day = (int) $this->_params->SoNgay;
		$last = $day? (int) strtotime("-{$day} day") : strtotime(date('Y-m-d'));
		$sql = "delete from qsiforms where deleted = 1 and (LastModify < {$last} or (LastModify is null and SDate < {$last}))";
		$this->_db->execute($sql);

		$sql = "delete from qsrecforms where IFID not in (select IFID from (select IFID from qsiforms) as T)";
		$this->_db->execute($sql);
		
		$sql = "delete from qsftrace where IFID not in (select IFID from (select IFID from qsiforms) as T)";
		$this->_db->execute($sql);
		
		$sql = "delete from qsfreader where IFID not in (select IFID from (select IFID from qsiforms) as T)";
		$this->_db->execute($sql);
		
		$sql = "delete from qsfsharing where IFID not in (select IFID from (select IFID from qsiforms) as T)";
		$this->_db->execute($sql);
		
		$sql = "delete from qsfprocesslog where IFID not in (select IFID from (select IFID from qsiforms) as T)";
		$this->_db->execute($sql);
		
		$sql = "delete from qsfcomment where IFID not in (select IFID from (select IFID from qsiforms) as T)";
		$this->_db->execute($sql);

		$sql = "delete from qsiobjects where IOID not in (select IOID from (select IOID from qsrecforms) as T)";
		$this->_db->execute($sql);

		$sql = "delete from qsrecobjects where IOID not in (select IOID from (select IOID from qsiobjects) as T)";
		$this->_db->execute($sql);

		foreach (Qss_Lib_Const::$DATABASE_TABLES as $key => $table)
		{
			if($key == 0 || $key == 12)
			{
				$sql = "delete from datshortdesc where ID not in (select RecordID from (select RecordID from qsrecobjects where DataFieldType = 1 or DataFieldType = 13) as T)";
				$this->_db->execute($sql);
			}
			elseif($key != 13 && $key != 14 && $key != 15)
			{
				$sql = "delete from " . $table . " where ID not in (select RecordID from (select RecordID from qsrecobjects where DataFieldType = " . ($key + 1) . ") as T)";
				$this->_db->execute($sql);
			}
		}
	}
}
?>