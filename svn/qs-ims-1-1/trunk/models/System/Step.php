<?php
class Qss_Model_System_Step extends Qss_Model_Abstract
{

	public $intStepID = 0;

	public $intWorkFlowID = 0;

	public $FormCode;

	public $intStepNo = 0;
	
	public $intOrderNo = 0;

	public $szNextStep;

	public $szBackStep;

	public $szStepName;

	public $szStepDesc;
	
	public $szColor;

	public $bDisable = false;
	
	public $intStepType = 0;
		
	public $FormRights;
	
	public $Mix;
	
	public $szQuickStep;

	//-----------------------------------------------------------------------
	/**
	* construct a group
	*
	* @access  public
	*/
	function __construct ($wfid)
	{
		parent::__construct();
		$this->intWorkFlowID = $wfid ? $wfid : 0;
		$sql = sprintf('select * from qsworkflows where WFID=%1$d', $this->intWorkFlowID);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL )
		{
			$this->FormCode = $dataSQL->FormCode;
		}
	}

	//-----------------------------------------------------------------------
	/**
	* instance of department
	*
	* This method set the department id
	*
	* @access  public
	* @param   variable group id for build an group (qsgroups)
	*/
	function v_fInit ($sid)
	{
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$dataSQL = $this->getById($sid);
		if ( $dataSQL )
		{
			$this->intStepID = $dataSQL->SID;
			$this->szStepName = $dataSQL->{"Name$lang"}?$dataSQL->{"Name$lang"}:$dataSQL->Name;
			$this->intStepNo = $dataSQL->StepNo;
			$this->intOrderNo = $dataSQL->OrderNo;
			$this->szBackStep = $dataSQL->BackStep;
			$this->szNextStep = $dataSQL->NextStep;
			$this->szStepDesc = $dataSQL->Description;
			$this->szColor = $dataSQL->Color;
			$this->intStepType = $dataSQL->StepType;
			$this->FormRights = $dataSQL->FormRights;
			$this->Mix = $dataSQL->Mix;
			$this->szQuickStep = @$dataSQL->QuickStep;
		}
	}

	//-----------------------------------------------------------------------
	/**
	* instance of department
	*
	* This method set the department id
	*
	* @access  public
	* @param   variable group id for build an group (qsgroups)
	*/
	function v_fInitByStepNumber ($stepno)
	{
		$sql = "select * from qsworkflowsteps where WFID=" . $this->intWorkFlowID . " and StepNo=" . $stepno;
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL )
		{
			$this->v_fInit($dataSQL->SID);
		}
	}

	//-----------------------------------------------------------------------
	/**
	* Assign value of instance of form that store this object
	*
	* This method also asign instance of objects id if exists.
	*
	* @access  public
	* @todo  row
	* @param   variable is width of the tree box
	*/
	function getAll ()
	{
		$sql = sprintf('select * from qsworkflowsteps where WFID=%1$d order by OrderNo', $this->intWorkFlowID);
		return $this->_o_DB->fetchAll($sql);
	}

	//-----------------------------------------------------------------------
	/**
	* Do update or insert a step
	*
	* @access  public
	* @todo        call form update form of department
	* @param   variable have default value is 200
	*/
	function save ($params)
	{
		$formrights = bindec(sprintf('%1$d%2$d%3$d%4$d%', (@$params['D_FormRights']) ? 1 : 0, (@$params['U_FormRights']) ? 1 : 0, (@$params['R_FormRights']) ? 1 : 0,(@$params['C_FormRights']) ? 1 : 0));
		$data = array('SID'=>(int)$params['SID'],
					'Name'=>$params['szName'],
					'Description'=>$params['szDesc'],
					'StepNo'=>$params['intStepNo'],
					'OrderNo'=>$params['intOrderNo'],
					'NextStep'=>$params['szNextStep'],
					'BackStep'=>$params['szBackStep'],
					'Color'=>$params['szColor'],
					'StepType'=>$params['intStepType'],
					'WFID'=>$this->intWorkFlowID,
					'FormRights'=>(int)$formrights,
					'QuickStep'=>@$params['szQuickStep'],
                    'Mix'=>(int)@$params['Mix']
		);
		$lang = new Qss_Model_System_Language();
		$languages = $lang->getAll(1,false);
		foreach ($languages as $item)
		{
			$data['Name_'.$item->Code] = @$params['szName_'.$item->Code];
		}
		if ( $params['SID'] )
		{
			$sql = sprintf('update qsworkflowsteps set %1$s where SID = %2$d',
			$this->arrayToUpdate($data),$params['SID']);
		}
		else
		{
			$sql = sprintf('insert into qsworkflowsteps %1$s',
			$this->arrayToInsert($data));
		}
		$stepid = $this->_o_DB->execute($sql);
		if ( !$this->intStepID )
		{
			$this->intStepID = $stepid;
		}
		$sql = sprintf('delete from qssteprights where SID = %1$d and ifnull(GroupID,0) = 0',
		$this->intStepID);
		$this->_o_DB->execute($sql);
		$sql = sprintf('select qsfields.FieldCode,qsfields.FieldName,qsfields.ObjectCode#,qssteprights.* ,qsgroups.GroupID as gid
								from qsfields
								#inner join qsgroups on 1=1
                                left join qssteprights on qssteprights.FieldCode=qsfields.FieldCode and qssteprights.GroupID = 0
                                and qssteprights.sid=%1$d
                                where qsfields.ObjectCode in(select ObjectCode from qsfobjects where FormCode = "%2$s")', 
		$this->intStepID, $this->FormCode);
		$dataSQL = $this->_o_DB->fetchAll($sql);
		
		$sqlInsert = '';
		foreach ( $dataSQL as $data)
		{
			$rights = bindec(sprintf('%1$d%2$d%3$d%', 
					(@$params['U_' . $data->ObjectCode . '_' .$data->FieldCode]) ? 1 : 0, 
					(@$params['R_' . $data->ObjectCode . '_' .$data->FieldCode]) ? 1 : 0, 
					(@$params['C_' . $data->ObjectCode . '_' .$data->FieldCode]) ? 1 : 0));
			if($rights)
			{
				if($sqlInsert != '')
				{
					$sqlInsert .= ',';
				}
				$sqlInsert .= sprintf('(%1$d,"%2$s","%4$s",%3$d,0)',
					$this->intStepID, $data->FieldCode, $rights,$data->ObjectCode);
			}
		}
		if($sqlInsert != '')
		{
			$sql = sprintf('insert into qssteprights(SID,FieldCode,ObjectCode,Rights,GroupID) values%1$s',
				$sqlInsert);
			$this->_o_DB->execute($sql);
		}
		
		//Update group 
		$sql = sprintf('delete from qsstepgroups where SID = %1$d',
		$this->intStepID);
		$this->_o_DB->execute($sql);
		$sql = sprintf('select * from qsgroups');
		$dataSQL = $this->_o_DB->fetchAll($sql);
		$sqlInsert = '';
		foreach ( $dataSQL as $data)
		{
			$rights = bindec(sprintf('%1$d%2$d%3$d%4$d%5$d%6$d%', 
					(@$params['group_' . $data->GroupID . '_b']) ? 1 : 0,
					(@$params['group_' . $data->GroupID . '_s']) ? 1 : 0,
					(@$params['group_' . $data->GroupID . '_d']) ? 1 : 0, 
					(@$params['group_' . $data->GroupID . '_u']) ? 1 : 0, 
					(@$params['group_' . $data->GroupID . '_r']) ? 1 : 0, 
					(@$params['group_' . $data->GroupID . '_c']) ? 1 : 0));
			if($rights)
			{
				if($sqlInsert != '')
				{
					$sqlInsert .= ',';
				}
				$sqlInsert .= sprintf('(%1$d,%2$d,%3$d)'
							, $this->intStepID
							, $data->GroupID
							, $rights);
			}
		}
		if($sqlInsert != '')
		{
			$sql = sprintf('insert into qsstepgroups(SID,GroupID,Rights) values%1$s',
				$sqlInsert);
			$this->_o_DB->execute($sql);
		}
		
		$dataSQL = $this->getObjectRights();
		foreach ( $dataSQL as $data)
		{
			$rights = bindec(sprintf('%1$d%2$d%3$d%4$d%', 
				(@$params['D_ObjectRights_' . $data->ObjectCode]) ? 1 : 0, 
				(@$params['U_ObjectRights_' . $data->ObjectCode]) ? 1 : 0, 
				(@$params['R_ObjectRights_' . $data->ObjectCode]) ? 1 : 0, 
				(@$params['C_ObjectRights_' . $data->ObjectCode]) ? 1 : 0));
			//if($rights)
			{
				$sql = sprintf('replace into qsstepobjects(SID,ObjectCode,ObjectRights) values(%1$d,"%2$s",%3$d)',
				$this->intStepID, $data->ObjectCode,$rights);
				$this->_o_DB->execute($sql);
			}
		}
		$this->intStepNo = (int) $params['intStepNo'];
		//$this->updateRights();
		return true;
	}

	//-----------------------------------------------------------------------
	/**
	* Return array of user
	*
	* @param $stepno
	* @return array
	*/
	function a_fGetUsers ($userid = 0, $stepno = 0)
	{
		if ( !$this->bDisable )
		{
			$sql = sprintf('select distinct qsusers.* from qsusers
                                inner join qsusergroups on qsusers.UID=qsusergroups.UID
                                inner join qssteprights on qssteprights.GroupID = qsusergroups.GroupID  
                                inner join qsworkflowsteps on qsworkflowsteps.SID=qssteprights.SID
                                where (Type&%4$d) and qsworkflowsteps.WFID=%1$d and StepNo=%2$d and qsusers.UID!=%3$d', /* */
			$this->intWorkFlowID, $stepno ? $stepno : $this->intStepNo, $userid, (Qss_Lib_Const::USER_TYPE_USER));
			return $this->_o_DB->fetchAll($sql);
		}
		else
		{
			return $this->szUserDesc;
		}

	}
	//-----------------------------------------------------------------------
	/**
	* Return textbox element of group name
	*
	* @access  public
	* @todo        call form update form of step
	* @param   variable have default value is 200
	*/
	function a_fGetNextSteps ()
	{
		$sql = sprintf('select * from qsworkflowsteps where WFID=%1$d and StepNo in (%2$s)', /* */
		$this->intWorkFlowID, $this->szNextStep ? $this->szNextStep : '0');
		return $this->_o_DB->fetchAll($sql);
	}
	
	
	function a_fGetNextBackSteps ()
	{
		$sql = sprintf('select * from qsworkflowsteps where WFID=%1$d and StepNo in (%2$s) order by OrderNo', /* */
		$this->intWorkFlowID, ($this->szNextStep ? $this->szNextStep : '0') .','. ($this->szBackStep ? $this->szBackStep : '0').','.$this->intStepNo);
		return $this->_o_DB->fetchAll($sql);
	}
	//-----------------------------------------------------------------------
	/**
	* Return combobox element of back step
	*
	* @access  public
	* @todo        call by form action
	* @param   variable have default value is 200
	*/
	function a_fGetBackSteps ()
	{
		//if ( $this->szBackStep )
		$sql = sprintf('select * from qsworkflowsteps where WFID=%1$d and StepNo in (%2$s) order by OrderNo', $this->intWorkFlowID, $this->szBackStep);
		//else
		//$sql = sprintf('select * from qsworkflowsteps where WFID=%1$d and 0 in (NextStep)', $this->intWorkFlowID);
		return $this->_o_DB->fetchAll($sql);
	}
	function getRights($groupid = 0)
	{
		$sql=sprintf('select qsfields.FieldCode,qsfields.FieldName,qssteprights.Rights,qsfields.ObjectCode,
				qsobjects.objectname
				from qsfields
				inner join qsobjects on qsobjects.ObjectCode=qsfields.ObjectCode
				inner join qsfobjects on qsfobjects.ObjectCode = qsobjects.ObjectCode 
				left join qssteprights on qssteprights.FieldCode=qsfields.FieldCode 
						and qssteprights.ObjectCode = qsfields.ObjectCode
						and GroupID = %3$d
				and qssteprights.sid=%1$d 
				where qsfobjects.FormCode = "%2$s"
				
				order by qsfobjects.ObjNo, qsfields.ObjectCode, qsfields.FieldNo'
			,$this->intStepID
			,$this->FormCode
			,$groupid);
			//echo '<pre>';
			//echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
	function getObjectRights()
	{
		$sql=sprintf('select qsobjects.*,qsstepobjects.ObjectRights
				from qsobjects
				inner join qsfobjects on qsfobjects.ObjectCode = qsobjects.ObjectCode
				left join qsstepobjects on qsstepobjects.ObjectCode = qsobjects.ObjectCode
				and SID = %1$d
				where qsfobjects.FormCode = "%2$s" and Main =0
				order by ObjNo',$this->intStepID,$this->FormCode);
		return $this->_o_DB->fetchAll($sql);
	}
	public function delete()
	{
		$sql = sprintf('delete from qsworkflowsteps where SID=%1$d',$this->intStepID);
		$this->_o_DB->execute($sql);
	}
	public function deleteAll()
	{
		$sql = sprintf('delete from qsworkflowsteps where WFID=%1$d',$this->intWorkFlowID);
		$this->_o_DB->execute($sql);
	}
	public function getById($id)
	{
		$sql = sprintf('select * from qsworkflowsteps where SID= %1$d', $id);
		return $this->_o_DB->fetchOne($sql);
	}
	/*public function updateRights()
	{
		$sql = sprintf('delete from qsstepusers
				where StepNo = %1$d and WFID = %2$d',
		$this->intStepNo,$this->intWorkFlowID);
		$this->_o_DB->execute($sql);
		$sql = sprintf('insert into qsstepusers(WFID,StepNo,FieldID,UID,Rights)
				select qsworkflowsteps.WFID, qsworkflowsteps.StepNo,qssteprights.FieldID, qsusergroups.UID, bit_or(Rights)
				from qsworkflowsteps
				inner join qssteprights on qssteprights.SID = qsworkflowsteps.SID 
				inner join qsgroups on qsgroups.GroupID = qssteprights.GroupID
				inner join qsusergroups on qsusergroups.GroupID = qsgroups.GroupID
				where qsworkflowsteps.StepNo = %1$d and qsworkflowsteps.WFID = %2$d
				group by qsworkflowsteps.WFID, qsworkflowsteps.StepNo,qssteprights.FieldID, qsusergroups.UID',
		$this->intStepNo,$this->intWorkFlowID);
		return $this->_o_DB->execute($sql);
	}*/
	/*public function getGroups($stepno)
	{
		$sql = sprintf('select distinct qsgroups.*,qsdepartments.* from qsgroups
						inner join qsdepartments on qsdepartments.DepartmentID=qsgroups.DepartmentID
						inner join qssteprights on qssteprights.GroupID = qsgroups.GroupID
						inner join qsworkflowsteps on qsworkflowsteps.SID = qssteprights.SID
						where StepNo= %1$d and qsworkflowsteps.WFID = %2$d and Rights
						order by qsdepartments.DepartmentID', $stepno,$this->intWorkFlowID);
		return $this->_o_DB->fetchAll($sql);
	}*/
	
	public function getStepDocuments()
	{
		$sql = sprintf('select * from qsdocumenttype as doc
						inner join qsfrecords as rec
						on rec.DTID = doc.DTID
						where rec.StepNo = %1$d and FormCode = "%2$s" and FormCode <> ""', 
				$this->intStepNo,$this->FormCode);
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getStepActivities()
	{
		$sql = sprintf('select * from qseventtype as ev
						inner join qsfactivities as act
						on ev.TypeID = act.ETID
						where act.StepNo = %1$d and FormCode = "%2$s"', 
				$this->intStepNo,$this->FormCode);
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function updateStepDocuments($dtid)
	{
		$data = array('DTID'=>$dtid,
						'FormCode'=>$this->FormCode,
						'StepNo'=>$this->intStepNo);
		$sql = sprintf('replace into qsfrecords%1$s', $this->arrayToInsert($data));
		//echo $sql; die;
		$this->_o_DB->execute($sql);
	}
	
	public function updateStepActivities($etid)
	{
		$data = array('ETID'=>$etid,
						'FormCode'=>$this->FormCode,
						'StepNo'=>$this->intStepNo);
		$sql = sprintf('replace into qsfactivities%1$s', $this->arrayToInsert($data));
		$this->_o_DB->execute($sql);
	}
	
	public function deleteStepDocuments()
	{
		$sql = sprintf('delete from qsfrecords where FormCode = "%1$s" AND StepNo= %2$d ', 
				$this->FormCode, $this->intStepNo);
		$this->_o_DB->execute($sql);
	}
	
	public function deleteStepActivities()
	{
		$sql = sprintf('delete from qsfactivities where FormCode = "%1$s" AND StepNo= %2$d ', 
				$this->FormCode, $this->intStepNo);
		$this->_o_DB->execute($sql);
	}
	public function getGroups()
	{
		$sql = sprintf('select qsgroups.*,ifnull(Rights,0) as Rights
					from qsgroups

					left join qsstepgroups on qsstepgroups.GroupID = qsgroups.GroupID
					and SID= %1$d 
					', $this->intStepID);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getApprovers()
	{
		$sql = sprintf('select * from qsstepapprover
					where SID= %1$d 
					order by OrderNo', $this->intStepID);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getApprover($said)
	{
		$sql = sprintf('select * from qsstepapprover
					where SAID= %1$d', 
				$said);
		return $this->_o_DB->fetchOne($sql);
	}
	public function saveApprover($said,$orderno,$name,$condition)
	{
		if($said)
		{
			$sql = sprintf('update qsstepapprover set Name = %1$s,OrderNo = %2$d,`Condition` = %3$s
					where SAID =%4$d', 
				$this->_o_DB->quote($name),
				$orderno,
				$this->_o_DB->quote($condition),
				$said);
		}
		else
		{
			$sql = sprintf('insert into qsstepapprover(SID,Name,OrderNo,`Condition`)
					values(%1$d,%2$s,%3$d,%4$s)', 
				$this->intStepID,
				$this->_o_DB->quote($name),
				$orderno,
				$this->_o_DB->quote($condition));
		}
		return $this->_o_DB->execute($sql);
	}
	public function deleteApprover($said)
	{
		$sql = sprintf('delete from qsstepapprover
					where SAID = %1$d', 
				$said);
		return $this->_o_DB->execute($sql);
	}
	public function getApproverByIFID($ifid)
	{
		$sql = sprintf('select  * 
					from qsstepapprover
					left join (select SAID,StepNo, group_concat(concat(UserName,":",DATE_FORMAT(ADate,"%%d-%%m-%%Y %%H:%%i")) SEPARATOR ",") as UserName 
					from qsfapprover
					inner join qsusers on qsusers.UID = qsfapprover.UID
					where IFID = %1$d and RDate is null group by SAID,StepNo) as T
					on T.SAID = qsstepapprover.SAID 
					where qsstepapprover.SID= %2$d 
					order by OrderNo', 
				$ifid,
				$this->intStepID);
				//echo '<pre>';echo $sql;die;
		return $this->filterApprover($this->_o_DB->fetchAll($sql),$ifid);
	}
	public function getApproverByUser($ifid,$uid)
	{
        // Update 15-05-2017: bỏ limit 1 trong sub query, do một bước có thể có nhiều cấp duyệt
		$sql = sprintf('select  qsstepapprover.*, qsstepapproverrights.*,T.StepNo,T.UID as AUID
					from qsstepapprover
					left join qsstepapproverrights on qsstepapproverrights.SAID = qsstepapprover.SAID and qsstepapproverrights.UID = %3$d 
					left join (select SAID,StepNo,UID from qsfapprover where IFID = %1$d and RDate is null 
					Group by SAID,StepNo
					order by FIELD(UID,%3$d) limit 100 ) as T
					on T.SAID = qsstepapprover.SAID
					where qsstepapprover.SID= %2$d 
					order by OrderNo', 
				$ifid,
				$this->intStepID,
				$uid);
				// echo '<pre>';echo $sql;die;
		return $this->filterApprover($this->_o_DB->fetchAll($sql),$ifid);
	}
	public function filterApprover($data,$ifid)
	{
		$retval = array();
		foreach ($data as $item)
		{
			if($item->Condition)
			{
				$condition = (array) Qss_Json::decode($item->Condition);
				if(count($condition) && !$this->checkFormCondition($ifid, $condition))
				{
					continue;
				}
			}
			$retval[] = $item;
		}
		return $retval;
	}
	public function checkFormCondition($ifid,$condition)
	{
		//getfields
		$retval = true;
		$sql = sprintf('select qsfields.* from qsfields
				inner join qsobjects on qsobjects.ObjectCode = qsfields.ObjectCode
				inner join qsfobjects on qsfobjects.ObjectCode = qsobjects.ObjectCode
				inner join qsiforms on qsiforms.FormCode = qsfobjects.FormCode
				where qsiforms.IFID=%1$d',
			$ifid);
		$key = array_keys($condition);
		$dataSQL = $this->_o_DB->fetchAll($sql);
		$where = '';
		foreach ($dataSQL as $item)
		{
			if(in_array($item->FieldCode, $key))
			{
				$val = $condition[$item->FieldCode];
				if ( $val !== '')
				{
					if(preg_match_all("/(>|<|=|<=|>=|<>)([0-9(.)]+)/", $val, $match))
					{
						$regcon = '';
						$operator = 'and';
						if(in_array('=',$match[1]))
						{
							$operator = 'or';
						}
						elseif(preg_match_all("/(>|>=)([0-9(.)]+)/", $val, $match1) && preg_match_all("/(<|<=)([0-9(.)]+)/", $val, $match2))
						{
							if(max($match1[2]) > min($match2[2]))
							{
								$operator = 'or';
							}
						}
						foreach($match[0] as $value)
						{
							if($regcon != '')
							{
								$regcon .= ' ' . $operator . ' ';
							}
							$regcon .= sprintf('%1$d %3$s ', $item->FieldCode, $value);
						}
						$where .= sprintf(' and (%$1s)',$regcon);
					}
					else
					{
						$where .= sprintf(' and %1$s = %2$s',$item->FieldCode,$this->_o_DB->quote($val));
					}
				}
			}
		}
		if($where)
		{
			$main = array();
			$sub = array();
			$sql = sprintf('select * from qsiforms
					inner join qsforms on qsforms.FormCode = qsiforms.FormCode
					inner join qsfobjects on qsfobjects.FormCode = qsforms.FormCode
					left join qsobjects on qsobjects.ObjectCode = qsfobjects.ObjectCode
					where qsiforms.IFID=%1$d
					order by Main desc',
				$ifid); 
			$dataSQL = $this->_o_DB->fetchAll($sql);
			$code = '';
			foreach ($dataSQL as $item)
			{
				$code = $item->FormCode;
				if($item->Main)
				{
					$main[] = $item->ObjectCode;
				}
				else
				{
					$sub[] = $item->ObjectCode;
				}
			}
			$sql = '';
			foreach ($main as $item)
			{
				if($sql)
				{
					$sql .= sprintf(' inner join %1$s on %1$s.IFID_%2$s = v.IFID_%2$s',
						$item,
						$code);
				}
				else
				{
					$sql .= sprintf('select 1 from %1$s as v',
						$item);
				}
			}
			foreach ($sub as $item)
			{
				$sql .= sprintf(' left join %1$s on %1$s.IFID_%2$s = v.IFID_%2$s',
					$item,
					$code);
			}
			$checkSQL = sprintf('%1$s where v.IFID_%2$s = %3$d %4$s',
							$sql,
							$code,
							$ifid,
							$where);
						
			$dataCheck = $this->_o_DB->fetchOne($checkSQL);
			if(!$dataCheck)
			{
				$retval = false;
			}
		}
		return $retval;
	}
	public function deleteCustom($groupid)
	{
		$sql = sprintf('delete from qssteprights
					where SID = %1$d and GroupID = %2$d and GroupID != %2$d', 
				$this->intStepID
				,$groupid);
		return $this->_o_DB->execute($sql);
	}
	function saveCustom ($params)
	{
		$groupid = $params['GroupID'];
		$sql = sprintf('delete from qssteprights where SID = %1$d and GroupID = %2$d',
			$this->intStepID
			,$groupid);
		$this->_o_DB->execute($sql);
		$sql = sprintf('select qsfields.FieldCode,qsfields.FieldName,qsfields.ObjectCode#,qssteprights.* ,qsgroups.GroupID as gid
								from qsfields
								where qsfields.ObjectCode in(select ObjectCode from qsfobjects where FormCode = "%2$s")', 
		$this->intStepID, $this->FormCode);
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach ( $dataSQL as $data)
		{
			$rights = bindec(sprintf('%1$d%2$d%3$d%', 
					(@$params['U_' . $data->ObjectCode . '_' .$data->FieldCode]) ? 1 : 0, 
					(@$params['R_' . $data->ObjectCode . '_' .$data->FieldCode]) ? 1 : 0, 
					(@$params['C_' . $data->ObjectCode . '_' .$data->FieldCode]) ? 1 : 0));
			if($rights)
			{
				$sql = sprintf('insert into qssteprights(SID,FieldCode,ObjectCode,Rights,GroupID) values(%1$d,"%2$s","%4$s",%3$d,%5$d)',
				$this->intStepID, $data->FieldCode, $rights,$data->ObjectCode,$groupid);
				$this->_o_DB->execute($sql);
			}
		}
		return true;
	}
	public function getApproverUsers()
	{
		$sql = sprintf('select qsusers.* from qsstepapprover
					inner join qsworkflowsteps on qsworkflowsteps.SID= qsstepapprover.SID
					inner join qsstepapproverrights on qsstepapproverrights.SAID = qsstepapprover.SAID
					inner join qsusers on qsusers.UID = qsstepapproverrights.UID
					where qsworkflowsteps.WFID = %1$s and qsworkflowsteps.StepNo=%2$d'
				, $this->intWorkFlowID
				, $this->intStepNo);
				//echo '<pre>';echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
	public function checkApproveRights($uid)
	{
		$sql = sprintf('select 1 from qsstepapprover v
					inner join qsstepapproverrights t on v.SAID = t.SAID
					where UID = %1$d and SID = %2$d'
				, $uid
				, $this->intStepID);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL?true:false; 
	}
}
?>