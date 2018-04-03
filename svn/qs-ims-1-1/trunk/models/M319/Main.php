<?php
class Qss_Model_M319_Main extends Qss_Model_Abstract
{
	public function getDepartments()
	{
		$sqlwhere = '';
 		if(Qss_Lib_System::formSecure('M319'))
 		{
 			$sqlwhere .= sprintf(' and IFID_M319 in (select IFID from qsrecordrights where FormCode = "M319" and UID=%1$d)'
 				,$this->_user->user_id);
		}
		$sql = sprintf('select * from OPhongBan where 1=1 %1$s',$sqlwhere); 		
 		return $this->_o_DB->fetchAll($sql);
	}
}