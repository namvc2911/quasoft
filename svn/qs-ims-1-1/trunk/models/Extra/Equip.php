<?php
class Qss_Model_Extra_Equip extends Qss_Model_Abstract
{
	const GROUP_EQ_NONE = 'NONE';
	const GROUP_EQ_BY_GROUP = 'EQ_GROUP';
	const GROUP_EQ_BY_TYPE = 'EQ_TYPE';	
	
	//-----------------------------------------------------------------------
	/**
	* Build constructor
	*'
	* @return void
	*/
	public function __construct ()
	{
		parent::__construct();
	}

	public function getHightestLevelEquips($user)
    {
        $where = '';
        if(Qss_Lib_System::formSecure('M720'))
        {
            $where = sprintf(' 
			    AND 
			    (   
			        IFNULL(cm.IOID, 0) IN (
                        SELECT ODanhSachThietBi.IOID
                        FROM ODanhSachThietBi 
                        INNER JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ODanhSachThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
                        INNER JOIN OKhuVuc AS KhuVucCha ON KhuVucThietBi.lft >=  KhuVucCha.lft AND KhuVucThietBi.rgt <= KhuVucCha.rgt
                        INNER JOIN qsrecordrights on KhuVucCha.IFID_M720 = qsrecordrights.IFID 
                        WHERE UID = %1$d
			        )
			        OR IFNULL(cm.IOID, 0) = 0
                )
			', $user->user_id);
        }

        $sql = sprintf('
            SELECT 
                cm.*
                , (select 1 from OCauTrucThietBi as ct where ct.IFID_M705 = cm.IFID_M705 limit 1
                ) as HasComponent
                , IF( (cm.rgt - cm.lft) > 1, 1, 0) AS HasChild
            
            FROM ODanhSachThietBi AS cm
            WHERE  IFNULL(cm.Ref_TrucThuoc, 0) = 0 %1$s
            ORDER BY  cm.lft         
        ', $where);


        // echo '<pre>'; print_r($sql);die;
        return $this->_o_DB->fetchAll($sql);
    }

	/**
	 * Lay loai thiet bi cua cac thiet bi cap cao nhat trong mot khu vuc
	 * @param type $locIOID
	 * @return type
	 */
	public function getEquipTypeByEquipsOfLoc($locIOID = 0)
	{
	    $locIOID = @(int)$locIOID;
        $where   = '';

        if(Qss_Lib_System::formActive('M720')) {
            $where .= sprintf('
                AND KhuVuc.IFID_M720 in (SELECT IFID FROM qsrecordrights WHERE UID = %1$d) 
            ', $this->_user->user_id);
        }

	    $sql = sprintf('
			select 
                if(ifnull(ltb.IOID, 0) <>0, ltb.TenLoai, g.TenLoai)  AS `Name`
    			, if(ifnull(ltb.IOID, 0) <>0, ltb.IFID_M770, g.IFID_M770)   AS `IFID`
    		    , if(ifnull(ltb.IOID, 0) <>0, ltb.IOID, g.IOID)   AS `IOID`
    			, %1$d AS LocationIOID
    			, cm.MaKhuVuc AS Location
            from  ODanhSachThietBi  as cm            
            inner join OLoaiThietBi as g ON   g.IOID = cm.Ref_LoaiThietBi
		    left join OLoaiThietBi AS ltb ON ltb.lft <= g.lft and ltb.rgt >= g.rgt
            left join OKhuVuc as KhuVuc ON cm.Ref_MaKhuVuc = KhuVuc.IOID
            WHERE ifnull(Ref_MaKhuVuc, 0) = %1$d
            and  ifnull(ltb.Ref_TrucThuoc, 0) = 0
            and  ifnull(cm.Ref_TrucThuoc, 0) = 0
            and cm.DeptID in (%2$s) %3$s
            GROUP BY  if(ifnull(ltb.IOID, 0) <>0, ltb.IOID, g.IOID)
			ORDER BY g.lft 
		',$locIOID
	    ,$this->_user->user_dept_list, $where);
		return $this->_o_DB->fetchAll($sql);	
	}

    public function getEquipGroupByEquipsOfLoc($locIOID = 0)
    {
        $locIOID = @(int)$locIOID;
        $where   = '';

        if(Qss_Lib_System::formActive('M720')) {
            $where .= sprintf('
                AND KhuVuc.IFID_M720 in (SELECT IFID FROM qsrecordrights WHERE UID = %1$d) 
            ', $this->_user->user_id);
        }

        $sql = sprintf('
			select 
                g.LoaiThietBi  AS `Name`
    			, g.IFID_M704   AS `IFID`
    		    , g.IOID   AS `IOID`
    			, %1$d AS LocationIOID
    			, cm.MaKhuVuc AS Location
            from  ODanhSachThietBi  as cm
            inner join ONhomThietBi as g ON   g.IOID = cm.Ref_NhomThietBi
            left join OKhuVuc as KhuVuc ON cm.Ref_MaKhuVuc = KhuVuc.IOID
            WHERE ifnull(Ref_MaKhuVuc, 0) = %1$d and  ifnull(cm.Ref_TrucThuoc, 0) = 0 and cm.DeptID in (%2$s) %3$s
            GROUP BY  g.IOID
			ORDER BY g.lft 
		', $locIOID, $this->_user->user_dept_list, $where);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getEquipTypeByGroupAndLoc($groupIOID = 0, $locIOID = 0)
    {
        $locIOID = @(int)$locIOID;
        $where   = '';

        if(Qss_Lib_System::formActive('M720')) {
            $where .= sprintf('
                AND KhuVuc.IFID_M720 in (SELECT IFID FROM qsrecordrights WHERE UID = %1$d) 
            ', $this->_user->user_id);
        }

        $sql = sprintf('
			select 
                if(ifnull(ltb.IOID, 0) <>0, ltb.TenLoai, g.TenLoai)  AS `Name`
    			, if(ifnull(ltb.IOID, 0) <>0, ltb.IFID_M770, g.IFID_M770)   AS `IFID`
    		    , if(ifnull(ltb.IOID, 0) <>0, ltb.IOID, g.IOID)   AS `IOID`
    			, %1$d AS LocationIOID
    			, cm.MaKhuVuc AS Location
            from  ODanhSachThietBi  as cm
            inner join OLoaiThietBi as g ON   g.IOID = cm.Ref_LoaiThietBi
		    left join OLoaiThietBi AS ltb ON ltb.lft <= g.lft and ltb.rgt >= g.rgt
		    left join OKhuVuc as KhuVuc ON cm.Ref_MaKhuVuc = KhuVuc.IOID
            WHERE ifnull(Ref_MaKhuVuc, 0) = %1$d
                and  ifnull(ltb.Ref_TrucThuoc, 0) = 0
                and  ifnull(cm.Ref_TrucThuoc, 0) = 0
                and cm.DeptID in (%2$s)
                and IFNULL(cm.Ref_NhomThietBi, 0) = %3$s
            GROUP BY  if(ifnull(ltb.IOID, 0) <>0, ltb.IOID, g.IOID)
			ORDER BY g.lft 
		', $locIOID, $this->_user->user_dept_list, $groupIOID);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
	
	/**
	 * Dem loai thiet bi cua cac thiet bi cap cao nhat trong mot khu vuc
	 * @param type $locIOID
	 * @return type
	 */
	public function countEquipTypeByEquipsOfLoc($locIOID = 0)
	{
	    $locIOID = @(int)$locIOID;
	
	    $sql = sprintf('
			select
                count(1) AS Total
            from  ODanhSachThietBi  as cm
            left join OLoaiThietBi as g ON   g.IOID = cm.Ref_LoaiThietBi
		    left join OLoaiThietBi AS ltb ON
		      ltb.lft < g.lft and ltb.rgt > g.rgt
            WHERE ifnull(Ref_MaKhuVuc, 0) = %1$d
            GROUP BY  if(ifnull(ltb.IOID, 0) <>0, ltb.IOID, g.IOID)
			ORDER BY ltb.IOID DESC
			limit 1
		', $locIOID);
	
	    return $this->_o_DB->fetchOne($sql);
	}	
	
//	/**
//	 * Lay nhom thiet bi cua cac thiet bi trong mot khu vuc
//	 * @param type $locIOID
//	 * @return type
//	 */
//	public function getEquipGroupByEquipsOfLoc($locIOID)
//	{
//		$sql = sprintf('
//			select
//                g.LoaiThietBi AS `GroupName`
//    			, g.IFID_M704 AS GroupIFID
//    			, g.IOID AS GroupIOID
//    			, %1$d AS LocationIOID
//    			, cm.MaKhuVuc AS Location
//            from  ODanhSachThietBi  as cm
//            left join ONhomThietBi as g ON   g.IOID = cm.Ref_NhomThietBi
//            WHERE Ref_MaKhuVuc = %1$d
//            GROUP BY  Ref_NhomThietBi
//			ORDER BY Ref_NhomThietBi
//		', $locIOID);
//		return $count?$this->_o_DB->fetchOne($sql):$this->_o_DB->fetchAll($sql);
//	}
	
	/**
	 * Dem nhom thiet bi cua cac thiet bi trong mot khu vuc
	 * @param type $locIOID
	 * @return type
	 */
	public function countEquipGroupByEquipsOfLoc($locIOID)
	{
	    $sql = sprintf('
			select
                count(1) AS Total
            from  ODanhSachThietBi  as cm
            left join ONhomThietBi as g ON   g.IOID = cm.Ref_NhomThietBi
            WHERE Ref_MaKhuVuc = %1$d
            GROUP BY  Ref_NhomThietBi
			ORDER BY Ref_NhomThietBi
			limit 1
		', $locIOID, $select, $limit);
	    return $count?$this->_o_DB->fetchOne($sql):$this->_o_DB->fetchAll($sql);
	}	

	/**
	 * Lay thong tin thiet bi theo id
	 * su dung trong module thong tin thiet bi
	 * @todo: chuyen ve cau sql thong thuong ko dung ham getDataset
	 * @param type $locIOID chi lay thiet bi truc thuoc, khong lay tb kvuc con
	 * @param type $orderEq
	 * @param type $count
	 * @param type $inProject 1: co trong project
	 * @return type
	 */
	public function getEquipByEquipTypeAndLocation($equipTypeIOID = 0, $locationIOID = 0)
	{
		$where       = array();
		
		$where[]     = sprintf(' ifnull(cm.Ref_MaKhuVuc, 0) = %1$d', @(int)$locationIOID);
		$where[]     = sprintf(' ifnull(cm.Ref_LoaiThietBi, 0) = %1$d', @(int)$equipTypeIOID);
		
		$sql = sprintf('
			SELECT 
                cm.*
                , (select 1 from OCauTrucThietBi as ct where ct.IFID_M705 = cm.IFID_M705 limit 1
                ) as HasComponent 
			FROM ODanhSachThietBi AS cm
			WHERE %1$s
		', implode(' and ', $where));
		return $this->_o_DB->fetchAll($sql);
	}
	
	// Chi lay equip o lv 1
	public function getEquipByEquipTypeAndLocationNotInProject($equipTypeIOID = 0, $locationIOID = 0)
	{
	    $where   = array();
	    $where[] = sprintf(' ifnull(cm.Ref_MaKhuVuc, 0) = %1$d', @(int)$locationIOID);
	    $where[] = sprintf(' ifnull(cm.Ref_LoaiThietBi, 0) = %1$d', @(int)$equipTypeIOID);
	    $where[] = sprintf(' ifnull(cm.Ref_DuAn, 0) = 0');

        if(Qss_Lib_System::formActive('M720')) {
            $where[] = sprintf('
                KhuVuc.IFID_M720 in (SELECT IFID FROM qsrecordrights WHERE UID = %1$d) 
            ', $this->_user->user_id);
        }


        if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'TrucThuoc')) {
            $where[] = sprintf(' ifnull(cm.Ref_TrucThuoc, 0) = 0');

            $sql = sprintf('
			SELECT
                cm.*
                , (select 1 from OCauTrucThietBi as ct where ct.IFID_M705 = cm.IFID_M705 limit 1
                ) as HasComponent
                , IF( (cm.rgt - cm.lft) > 1, 1, 0) AS HasChild
			FROM ODanhSachThietBi AS cm
			left join OKhuVuc as KhuVuc ON cm.Ref_MaKhuVuc = KhuVuc.IOID
			WHERE %1$s
			order by cm.lft
		', implode(' and ', $where));
        }
        else {
            $sql = sprintf('
			SELECT
                cm.*
                , (select 1 from OCauTrucThietBi as ct where ct.IFID_M705 = cm.IFID_M705 limit 1
                ) as HasComponent
                , 0 AS HasChild
			FROM ODanhSachThietBi AS cm
			left join OKhuVuc as KhuVuc ON cm.Ref_MaKhuVuc = KhuVuc.IOID
			WHERE %1$s
			order by cm.MaThietBi
		  ', implode(' and ', $where));
        }



	    return $this->_o_DB->fetchAll($sql);
	}
	
	public function getProjectByEquipTypeAndLocation($equipTypeIOID = 0, $locationIOID = 0)
	{
	    $where   = array();
	    $where[] = sprintf(' ifnull(cm.Ref_MaKhuVuc, 0) = %1$d', @(int)$locationIOID);
	    $where[] = sprintf(' ifnull(cm.Ref_LoaiThietBi, 0) = %1$d', @(int)$equipTypeIOID);
	    $where[] = sprintf(' ifnull(cm.Ref_DuAn, 0) <> 0');

        if(Qss_Lib_System::formActive('M720')) {
            $where[] = sprintf('
                KhuVuc.IFID_M720 in (SELECT IFID FROM qsrecordrights WHERE UID = %1$d) 
            ', $this->_user->user_id);
        }
	    
	    $sql = sprintf('
			SELECT
                da.*
			FROM ODanhSachThietBi AS cm
	        INNER JOIN ODuAn AS da ON cm.Ref_DuAn = da.IOID
	        left join OKhuVuc as KhuVuc ON cm.Ref_MaKhuVuc = KhuVuc.IOID
			WHERE %1$s
	        GROUP BY cm.Ref_DuAn
		', implode(' and ', $where));
	    return $this->_o_DB->fetchAll($sql);	    
	}
	
	public function countEquipInProject($projectIOID = 0, $locationIOID = 0, $equipTypeIOID = 0)
	{
	    $where   = array();
	    $where[] = sprintf(' ifnull(cm.Ref_MaKhuVuc, 0) = %1$d', @(int)$locationIOID);
	    $where[] = sprintf(' ifnull(cm.Ref_LoaiThietBi, 0) = %1$d', @(int)$equipTypeIOID);
	    $where[] = sprintf(' ifnull(cm.Ref_DuAn, 0) =  %1$d', @(int)$projectIOID);
	    $where[] = sprintf(' ifnull(cm.Ref_TrucThuoc, 0) =  0');
	     
	    $sql = sprintf('
			SELECT
                count(1) AS Total
			FROM ODanhSachThietBi AS cm
			WHERE %1$s
		', implode(' and ', $where));
	    return $this->_o_DB->fetchOne($sql);
	}	
	
	public function countActiveEquipNotInProjectForLocationNode($locationIOID = 0, $equipTypeIOID = 0)
	{
// 	    $where   = array();
// 	    $where[] = sprintf(' ifnull(cm.Ref_MaKhuVuc, 0) = %1$d', @(int)$locationIOID);
// 	    $where[] = sprintf(' ifnull(cm.Ref_LoaiThietBi, 0) = %1$d', @(int)$equipTypeIOID);
// 	    $where[] = sprintf(' ifnull(cm.Ref_DuAn, 0) =  0');
	    
// 	    $sql = sprintf('
// 			SELECT
//                 count(1) AS Total
// 			FROM ODanhSachThietBi AS cm
// 			WHERE %1$s
// 		', implode(' and ', $where));
// 	    return $this->_o_DB->fetchOne($sql);	  

        if($locationIOID) {
            $locSql = ($locationIOID)?sprintf(' select * from OKhuVuc Where IOID = %1$d', $locationIOID):'';
            $locDat = ($locationIOID)?$this->_o_DB->fetchOne($locSql):'';
            $locFil = $locDat?sprintf(' and (khuvuc1.lft >= %1$d and khuvuc1.rgt <= %2$d) ', $locDat->lft, $locDat->rgt):'';
        }
        else {
            $locFil = ' AND IFNULL(thietbi1.Ref_MaKhuVuc, 0) = 0';
        }

            /*
	        $locFil  = ($locationIOID)?sprintf(' and ifnull(thietbi1.Ref_MaKhuVuc, 0) = %1$d ', $locationIOID)
	        : ' and ifnull(thietbi1.Ref_MaKhuVuc,0) = 0 ';
            */

	        $typeSql = ($equipTypeIOID)?sprintf(' select * from OLoaiThietBi Where IOID = %1$d', $equipTypeIOID):'';
	        $typeDat = ($equipTypeIOID)?$this->_o_DB->fetchOne($typeSql):'';
	        $typeFil = $typeDat?sprintf(' and (loaibt.lft >= %1$d and loaibt.rgt <= %2$d) ', $typeDat->lft, $typeDat->rgt):'';
	         
	        $sql = sprintf('
	       SELECT sum(`Total`) AS `Total`
	       FROM
	       (
           SELECT
	           sum(
	               case when
	                   ifnull(khuvuc1.NgungHoatDong, 0) = 0
                       and ifnull(thietbi1.TrangThai, 0) not in (%3$s)
	               then 1
	               else 0 end
	           ) AS `Total`
           FROM ODanhSachThietBi AS thietbi1
           LEFT JOIN OKhuVuc AS khuvuc1 ON thietbi1.Ref_MaKhuVuc = khuvuc1.IOID
	       LEFT JOIN OLoaiThietBi AS loaibt ON loaibt.IOID = thietbi1.Ref_LoaiThietBi
	       /*LEFT JOIN OLoaiThietBi AS loaicon ON loaibt.lft <= loaicon.lft and loaibt.rgt >= loaicon.rgt*/
	       WHERE ifnull(thietbi1.Ref_DuAn, 0) =  0 AND ifnull(thietbi1.Ref_TrucThuoc, 0) =  0 %1$s %2$s
	       GROUP BY loaibt.IOID
	       order by loaibt.lft
           ) AS t
        ', $locFil, $typeFil, implode(',', Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP));
        //echo '<pre>'; print_r($sql); die;
	        return $this->_o_DB->fetchOne($sql);
	}

    public function countActiveEquipNotInProjectForEquipTypeNode($locationIOID = 0, $equipTypeIOID = 0)
    {
// 	    $where   = array();
// 	    $where[] = sprintf(' ifnull(cm.Ref_MaKhuVuc, 0) = %1$d', @(int)$locationIOID);
// 	    $where[] = sprintf(' ifnull(cm.Ref_LoaiThietBi, 0) = %1$d', @(int)$equipTypeIOID);
// 	    $where[] = sprintf(' ifnull(cm.Ref_DuAn, 0) =  0');

// 	    $sql = sprintf('
// 			SELECT
//                 count(1) AS Total
// 			FROM ODanhSachThietBi AS cm
// 			WHERE %1$s
// 		', implode(' and ', $where));
// 	    return $this->_o_DB->fetchOne($sql);

//        if($locationIOID) {
//            $locSql = ($locationIOID)?sprintf(' select * from OKhuVuc Where IOID = %1$d', $locationIOID):'';
//            $locDat = ($locationIOID)?$this->_o_DB->fetchOne($locSql):'';
//            $locFil = $locDat?sprintf(' and (khuvuc1.lft >= %1$d and khuvuc1.rgt <= %2$d) ', $locDat->lft, $locDat->rgt):'';
//        }
//        else {
//            $locFil = ' AND IFNULL(thietbi1.Ref_MaKhuVuc, 0) = 0';
//        }
//
//        /*
//        $locFil  = ($locationIOID)?sprintf(' and ifnull(thietbi1.Ref_MaKhuVuc, 0) = %1$d ', $locationIOID)
//        : ' and ifnull(thietbi1.Ref_MaKhuVuc,0) = 0 ';
//        */
//
//        $typeSql = ($equipTypeIOID)?sprintf(' select * from OLoaiThietBi Where IOID = %1$d', $equipTypeIOID):'';
//        $typeDat = ($equipTypeIOID)?$this->_o_DB->fetchOne($typeSql):'';
//        $typeFil = $typeDat?sprintf(' and (loaibt.lft >= %1$d and loaibt.rgt <= %2$d) ', $typeDat->lft, $typeDat->rgt):'';

        $locFil  = sprintf(' AND IFNULL(thietbi1.Ref_MaKhuVuc, 0) = %1$d ', @(int)$locationIOID);
        $typeSql = ($equipTypeIOID)?sprintf(' select * from OLoaiThietBi Where IOID = %1$d', $equipTypeIOID):'';
        $typeDat = ($equipTypeIOID)?$this->_o_DB->fetchOne($typeSql):'';
        $typeFil = $typeDat?sprintf(' and (loaibt.lft >= %1$d and loaibt.rgt <= %2$d) ', $typeDat->lft, $typeDat->rgt):'';


        $sql = sprintf('
	       SELECT sum(`Total`) AS `Total`
	       FROM
	       (
           SELECT
	           sum(
	               case when
	                   ifnull(khuvuc1.NgungHoatDong, 0) = 0
                       and ifnull(thietbi1.TrangThai, 0) not in (%3$s)
	               then 1
	               else 0 end
	           ) AS `Total`
           FROM ODanhSachThietBi AS thietbi1
           LEFT JOIN OKhuVuc AS khuvuc1 ON thietbi1.Ref_MaKhuVuc = khuvuc1.IOID
	       LEFT JOIN OLoaiThietBi AS loaibt ON loaibt.IOID = thietbi1.Ref_LoaiThietBi
	       /*LEFT JOIN OLoaiThietBi AS loaicon ON loaibt.lft <= loaicon.lft and loaibt.rgt >= loaicon.rgt*/
	       WHERE ifnull(thietbi1.Ref_DuAn, 0) =  0 AND ifnull(thietbi1.Ref_TrucThuoc, 0) =  0 %1$s %2$s
	       GROUP BY loaibt.IOID
	       order by loaibt.lft
           ) AS t
        ', $locFil, $typeFil, implode(',', Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP));
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }


    public function countActiveEquipNotInProjectForEquipGroupNode($locationIOID = 0, $equipGroupIOID = 0)
    {
        $locFil  = sprintf(' AND IFNULL(thietbi1.Ref_MaKhuVuc, 0) = %1$d ', @(int)$locationIOID);
        $typeFil = $equipGroupIOID?sprintf(' AND IFNULL(thietbi1.Ref_NhomThietBi, 0) = %1$d ', $equipGroupIOID):'';


        $sql = sprintf('
	       SELECT sum(`Total`) AS `Total`
	       FROM
	       (
           SELECT
	           sum(
	               case when
	                   ifnull(khuvuc1.NgungHoatDong, 0) = 0
                       and ifnull(thietbi1.TrangThai, 0) not in (%3$s)
	               then 1
	               else 0 end
	           ) AS `Total`
           FROM ODanhSachThietBi AS thietbi1
           LEFT JOIN OKhuVuc AS khuvuc1 ON thietbi1.Ref_MaKhuVuc = khuvuc1.IOID
	       LEFT JOIN ONhomThietBi AS loaibt ON loaibt.IOID = thietbi1.Ref_NhomThietBi
	       /*LEFT JOIN OLoaiThietBi AS loaicon ON loaibt.lft <= loaicon.lft and loaibt.rgt >= loaicon.rgt*/
	       WHERE ifnull(thietbi1.Ref_DuAn, 0) =  0 AND ifnull(thietbi1.Ref_TrucThuoc, 0) =  0 %1$s %2$s
	       GROUP BY loaibt.IOID
	       order by loaibt.lft
           ) AS t
        ', $locFil, $typeFil, implode(',', Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP));
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }
	
	
	public function getEquipByProject($projectIOID = 0, $locationIOID = 0, $equipTypeIOID = 0)
	{
	    $where   = array();
	    $where[] = sprintf(' ifnull(cm.Ref_MaKhuVuc, 0) = %1$d', @(int)$locationIOID);
	    $where[] = sprintf(' ifnull(cm.Ref_LoaiThietBi, 0) = %1$d', @(int)$equipTypeIOID);
	    $where[] = sprintf(' ifnull(cm.Ref_DuAn, 0) =  %1$d', @(int)$projectIOID);
	    $where[] = sprintf(' ifnull(cm.Ref_TrucThuoc, 0) =  0');

        if(Qss_Lib_System::formActive('M720')) {
            $where[] = sprintf('
                 KhuVuc.IFID_M720 in (SELECT IFID FROM qsrecordrights WHERE UID = %1$d) 
            ', $this->_user->user_id);
        }
	     
	    $sql = sprintf('
			SELECT
                cm.*
                , (select 1 from OCauTrucThietBi as ct where ct.IFID_M705 = cm.IFID_M705 limit 1
                ) as HasComponent
			    , IF( (cm.rgt - cm.lft) > 1, 1, 0) AS HasChild
			FROM ODanhSachThietBi AS cm
			LEFT JOIN OKhuVuc AS KhuVuc ON cm.Ref_MaKhuVuc = KhuVuc.IOID
			WHERE %1$s
		', implode(' and ', $where));
	    return $this->_o_DB->fetchAll($sql);	    
	}
	
	/**
	 * Lay thong tin thiet bi theo id
	 * su dung trong module thong tin thiet bi
	 * @todo: chuyen ve cau sql thong thuong ko dung ham getDataset
	 * @param type $locIOID chi lay thiet bi truc thuoc, khong lay tb kvuc con
	 * @param type $orderEq
	 * @param type $count
	 * @return type
	 */
	public function countEquipByEquipTypeAndLocation($eqTypeIOID = 0,$locIOID = 0)
	{
	    $where   = array();
	    $where[] = sprintf(' ifnull(cm.Ref_MaKhuVuc, 0) = %1$d', @(int)$locIOID);
	    $where[] = sprintf(' ifnull(cm.Ref_LoaiThietBi, 0) = %1$d', @(int)$eqTypeIOID);
	    
	    $sql     = sprintf('
			SELECT count(1) AS Total
			FROM ODanhSachThietBi AS cm
			WHERE %1$s
		', implode(' and ', $where));
	    return $this->_o_DB->fetchOne($sql);
	}	
	
	public function getRootComponent($eqIFID)
	{
	    // Lay danh sach khu vuc truc thuoc khu vuc va la con ke tiep
	    $sql = sprintf('
			SELECT cm.*,
	           (SELECT 1 
				FROM OCauTrucThietBi as u1
				where u1.IFID_M705 = %1$d 
				and u1.Ref_TrucThuoc = cm.IOID
				and u1.IOID != cm.IOID
				LIMIT 1) AS HasChild
			FROM OCauTrucThietBi AS cm
			WHERE
            ifnull(cm.Ref_TrucThuoc, 0) = 0 
			AND cm.IFID_M705 = %1$d
			order by cm.lft
		', $eqIFID);
	    //echo $sql; die;
	    return $this->_o_DB->fetchAll($sql);
	}	
		
	/**
	 * Lay toan bo phan con cua mot bo phan trong thiet bi (Lay cap tiep theo)
	 * @param type $eqIFID
	 * @param type $parentLft
	 * @param type $parentRgt
	 * @return type
	 */
	public function getChildComponent($eqIFID, $parentLevel = 0, $nodeioid =0 )
	{
		$parentLft = 0;
		$parentRgt = 0;
		if($nodeioid)
		{
			$sql = sprintf('select * from OCauTrucThietBi where IOID = %1$d',$nodeioid);
			$dataSQL = $this->_o_DB->fetchOne($sql);
			if($dataSQL)
			{
				$parentLft = $dataSQL->lft;
				$parentRgt = $dataSQL->rgt;
			}
		}
		$sqlTemp = 
			(($parentLft <= $parentRgt) && $parentLft != 0 &&  $parentRgt != 0)?
			sprintf(' and (%1$d < cm.lft and cm.rgt < %2$d )', $parentLft, $parentRgt):'';
		
		// Lay danh sach khu vuc truc thuoc khu vuc va la con ke tiep
		$sql = sprintf('
			SELECT *,
				(SELECT 1 
				FROM OCauTrucThietBi as u1
				where u1.IFID_M705 = %2$d 
				and u1.Ref_TrucThuoc = cm.IOID
				and u1.IOID != cm.IOID
				LIMIT 1) AS HasChild
			FROM OCauTrucThietBi AS cm
			WHERE 
			cm.IFID_M705 = %2$d
			and ifnull(cm.Ref_TrucThuoc,0) = %1$d
			order by cm.lft
		', $nodeioid, $eqIFID);
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);	
	}
	
	
	public function getChildEquips($nodeIFID, $parentLevel = 0, $nodeioid =0)
	{
	    // Lay danh sach khu vuc truc thuoc khu vuc va la con ke tiep
	    $sql = sprintf('
			SELECT 
                cm.*
                ,ct.IOID as HasComponent
                ,IF( (cm.rgt - cm.lft) > 1, 1, 0) as HasChild
			FROM ODanhSachThietBi AS cm
	        LEFT JOIN OCauTrucThietBi as ct ON cm.IFID_M705 = ct.IFID_M705	       
			WHERE
            ifnull(cm.Ref_TrucThuoc,0) = %2$d
	        group by cm.IFID_M705
			order by cm.lft
		', ($parentLevel + 1), $nodeioid, $nodeIFID);
	    return $this->_o_DB->fetchAll($sql);	    
	}	
	
	/**
	 * Láº¥y káº¿ hoáº¡ch nháº­t trÃ¬nh mÃ¡y mÃ³c Ä‘Æ°á»£c cÃ i Ä‘áº·t trong loáº¡i thiáº¿t bá»‹
	 * @param type $eqTypeIOID
	 * @param type $periodIOID
	 */
	/*public function getDailyRecordPlanByEquipType($eqIOID = 0, $refPeriodIOID = '')
	{
		$whereArr = array();
		$where    = '';
		
		if($eqIOID)
		{
			$whereArr[] = sprintf('dstb.IOID = %1$d', $eqIOID);
		}
		
		if($refPeriodIOID)
		{
			$whereArr[] = sprintf('cshd.Ref_Ky = %1$d', $refPeriodIOID);
		}
		
		// where
		$where .= count($whereArr)?sprintf('WHERE %1$s', implode(' and ', $whereArr)):'';
		
		$sql = sprintf('
			SELECT cshd.*
			FROM OLoaiThietBi AS ltb
			RIGHT JOIN OChiSoHoatDongTB AS cshd ON ltb.`IFID_M770` = cshd.`IFID_M770`
			LEFT JOIN ODanhSachThietBi AS dstb ON ltb.`IOID` = dstb.`Ref_LoaiThietBi`
			%1$s
		', $where);
		return $this->_o_DB->fetchAll($sql);
	}*/
	/**
	 * Láº¥y loai thiet bi cua thiáº¿t bá»‹ khÃ´ng thuá»™c khu vá»±c nÃ o cáº£
	 */
	public function getEquipTypeOfEquipNotInAnyWhere()
	{
	    $sql = sprintf('
	        SELECT
	           ltb.IOID AS IOID,
	           ltb.TenLoai AS Name,
	           ltb.IFID_M770 AS IFID
	        FROM ODanhSachThietBi AS dstb
	        LEFT JOIN OLoaiThietBi AS ltb ON dstb.Ref_LoaiThietBi = ltb.IOID
	        WHERE ifnull(dstb.Ref_MaKhuVuc, 0) = 0
	        GROUP BY ltb.IOID
	        order by ltb.lft
        ');
	    return $this->_o_DB->fetchAll($sql);
	}

    public function getEquipGroupOfEquipNotInAnyWhere()
    {
        $sql = sprintf('
	        SELECT
	           ltb.IOID AS IOID,
	           ltb.LoaiThietBi AS Name,
	           ltb.IFID_M704 AS IFID
	        FROM ODanhSachThietBi AS dstb
	        LEFT JOIN ONhomThietBi AS ltb ON dstb.Ref_NhomThietBi = ltb.IOID
	        WHERE ifnull(dstb.Ref_MaKhuVuc, 0) = 0
	        GROUP BY ltb.IOID
	        order by ltb.lft
        ');
        return $this->_o_DB->fetchAll($sql);
    }
	
	public function getChildEquipType($locIOID, $equipTypeIOID, $parentLevel)
	{
        $retval = array();
        $where  = '';

        if(Qss_Lib_System::formActive('M720')) {
            $where .= sprintf('
                AND KhuVuc.IFID_M720 in (SELECT IFID FROM qsrecordrights WHERE UID = %1$d) 
            ', $this->_user->user_id);
        }


        // Lấy thiết bị trực thuộc kế cận
        $sql = sprintf('
            SELECT 
                LoaiTBCon.IOID AS `IOID`,
                LoaiTBCon.TenLoai AS `Name`,
                LoaiTBCon.IFID_M770 AS `IFID`,	   
                %2$d AS LocationIOID,
                LoaiTBCon.lft
            FROM OLoaiThietBi AS LoaiTBCon
            WHERE IFNULL(LoaiTBCon.Ref_TrucThuoc, 0) = %1$d
            ORDER BY LoaiTBCon.lft
        ', $equipTypeIOID, $locIOID);
        // echo '<pre>'; print_r($sql); die;
        $dat    = $this->_o_DB->fetchAll($sql);

        foreach ($dat as $item)
        {
            $sql1 = sprintf('
                SELECT COUNT(1) AS Total
                FROM OLoaiThietBi AS LoaiTBCha
                INNER JOIN OLoaiThietBi AS LoaiTBCon ON LoaiTBCon.lft >= LoaiTBCha.lft AND LoaiTBCon.rgt <= LoaiTBCha.rgt
                INNER JOIN ODanhSachThietBi AS TBCon ON LoaiTBCon.IOID = TBCon.Ref_LoaiThietBi
                LEFT JOIN OKhuVuc AS KhuVuc ON TBCon.Ref_MaKhuVuc = KhuVuc.IOID
                WHERE IFNULL(LoaiTBCha.IOID, 0) = %1$d AND IFNULL(TBCon.Ref_TrucThuoc,0) = 0 AND TBCon.DeptID IN (%2$s)
                    AND IFNULL(TBCon.Ref_MaKhuVuc, 0) = %3$d %4$s
                GROUP BY LoaiTBCha.IOID
            ', $item->IOID, $this->_user->user_dept_list, $locIOID, $where);

            //echo '<pre>'; print_r($sql1);
            $dat1 = $this->_o_DB->fetchOne($sql1);

            if($dat1 && $dat1->Total > 0) {
                $retval[] = $item;
            }
        }
        //die;
        return $retval;
	}
	
	/**
	 * Ä�áº¿m sá»‘ lÆ°á»£ng thiáº¿t bá»‹ trong khu vá»±c
	 * @param unknown $locationIOID
	 * @param number $equipTypeIOID
	 */
	public function countEquipByLocation($locationIOID)
	{
	    $locSql  = ($locationIOID)?sprintf(' select * from OKhuVuc Where IOID = %1$d', $locationIOID):'';
	    $locDat  = ($locationIOID)?$this->_o_DB->fetchOne($locSql):'';
	    $locFil  = $locDat?sprintf(' and (khuvuc1.lft >= %1$d and khuvuc1.rgt <= %2$d) ', $locDat->lft, $locDat->rgt):'';
	     
	    $sql = sprintf('
	       SELECT sum(`NotActive`) AS `NotActive`, sum(`Active`) AS `Active`
	       FROM
	       (	        
           SELECT
	           sum(
	               case when
	                   ifnull(khuvuc1.NgungHoatDong, 0) = 1
                       or ifnull(thietbi1.TrangThai, 0) IN (%2$s)
	               then 1
	               else 0 end
	           ) AS `NotActive`,
	           sum(
	               case when
	                   ifnull(khuvuc1.NgungHoatDong, 0) = 0
                       and ifnull(thietbi1.TrangThai, 0) NOT IN (%2$s)
	               then 1
	               else 0 end
	           ) AS `Active`
           FROM ODanhSachThietBi AS thietbi1
           LEFT JOIN OKhuVuc AS khuvuc1 ON thietbi1.Ref_MaKhuVuc = khuvuc1.IOID
	       WHERE ifnull(thietbi1.Ref_TrucThuoc, 0) = 0  %1$s 
	       GROUP BY khuvuc1.IOID
           ) AS t	        
        ', $locFil, implode(',', Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP));
	    return $this->_o_DB->fetchOne($sql);
	}	
	
	/**
	 * Ä�áº¿m sá»‘ lÆ°á»£ng thiáº¿t bá»‹ theo loai thiet bi
	 * @param unknown $locationIOID
	 * @param number $equipTypeIOID
	 */
	public function countEquipByEquipType($equipTypeIOID, $locationIOID = 0)
	{
	    $locFil  = ($locationIOID)?sprintf(' and ifnull(thietbi1.Ref_MaKhuVuc, 0) = %1$d ', $locationIOID)
	       : ' and ifnull(thietbi1.Ref_MaKhuVuc,0) = 0 ';
	    $typeSql = ($equipTypeIOID)?sprintf(' select * from OLoaiThietBi Where IOID = %1$d', $equipTypeIOID):'';
	    $typeDat = ($equipTypeIOID)?$this->_o_DB->fetchOne($typeSql):'';
	    $typeFil = $typeDat?sprintf(' and (loaibt.lft >= %1$d and loaibt.rgt <= %2$d) ', $typeDat->lft, $typeDat->rgt):'';
	    
	    $sql = sprintf('
	       SELECT sum(`NotActive`) AS `NotActive`, sum(`Active`) AS `Active`
	       FROM
	       (
           SELECT
	           sum(
	               case when
	                   ifnull(khuvuc1.NgungHoatDong, 0) = 1
                       or ifnull(thietbi1.TrangThai, 0) IN (%3$s)

	               then 1
	               else 0 end
	           ) AS `NotActive`,
	           sum(
	               case when
	                   ifnull(khuvuc1.NgungHoatDong, 0) = 0

                       and ifnull(thietbi1.TrangThai, 0) NOT IN (%3$s)
	               then 1
	               else 0 end
	           ) AS `Active`
           FROM ODanhSachThietBi AS thietbi1
           LEFT JOIN OKhuVuc AS khuvuc1 ON thietbi1.Ref_MaKhuVuc = khuvuc1.IOID
	       LEFT JOIN OLoaiThietBi AS loaibt ON loaibt.IOID = thietbi1.Ref_LoaiThietBi
	       WHERE ifnull(thietbi1.Ref_TrucThuoc, 0) = 0 %1$s %2$s
	       GROUP BY loaibt.IOID
           ) AS t
        ', $locFil, $typeFil, implode(',', Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP));
	    // echo '<pre>'; print_r($sql); die;
	    return $this->_o_DB->fetchOne($sql);
	}

    public function countEquipByEquipGroup($equipGroupIOID, $locationIOID = 0)
    {
        $locFil  = ($locationIOID)?sprintf(' and ifnull(thietbi1.Ref_MaKhuVuc, 0) = %1$d ', $locationIOID)
            : ' and ifnull(thietbi1.Ref_MaKhuVuc,0) = 0 ';
        $typeFil = $equipGroupIOID?sprintf(' and loaibt.IOID = %1$d ', $equipGroupIOID):'';

        $sql = sprintf('
	       SELECT sum(`NotActive`) AS `NotActive`, sum(`Active`) AS `Active`
	       FROM
	       (
           SELECT
	           sum(
	               case when
	                   ifnull(khuvuc1.NgungHoatDong, 0) = 1
                       or ifnull(thietbi1.TrangThai, 0) IN (%3$s)

	               then 1
	               else 0 end
	           ) AS `NotActive`,
	           sum(
	               case when
	                   ifnull(khuvuc1.NgungHoatDong, 0) = 0

                       and ifnull(thietbi1.TrangThai, 0) NOT IN (%3$s)
	               then 1
	               else 0 end
	           ) AS `Active`
           FROM ODanhSachThietBi AS thietbi1
           LEFT JOIN OKhuVuc AS khuvuc1 ON thietbi1.Ref_MaKhuVuc = khuvuc1.IOID
	       LEFT JOIN ONhomThietBi AS loaibt ON loaibt.IOID = thietbi1.Ref_NhomThietBi
	       WHERE ifnull(thietbi1.Ref_TrucThuoc, 0) = 0 %1$s %2$s
	       GROUP BY loaibt.IOID
           ) AS t
        ', $locFil, $typeFil, implode(',', Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP));
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }

	public function countChildOfEquip($equipIOID)
	{
	    $sql = sprintf('
	        SELECT count(1) AS Total
	        FROM ODanhSachThietBi AS thietbi1
	        WHERE Ref_TrucThuoc = %1$d
        ', $equipIOID);
	    // echo '<pre>'; print_r($sql); die;
	    $dataSql = $this->_o_DB->fetchOne($sql);
	    return $dataSql?$dataSql->Total:0;
	}
	
}