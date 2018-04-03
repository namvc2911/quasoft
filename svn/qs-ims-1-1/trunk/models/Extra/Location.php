<?php
class Qss_Model_Extra_Location extends Qss_Model_Abstract
{
	/**
	 * Lay toan bo khu vuc root (Level = 1)
	 * @return object 
	 */
	public function getRootLoc()
	{
		$whereDept = '';
	    $user = Qss_Register::get('userinfo');
	    if($user)
	    {
	    	if($user->user_dept_list)
	    	{
	    		$whereDept .= sprintf(' and cm.DeptID in (%1$s)',$user->user_dept_list);
	    	}
	    }

	    if(Qss_Lib_System::formActive('M720')) {
            $whereDept .= sprintf('
                AND (
                        (
                            SELECT count(qsrecordrights.IFID) AS Total
                            FROM OKhuVuc AS TempKhuVuc
                            INNER JOIN qsrecordrights ON qsrecordrights.FormCode = "M720" AND TempKhuVuc.IFID_M720 = qsrecordrights.IFID
                            WHERE TempKhuVuc.lft >= cm.lft AND TempKhuVuc.rgt <= cm.rgt AND UID = %1$d
                        ) > 0
                ) 
            ', $this->_user->user_id);
        }

		$sql = sprintf('
			SELECT cm.*,ifnull(cm.NgungHoatDong, 0) AS `Stop`
			FROM OKhuVuc AS cm
			WHERE IFNULL(Ref_TrucThuoc, 0) = 0 %1$s
			ORDER BY cm.lft
		',$whereDept);

	    //echo '<pre>'; print_r($sql); die;
		return $this->_o_DB->fetchAll($sql);
	}
	
	/**
	 * Lay toan bo khu vuc duoi khu vuc hien tai 1 cap
	 * @param int $locID IOID cua khu vuc cha
	 */
	public function getNextChild($locID)
	{
		// Lay level tiep theo 
		$whereDept = '';
	    $user = Qss_Register::get('userinfo');
	    if($user)
	    {
	    	if($user->user_dept_list)
	    	{
	    		$whereDept .= sprintf(' and cm.DeptID in (%1$s)',$user->user_dept_list);
	    	}
	    }

        if(Qss_Lib_System::formActive('M720')) {
            $whereDept .= sprintf('                                
                AND (
                        (
                            SELECT count(qsrecordrights.IFID) AS Total
                            FROM OKhuVuc AS TempKhuVuc
                            INNER JOIN qsrecordrights ON qsrecordrights.FormCode = "M720" AND TempKhuVuc.IFID_M720 = qsrecordrights.IFID
                            WHERE TempKhuVuc.lft >= cm.lft AND TempKhuVuc.rgt <= cm.rgt AND UID = %1$d
                        ) > 0
                ) 
            ', $this->_user->user_id);
        }
		
		
		// Lay danh sach khu vuc truc thuoc khu vuc va la con ke tiep
		$sql = sprintf('
			SELECT cm.*, ifnull(cm.NgungHoatDong, 0) AS `Stop`
			FROM OKhuVuc AS cm
			WHERE 
			ifnull(cm.Ref_TrucThuoc,0) = %1$d %2$s
			order by cm.lft
		', $locID, $whereDept);

	    // echo '<pre>'; print_r($sql); die;
		return $this->_o_DB->fetchAll($sql);		
	}
	
	public function countNextChild($locID)
	{
	    // Lay level tiep theo
	    $sqlCurrentLoc = sprintf('
			SELECT (
				SELECT
					count(*)
				FROM
					OKhuVuc AS u
				WHERE
					u.lft <= cm.lft
					AND u.rgt >= cm.rgt
			) AS `LEVEL`,
			cm.lft, cm.rgt,
			ifnull(cm.NgungHoatDong, 0) AS `Stop`
			FROM OKhuVuc AS cm
			WHERE
			cm.IOID = %1$d
		', $locID);
	    
	    $dataCurrentLoc = $this->_o_DB->fetchOne($sqlCurrentLoc);
	    $nextLevel      = $dataCurrentLoc?((int)$dataCurrentLoc->LEVEL+1):0;
	    $locLeft        = $dataCurrentLoc?((int)$dataCurrentLoc->lft):0;
	    $locRight       = $dataCurrentLoc?((int)$dataCurrentLoc->rgt):0;
	    
	    // Lay danh sach khu vuc truc thuoc khu vuc va la con ke tiep
	    $sql = sprintf('
			SELECT count(1) AS Total
			FROM OKhuVuc AS cm
			WHERE
			(
				SELECT
					count(*)
				FROM
					OKhuVuc AS u
				WHERE
					u.lft <= cm.lft
					AND u.rgt >= cm.rgt
			) = %1$d
			and (%2$d < cm.lft and cm.rgt < %3$d )
			order by cm.lft
		', $nextLevel, $locLeft, $locRight);
	    return $this->_o_DB->fetchOne($sql);	    
	}
}