<?php
class Qss_Model_M077_Main extends Qss_Model_Abstract
{
	public function getDataByPeriod($startdate,$enddate,$departmentid = 0)
    {
    	$sqlwhere = sprintf('  lich.NgayBatDau <= %2$s
								and
			 					lich.NgayKetThuc >=%1$s'
					,$this->_o_DB->quote($startdate)
					,$this->_o_DB->quote($enddate));
   	 	if($departmentid) 
    	{
    		$sqlwhere .= sprintf(' and (pb.lft >= (select lft from OPhongBan where IOID = %1$d)
    						and pb.rgt >= (select rgt from OPhongBan where IOID = %1$d))'
					,$departmentid);
    	}
    	if(Qss_Lib_System::formSecure('M319'))
		{
			$sqlwhere .= sprintf(' and pb.IFID_M319 in (select IFID from qsrecordrights where FormCode = "M319" and UID=%1$d)'
					,$this->_user->user_id);
		}
        
        $sql = sprintf('
			SELECT
				lich.*
			FROM ODangKyNghi as lich
			inner join ODanhSachNhanVien as nv on lich.Ref_MaNhanVien = nv.IOID
			inner join OPhongBan as pb on pb.IOID = nv.Ref_MaPhongBan 
			WHERE %1$s
			order by pb.lft, lich.MaNhanVien'
            , $sqlwhere);
        return $this->_o_DB->fetchAll($sql);
    }
}