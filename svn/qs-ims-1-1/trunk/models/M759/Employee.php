<?php
class Qss_Model_M759_Employee extends Qss_Model_Abstract
{
    public function getWorkCenter($startdate,$enddate)
    {
    	$sqlwhere = sprintf('  NgayBatDauDuKien <= %2$s
								and
			 					NgayBatDauDuKien >=%1$s'
					,$this->_o_DB->quote($startdate)
					,$this->_o_DB->quote($enddate));
		if(Qss_Lib_System::formSecure('M125'))
		{
			$sqlwhere .= sprintf(' and ODonViSanXuat.IFID_M125 in (select IFID from qsrecordrights where FormCode = "M125" and UID=%1$d)'
					,$this->_user->user_id);
		}
        $sql = sprintf('
			SELECT
				ODonViSanXuat.*,
				count(ifnull(OPhieuBaoTri.IOID,1)) as TongSo,
				sum(case when OPhieuBaoTri.Ref_NguoiThucHien is null then 0 else 1 end) as DaGiao,
				NgayBatDauDuKien
			FROM ODonViSanXuat
			LEFT JOIN OPhieuBaoTri ON OPhieuBaoTri.Ref_MaDVBT = ODonViSanXuat.IOID
			WHERE %1$s
			group by ODonViSanXuat.IOID,NgayBatDauDuKien'
            /* ORDER BY */
            , $sqlwhere);
        return $this->_o_DB->fetchAll($sql);
    }
	public function getCountWOByEmployee($startdate,$enddate,$workcenter  = 0)
    {
    	$select = '';
    	if(Qss_Lib_System::fieldActive('OPhieuBaoTri', 'ThoiGianDuKien'))
    	{
    		$select = 'sum(ifnull(ThoiGianDuKien,0)) as ThoiGianDuKien';
    	}
    	else 
    	{
    		$select = '0 as ThoiGianDuKien';
    	}
    	$sqlwhere = sprintf('  NgayBatDauDuKien <= %2$s
								and
			 					NgayBatDauDuKien >=%1$s'
					,$this->_o_DB->quote($startdate)
					,$this->_o_DB->quote($enddate));
    	if($workcenter)
    	{
    		$sqlwhere .= sprintf(' and Ref_MaDVBT =%1$d'
					,$workcenter);
    	}
    	if(Qss_Lib_System::formSecure('M125'))
		{
			$sqlwhere .= sprintf(' and ODonViSanXuat.IFID_M125 in (select IFID from qsrecordrights where FormCode = "M125" and UID=%1$d)'
					,$this->_user->user_id);
		}
        
        $sql = sprintf('
			SELECT
				OPhieuBaoTri.Ref_NguoiThucHien,
				OPhieuBaoTri.NguoiThucHien,
				OPhieuBaoTri.NgayBatDauDuKien,
				count(*) as SoLuong,
				%2$s
			FROM OPhieuBaoTri
			inner join ODonViSanXuat on ODonViSanXuat.IOID =OPhieuBaoTri.Ref_MaDVBT 
			WHERE %1$s
			group by OPhieuBaoTri.Ref_NguoiThucHien,OPhieuBaoTri.NgayBatDauDuKien'
            /* ORDER BY */
            , $sqlwhere
            , $select);
        return $this->_o_DB->fetchAll($sql);
    }
	public function getEmployee($workcenter  = 0)
    {
    	$sqlwhere = '';
   		if($workcenter)
    	{
    		$sqlwhere = sprintf(' where ODonViSanXuat.IOID =%1$d'
					,$workcenter);
    	}
    	if(Qss_Lib_System::formSecure('M125'))
		{
			$sqlwhere .= sprintf(' and ODonViSanXuat.IFID_M125 in (select IFID from qsrecordrights where FormCode = "M125" and UID=%1$d)'
					,$this->_user->user_id);
		}
    	$sql = sprintf('
			SELECT
				*
			FROM ONhanVien
			inner join ODonViSanXuat on ODonViSanXuat.IFID_M125=ONhanVien.IFID_M125  
			%1$s
			order by ODonViSanXuat.IOID, ONhanVien.TenNV',
    	$sqlwhere);
        return $this->_o_DB->fetchAll($sql);
    }
	public function getWorkOrderByDate($eid,$date)
    {
    	$sqlwhere = '';
     	$sql = sprintf('
			SELECT
				*
			FROM OPhieuBaoTri
			where Ref_NguoiThucHien = %1$d and NgayBatDauDuKien = %2$s
			order by SoPhieu',
    	$eid,
    	$this->_o_DB->quote($date));
        return $this->_o_DB->fetchAll($sql);
    }
	public function getUnassignedWO($date = '')
    {
    	$sqlwhere = '';
    	if($date != '')
    	{
    		$sqlwhere = sprintf(' and NgayBatDauDuKien = %1$s',$this->_o_DB->quote($date));
    	}
     	$sql = sprintf('
			SELECT
				*
			FROM OPhieuBaoTri
			inner join qsiforms on qsiforms.IFID = OPhieuBaoTri.IFID_M759  
			where qsiforms.Status = 1 and ifnull(Ref_NguoiThucHien,0) = 0 
			%1$s
			order by NgayBatDauDuKien',
     	$sqlwhere);
        return $this->_o_DB->fetchAll($sql);
    }
    
