<?php

class Qss_Model_Maintenance_Location extends Qss_Model_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}

	public function getLocationByIOID($locationIOID)
	{
		$sql = sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID);
		return $this->_o_DB->fetchOne($sql);
	}
	
	public function getLocations($locIOID = 0)
	{
		$where = '';
		
		if($locIOID)
		{
			$findLocSql = sprintf('
				SELECT * FROM OKhuVuc WHERE IOID = %1$d
			', $locIOID);
			$findLoc = $this->_o_DB->fetchOne($findLocSql);
			
			if($findLoc)
			{
				$where = sprintf(' 
						and (lft>= %1$d and  rgt <= %2$d)'
					, $findLoc->lft
					, $findLoc->rgt);
			}
		}			
		
		$sql = sprintf('			
			SELECT (
				SELECT
					count(*)
				FROM 
					OKhuVuc AS u
				WHERE 
					u.lft <= cm.lft
					AND u.rgt >= cm.rgt
					%1$s
					AND u.DeptID in (%2$s)
			) AS `Level`
			, cm.lft, cm.rgt
			, cm.MaKhuVuc AS LocCode
			, cm.Ten AS LocName
			, cm.IOID AS LocIOID
			FROM OKhuVuc AS cm
			WHERE 1=1 %1$s
			and cm.DeptID in (%2$s)
			ORDER BY cm.lft'	
				, $where
				, $this->_user->user_dept_list);
		return $this->_o_DB->fetchAll($sql);
	}
   
    /**
     * Lấy đơn vị quản lý thiết bị
     * @param int $eqIOID IOID của thiết bị
     */
    public function getManageDepOfEquip($eqIOID)
    {
        $sql = sprintf('
            SELECT 
                donvi.*
            FROM
            (
                SELECT
                        *
                FROM ODanhSachThietBi 
                WHERE IOID = %1$d 
            ) AS thietbi
            LEFT JOIN OKhuVuc AS khuvuc ON thietbi.Ref_MaKhuVuc = khuvuc.IOID
            LEFT JOIN OKhuVuc AS khuvuccha ON khuvuc.lft >= khuvuccha.lft  AND khuvuc.rgt <= khuvuccha.rgt 
            INNER JOIN OThietBi AS khuvucdonvi ON khuvuccha.IOID = khuvucdonvi.Ref_Ma
            INNER JOIN ODonViSanXuat AS donvi ON khuvucdonvi.IFID_M125 = donvi.IFID_M125
            ORDER BY khuvuccha.lft            
        ', $eqIOID);
        return $this->_o_DB->fetchAll($sql);
    }
	public function getLocationByCurrentUser($filterLocStr = '')
	{
        $extWhere = $filterLocStr?sprintf(' AND (MaKhuVuc like "%%%1$s%%" or Ten like "%%%1$s%%")', $filterLocStr):'';

		$sql = sprintf('SELECT OKhuVuc.*, 
			(
                SELECT count(*) 
                FROM OKhuVuc as u 
                inner join qsrecordrights as t on t.IFID = u.IFID_M720 
				    and t.FormCode = "M720"                
                WHERE 
                    u.lft<=OKhuVuc.lft and u.rgt >= OKhuVuc.rgt 
                    and DeptID in (%2$s)
                    and t.UID = %1$d
                    %3$s
            ) as level
			FROM OKhuVuc
			inner join qsrecordrights on qsrecordrights.IFID = OKhuVuc.IFID_M720 
				and qsrecordrights.FormCode = "M720"
			where qsrecordrights.UID = %1$d and OKhuVuc.DeptID in (%2$s) %3$s 
			ORDER BY lft',
			$this->_user->user_id,
			$this->_user->user_dept_list,
            $extWhere);
			//echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
	
}