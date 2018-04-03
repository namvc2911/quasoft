<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Bash extends Qss_Model_Abstract
{
	public static $arrField = array(1=>'BashName',2=>'UserName',3=>'CDate',4=>'FormCode',5=>'ToFormCode',6=>'Record',7=>'Class');
	public static $arrGroup = array(1=>'UserName',2=>'FromName',3=>'ToName');
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
		if(isset(self::$arrField[$groupby]))
		{
			$orderfields[] = self::$arrField[$groupby] . ' ' . $ordertype;
		}
		if(isset(self::$arrField[$fieldorder]))
		{
			$orderfields[] = self::$arrField[$fieldorder] . ' ' . $ordertype;
		}
		else
		{
			$orderfields[] = 'qsbash.CDate DESC';
		}
		$sqlorder = sprintf('order by %1$s',implode(',', $orderfields));
		$sqlwhere = '';
		if(is_array($a_Filter))
		{
			foreach ($a_Filter as $key=>$val)
			{
				if(isset(self::$arrField[$key]))
				{
					if($key == 1)
					{
						$sqlwhere .= sprintf(' and %1$s like %2$s',self::$arrField[$key],$this->_o_DB->quote('%'.$val.'%'));
					}
					elseif($key == 4 || $key == 5)
					{
						$sqlwhere .= sprintf(' and qsbash.%1$s = "%2$s"',self::$arrField[$key],$val);
					}
				}
			}
		}
		$sql = sprintf('select qsbash.*,fr.Name as FromName,t.Name as ToName,qsusers.UserName
					from qsbash
					inner join qsusers on qsusers.UID = qsbash.UID
					inner join qsforms as fr on fr.FormCode = qsbash.FormCode
					inner join qsforms as t on t.FormCode = qsbash.ToFormCode
					where 1=1 %3$s 
					%1$s %2$s
					',$sqlorder,$sqllimit,$sqlwhere);
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
				if(isset(self::$arrField[$key]))
				{
					if($key == 1)
					{
						$sqlwhere .= sprintf(' and %1$s like %2$s',self::$arrField[$key],$this->_o_DB->quote('%'.$val.'%'));
					}
					elseif($key == 4 || $key == 5)
					{
						$sqlwhere .= sprintf(' and qsbash.%1$s = "%2$s"',self::$arrField[$key],$val);
					}
				}
			}
		}
		$sql = sprintf('select count(distinct qsbash.BID) as count
					from qsbash
					inner join qsusers on qsusers.UID = qsbash.UID
					inner join qsforms as fr on fr.FormCode = qsbash.FormCode
					inner join qsforms as t on t.FormCode = qsbash.ToFormCode
					where 1=1 %1$s 
					',$sqlwhere);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->count:0;
	}
	public function getByID($id)
	{
		$sql = sprintf('select * from qsbash where BID = %1$d',$id);
		return $this->_o_DB->fetchOne($sql);
	}
	public function save($params)
	{
		$id = $params['id'];
		$data = array(
				'BashName'=>$params['name'],
				'UID'=>$params['uid'],
				'FormCode'=>$params['fromfid'],
				'ToFormCode'=>$params['tofid'],
				'ObjectCode'=>$params['objid'],
				'Active'=>@$params['active'],
				'Record'=>@$params['record'],
				'Type'=>@$params['type'],
				'Step'=>$params['step'],
				'Class'=>$params['class']
		);
		$lang = new Qss_Model_System_Language();
		$languages = $lang->getAll(1,false);
		foreach ($languages as $item)
		{
			$data['BashName_'.$item->Code] = @$params['name_'.$item->Code];
		}
		if($id)
		{
			$sql = sprintf('update qsbash set %2$s where BID=%1$d',$id,$this->arrayToUpdate($data));
			$this->_o_DB->execute($sql);
		}
		else
		{
			$sql = sprintf('insert into qsbash%1$s',$this->arrayToInsert($data));
			//echo $sql;die;
			$id = $this->_o_DB->execute($sql);
		}
		return $id;
	}
	public function getFields($id)
	{
		$sql = sprintf('select qsbashfields.*,qsfields.FieldName,t.FieldName as fromfield ,
					source.ObjectName as sName,destination.ObjectName as dName
					from qsbashfields
					inner join qsfields on qsfields.FieldCode = qsbashfields.ToFieldCode
					left join qsfields as t on t.FieldCode = qsbashfields.FieldCode
					left join qsobjects as source on source.ObjectCode = qsfields.ObjectCode
					left join qsobjects as destination on destination.ObjectCode = t.ObjectCode
					where BID = %1$d',
		$id);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getFieldByID($id)
	{
		$sql = sprintf('select * from qsbashfields where BFID = %1$d',$id);
		return $this->_o_DB->fetchOne($sql);
	}
	public function saveField($params)
	{
		$id = $params['id'];
		$data = array(
				'BID'=>$params['bashid'],
				'FieldCode'=>$params['fieldid'],
				'ToFieldCode'=>$params['tofieldid'],
				'Regx'=>$params['regx']
		);
		if($id)
		{
			$sql = sprintf('update qsbashfields set %2$s where BFID=%1$d',$id,$this->arrayToUpdate($data));
			$this->_o_DB->execute($sql);
		}
		else
		{
			$sql = sprintf('insert into qsbashfields%1$s',$this->arrayToInsert($data));
			//echo $sql;die;
			$id = $this->_o_DB->execute($sql);
		}
		return $id;
	}
	public function delete($id)
	{
		$sql = sprintf('delete from qsbash where BID = %1$d',$id);
		$this->_o_DB->execute($sql);
	}
	public function deleteField($id)
	{
		$sql = sprintf('delete from qsbashfields where BFID = %1$d',$id);
		$this->_o_DB->execute($sql);
	}
	public function getHistory($id,$ifid)
	{
		$sql = sprintf('select qsbashhistory.*,qsbashsubhistory.FromIOID,qsbashsubhistory.ToIOID 
					from qsbashhistory
					left join qsbashsubhistory on qsbashsubhistory.BHID = qsbashhistory.BHID
					where IFID = %1$d and BID = %2$d',$ifid,$id);		
		return $this->_o_DB->fetchAll($sql);
	}
	public function getByFormAndStep($fid,$step)
	{
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$sql = sprintf('select *,ifnull(BashName%3$s,BashName) as BashName from qsbash 
						where FormCode = "%1$s" and Step like %2$s and Active = 1 and Type = 1',
					$fid,
					$this->_o_DB->quote('%'.(int)$step.'%'),
					$lang);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getManualByFormAndStep($fid,$step)
	{
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$sql = sprintf('select *,ifnull(BashName%3$s,BashName) as BashName from qsbash 
					where FormCode = "%1$s" and Step like %2$s and Active = 1 and Type > 1',
				$fid,
				$this->_o_DB->quote('%'.(int)$step.'%'),
				$lang);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getManualByForm($code)
	{
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$sql = sprintf('select qsbash.*,ifnull(BashName%2$s,BashName) as BashName 
				from qsbash 
				inner join qsforms on qsforms.FormCode = qsbash.ToFormCode
				where qsbash.FormCode = %1$s 
				and (ObjectCode is null or ObjectCode = \'\' ) 
				and qsbash.Active = 1 
				and qsbash.Type > 1
				and qsforms.Effected = 1',
				$this->_o_DB->quote($code),
				$lang);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getManualByObjID($fid,$objid)
	{
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$sql = sprintf('select qsbash.*,ifnull(BashName%3$s,BashName) as BashName 
					from qsbash 
					inner join qsforms on qsforms.FormCode = qsbash.ToFormCode
					where qsbash.FormCode = "%1$s" and ObjectCode="%2$s" and 
					qsbash.Active = 1 and 
					qsbash.Type > 1
					and qsforms.Effected = 1',
				$fid,
				$objid,
				$lang);
		return $this->_o_DB->fetchAll($sql);
	}
	public function saveHistory($data)
	{
		$id = $data['BHID'];
		if($id)
		{
			$sql = sprintf('update qsbashhistory set %2$s where BHID=%1$d',$id,$this->arrayToUpdate($data));
			$this->_o_DB->execute($sql);
		}
		else
		{
			$sql = sprintf('insert into qsbashhistory%1$s',$this->arrayToInsert($data));
			//echo $sql;die;
			$id = $this->_o_DB->execute($sql);
		}
		return $id;
	}
	public function getAllHistory($id,$currentpage = 1 , $limit = 20, $ordertype='ASC')
	{
		$sqllimit = sprintf(' LIMIT %1$d,%2$d', (($currentpage?$currentpage:1) - 1) * $limit, $limit);
		$orderfields = array();
		$orderfields[] = 'qsbash.CDate DESC';
		$sqlorder = sprintf('order by %1$s',implode(',', $orderfields));
		$sql = sprintf('select qsbashhistory.Error,qsbashhistory.Message,qsbashhistory.LastRun,qsbashhistory.BID,qsbash.BashName,qsbash.Active,ifrom.IFID,ito.IFID as ToIFID,
					ifrom.DepartmentID as DepartmentID,ito.DepartmentID as deptid,
					fr.Name as FromName,t.Name as ToName,qsusers.UserName
					from qsbashhistory 
					inner join qsbash on qsbash.BID = qsbashhistory.BID
					inner join qsusers on qsusers.UID = qsbashhistory.UID
					inner join qsforms as fr on fr.FormCode = qsbash.FormCode
					inner join qsforms as t on t.FormCode = qsbash.ToFormCode
					left join qsiforms as ifrom on ifrom.IFID = qsbashhistory.IFID and ifrom.deleted = 0
					left join qsiforms as ito on ito.IFID = qsbashhistory.ToIFID and ito.deleted = 0
					where qsbashhistory.BID = %3$d 
					%1$s %2$s
					',$sqlorder,$sqllimit,$id);
		return $this->_o_DB->fetchAll($sql);
	}
	public function countAllHistory($id)
	{
		$sql = sprintf('select count(distinct qsbashhistory.BHID) as count
					from qsbashhistory 
					inner join qsbash on qsbash.BID = qsbashhistory.BID
					inner join qsusers on qsusers.UID = qsbash.UID
					inner join qsforms as fr on fr.FormCode = qsbash.FormCode
					inner join qsforms as t on t.FormCode = qsbash.ToFormCode
					left join qsiforms as ifrom on ifrom.IFID = qsbashhistory.IFID and ifrom.deleted = 0
					left join qsiforms as ito on ito.IFID = qsbashhistory.ToIFID and ito.deleted = 0
					where qsbashhistory.BID = %1$d 
					',$id);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->count:0;
	}
	public function getHistoryByToIFID($ifid)
	{
		$sql = sprintf('select * from qsbashhistory 
						inner join qsiforms on qsiforms.IFID = qsbashhistory.IFID
						inner join qsforms on qsforms.FormCode = qsiforms.FormCode
						where ToIFID = %1$d',$ifid);
		return $this->_o_DB->fetchOne($sql);
	}
	public function getHistoryByIFID($ifid)
	{
		$sql = sprintf('select unix_timestamp(qsbashhistory.LastRun) as Time, qsbashhistory.Error,qsbashhistory.Message,qsbashhistory.LastRun,qsbashhistory.BID,qsbash.BashName,qsbash.Active,ifrom.IFID,ito.IFID as ToIFID,
					ifrom.DepartmentID as DepartmentID,ito.DepartmentID as deptid,
					fr.Name as FromName,t.Name as ToName,qsusers.UserName
					from qsbashhistory 
					inner join qsbash on qsbash.BID = qsbashhistory.BID
					inner join qsusers on qsusers.UID = qsbashhistory.UID
					inner join qsforms as fr on fr.FormCode = qsbash.FormCode
					inner join qsforms as t on t.FormCode = qsbash.ToFormCode
					left join qsiforms as ifrom on ifrom.IFID = qsbashhistory.IFID and ifrom.deleted = 0
					left join qsiforms as ito on ito.IFID = qsbashhistory.ToIFID and ito.deleted = 0
					where qsbashhistory.IFID = %1$d',$ifid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getObjectByToObject($bid,$toobjid)
	{
		$sql = sprintf('select distinct ObjectCode from qsbashfields 
						inner join qsfields on qsfields.FieldCode = qsbashfields.ToFieldCode
						where qsbashfields.BID = %1$d and qsfields.ObjectCode= "%2$s"',$bid,$toobjid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getFromObjectToMain($bid,$tomainobjid,$frommainobjid)
	{
		$sql = sprintf('select distinct fr.ObjectCode as ObjectCode from qsbashfields 
						inner join qsfields on qsfields.FieldCode = qsbashfields.ToFieldCode
						inner join qsfields as fr on fr.FieldCode = qsbashfields.FieldCode
						where qsbashfields.BID = %1$d and qsfields.ObjectCode= "%2$s"',$bid,$tomainobjid);
		$dataSQL = $this->_o_DB->fetchAll($sql);
		if(!count($dataSQL))
		{
			$sql = sprintf('select %1$d as ObjectCode',$frommainobjid);
			$dataSQL = $this->_o_DB->fetchAll($sql);
		}
		return $dataSQL;
	}
	public function getFromObjectToSub($id,$subobjectid)
	{
		$sql = sprintf('select distinct fr.ObjectCode as ObjID from qsbashfields 
						inner join qsfields on qsfields.sFieldID = qsbashfields.ToFieldID
						inner join qsfields as fr on fr.sFieldID = qsbashfields.FieldID
						where qsbashfields.BID = %1$d and qsfields.ObjectCode= "%2$s"',$id,$subobjectid);
	//	echo $sql;
		return $this->_o_DB->fetchAll($sql);
	}
	public function saveSubHistory($data)
	{
		$sql = sprintf('replace into qsbashsubhistory%1$s',$this->arrayToInsert($data));
			//echo $sql;die;
		$this->_o_DB->execute($sql);
	}
}
?>