	public function getUnassignedTask($date = '')
    {
    	$sqlwhere = '';
    	if($date != '')
    	{
    		$sqlwhere = sprintf(' and NgayBatDauDuKien = %1$s',$this->_o_DB->quote($date));
    	}
     	$sql = sprintf('
			SELECT
				OPhieuBaoTri.*,OCongViecBTPBT.*,OCongViecBTPBT.IOID as CVIOID
			FROM OPhieuBaoTri
			inner join OCongViecBTPBT on OCongViecBTPBT.IFID_M759 =  OPhieuBaoTri.IFID_M759
			inner join qsiforms on qsiforms.IFID = OPhieuBaoTri.IFID_M759  
			where qsiforms.Status = 1 and ifnull(OCongViecBTPBT.Ref_NguoiThucHien,0) = 0 
			%1$s
			order by NgayBatDauDuKien',
     	$sqlwhere);
        return $this->_o_DB->fetchAll($sql);
    }
 	public function getTaskWorkCenter($startdate,$enddate)
    {
    	$sqlwhere = sprintf('  NgayBatDauDuKien <= %2$s
								and
			 					NgayBatDauDuKien >=%1$s'
					,$this->_o_DB->quote($startdate)
					,$this->_o_DB->quote($enddate));
		if(Qss_Lib_System::formSecure('M125'))
		{
			$sqlwhere .= sprintf(' and ODonViSanXuat.IFID_M125 in (select IFID from qsrecordrights where FormCode = "M125" and UID=%1$d)'
					,$this->_user->user_id);
		}
        $sql = sprintf('
			SELECT
				ODonViSanXuat.*,
				count(ifnull(OCongViecBTPBT.IOID,1)) as TongSo,
				sum(case when OCongViecBTPBT.Ref_NguoiThucHien is null then 0 else 1 end) as DaGiao,
				NgayBatDauDuKien
			FROM ODonViSanXuat
			LEFT JOIN OPhieuBaoTri ON OPhieuBaoTri.Ref_MaDVBT = ODonViSanXuat.IOID
			left join OCongViecBTPBT on OCongViecBTPBT.IFID_M759 =  OPhieuBaoTri.IFID_M759
			WHERE %1$s
			group by ODonViSanXuat.IOID,NgayBatDauDuKien'
            /* ORDER BY */
            , $sqlwhere);
        return $this->_o_DB->fetchAll($sql);
    }
	public function getCountTaskByEmployee($startdate,$enddate,$workcenter  = 0)
    {
    	$select = '';
    	if(Qss_Lib_System::fieldActive('OCongViecBTPBT', 'ThoiGianDuKien'))
    	{
    		$select = 'sum(ifnull(OCongViecBTPBT.ThoiGianDuKien,0)) as ThoiGianDuKien';
    	}
    	else 
    	{
    		$select = '0 as ThoiGianDuKien';
    	}
    	$sqlwhere = sprintf('  NgayBatDauDuKien <= %2$s
								and
			 					NgayBatDauDuKien >=%1$s'
					,$this->_o_DB->quote($startdate)
					,$this->_o_DB->quote($enddate));
    	if($workcenter)
    	{
    		$sqlwhere .= sprintf(' and Ref_MaDVBT =%1$d'
					,$workcenter);
    	}
    	if(Qss_Lib_System::formSecure('M125'))
		{
			$sqlwhere .= sprintf(' and ODonViSanXuat.IFID_M125 in (select IFID from qsrecordrights where FormCode = "M125" and UID=%1$d)'
					,$this->_user->user_id);
		}
        
        $sql = sprintf('
			SELECT
				OCongViecBTPBT.Ref_NguoiThucHien,
				OCongViecBTPBT.NguoiThucHien,
				OPhieuBaoTri.NgayBatDauDuKien,
				count(*) as SoLuong,
				%2$s
			FROM OPhieuBaoTri
			inner join OCongViecBTPBT on OCongViecBTPBT.IFID_M759 = OPhieuBaoTri.IFID_M759
			inner join ODonViSanXuat on ODonViSanXuat.IOID =OPhieuBaoTri.Ref_MaDVBT 
			WHERE %1$s
			group by OCongViecBTPBT.Ref_NguoiThucHien,OPhieuBaoTri.NgayBatDauDuKien'
            /* ORDER BY */
            , $sqlwhere
            , $select);
        return $this->_o_DB->fetchAll($sql);
    }
	public function getTaskByDate($eid,$date)
    {
    	$sqlwhere = '';
     	$sql = sprintf('
			SELECT
				OPhieuBaoTri.*,OCongViecBTPBT.*,OCongViecBTPBT.IOID as CVIOID
			FROM OPhieuBaoTri
			inner join OCongViecBTPBT on OCongViecBTPBT.IFID_M759 =  OPhieuBaoTri.IFID_M759
			where OCongViecBTPBT.Ref_NguoiThucHien = %1$d and NgayBatDauDuKien = %2$s
			order by SoPhieu',
    	$eid,
    	$this->_o_DB->quote($date));
        return $this->_o_DB->fetchAll($sql);
    }   
}