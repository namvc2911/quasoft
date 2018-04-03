<?php
class Qss_Model_M056_Main extends Qss_Model_Abstract
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
        $sql = sprintf('
			SELECT
				lich.*,nv.*
			from ODanhSachNhanVien as nv 
			inner join OPhongBan as pb on pb.IOID = nv.Ref_MaPhongBan
			inner join OPhongBan as pbc on pbc.lft<=pb.lft and pbc.rgt>=pb.rgt  
			inner join ODanhSachNgayNghiLe as lich on lich.Ref_PhongBan = nv.IOID
			WHERE %1$s
			order by pb.lft, nv.MaNhanVien'
            , $sqlwhere);
        return $this->_o_DB->fetchAll($sql);
    }
    /**
     * Lấy toàn công ty
     */
	public function getAllDataByPeriod($startdate,$enddate)
    {
    	$sqlwhere = sprintf('  lich.NgayBatDau <= %2$s
								and
			 					lich.NgayKetThuc >=%1$s'
					,$this->_o_DB->quote($startdate)
					,$this->_o_DB->quote($enddate));
        $sql = sprintf('
			SELECT
				lich.*
				from  ODanhSachNgayNghiLe as lich 
			WHERE %1$s and ifnull(Ref_PhongBan,0) = 0'
            , $sqlwhere);
        return $this->_o_DB->fetchAll($sql);
    }
}