<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Event extends Qss_Model_Abstract
{

	//public static $arrField = array(1=>'EventType',2=>'Title',3=>'Date',5=>'UID',4=>'CreatedID',6=>'Status');
	//public static $arrGroup = array(1=>'TypeName',2=>'Title',3=>'Date',5=>'UserName',4=>'CreatedName',6=>'Status');
	public $dataField = 'TypeID';
	public $arrField = array(1=>'TypeName');
	public $arrFieldName = array(1=>'Loại công việc');
	public $arrFieldType = array(1=>1);
	/**
	 * Working with all design of form that user acess via module management
	 *'
	 * @access  public
	 */
	public function __construct ()
	{
		parent::__construct();

	}
	public function getAll($uid,$currentpage = 1 , $limit = 20, $fieldorder, $ordertype='ASC',$groupby,$a_Filter)
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
	public function countAll($uid,$currentpage = 1 , $limit = 20, $fieldorder, $ordertype='ASC',$a_Filter)
	{
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

		$sql = sprintf('select count(distinct qseventlogs.ELID) as count
					from qsevents
					left join qseventtimes on qseventtimes.EventID = qsevents.EventID
					left join qseventlogs on qseventlogs.EventID = qsevents.EventID
					left join qseventmembers on qseventmembers.EventID = qsevents.EventID
					where (qsevents.UID = %1$d or qsevents.CreatedID = %1$d or qseventmembers.UID = %1$d) %2$s 
					',$uid,$sqlwhere);

		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->count:0;
	}
	public function getAllType($id=0)
	{
		$sql = sprintf('select * from qseventtype where TypeID!=%1$d',$id);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getByID($id)
	{
		$sql = sprintf('select * from qsevents where EventID = %1$d',$id);
		return $this->_o_DB->fetchOne($sql);
	}
	public function getType($id)
	{
		$sql = sprintf('select * from qseventtype where TypeID = %1$d',$id);
		return $this->_o_DB->fetchOne($sql);
	}
	public function saveType($params)
	{
		$id = $params['id'];
		$data = array('TypeName'=>$params['name'],
						'File'=>$params['szFile']);
		if($id)
		{
			$sql = sprintf('update qseventtype set %1$s where TypeID = %2$d',
				$this->arrayToUpdate($data),$id);	
		}
		else 
		{
			$sql = sprintf('insert into qseventtype%1$s',$this->arrayToInsert($data));
		}
		return $this->_o_DB->execute($sql);
	}
	public function deleteType($id)
	{
		$sql = sprintf('delete from qseventtype where TypeID = %1$d',$id);
		return $this->_o_DB->execute($sql);
	}
	public function saveLog($params)
	{
		$id = isset($params['elid'])?$params['elid']:0;
		$data = array(
							'UID'=>$params['uid'],
							'IFID'=>(int) @$params['ifid'],
							'EventID'=>(int)@$params['eventid'],
							'StepNo'=>(int) @$params['stepno'],
							'ETID'=>$params['eventtype'],
							'Date'=>Qss_Lib_Date::displaytomysql($params['date']),
							'STime'=>$params['stime'],
							'ETime'=>$params['etime'],
							'Status'=>(int) @$params['status'],
							'Note'=>$params['note']					
						  );
		if($id)
		{
			// cập nhật 
			$sql = sprintf('update qseventlogs set %1$s where ELID = %2$d',
					$this->arrayToUpdate($data),$id);
			return $this->_o_DB->execute($sql);
		}
		else 
		{
			// Thêm mới
			$sql = sprintf('insert into qseventlogs%1$s',$this->arrayToInsert($data));
			return $this->_o_DB->execute($sql);
		}
	}
	public function save($params)
	{
		$id = $params['id'];
		$data = array(
				'Title'=>$params['name'],
				'Description'=>$params['desc'],
				'EventType'=>$params['type'],
				'Alarm'=>$params['alarm'],
				'isRepeat'=>@$params['repeat'],
				'CreatedID'=>$params['createdid'],
				'Public'=>!@$params['public']
		);
		if($id)
		{
			$sql = sprintf('update qsevents set %2$s where EventID=%1$d',$id,$this->arrayToUpdate($data));
			$this->_o_DB->execute($sql);
		}
		else
		{
			$sql = sprintf('insert into qsevents%1$s',$this->arrayToInsert($data));
			//echo $sql;die;
			$id = $this->_o_DB->execute($sql);
		}
		if($id)
		{
			if(isset($params['times']) && is_array($params['times']))
			{
				$sql = sprintf('delete from qseventtimes where EventID = %1$d',	$id);
				$this->_o_DB->execute($sql);
				foreach ($params['times'] as $item)
				{
					$arrTimes = explode(',', $item);
					if(count($arrTimes) >= 8)
					{
						$dataTime = array(
                                                                'EventID'=>$id,
                                                                'Type'=>$arrTimes[0],
                                                                'SDate'=>Qss_Lib_Date::displaytomysql(trim($arrTimes[3])),
                                                                'EDate'=>Qss_Lib_Date::displaytomysql(trim($arrTimes[4])),
                                                                'STime'=>$arrTimes[1],
                                                                'ETime'=>$arrTimes[2],
                                                                'Day'=>$arrTimes[6],
                                                                'WDay'=>$arrTimes[5],
                                                                'Month'=>$arrTimes[7],
                                                                'Interval'=>$arrTimes[8]
						);
						$sql = sprintf('replace into qseventtimes%1$s',$this->arrayToInsert($dataTime));
						$this->_o_DB->execute($sql);
						/*if ($arrTimes[0] == 0)
						{
							if (!$arrTimes[4])
							{
								$arrTimes[4] = $arrTimes[3];
							}
							$sdate = date_create($arrTimes[3]);
							$edate = date_create($arrTimes[4]);
							$i = 0;
							while ($sdate <= $edate && $arrTimes[3] && $arrTimes[4] )
							{
								if ($i == 30)
								{
									break;
								}
								//save to log
								$dataLog = array(
										'`EventID`'=>$id,
										'`Date`'=>$sdate->format('Y-m-d'),
										'`STime`'=>trim($arrTimes[1]),
										'`ETime`'=>trim($arrTimes[2]),
										'`Status`'=>@$arrTimes[8]
								);
								$sql = sprintf('insert ignore into qseventlogs%1$s',$this->arrayToInsert($dataLog));
								$logid = $this->_o_DB->execute($sql);
								$sdate = Qss_Lib_Date::add_date($sdate,1);
								$i++;
							}
						}*/
					}
				}
			}
//			else
//			{
//				$dataTime = array(
//					'`EventID`'=>$id,
//					'`Type`'=>$params['caltype'],
//					'`SDate`'=>Qss_Lib_Date::displaytomysql(@$params['sdate']),
//					'`EDate`'=>Qss_Lib_Date::displaytomysql(@$params['edate']),
//					'`STime`'=>$params['stime'],
//					'`ETime`'=>$params['etime'],
//					'`Day`'=>@$params['day'],
//					'`WDay`'=>@$params['wday'],
//					'`Month`'=>@$params['month'],
//                                        '`Interval`'=>@$params['interval']
//				);
//				$sql = sprintf('delete from qseventtimes where EventID = %1$d',	$id);
//				$this->_o_DB->execute($sql);
//				$sql = sprintf('insert into qseventtimes%1$s',$this->arrayToInsert($dataTime));
//				$this->_o_DB->execute($sql);
//				/*if ($params['caltype'] == 0)
//				{
//					if (!@$params['edate'])
//					{
//						@$params['edate'] = @$params['sdate'];
//					}
//					$sdate = date_create(@$params['sdate']);
//					$edate = date_create(@$params['edate']);
//					$i = 0;
//					while ($sdate <= $edate && @$params['sdate'] && @$params['edate'] )
//					{
//						if ($i == 30)
//						{
//							break;
//						}
//						//save to log
//						$dataLog = array(
//								'EventID'=>$id,
//								'Date'=>$sdate->format('Y-m-d'),
//								'STime'=>@$params['stime'],
//								'ETime'=>@$params['etime'],
//								'Status'=>@$params['status']
//						);
//						$sql = sprintf('insert ignore into qseventlogs%1$s',$this->arrayToInsert($dataLog));
//						$logid = $this->_o_DB->execute($sql);
//						$sdate = Qss_Lib_Date::add_date($sdate,1);
//						$i++;
//					}
//				}*/
//			}
			$sql = sprintf('delete from qseventmembers where EventID = %1$d',	$id);
			$this->_o_DB->execute($sql);
			if(isset($params['members']) && is_array($params['members']))
			{
				foreach ($params['members'] as $item)
				{
					$dataMembers= array(
                                                'EventID'=>$id,
                                                'UID'=>$item,
                                                'Status'=>0
					);
					$sql = sprintf('replace into qseventmembers%1$s',$this->arrayToInsert($dataMembers));
					$this->_o_DB->execute($sql);
				}
			}
			if(isset($params['ifid']) && $params['ifid'] && isset($params['ioid']) && $params['ioid'])
			{
				$this->saveRefer($id,$params['ifid'],$params['ioid']);
			}
		}
		return $id;
	}
	public function saveRefer($eventid,$ifid,$ioid)
	{
		$dataRefer = array(
					'EventID'=>$eventid,
					'IFID'=>$ifid,
					'IOID'=>$ioid
		);
		$sql = sprintf('insert into qseventrefer%1$s',$this->arrayToInsert($dataRefer));
		$this->_o_DB->execute($sql);
	}
	//	public function saveIRefer($logid,$ifid,$ioid)
	//	{
	//		$dataRefer = array(
	//					'ELID'=>$logid,
	//					'IFID'=>$ifid,
	//					'IOID'=>$ioid
	//		);
	//		$sql = sprintf('insert into qseventirefer%1$s',$this->arrayToInsert($dataRefer));
	//		$this->_o_DB->execute($sql);
	//	}
	public function getEventTimes($id)
	{
		$sql = sprintf('select * from qseventtimes where EventID = %1$d',$id);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getEventTimeById($id)
	{
		$sql = sprintf('select * from qseventtimes where ETID = %1$d',$id);
		return $this->_o_DB->fetchOne($sql);
	}
	public function getEventMembers($id)
	{
		$sql = sprintf('select * from qseventmembers
					inner join qsusers on qsusers.UID = qseventmembers.UID
					where EventID = %1$d',$id);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getFormRefer($id)
	{
		$sql = sprintf('select distinct qsforms.FormCode,qsobjects.ObjID,Name,ObjectName from qseventrefer
					inner join qsiforms on qsiforms.IFID =  qseventrefer.IFID
					inner join qsforms on qsforms.FormCode = qsiforms.FormCode
					inner join qsiobjects on qsiobjects.IOID =  qseventrefer.IOID
					inner join qsobjects on qsobjects.ObjectCode = qsiobjects.ObjectCode
					where EventID = %1$d',$id);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getReferByFID($id,$fid)
	{
		$sql = sprintf('select qseventrefer.*,qsiforms.DepartmentID from qseventrefer
					inner join qsiforms on qsiforms.IFID =  qseventrefer.IFID
					where EventID = %1$d and FormCode = "%2$s"',$id,$fid);
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
	public function getCalendar($user,$startdate,$enddate,$filter,$owner,$limit = 1000)
	{
		$limit = $limit?$limit:1000;
		$sqlwhere = '';
		if(count($owner))
		{
			$sqlwhere .= sprintf(' and (qsevents.CreatedID in(%1$s))',
								implode(',',$owner));
		}
		$sqlwhere .= sprintf(' and SDate <= %2$s',// or %3$s >= %1$s)
				$this->_o_DB->quote($startdate),$this->_o_DB->quote($enddate));
		
		$sqlwhere .= sprintf(' and ((SDate >= %1$s) or (qseventtimes.Type!=0 and (EDate >= %1$s or EDate = \'0000-00-00\')))',
				$this->_o_DB->quote($startdate),$this->_o_DB->quote($enddate));
		//$sqlwhere .= sprintf(' and (qseventtimes.Type = 2 or  str_to_date(\'%Y-%m-%d\') between %1$s and %2$s)',
				//$this->_o_DB->quote($startdate),$this->_o_DB->quote($enddate));
		if(count($filter))
		{
			$sqlwhere .= sprintf(' and qseventtype.TypeID in(%2$s)',
								$reffieldname,implode(',',$filter));
		}
		$sql = sprintf('select distinct qsevents.*,qseventtype.TypeName,
					qsusers.UserName,qseventtimes.Type,qseventtimes.WDay,qseventtimes.Day,qseventtimes.Month,    
					qseventtimes.SDate,if(qseventtimes.Type=0,qseventtimes.SDate,qseventtimes.EDate) as EDate,
					qseventtimes.STime,qseventtimes.ETime,
					case when qsevents.CreatedID = %1$d then 1 else 2 end as Rights
					from qsevents
					inner join qseventtype on qsevents.EventType = qseventtype.TypeID
					inner join qsusers on qsevents.CreatedID = qsusers.UID
					left join qseventtimes on qseventtimes.EventID = qsevents.EventID
					left join qseventmembers on qseventmembers.EventID = qsevents.EventID
					where ( qsevents.CreatedID = %1$d or qseventmembers.UID = %1$d or qsevents.Public=1) %3$s
					order by qsevents.EventID 
					limit %2$d
					',$user->user_id,$limit,$sqlwhere);
		//echo $sql;die;
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
}
?>