<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Maintenance_Calendar extends Qss_Model_Abstract
{
	/**
	 * Working with workorders
	 *'
	 * @access  public
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->_user    = Qss_Register::get('userinfo');

	}
	public function getAll($uid,$currentpage = 1 , $limit = 20, $fieldorder, $ordertype='ASC',$groupby,$workcenter,$responseid, $assignid)
	{
		$sqllimit = sprintf(' LIMIT %1$d,%2$d', (($currentpage?$currentpage:1) - 1) * $limit, $limit);
		$orderfields = array();
		if(isset($this->$arrField[$groupby]))
		{
			$orderfields[] = $this->$arrField[$groupby] . ' ' . $ordertype;
		}
		if(isset($this->$arrField[$fieldorder]))
		{
			$orderfields[] = $this->$arrField[$fieldorder] . ' ' . $ordertype;
		}
		else
		{
			$orderfields[] = 'qseventlogs.Date DESC';
		}
		$sqlorder = sprintf('order by %1$s',implode(',', $orderfields));
		$sqlwhere = '';
		if(is_array($a_Filter))
		{
			foreach ($a_Filter as $key=>$val)
			{
				if(isset($this->$arrField[$key]))
				{
					if($key == 2)
					{
						$sqlwhere .= sprintf(' and %1$s like %2$s',$this->$arrField[$key],$this->_o_DB->quote('%'.$val.'%'));
					}
					elseif($key == 3)
					{
						$sqlwhere .= sprintf(' and %1$s = str_to_date(%2$s,\'%%d-%%m-%%Y\') ',$this->$arrField[$key],$this->_o_DB->quote($val));
					}
					elseif($key == 6)
					{
						$sqlwhere .= sprintf(' and qseventlogs.%1$s = %2$d',$this->$arrField[$key],$val);
					}
					else
					{
						$sqlwhere .= sprintf(' and qsevents.%1$s = %2$d',$this->$arrField[$key],$val);
					}
				}
			}
		}
		$sql = sprintf('select distinct qseventlogs.*,qsevents.*,qseventtype.TypeName,
					qsusers.UserName as CreatedName,u.UserName, 
					UNIX_TIMESTAMP(concat(qseventlogs.Date,\' \', qseventlogs.STime)) as Time,
					UNIX_TIMESTAMP(concat(qseventlogs.Date,\' \', qseventlogs.ETime)) as TimeDone,
					case when qsevents.UID = %1$d or qsevents.CreatedID = %1$d then 1 else 2 end as Rights
					from qsevents
					inner join qseventtype on qsevents.EventType = qseventtype.TypeID
					inner join qsusers on qsevents.CreatedID = qsusers.UID
					left join qsusers as u on qsevents.UID = u.UID
					left join qseventtimes on qseventtimes.EventID = qsevents.EventID
					left join qseventlogs on qseventlogs.EventID = qsevents.EventID
					left join qseventmembers on qseventmembers.EventID = qsevents.EventID
					where (qsevents.UID = %1$d or qsevents.CreatedID = %1$d or qseventmembers.UID = %1$d) %4$s 
					%2$s %3$s
					',$uid,$sqlorder,$sqllimit,$sqlwhere);
		//echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
	
	static public function getDateDisplay($date)
	{
		if(!$date)
		{
			return '';
		}
		$today = date('Y-m-d');
		$diff = self::dateDiff($today, $date)/84000;
		$arr = array(0=>'Hôm nay',
		1=>'Hôm qua',
		2=>'Hôm kia',
		-1=>'Ngày mai',
		-2=>'Ngày kia'
		);
		if(isset($arr[$diff]))
		{
			return $arr[$diff];
		}
		else
		{
			return Qss_Lib_Date::mysqltodisplay($date);
		}
		//$yesterday = self::add_date($today,-1);
	}
	protected function dateDiff($dt1,$dt2) {
		$y1 = substr($dt1,0,4);
		$m1 = substr($dt1,5,2);
		$d1 = substr($dt1,8,2);
		$h1 = substr($dt1,11,2);
		$i1 = substr($dt1,14,2);
		$s1 = substr($dt1,17,2);

		$y2 = substr($dt2,0,4);
		$m2 = substr($dt2,5,2);
		$d2 = substr($dt2,8,2);
		$h2 = substr($dt2,11,2);
		$i2 = substr($dt2,14,2);
		$s2 = substr($dt2,17,2);

		$r1=date('U',mktime($h1,$i1,$s1,$m1,$d1,$y1));
		$r2=date('U',mktime($h2,$i2,$s2,$m2,$d2,$y2));
		return ($r1-$r2);
	}
	public function action($id,$action)
	{
		$sql = sprintf('update qseventlogs set Status = %1$d where ELID=%2$d',$action,$id);
		$this->_o_DB->execute($sql);
	}
	public function getCheckRights($uid,$eventid)
	{
		$sql = sprintf('select case when qsevents.UID = %2$d or qsevents.CreatedID = %2$d then 1 else 2 end as Rights
					from qsevents 
					left join qseventmembers on qseventmembers.EventID = qsevents.EventID 	
					where qsevents.EventID=%1$d and (CreatedID = %2$d or qsevents.UID = %2$d or qseventmembers.UID = %2$d)',
		$eventid,$uid);
		$dataSQL =  $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->Rights:0;
	}
	public function delete($eventid)
	{
		$sql = sprintf('delete from qsevents where EventID = %1$d',
		$eventid);
		$this->_o_DB->execute($sql);
	}
	public function deleteRefer($eventid,$ifid,$ioid)
	{
		$sql = sprintf('delete from qseventrefer where EventID = %1$d and IFID = %2$d and IOID = %3$d',
		$eventid,$ifid,$ioid);
		$this->_o_DB->execute($sql);
	}
	public function getEventByLID($elid)
	{
		$sql = sprintf('select * from qseventlogs where ELID = %1$d',$elid);
		return (int) @$this->_o_DB->fetchOne($sql)->EventID;
	}
	public function getEventLogByID($elid)
	{
		$sql = sprintf('select * from qseventlogs where ELID = %1$d',$elid);
		return $this->_o_DB->fetchOne($sql);
	}
	public function deleteLog($eventid,$elid)
	{
		$sql = sprintf('delete from qseventlogs where EventID = %1$d and ELID = %2$d',
		$eventid,$elid);
		$this->_o_DB->execute($sql);
	}
	public function saveNote($elid,$note)
	{
		$sql = sprintf('update qseventlogs set Note = %1$s where ELID = %2$d',
		$this->_o_DB->quote($note),$elid);
		$this->_o_DB->execute($sql);
	}
	public function getCalendar($user,$startdate,$enddate,$workcenter,$responseid,$limit = 1000)
	{
		$limit = $limit?$limit:1000;
		$sqlwhere = '';
		if($workcenter)
		{
			$sqlwhere .= sprintf(' and pbt.Ref_MaDVBT = %1$d',
								$workcenter);
		}
		
		if($responseid)
		{
			$sqlwhere .= sprintf(' and pbt.Ref_NguoiThucHien= %1$d',
								$responseid);
		}
		
		$sqlwhere .= sprintf(' 
			and
			 NgayBatDauDuKien
			 <= %2$s
			and
			 NgayBatDauDuKien
			 >=%1$s'
			,$this->_o_DB->quote($startdate),$this->_o_DB->quote($enddate));

		$sqlwhere .= sprintf(' and pbt.DeptID in (%1$s) ', $this->_user->user_dept_id . ',' . $this->_user->user_dept_list);

//        if(Qss_Lib_System::formSecure('M125'))
//        {
//            $sqlwhere .= sprintf('
//                and (ifnull(pbt.Ref_MaDVBT,0) = 0 or pbt.Ref_MaDVBT in (SELECT IOID FROM ODonViSanXuat
//						inner join qsrecordrights on ODonViSanXuat.IFID_M125 = qsrecordrights.IFID
//						WHERE UID = %1$d))
//            ', $user->user_id);
//        }

        if(Qss_Lib_System::formSecure('M720'))
        {
            $sqlwhere .= sprintf(' 
			    AND (
                    IFNULL(pbt.Ref_MaThietBi, 0) IN (
                        SELECT ODanhSachThietBi.IOID
                        FROM ODanhSachThietBi 
                        INNER JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ODanhSachThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
                        INNER JOIN OKhuVuc AS KhuVucCha ON KhuVucThietBi.lft >=  KhuVucCha.lft AND KhuVucThietBi.rgt <= KhuVucCha.rgt			 
                        INNER JOIN qsrecordrights on KhuVucCha.IFID_M720 = qsrecordrights.IFID 
                        WHERE UID = %1$d
                    )
                    OR IFNULL(pbt.Ref_MaThietBi, 0) = 0
                )
			',$this->_user->user_id);
        }
		
		$sql = sprintf('select
		                pbt.*
						, qsiforms.IFID
						, qsiforms.DepartmentID
						, group_concat(concat(cv.MoTa,\' \',ifnull(cv.BoPhan,\'\')) SEPARATOR \'\n\') as CongViec
						, NgayBatDauDuKien SDate
						, NgayDuKienHoanThanh as EDate
						, Error
						, ErrorMessage
						, ifnull(qsiforms.Status, 1) AS `stepno` 
						, ifnull(qsworkflowsteps.color, \'green bgorange bold\') AS `color` 
						, plbt.LoaiBaoTri as KyHieuLoai
						, qsforms.FormCode AS `formcode`
					from OPhieuBaoTri as pbt
					inner join qsiforms on qsiforms.IFID = pbt.IFID_M759
                    inner join qsforms on qsforms.FormCode = qsiforms.FormCode
                   	inner join qsworkflows on qsworkflows.FormCode=qsforms.FormCode and Actived=1
                    inner join qsworkflowsteps on qsworkflowsteps.StepNo=qsiforms.Status 
						and qsworkflowsteps.WFID=qsworkflows.WFID
					inner join OPhanLoaiBaoTri as plbt on plbt.IOID = pbt.Ref_LoaiBaoTri
					left join OCongViecBTPBT as cv on cv.IFID_M759 = pbt.IFID_M759
					where (ifnull(pbt.IFID_M759, 0) != 0)  %3$s
					group by pbt.IOID
					order by Ref_MaDVBT, pbt.Ref_NguoiThucHien,  NgayBatDau,ifnull(pbt.Ngay,NgayDuKienHoanThanh), pbt.GioBatDau,pbt.GioKetThuc
					limit %2$d'
			,$user->user_id
			,$limit,
			$sqlwhere);
			//echo '<pre>'.$sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
	public function getUsers($user)
	{
		$sql = sprintf('select distinct qsusers.*
					from qsevents
					inner join qsusers on qsevents.CreatedID = qsusers.UID
					left join qseventtimes on qseventtimes.EventID = qsevents.EventID
					left join qseventlogs on qseventlogs.EventID = qsevents.EventID
					left join qseventmembers on qseventmembers.EventID = qsevents.EventID
					where (qsevents.CreatedID = %1$d or qseventmembers.UID = %1$d or qseventlogs.UID = %1$d)
					',$user->user_id);
		//echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
	public function getByParent($id=0)
	{
		$sql = sprintf('select * from qseventtype where ParentID=%1$d',$id);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getAllTypeByFID($fid,$status)
	{
		$sql = sprintf('select qseventtype.* from qseventtype 
						left join qsfactivities on qsfactivities.ETID = qseventtype.TypeID and FormCode = "%1$s" and StepNo = %2$d
						order by FID desc,TypeName',$fid,$status);
		return $this->_o_DB->fetchAll($sql);
	}
	public function adjustWorkOrder($ifid)
	{
		$sql = sprintf('update
 							OPhieuBaoTri AS pbt 
 							INNER JOIN OCongViecBTPBT as cv on cv.IFID_M759 = pbt.IFID_M759 
							set cv.Ngay = pbt.NgayBatDau
							WHERE pbt.IFID_M759 = %1$d and cv.Ngay not between pbt.NgayBatDau and ifnull(pbt.Ngay,pbt.NgayDuKienHoanThanh)'
				,$ifid);
		$this->_o_DB->execute($sql);
		$sql = sprintf('update
 							OPhieuBaoTri AS pbt 
 							INNER JOIN OCongViecBTPBT as cv on cv.IFID_M759 = pbt.IFID_M759 
							set cv.NguoiThucHien = pbt.NguoiThucHien,cv.Ref_NguoiThucHien = pbt.Ref_NguoiThucHien
							WHERE pbt.IFID_M759 = %1$d and ifnull(cv.Ref_NguoiThucHien,0)=0 and ifnull(pbt.Ref_NguoiThucHien,0) != 0'
				,$ifid);
		$this->_o_DB->execute($sql);
	}
}
?>