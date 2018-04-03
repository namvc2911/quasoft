<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Form extends Qss_Model_System_Form
{

	public $i_IFID = 0;

	public $i_DepartmentID = 0;

	public $i_WorkFlowID;

	public $i_Status;

	public $i_Rights = 0;
	
	public $a_Rights = array();

	public $a_Objects;

	public $startDate;

	public $lastModify;

	public $i_UserID = 0;

	public $lockid;
	
	public $verifyid;
	

	/**
	 * Working with all design of form that user acess via module management
	 *'
	 * @access  public
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->a_Objects = array();
	}

	/**
	 *
	 * @param $FormCode
	 * @param $i_DeptID
	 * @return void */
	public function init ($FormCode, $i_DeptID = 0, $i_UserID = 0)//v_fInit
	{
		if ( !$FormCode )
		{
			return false;
		}
		parent::b_fInit($FormCode);
		$this->i_DepartmentID = $this->_user->user_dept_id;
		$this->i_UserID = $this->_user->user_id;
		$this->v_fLoadObjects();
		return true;
	}
	/**
	 *
	 * @param $i_iFID
	 * @param $i_DeptID
	 * @return void
	 */
	public function initData ($i_iFID, $i_DeptID)
	{
		if ( !$i_iFID || !$i_DeptID )
		{
			return false;
		}
		$this->i_IFID = $i_iFID;
		$this->i_DepartmentID = $i_DeptID;
		$sql = "select * from qsiforms where IFID=" . (int) $i_iFID;
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL )
		{
			$this->FormCode = $dataSQL->FormCode;
			$this->i_UserID = $dataSQL->UID;
			$this->startDate = $dataSQL->SDate;
			$this->lastModify = $dataSQL->LastModify;
			$this->i_Status = $dataSQL->Status;
			$this->i_DepartmentID = $dataSQL->DepartmentID;
			$this->lockid = $dataSQL->LockID;
			$this->verifyid = $dataSQL->VerifyID;
		}
		else
		{
			return false;
		}
		parent::b_fInit($this->FormCode);
		$this->v_fLoadObjects();
		return true;
	}
	/**
	 * Load objects
	 *
	 * @return void
	 */
	private function v_fLoadObjects ()
	{
		$user = Qss_Register::get('userinfo');
		//Rights of form
		if($user && $user->user_group_list != '')
		{
			$sql = sprintf('select bit_or(Rights) as Rights from qsforms
                                        inner join qsuserforms on qsforms.FormCode=qsuserforms.FormCode 
                                        	and qsuserforms.GroupID in(%1$s)
                                        where qsforms.FormCode=%2$s group by qsforms.Type',
						$user->user_group_list,
						$this->_o_DB->quote($this->FormCode));
			$dataSQL = $this->_o_DB->fetchOne($sql);
			if($dataSQL)
			{
				$this->i_Rights = $dataSQL->Rights;//11111100000
			}
		}
		//Load object
		$sql = sprintf('select distinct qsfobjects.*, qsforms.*,qsworkflows.WFID ,
							qsworkflowsteps.FormRights, qsstepobjects.ObjectRights
							from qsfobjects 
							inner join qsforms on qsforms.FormCode = qsfobjects.FormCode
							left join qsworkflows on qsworkflows.FormCode=qsforms.FormCode and qsworkflows.Actived=1
							left join qsworkflowsteps on qsworkflowsteps.WFID = qsworkflows.WFID and StepNo = %2$d
							left join qsstepobjects on qsstepobjects.SID = qsworkflowsteps.SID and qsstepobjects.ObjectCode = qsfobjects.ObjectCode
							where qsfobjects.FormCode = %1$s
							order by Main desc,ObjNo',//and (qsfobjects.Main != 0 or (qsworkflows.WFID is null or (qsstepobjects.ObjectRights is not null and qsstepobjects.ObjectRights!=0)))
						$this->_o_DB->quote($this->FormCode),
						$this->i_Status);
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach ($dataSQL as $o_Object)
		{
			$obj = new Qss_Model_Object();
			$obj->v_fInit($o_Object->ObjectCode, $this->FormCode);
			$obj->intDepartmentID = $this->i_DepartmentID;
			$obj->i_IFID = $this->i_IFID;
			$obj->i_UserID = $this->i_UserID;
			$obj->intType = $this->i_Type;
			$obj->FormCode = $this->FormCode;
			//$obj->processDays = $o_Object->process_day;
			$obj->b_Main = $o_Object->Main;
			if(!$this->i_WorkFlowID)
			{
				$o_Object->ObjectRights = $this->i_Rights;
			}
			if($obj->b_Main)
			{
				$obj->intRights = $this->i_Rights & ($o_Object->ObjectRights | 2032);//11111110000
				//$this->i_Rights = $this->i_Rights & ($o_Object->FormRights | 2035);//11111110011
			}
			else
			{
				$obj->intRights = $o_Object->ObjectRights;
			}
			$obj->bPublic = $o_Object->Public;
			$obj->bEditing = $o_Object->Editing;
			$obj->bDeleting = $o_Object->Deleting;
			$obj->bInsert = $o_Object->Insert;
			$obj->bTrack = $o_Object->Track;
			$obj->ParentObjectCode = $o_Object->ParentObjectCode;
			$obj->RefFormCode = @$o_Object->RefFormCode;
			//if($this->i_Type > 2)
			{
				$this->i_WorkFlowID = $o_Object->WFID;
			}
			$obj->intWorkFlowID = $this->i_WorkFlowID;
			$this->a_Objects[$o_Object->ObjectCode] = $obj;
			$this->sz_Title = $o_Object->Name;
			$this->i_Type = $o_Object->Type;
			//$this->processDays = $o_Object->process_day;
			$obj->intStatus = $this->i_Status;
		}
		//Load rights for each step
		if($this->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE && $this->i_WorkFlowID && $user->user_group_list)
		{
			$sql = sprintf('select FormRights,StepNo,
					(select bit_or(Rights) from qsstepgroups where SID=qsworkflowsteps.SID and GroupID in(%2$s) limit 1) as grouprights 
						from qsworkflows
						inner join qsworkflowsteps on qsworkflowsteps.WFID = qsworkflows.WFID
						where qsworkflows.FormCode=%1$s and qsworkflows.Actived=1', 
					$this->_o_DB->quote($this->FormCode),
					$user->user_group_list);
			$dataSQL = $this->_o_DB->fetchAll($sql);
			foreach ($dataSQL as $item)
			{
				/* merge first 4 bit with other*/
				$rights =  $item->grouprights & ((int)$item->FormRights | 48);//thêm chuyển tình trạng
				$this->a_Rights[$item->StepNo] = $rights;
			}
		}
	}

	function getFormCode ()
	{
		return $this->FormCode;
	}

	function getIFID ()
	{
		return $this->i_IFID;
	}

	function getDepartmentID ()
	{
		return $this->i_DepartmentID;
	}

	/**
	 * Restore deleted form
	 *
	 * @return void
	 */
	public function v_fRestore ($userid)
	{
		$sql = sprintf('update qsiforms set Deleted=0, LastModify=%3$d where UID=%1$d and IFID=%2$d', /* */
		$userid, $this->i_IFID,time());
		$this->_o_DB->execute($sql);
		$this->updateTempByForm($this->i_IFID);
	}

	/**
	 * Get rights of group list
	 * @param $sz_GroupList
	 * @return int
	 */
	public function i_fGetRights ($sz_GroupList,$status = 0)
	{
		$rights = 0;
		$status = $status?$status:$this->i_Status;
		//kiểm tra filter trước
		$recordRights = 63;
		if($this->i_IFID)
		{
			$classname = 'Qss_Bin_Filter_'.$this->o_fGetMainObject()->ObjectCode;
			if(class_exists($classname))
			{
				$exfilter = new $classname($this->_user);
				$recordRights = $exfilter->getRights($this->i_IFID);
			}
		}
		if($recordRights == 63 && isset($this->a_Rights[$status]))
		{
			return $this->a_Rights[$status];
		}
		$sql = sprintf('select qsforms.Type,bit_or(Rights) as Rights from qsforms
                   		inner join qsuserforms on qsforms.FormCode=qsuserforms.FormCode and 
                   		qsuserforms.GroupID in(%1$s)
                        where qsforms.FormCode=%2$s group by qsforms.Type',
				$sz_GroupList,
				$this->_o_DB->quote($this->FormCode));
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL )
		{
			/* merge first 4 bit with other*/
			$rights =  (int)$dataSQL->Rights;
		}
		if($this->i_WorkFlowID)
		{
			if($status)
			{
				$sql = sprintf('select FormRights,
						(select bit_or(Rights) from qsstepgroups where SID=qsworkflowsteps.SID and GroupID in(%3$s) limit 1) as grouprights 
							from qsworkflows
							inner join qsworkflowsteps on qsworkflowsteps.WFID = qsworkflows.WFID and StepNo = %2$d
							where qsworkflows.FormCode=%1$s and qsworkflows.Actived=1',
						$this->_o_DB->quote($this->FormCode),
						$status,
						$sz_GroupList);
				$dataSQL = $this->_o_DB->fetchOne($sql);
				if ( $dataSQL )
				{
					/* merge first 4 bit with other*/
					$rights = ($rights | 48) & $dataSQL->grouprights & ($dataSQL->FormRights | 48);
				}
			}
		}
		$this->a_Rights[$status] = $rights;
		if($recordRights != 63)
		{
			$rights = $rights & ($recordRights | 48);
		}
		return $rights;
	}

	/**
	 *
	 * @return unknown_type
	 */
	public function b_fDelete ()
	{
		if ( $this->i_IFID && $this->i_DepartmentID )
		{
			$sql = sprintf('update qsiforms set deleted = 1, LastModify=%2$d,UID = %3$d where IFID=%1$d',
				$this->i_IFID, 
				time(),
				Qss_Register::get('userinfo')->user_id);
			$this->_o_DB->execute($sql);
			foreach ($this->a_Objects as $object)
			{
				if($object->b_Tree && $object->b_Main)
				{
					$object->initData($this->i_IFID, $this->i_DepartmentID, 0);
					$old = $object->getTreeRecord($object->i_IOID);
				}
				//delte taats ca view
				$sql = sprintf('delete from %1$s where IFID_%2$s = %3$d',
						$object->ObjectCode,
						$this->FormCode,
						$this->i_IFID);
				$this->_o_DB->execute($sql);
				$sql = sprintf('delete from qsioidlink where ToIFID = %1$d',$this->i_IFID);
				$this->_o_DB->execute($sql);
				if($object->b_Tree && $object->b_Main)
				{
					$new = $object->getTreeRecord($object->i_IOID);
					$this->updateTemp( $object->ObjectCode, $object->b_Tree,$object->i_IOID,$this->i_IFID,$old,$new);
				}
			}
		}
	}

	/**
	 * Update instance of form
	 *
	 * @return
	 */
	function b_fUpdate ($objid)
	{
		//$this->ArrObject[$objid]->setIFID($this->i_IFID,$this->i_DepartmentID);
		return $this->a_Objects[$objid]->b_fUpdate();
	}

	function a_fGetTrace ()
	{
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$sql = sprintf('select qsusers.UserName,qsftrace.*
						from qsftrace
                        inner join qsusers on qsusers.UID=qsftrace.UID
                        where IFID=%1$d order by date desc',
					$this->i_IFID);
		return $this->_o_DB->fetchAll($sql);
	}

	function o_fGetObjectByCode ($code)
	{
		$ret = null;
		foreach ($this->a_Objects as $key => $value)
		{
			if ( $value->ObjectCode == $code )
			{
				$ret = $value;
			}
		}
		return $ret;
	}
	function o_fGetObjectByName ($name)
	{
		$ret = null;
		foreach ($this->a_Objects as $key => $value)
		{
			if ( $value->sz_Name == $name )
			{
				$ret = $value;
			}
		}
		return $ret;
	}
	/**
	 * Check required
	 *
	 * @return boolean
	 */
	function b_fCheckRequire ()
	{
		/* Check required fields */
		foreach ($this->a_Objects as $key => $value)
		{
			$value->v_fInit($this->i_IFID, $this->i_DepartmentID, 0, 0);
			if ( $value->bMain )
			{
				$value->LoadFields();
				$check = $value->checkRequire();
				foreach ($check as $key => $f)
				{
					if ( !$ret )
					{
						return false;
					}
				}

			}
		}
		/* Check condition of the step */

		return true;
	}

	/**
	 * Update data of form
	 *
	 * @return boolean
	 */
	function b_fUpdateData ()
	{
		if ( !$this->i_IFID )
		{
			return;
		}
		foreach ($this->a_Objects as $o_Object)
		{
			if ( $o_Object->v_fInit($this->i_IFID, $this->i_DepartmentID, 0, 0) )
			{
				$o_Object->LoadFields();
				$o_Object->b_fUpdateData();
			}
		}
	}
	/**
	 * get deleted form by user
	 *
	 * @return array
	 */
	public function a_fGetDelFormsByUser ($o_User)
	{
		$sql = sprintf('select qsforms.*,qsiforms.*,qsusers.*,qsworkflowsteps.Name as SName,qsftrace.Logs,last.UserName as LastUser,qsftrace.Date,
								IFNULL((select case when UID=%1$d then 1 else 0 end from qsfreader where IFID=qsiforms.IFID order by qsfreader.UID!=%1$d limit 1),1) as Accepted
                                from qsiforms inner join qsforms on qsforms.FormCode=qsiforms.FormCode
                                inner join qsusers on qsiforms.UID=qsusers.UID
                                left join qsworkflows on qsworkflows.FormCode=qsforms.FormCode and Actived=1
                                left join qsworkflowsteps on qsworkflowsteps.StepNo=qsiforms.Status and qsworkflowsteps.WFID=qsworkflows.WFID
                                left join (select * from (select * from qsftrace order by IFID, Date limit 999999999) as trace group by IFID) as qsftrace on qsftrace.IFID = qsiforms.IFID
                                inner join qsusers as last on last.UID = qsftrace.UID
                                where qsiforms.UID=%1$d and qsiforms.deleted=1
                                order by ifnull(Date,SDate) Desc', $o_User->user_id);
		return $this->_o_DB->fetchAll($sql);
	}

	/**
	 * Get main object of form
	 *
	 * @return Qss_Model_Object
	 */
	public function o_fGetMainObject ()
	{

		if ( is_array($this->a_Objects) )
		{
			foreach ($this->a_Objects as $o_Object)
			{
				if ( $o_Object->b_Main )
				{
					return $o_Object;
				}
			}
		}
	}
	public function o_fGetMainObjects ()
	{
		$retval = array();
		if ( is_array($this->a_Objects) )
		{
			foreach ($this->a_Objects as $o_Object)
			{
				if ( $o_Object->b_Main )
				{
					$retval[$o_Object->ObjectCode] =  $o_Object;
				}
			}
		}
		return $retval;
	}
	/**
	 * Get sub objects of form
	 *
	 * @return aray of Qss_Model_Object
	 */
	public function a_fGetSubObjects ()
	{
		$retval = array();
		if ( is_array($this->a_Objects) )
		{
			foreach ($this->a_Objects as $key => $object)
			{
				if ( !$object->b_Main )
				{
					$retval[$key] = $object;
				}
			}
		}
		return $retval;
	}


	function i_fGetCerrentStep ()
	{
		$sql = sprintf('select Status from qsiforms where IFID=%1$d and DepartmentID=%2$d', $this->i_IFID, $this->i_DepartmentID);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL )
		{
			return $dataSQL->Status;
		}
	}

	public function v_fTrace ($uid, $logs)
	{
		$data = array('IFID'		=>	$this->i_IFID,
						'UID'		=>	$uid,
						'Date'		=>	time(),
						'Logs'		=>	$logs);
		$sql = sprintf('insert into qsftrace%1$s',
						$this->arrayToInsert($data));
		$id = $this->_o_DB->execute($sql);
		//lưu excel file
		$params = array();
		$params['fid'] = $this->FormCode;
		$params['ifid'] = $this->i_IFID;
		$params['deptid'] = $this->i_DepartmentID;
		$params['type'] = 3;
		$params['objid'] = '';
		$params['excel_import'] = QSS_DATA_DIR.'/documents/'.$id.'.xlsx';
		$service = new Qss_Service();
		$service = $service->Form->Export($params);
		$retval = $service->getData();
		if(isset($retval[0]))
		{
			$fn = $retval[0];
			if(file_exists($fn))
			{
				rename($fn,QSS_DATA_DIR.'/documents/'.$id.'.xlsx');
			}
		}
	}
	public function updateError($error,$message)
	{
		$sql = sprintf('update qsiforms set Error = %1$d, ErrorMessage = %2$s,EDate = now() where IFID = %3$d and DepartmentID = %4$d',
		$error,$this->_o_DB->quote($message),$this->i_IFID,$this->i_DepartmentID);
		$this->_o_DB->execute($sql);
	}
	public function getRefUsers()
	{
		$sql = sprintf('select distinct qsusers.* from qsusers
					 where UID in(select UID from qsiforms where IFID=%1$d) 
					 #or UID in(select toUID from qsftrace where IFID=%1$d)
					 or UID in(select UID from qsftrace where IFID=%1$d)
					 or UID in(select UID from qsfcomment where IFID=%1$d)
					 or UID in (select UID from qsusergroups where GroupID in 
					 (select GroupID from qsiforms where UID=0 and IFID=%1$d))',
		$this->i_IFID);
		return $this->_o_DB->fetchAll($sql);
	}

	public function read($uid)
	{
		if($this->i_IFID)
		{
			$sql = sprintf('insert ignore into qsfreader set IFID = %1$d, UID = %2$d ,Date =now()',$this->i_IFID,$uid);
			return $this->_o_DB->execute($sql);
		}
	}
	public function updateReader($uid)
	{
		/*if($this->i_ProcessDays)
		{
			$sql = sprintf('delete from qsfreader where IFID = %1$d',$this->i_IFID);
			$this->_o_DB->execute($sql);
			$sql = sprintf('replace into qsfreader(IFID,UID,Date) values(%1$d,%2$d,now())',$this->i_IFID,$uid);
			$this->_o_DB->execute($sql);
		}*/
	}
	public function getReaders()
	{
		$sql = sprintf('select *,UNIX_TIMESTAMP(Date) as Time from qsfreader
						inner join qsusers on qsusers.UID = qsfreader.UID
						where IFID = %1$d',$this->i_IFID);

		return $this->_o_DB->fetchAll($sql);
	}
	public function getComments()
	{
		$sql = sprintf('select qsusers.UserName,Comment,Date,UNIX_TIMESTAMP(Date) as Time from qsfcomment
						inner join qsusers on qsusers.UID = qsfcomment.UID
						where IFID = %1$d 
						union
						select qsusers.UserName,Logs as Comment,FROM_UNIXTIME(Date) as Date,Date as Time from qsftrace
						inner join qsusers on qsusers.UID = qsftrace.UID
						where IFID = %1$d
						order by Date desc',$this->i_IFID);
		return $this->_o_DB->fetchAll($sql);
	}
	public function comment($uid,$comment)
	{
		$sql = sprintf('insert into qsfcomment(IFID,UID,Comment)
						values(%1$d,%2$d,%3$s)',
		$this->i_IFID,$uid,$this->_o_DB->quote($comment));
		return $this->_o_DB->execute($sql);
	}
	public function report($uid,$content,$date)
	{
		$sql = sprintf('insert into qsfreports(IFID,UID,Content,Date)
						values(%1$d,%2$d,%3$s,str_to_date(%4$s ,\'%%d-%%m-%%Y\'))',
		$this->i_IFID,$uid,$this->_o_DB->quote($content),$this->_o_DB->quote($date));
		return $this->_o_DB->execute($sql);
	}
	public function checkSharingUser($uid)
	{
		$sql = sprintf('select 1 from qsfsharing where IFID = %1$d and UID = %2$d',
		$this->i_IFID,$uid);
		$ret = $this->_o_DB->fetchOne($sql);
		return $ret? 1 :0 ;
	}

	public function hasPrint()
	{
		$sql = sprintf('select 1
				from qsfprints where FormCode = %1$s' ,
			$this->_o_DB->quote($this->FormCode));
		return (bool)$this->_o_DB->fetchOne($sql);
	}
	public function hasDesign()
	{
		$sql = sprintf('select 1
				from qsfdesigns where FormCode = %1$s' ,$this->_o_DB->quote($this->FormCode));
		return (bool)$this->_o_DB->fetchOne($sql);
	}
	public function getFieldByCode($ObjectCode,$FieldCode)
	{
		foreach($this->a_Objects as $object)
		{
			if($object->ObjectCode == $ObjectCode)
			{
				$fields = $object->loadFields();
				foreach ($fields as $key => $value)
				{
					if ( $value->FieldCode == $FieldCode)
					return $value;
				}
			}
		}
		return new Qss_Model_Field();
	}
	public function getFields()
	{
		$retval = array();
		foreach($this->a_Objects as $object)
		{
			$retval = array_merge($retval,$object->loadFields());
		}
		return $retval;
	}
	public function countDocument()
	{
		$sql = sprintf('select count(*) as Tol from qsfdocuments where IFID = %1$d',$this->i_IFID);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return ($dataSQL->Tol?$dataSQL->Tol:'');
	}
	public function countEvent()
	{
		if($this->i_Type == Qss_Lib_Const::FORM_TYPE_PROCESS)
		{
			$sql = sprintf('select count(*) as Tol from qsfprocesscalendar where IFID != 0 and IFID = %1$d',$this->i_IFID);
		}
		else
		{
			$sql = sprintf('select count(*) as Tol from qseventlogs where IFID != 0 and IFID = %1$d',$this->i_IFID);
		}
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return ($dataSQL->Tol?$dataSQL->Tol:'');
	}
	public function countProcessLog()
	{
		$sql = sprintf('select count(*) as Tol from qsfprocesslog where IFID = %1$d',$this->i_IFID);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return ($dataSQL->Tol?$dataSQL->Tol:'0');
	}
	function sz_fGetSQLByUser (Qss_Model_UserInfo $o_User,
	$i_CurrentPage = 1,
	$i_Limit = 50,
	$fieldorder = array(),
	$a_Condition = array(),//filter cretical
	$groupbys=array(),
	$status = array(),//filter by status
	$uid = '',
	$tree = true)//filter by uid
	{
		$params = $a_Condition;
		$retval = array();
		$filter = array();
		$mainobjects = $this->o_fGetMainObjects();
		$filterstatus = '';
		$treeselect = '';
		$where = '';
		$join = '';
		if ( count($a_Condition) )
		{
			if(isset($a_Condition['IFID']))//lọc theo IFID dành cho export chỉ 1 dòng
			{
				$where .= sprintf(' and qsiforms.IFID = %1$d ',
										$a_Condition['IFID']);
			}
			foreach($this->a_Objects as $item)
			{
				$hasSearch = false;
				$alias = $item->b_Main?'v':$item->ObjectCode;
				foreach ($item->loadFields() as $key => $f)
				{
					$fieldalias = ($this->o_fGetMainObject()->ObjectCode === $f->ObjectCode)?('v.'.$f->FieldCode):($f->ObjectCode.'.'.$f->FieldCode);
					if(isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode.'_R']))
					{
						$val = $a_Condition[$f->ObjectCode . '_' . $f->FieldCode.'_R'];
						$where .= sprintf(' and %1$s = %2$d',
									$alias . '.Ref_' . $f->FieldCode, $val);
						$hasSearch = true;
					}
					if ( $f->bSearch )
					{
						if(!isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode])
								&& !isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode.'_S'])
								&& !isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode.'_E']))
						{
							continue;
						}
						if(($f->intInputType == 5 || $f->intInputType == 3) && $f->getJsonRegx())
						{
							$val = $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
							if ( $val )
							{
								$where .= sprintf(' and %1$s like %2$s ',
										$alias . '.Ref_' . $f->FieldCode,
										$this->_o_DB->quote( '%'.$val.'%' ));
								$hasSearch = true;
							}
							continue;
						}
						switch ( $f->intFieldType )
						{
							case 1:
							case 2:
							case 3:
							case 4:
							case 12:
							case 13:
							case 14:
							case 15:
							case 16:
								$val = $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
								if ( $val )
								{
									$where .= sprintf(' and replace(%1$s, "\n"," ") like trim(%2$s) ',
											$alias . '.' . $f->FieldCode,
											$this->_o_DB->quote( '%'.$val.'%' ));
									$hasSearch = true;
								}
								break;
							case 5:
							case 6:
								$val = $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
								if ( $val )
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
											$regcon .= sprintf('%1$s %2$s ', $alias . '.' . $f->FieldCode, $value);
										}
										$where .= sprintf(' and (%1$s) ', $regcon);
									}
									else
									{
										$where .= sprintf(' and %1$s = %2$d ',
											$alias . '.' . $f->FieldCode,
											$val);
									}
									$hasSearch = true;
								}
								break;
							case 11:
								$val = $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
								if ( $val )
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
											$regcon .= sprintf('%1$s/1000 %2$s ', $alias . '.' . $f->FieldCode, $value);
										}
										$where .= sprintf(' and (%3$s) ', $regcon);
									}
									else
									{
										$where .= sprintf(' and %1$s/1000 = %2$d ',
											$alias . '.' . $f->FieldCode,
											$val);
									}
									$hasSearch = true;
								}
								break;
							case 7:
								$val = (int) $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
								if ( $val != -1 )
								{
									if($val == 1)
									{
										$where .= sprintf(' and %1$s = %2$d ',
											$alias . '.' . $f->FieldCode,
											$val);
									}
									else
									{
										$where .= sprintf(' and (%1$s is null or %1$s = 0) ',
											$alias . '.' . $f->FieldCode,
											$val);
									}
									$hasSearch = true;
								}
								break;
							case 10:
								$val1 = isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode . '_S'])?$a_Condition[$f->ObjectCode . '_' . $f->FieldCode . '_S']:'';
								$val2 = isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode . '_E'])?$a_Condition[$f->ObjectCode . '_' . $f->FieldCode . '_E']:'';
								if ( $val1 && $val2 )
								{
									$where .= sprintf(' and %1$s between str_to_date(%2$s ,\'%%d-%%m-%%Y\') and str_to_date(%3$s ,\'%%d-%%m-%%Y\')',
									$alias . '.' . $f->FieldCode, $this->_o_DB->quote($val1), $this->_o_DB->quote($val2));
									$hasSearch = true;
								}
								elseif ( $val1 && !$val2 )
								{
									$where .= sprintf(' and %1$s >= str_to_date(%2$s ,\'%%d-%%m-%%Y\')',
									$alias . '.' . $f->FieldCode, $this->_o_DB->quote($val1));
									$hasSearch = true;
								}
								elseif ( !$val1 && $val2 )
								{
									$where .= sprintf(' AND %1$s <= str_to_date(%2$s ,\'%%d-%%m-%%Y\')',
									$alias . '.' . $f->FieldCode, $this->_o_DB->quote($val2));
									$hasSearch = true;
								}
								break;
						}
					}
				}
				if($hasSearch && $this->o_fGetMainObject()->ObjectCode !== $f->ObjectCode)
				{
					$join .= sprintf(' left join %1$s on v.IFID_%2$s = %1$s.IFID_%2$s ',$item->ObjectCode,$this->FormCode);
				}
			}
		}

		$order = array();
		$sort = false;
		foreach($mainobjects as $object)
		{
			if($object->b_Tree && (!count($groupbys) || !is_array($groupbys)))//đã group by thì không cần tree nữa
			{
				$treeselect = sprintf(' ,(SELECT count(*) FROM %1$s as u WHERE u.lft<=v.lft and u.rgt >= v.rgt) as level ',
									$object->ObjectCode);
				$treecookie = Qss_Cookie::get('form_'.$this->FormCode.'_tree', 'a:0:{}');
				$treecookie = unserialize($treecookie);
				$order[] = 'v.lft';
				foreach ($treecookie as $closefid)
				{
					$where .= sprintf(' and not (lft > ifnull((SELECT lft FROM %1$s WHERE IFID_%2$s = %3$d),0) and v.rgt < ifnull((SELECT rgt FROM %1$s WHERE IFID_%2$s = %3$d),0)) ',
								$object->ObjectCode,
								$this->FormCode,
								$closefid);

				}
				$treeselect .= sprintf(' , case when v.IFID in (%1$s) then 1 else 0 end as close ',implode(',', count($treecookie)?$treecookie:array(-1)));
				//echo $where;die;
			}
			if(count($groupbys) && is_array($groupbys))
			{
				foreach ($groupbys as $groupby)
				{
					if($groupby == -2)
					{
						$order[] = sprintf('qsiforms.UID');
					}
					elseif($groupby == -1 && $this->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE)
					{
						if(isset($fieldorder[-1]))
						{
							$order[] = sprintf('qsiforms.Status %1$s',$fieldorder[-1]);
						}
						else
						{
							$order[] = sprintf('qsiforms.Status');
						}
					}
					else
					{
						$fgroupby = $this->getFieldByCode($object->ObjectCode,$groupby);
						if($fgroupby->ObjectCode == $object->ObjectCode
							&& $fgroupby->intFieldType != 4
							&& $fgroupby->intFieldType != 9
							&& $fgroupby->intFieldType != 8
							&& $fgroupby->intFieldType != 17 )
						{
							if(isset($fieldorder[$groupby]))
							{
								$order[] = sprintf('v.%1$s %2$s',$fgroupby->FieldCode,$fieldorder[$groupby]);
							}
							else
							{
								$order[] = sprintf('v.%1$s',$fgroupby->FieldCode);
							}
							$sort = true;
						}
					}
				}
			}
			if(!count($fieldorder) && $object->orderField)//nếu ko order by thì lấy mặc định
			{
				$foder = $this->getFieldByCode($object->ObjectCode,$object->orderField);
				if ( $foder->ObjectCode == $object->ObjectCode
					&& $foder->intFieldType != 4
					&& $foder->intFieldType != 9
					&& $foder->intFieldType != 8
					&& $foder->intFieldType != 17 )
				{
					$jsonData = $object->getJsonData();
					if(is_array($jsonData))
					{
						$order[] = sprintf('FIELD(%2$s,%1$s) DESC', "'".implode(',', array_keys($jsonData))."'",$foder->FieldCode);
						//$order[] = sprintf('qsiforms.SDate DESC');
						//@TODO$order[] = sprintf('TIME(FROM_UNIXTIME(qsiforms.SDate)) DESC');
					}
					else
					{
						$order[] = sprintf('%2$s %1$s', $object->orderType,$foder->FieldCode);
					}
					$sort = true;
				}
				else 
				{
					$order[] = sprintf('%2$s %1$s', $object->orderType,$object->orderField);
				}
			}
			foreach($fieldorder as $k=>$v)
			{
				if($k == -1)
				{
					$order[] = sprintf('qsiforms.Status %1$s', $v);
				}
				else
				{
					$foder = $this->getFieldByCode($object->ObjectCode, $k);
					if ( $foder->ObjectCode == $object->ObjectCode
							&& $foder->intFieldType != 4
							&& $foder->intFieldType != 9
							&& $foder->intFieldType != 8
							&& $foder->intFieldType != 17 )
					{
						$order[] = sprintf('v.%2$s %1$s', $v,$foder->FieldCode);
						$sort = true;
					}
				}
			}
			if($this->i_WorkFlowID)
			{
				$order[] = sprintf('v.IFID_%1$s DESC',$this->FormCode);
				//$order[] = sprintf('DATE(FROM_UNIXTIME(qsiforms.SDate)) DESC');@TODO
			}
			else
			{
				$order[] = sprintf('v.IFID_%1$s',$this->FormCode);
			}
			//if(!$i_FieldOrder && $this->i_WorkFlowID && !$object->orderField)
			{
				//$order[] = sprintf('qsiforms.SDate DESC');
				//@TODO$order[] = sprintf('TIME(FROM_UNIXTIME(qsiforms.SDate)) DESC');
			}
			$classname = 'Qss_Bin_Filter_'.$object->ObjectCode;
			if(class_exists($classname))
			{
				//echo $classname;die;
				$exfilter = new $classname($o_User,$params);
				//$join .= $exfilter->getJoin();
				$where .= $exfilter->getWhere();
				$join .= $exfilter->getJoin();
			}
			//gộp với tab
			if(count($status) && $this->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE)
			{
				/*$classname = 'Qss_Bin_Tabs_'.$this->FormCode.'_Tab'.$status;
				if(class_exists($classname) && 0)
				{
					$exfilter = new $classname($o_User,$params);
					//$join .= $exfilter->getJoin();
					$where .= $exfilter->getWhere();
				}
				else
				{
					$where .= sprintf(' and qsiforms.Status in (%1$s)',implode(',',$status));
				}*/
				$where .= sprintf(' and qsiforms.Status in (%1$s)',implode(',',$status));
			}
			if(count($order))
			{
				$where .= sprintf(' order by %1$s',implode(',', $order));
			}
			//start build sql
			$readselect = '';
			$readjoin = '';
			if($this->reader)
			{
				$readselect = sprintf(',if(qsfreader.IFID is null,1,0) as unRead');
				$readjoin = sprintf('left join qsfreader on qsfreader.IFID = qsiforms.IFID and qsfreader.UID=%1$d'
						,$this->_user->user_id);
			}
			if ( $this->i_Type == 1 || $this->i_Type == Qss_Lib_Const::FORM_TYPE_PROCESS)
			{
				$sql = sprintf('select v.*, qsiforms.*,qsusers.UserName %8$s
									/*%1$s*/
									from %2$s as v
									inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
									inner join qsusers on qsiforms.UID=qsusers.UID
									%7$s
									%9$s
									where qsiforms.Deleted<>1 
									%5$s
									%6$s', /* */
						$treeselect,
						$this->o_fGetMainObject()->ObjectCode,
						$this->FormCode,
						$this->_o_DB->quote($this->FormCode),
						$filterstatus,
						$where,
						$join,
						$readselect,
						$readjoin
						);
			}
			elseif ( $this->i_Type == 2 )
			{
				$sql = sprintf('select v.*, qsiforms.*,qsusers.UserName %9$s
									/*%1$s*/
									from %2$s as v
									inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
									inner join qsusers on qsiforms.UID=qsusers.UID
									%8$s
									%10$s
									where qsiforms.Deleted<>1 
									and qsiforms.DepartmentID in(%7$s)
									%5$s
									%6$s', /* */
						$treeselect,
						$this->o_fGetMainObject()->ObjectCode,
						$this->FormCode,
						$this->_o_DB->quote($this->FormCode),
						$filterstatus,
						$where,
						$o_User->user_dept_id . ',' . $o_User->user_dept_list,
						$join,
						$readselect,
						$readjoin);
			}
			else
			{
				$statusRights = '';
				foreach ($this->a_Rights as $k=>$v)
				{
					if($statusRights)
					{
						$statusRights .= ' or ';
					}
					$statusRights .= sprintf('(case when Status = %1$d then %2$d end)'
						,$k,$v);
				}
				$sql = sprintf('select v.*, qsiforms.*,qsusers.UserName %11$s
									/*%1$s*/
									from %2$s as v
									inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
									inner join qsusers on qsiforms.UID=qsusers.UID
									%9$s
									%12$s
									where (%10$s)
									and qsiforms.Deleted<>1 
									and qsiforms.DepartmentID in(%7$s) 
									%5$s
									%6$s', /* */
						$treeselect,
						$this->o_fGetMainObject()->ObjectCode,
						$this->FormCode,
						$this->_o_DB->quote($this->FormCode),
						$filterstatus,
						$where,
						$o_User->user_dept_id . ',' . $o_User->user_dept_list,
						$this->i_Rights,
						$join,
						$statusRights,
						$readselect,
						$readjoin);
			}
			if($treeselect && $tree)
			{
				//$sql = str_ireplace(array('select','from','where'),array('SELECT','FROM','WHERE'),$sql);
				$sql = sprintf('select v.* %1$s from (%2$s LIMIT 18446744073709551615) as v', $treeselect,$sql);
			}
			$retval[$object->ObjectCode] = $sql;
			//for sort
			if($sort)
			{
				$retval[0] = $sql;
			}
		}
		//echo $sql;die;
		if(!isset($retval[0]))
		{
			$retval[0] = $retval[$this->o_fGetMainObject()->ObjectCode];
		}
		return $retval;
	}
	public function lock($userid,$verityid)
	{
		$sql = sprintf('update qsiforms set LockID=%1$d,VerifyID=%3$d where IFID = %2$d',
						$userid,$this->i_IFID,$verityid);
		$this->_o_DB->execute($sql);
	}
	public function changeUser($userid)
	{
		$sql = sprintf('update qsiforms set UID=%1$d where IFID = %2$d',
						$userid,$this->i_IFID);
		$this->_o_DB->execute($sql);
	}
	public function changeStatus($stepno)
	{
		$sql = sprintf('update qsiforms set Status=%1$d where IFID = %2$d',
						$stepno,$this->i_IFID);
		$this->_o_DB->execute($sql);
	}
	public function unLock()
	{
		$sql = sprintf('update qsiforms set LockID=NULL where IFID = %1$d',
						$this->i_IFID);
		$this->_o_DB->execute($sql);
	}
	public function setModify ()
	{
		$sql = sprintf('update qsiforms set LastModify=%1$d where IFID=%2$d', /* */
				time(), 
				$this->i_IFID);
		$this->_o_DB->execute($sql);
	}
	public function getCrossLinks()
	{
		//xử lý tất cả lookup
		$exists = array();
		$sql = sprintf('select distinct qsfields.FieldCode,qsfields.ObjectCode as object,qsfields.RefObjectCode as curobject,
					qsfobjects.FormCode,qsforms.*
					from qsfields 
					inner join qsfobjects on qsfobjects.ObjectCode = qsfields.ObjectCode  
					inner join qsforms on qsforms.FormCode = qsfobjects.FormCode
					where qsforms.Effected = 1 and qsfields.RefFormCode="%1$s" 
					and (qsfobjects.FormCode != "%1$s" or (qsfobjects.FormCode = "%1$s" and qsfobjects.Main=1))
					and (RefFieldCode is not null or RefFieldCode != \'\')
					order by FieldNo',$this->FormCode);
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$sql = sprintf('select 1 from %1$s a
						inner join %2$s as b on a.Ref_%3$s = b.IOID
						where b.IFID_%4$s = %5$d
						limit 1',
						$item->object,
						$item->curobject,
						$item->FieldCode,
						$this->FormCode,
						$this->i_IFID);
			$checkSQL = $this->_o_DB->fetchOne($sql);
			if($checkSQL)
			{
				$exists[] = $item;
			} 
		}
		return $exists;
	}
	function saveIOIDLink ($fromioid,$toioid,$fromifid,$toifid )
	{
		$sql = sprintf('replace into qsioidlink(FromIOID,ToIOID,FromIFID,ToIFID) 
						values(%1$d,%2$d,%3$d,%4$d)',
				$fromioid,
				$toioid,
				$fromifid,
				$toifid);
		return $this->_o_DB->execute($sql);
	}
	function deleteIOIDLink ($fromioid,$toioid,$fromifid,$toifid)
	{
		$sql = sprintf('delete from qsioidlink where FromIOID = %1$d and ToIOID = %2$d and FromIFID = %3$d and ToIFID = %4$d',
				$fromioid,
				$toioid,
				$fromifid,
				$toifid);
		return $this->_o_DB->execute($sql);
	}
	function getIOIDLink ($fromioid,$fromifid)
	{
		$sql = sprintf('select * from qsioidlink where FromIOID = %1$d and FromIFID = %2$d and ToIOID != 0',
				$fromioid,
				$fromifid);
		return $this->_o_DB->fetchOne($sql);
	}
	function getCalendar ()
	{
		$sql = sprintf('select * from qsfcalendar where FormCode = %1$s ',$this->_o_DB->quote($this->FormCode));
		return $this->_o_DB->fetchOne($sql);
	}
	public function getUsers ()
	{
		$sql = sprintf('select distinct qsusers.* from qsusers
						inner join qsiforms on qsiforms.UID = qsusers.UID 
						where FormCode = %1$s',$this->_o_DB->quote($this->FormCode));
		return $this->_o_DB->fetchAll($sql);
	}
	public function getDocuments($ioid = 0)
	{
		$sql = sprintf('select qsfdocuments.*,qsdocumenttype.Code,qsdocumenttype.Type,
					qsworkflowsteps.Name,qsdocuments.Ext,qsdocuments.DID,qsdocuments.Name as docname,
					qsdocuments.Modify,qsdocuments.CDate
					from qsfdocuments 
					left join qsdocumenttype on qsdocumenttype.DTID = qsfdocuments.DTID
					left join qsdocuments on qsdocuments.DID = qsfdocuments.DID
					left join qsworkflowsteps on qsworkflowsteps.StepNo=qsfdocuments.StepNo and qsworkflowsteps.WFID=%2$d
					where qsfdocuments.IFID = %1$d and IFNULL(IOID,0) = %3$d 
					order by qsfdocuments.StepNo,qsdocumenttype.Type,ifnull(qsdocuments.Modify,qsdocuments.CDate) desc',
				$this->i_IFID,
				$this->i_WorkFlowID,
				$ioid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getActivities()
	{
		$sql = sprintf('select qseventlogs.*,qseventtype.TypeName,
					qsworkflowsteps.Name
					from qseventlogs
					left join qseventtype on qseventlogs.ETID = qseventtype.TypeID
					left join qsworkflowsteps on qsworkflowsteps.StepNo=qseventlogs.StepNo and qsworkflowsteps.WFID=%2$d
					where qseventlogs.IFID = %1$d 
					order by qseventlogs.StepNo,qseventtype.TypeName',$this->i_IFID,$this->i_WorkFlowID);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getDocumentById($id)
	{
		$sql = sprintf('select qsfdocuments.*,qsdocumenttype.Code,qsdocumenttype.Type,
					qsworkflowsteps.Name,qsdocuments.Ext,qsdocuments.DID,qsdocuments.Name as docname,
					qsdocuments.Modify,qsdocuments.CDate
					from qsfdocuments 
					left join qsdocumenttype on qsdocumenttype.DTID = qsfdocuments.DTID
					left join qsdocuments on qsdocuments.DID = qsfdocuments.DID
					left join qsworkflowsteps on qsworkflowsteps.StepNo=qsfdocuments.StepNo and qsworkflowsteps.WFID=%2$d
					where qsfdocuments.IFID = %1$d and qsfdocuments.FDID=%3$f',
				$this->i_IFID,
				$this->i_WorkFlowID,
				$id);
		return $this->_o_DB->fetchOne($sql);
	}
	public function getActivityById($id)
	{
		$sql = sprintf('select qseventlogs.*,qseventtype.TypeName,
					qsworkflowsteps.Name
					from qseventlogs
					left join qseventtype on qseventlogs.ETID = qseventtype.TypeID
					left join qsworkflowsteps on qsworkflowsteps.StepNo=qseventlogs.StepNo and qsworkflowsteps.WFID=%2$d
					where qseventlogs.IFID = %1$d and qseventlogs.ELID =%3$d
					',$this->i_IFID,$this->i_WorkFlowID,$id);
		return $this->_o_DB->fetchOne($sql);
	}
	public function attach($params)
	{
		$id = $params['fdid'];
		//@todo chưa làm vội
		$data = array(
				'DTID'=>@$params['document_type'],
				'IFID'=>$this->i_IFID,
				'IOID'=>(int)@$params['ioid'],
				'StepNo'=>$this->i_Status,
				'DID'=>$params['document_id'],
				'Reference'=>$params['reference']);
		/*if($params['document'] && $params['document_id'])
		{
			$sql = sprintf('update qsdocuments set Name = %1$s where DID=%2$d',
				$this->_o_DB->quote($params['document']),
				$params['document_id']);
			$this->_o_DB->execute($sql);
		}*/
		if($id)
		{
			$sql = sprintf('update qsfdocuments set %1$s where FDID = %2$d',
				$this->arrayToUpdate($data),$id);
		}
		else 
		{
			$sql = sprintf('insert into qsfdocuments%1$s',
				$this->arrayToInsert($data));
		}
		return $this->_o_DB->execute($sql);
	}
	public function dettach($id)
	{
		$sql = sprintf('delete from qsfdocuments where FDID = %1$d',
				$id);
		$this->_o_DB->execute($sql);
	}
	public function getRequiredDocuments()
	{
		$sql = sprintf('select * from qsfrecords
					inner join qsdocumenttype on qsdocumenttype.DTID = qsfrecords.DTID
					where qsfrecords.DTID not in(select DTID from qsfdocuments where IFID = %1$d and StepNo=%2$d)
					and qsfrecords.StepNo = %2$d and qsfrecords.FormCode = "%3$s"
					order by qsdocumenttype.Code',
					$this->i_IFID,
					$this->i_Status,
					$this->FormCode);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getRequiredActivities()
	{
		$sql = sprintf('select * from qsfactivities
					inner join qseventtype on qseventtype.TypeID = qsfactivities.ETID
					where qsfactivities.ETID not in(select ETID from qseventlogs where IFID = %1$d and StepNo=%2$d and Status=1)
					and qsfactivities.StepNo = %2$d and qsfactivities.FormCode = "%3$s"
					order by qseventtype.TypeName',
					$this->i_IFID,
					$this->i_Status,
					$this->FormCode);
		return $this->_o_DB->fetchAll($sql);
	}
	public function removeActivity($id)
	{
		$sql = sprintf('delete from qseventlogs where ELID = %1$d',
				$id);
		$this->_o_DB->execute($sql);
	}
	public function getStatusData($lang)
	{
		$lang = Qss_Translation::getInstance()->getLanguage();
		$name = ($lang=='vn')?'':'_'.$lang;
		$sql = sprintf('select Color,Name%3$s as Name 
								from qsworkflowsteps where WFID = %1$d and StepNo = %2$d',
				$this->i_WorkFlowID,
				$this->i_Status?$this->i_Status:1,
				$name);
		return $this->_o_DB->fetchOne($sql);
	}
	public function approve($said,$uid)
	{
		$sql = sprintf('replace into qsfapprover(IFID,StepNo,UID,SAID,RDate)
				values(%4$d,%1$d,%2$d,%3$d,NULL)',
				$this->i_Status,
				$uid,
				$said,
				$this->i_IFID);
				//echo $sql;die;
		$this->_o_DB->execute($sql);
		$sql = sprintf('update qsiforms set EDate = now()
				where IFID = %1$d',
				$this->i_IFID);
		$this->_o_DB->execute($sql);
	}
	public function reject($said,$uid)
	{
		$sql = sprintf('update qsfapprover
				set RDate = now()
				where StepNo=%1$d and UID = %2$d and IFID=%4$d and SAID = %3$d',
				$this->i_Status,
				$uid,
				$said,
				$this->i_IFID);
		$this->_o_DB->execute($sql);
	}

	public function getApproveByStep($formCode, $stepNo, $uid = 0, $said = '')
    {
        $where = ($said)?" AND stepApprover.SAID = {$said} ":"";
        $where.= ($uid)?sprintf(' AND UID = %1$d ', $uid):'';
        $lang  = ($this->_user->user_lang != 'vn')?"_{$this->_user->user_lang}":'';


        $sql = sprintf('
            SELECT 
                stepApprover.* 
                , workFlowStep.StepType as intStepType
                , workFlowStep.Name%4$s as StepName
            FROM qsworkflows AS workFlow
            INNER JOIN qsworkflowsteps AS workFlowStep ON workFlow.WFID = workFlowStep.WFID
            INNER JOIN qsstepapprover AS stepApprover ON workFlowStep.SID = stepApprover.SID
            INNER JOIN qsstepapproverrights AS stepApproverRight ON stepApprover.SAID = stepApproverRight.SAID
            WHERE workFlow.FormCode = %1$s AND workFlowStep.StepNo = %2$d %3$s
            ORDER BY stepApprover.OrderNo
        ', $this->_o_DB->quote($formCode), $stepNo, $where, $lang);

        // echo '<pre>';echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }


	public function getApprovers()
	{
		$sql = sprintf('select  qsstepapprover.*,T.StepNo, T.UserName, qsworkflowsteps.StepType as intStepType,qsworkflowsteps.Name as StepName
					from qsstepapprover
					inner join qsworkflowsteps on qsworkflowsteps.SID = qsstepapprover.SID
					inner join qsworkflows on qsworkflows.WFID = qsworkflowsteps.WFID
					left join (select SAID,StepNo, group_concat(concat(UserName,":",DATE_FORMAT(ADate,"%%d-%%m-%%Y %%H:%%i")) SEPARATOR ",") as UserName from qsfapprover
					inner join qsusers on qsusers.UID = qsfapprover.UID
					where IFID = %1$d and RDate is null group by SAID,StepNo) as T
					on T.SAID = qsstepapprover.SAID 
					where FormCode = %2$s
					order by qsstepapprover.SID,OrderNo', 
				$this->i_IFID
				, $this->_o_DB->quote($this->FormCode));
//				echo '<pre>';echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
	public function changeDepartment($deptid)
	{
		$sql = sprintf('update qsiforms set DepartmentID = %1$d where IFID = %2$d',
					$deptid,
					$this->i_IFID);
		$this->_o_DB->execute($sql);
		foreach ($this->a_Objects as $object)
		{
			$sql = sprintf('update %1$s set DeptID = %2$s where IFID_%3$s = %4$d',
					$object->ObjectCode,
					$deptid,
					$this->FormCode,
					$this->i_IFID);
		$this->_o_DB->execute($sql);
		}
	}
	function sz_fGetSubSQLByUser(Qss_Model_UserInfo $o_User,
	$i_CurrentPage = 1,
	$i_Limit = 50,
	$i_FieldOrder = array(),
	$a_Condition = array(),//filter cretical 
	$groupbys=array(),
	$status = array(),//filter by status
	$objectcode = '')//filter by uid
	{
		$params = $a_Condition;
		$retval = array();
		$filter = array();
		$mainobjects = $this->o_fGetMainObjects();
		$filterstatus = '';
		/*if($this->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE && $status !== '')
		{
			$filterstatus .= sprintf(' and qsiforms.Status = %1$d',$status);
		}*/
		$treeselect = '';
		$where = '';
		/*if($this->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE && $uid !== '')
		{
			$filterstatus .= sprintf(' and qsiforms.UID = %1$d',$uid);
		}*/
		/*if($this->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE && !($this->i_Rights & Qss_Lib_Const::FORM_RIGHTS_SUPPER))
		{
			$where .= sprintf(' and qsiforms.UID = %1$d',$this->_user->user_id);
		}*/
		$join = '';
		if ( count($a_Condition) )
		{
			foreach($this->a_Objects as $item)
			{
				$hasSearch = false;
				$alias = $item->ObjectCode;
				foreach ($item->loadFields() as $key => $f)
				{
					$fieldalias = ($this->o_fGetMainObject()->ObjectCode === $f->ObjectCode)?('v.'.$f->FieldCode):($f->ObjectCode.'.'.$f->FieldCode);
					if ( $f->bSearch )
					{
						if(!isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode]) 
								&& !isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode.'_S']) 
								&& !isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode.'_E']))
						{
							continue;
						}
						if($f->intInputType == 5 || $f->intInputType == 3)
						{
							$val = $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
							if ( $val )
							{
								$where .= sprintf(' and %1$s like %2$s ', 
										$alias . '.Ref_' . $f->FieldCode, 
										$this->_o_DB->quote( '%'.$val.'%' ));
								$hasSearch = true;
							}	
							continue;
						}
						switch ( $f->intFieldType )
						{
							case 1:
							case 2:
							case 3:
							case 4:
							case 12:
							case 13:
							case 14:
							case 15:
							case 16:
								$val = $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
								if ( $val )
								{
									$where .= sprintf(' and %1$s like %2$s ', 
											$alias . '.' . $f->FieldCode, 
											$this->_o_DB->quote( '%'.$val.'%' ));
									$hasSearch = true;
								}
								break;
							case 5:
							case 6:
								$val = $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
								if ( $val )
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
											$regcon .= sprintf('%1$s %2$s ', $alias . '.' . $f->FieldCode, $value);
										}
										$where .= sprintf(' and (%1$s) ', $regcon);
									}
									else
									{
										$where .= sprintf(' and %1$s = %2$d ',
											$alias . '.' . $f->FieldCode, 
											$val);
									}
									$hasSearch = true;
								}
								break;
							case 11:
								$val = $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
								if ( $val )
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
											$regcon .= sprintf('%1$s/1000 %2$s ', $alias . '.' . $f->FieldCode, $value);
										}
										$where .= sprintf(' and (%3$s) ', $regcon);
									}
									else
									{
										$where .= sprintf(' and %1$s/1000 = %2$d ',
											$alias . '.' . $f->FieldCode, 
											$val);
									}
									$hasSearch = true;
								}
								break;
							case 7:
								$val = (int) $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
								if ( $val != -1 )
								{
									if($val == 1)
									{
										$where .= sprintf(' and %1$s = %2$d ',
											$alias . '.' . $f->FieldCode, 
											$val);
									}
									else 
									{
										$where .= sprintf(' and (%1$s is null or %1$s = 0) ',
											$alias . '.' . $f->FieldCode, 
											$val);
									}
									$hasSearch = true;
								}
								break;
							case 10:
								$val1 = isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode . '_S'])?$a_Condition[$f->ObjectCode . '_' . $f->FieldCode . '_S']:'';
								$val2 = isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode . '_E'])?$a_Condition[$f->ObjectCode . '_' . $f->FieldCode . '_E']:'';
								if ( $val1 && $val2 )
								{
									$where .= sprintf(' and %1$s between str_to_date(%2$s ,\'%%d-%%m-%%Y\') and str_to_date(%3$s ,\'%%d-%%m-%%Y\')', 
									$alias . '.' . $f->FieldCode, $this->_o_DB->quote($val1), $this->_o_DB->quote($val2));
									$hasSearch = true;
								}
								elseif ( $val1 && !$val2 )
								{
									$where .= sprintf(' and %1$s >= str_to_date(%2$s ,\'%%d-%%m-%%Y\')', 
									$alias . '.' . $f->FieldCode, $this->_o_DB->quote($val1));
									$hasSearch = true;
								}
								elseif ( !$val1 && $val2 )
								{
									$where .= sprintf(' %1$s <= str_to_date(%2$s ,\'%%d-%%m-%%Y\')', 
									$alias . '.' . $f->FieldCode, $this->_o_DB->quote($val2));
									$hasSearch = true;
								}
								break;
						}
					}
				}
				if($hasSearch && $this->o_fGetMainObject()->ObjectCode !== $f->ObjectCode)
				{
					$join .= sprintf(' left join %1$s on v.IFID_%2$s = %1$s.IFID_%2$s ',$item->ObjectCode,$this->FormCode);
				}
			}
		}
		
		$order = array();
		$sort = false;
		$object = $this->o_fGetObjectByCode($objectcode);
		{
			if($object->b_Tree)
			{	
				$order[] = sprintf('v.IFID_%1$s DESC',$this->FormCode);
				$order[] = sprintf('v.lft');
			}
			elseif($object->orderField)
			{
				$foder = $this->getFieldByCode($object->ObjectCode,$object->orderField);
				if ( $foder->ObjectCode == $object->ObjectCode && $foder->intFieldType != 4 && $foder->intFieldType != 9 && $foder->intFieldType != 8 )
				{
					$jsonData = $object->getJsonData();
					if(is_array($jsonData))
					{
						$order[] = sprintf('FIELD(%2$s,%1$s) DESC', "'".implode(',', array_keys($jsonData))."'",$foder->FieldCode);
					}
					else 
					{
						$order[] = sprintf('%2$s %1$s', $object->orderType,$foder->FieldCode);
					}
					$sort = true;
				}
				else
				{
					$order[] = sprintf('%2$s %1$s', $object->orderType,$object->orderField);
				}
				$order[] = sprintf('v.IFID_%1$s DESC',$this->FormCode);
			}
			else
			{
				$order[] = sprintf('v.IFID_%1$s DESC',$this->FormCode);
			}
			if($this->i_IFID)
			{
				$where .= sprintf(' and qsiforms.IFID = %1$d ',$this->i_IFID);
			}
			$classname = 'Qss_Bin_Filter_'.$this->o_fGetMainObject()->ObjectCode;
			if(!$this->i_IFID && class_exists($classname))
			{
				//echo $classname;die;
				$exfilter = new $classname($o_User,$params);
				//$join .= $exfilter->getJoin();
				$where .= str_ireplace('v.', $this->o_fGetMainObject()->ObjectCode.'.', $exfilter->getWhere());
				//$join .= $exfilter->getJoin();
				$join .= str_ireplace('v.', $this->o_fGetMainObject()->ObjectCode.'.', $exfilter->getJoin());
			}
			if(count($order))
			{
				$where .= sprintf(' order by %1$s',implode(',', $order));
			}
			if ( $this->i_Type == 1 || $this->i_Type == Qss_Lib_Const::FORM_TYPE_PROCESS)
			{
				$sql = sprintf('select v.*, qsiforms.*,qsusers.UserName
									%1$s
									from %8$s as v
									inner join %2$s on v.IFID_%3$s = %2$s.IFID_%3$s
									inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
									inner join qsusers on qsiforms.UID=qsusers.UID
									%7$s
									where qsiforms.Deleted<>1 
									%5$s
									%6$s', /* */
						$treeselect,
						$this->o_fGetMainObject()->ObjectCode, 
						$this->FormCode,
						$this->_o_DB->quote($this->FormCode), 
						$filterstatus,
						$where,
						$join,
						$objectcode);
			}
			elseif ( $this->i_Type == 2 )
			{
				$sql = sprintf('select v.*, qsiforms.*,qsusers.UserName
									%1$s
									from %9$s as v
									inner join %2$s on v.IFID_%3$s = %2$s.IFID_%3$s
									inner join qsiforms on qsiforms.IFID = %2$s.IFID_%3$s
									inner join qsusers on qsiforms.UID=qsusers.UID
									%8$s
									where qsiforms.Deleted<>1 
									and qsiforms.DepartmentID in(%7$s)
									%5$s
									%6$s', /* */
						$treeselect,
						$this->o_fGetMainObject()->ObjectCode, 
						$this->FormCode,
						$this->_o_DB->quote($this->FormCode), 
						$filterstatus,
						$where,
						$o_User->user_dept_id . ',' . $o_User->user_dept_list,
						$join,
						$objectcode);
			}
			else
			{
				$statusRights = '';
				foreach ($this->a_Rights as $k=>$v)
				{
					if($statusRights)
					{
						$statusRights .= ' or ';
					}
					$statusRights .= sprintf('(case when Status = %1$d then %2$d end)'
						,$k,$v);
				}
				$sql = sprintf('select v.*, qsiforms.*,qsusers.UserName
									%1$s
									from %11$s as v
									inner join %2$s on v.IFID_%3$s = %2$s.IFID_%3$s
									inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
									inner join qsusers on qsiforms.UID=qsusers.UID
									%9$s
									where (%10$s)
									and qsiforms.Deleted<>1 
									and qsiforms.DepartmentID in(%7$s) 
									%5$s
									%6$s', /* */
						$treeselect,
						$this->o_fGetMainObject()->ObjectCode, 
						$this->FormCode,
						$this->_o_DB->quote($this->FormCode), 
						$filterstatus,
						$where,
						$o_User->user_dept_id . ',' . $o_User->user_dept_list,
						$this->i_Rights,
						$join,
						$statusRights,
						$objectcode);
			}
			$retval[$object->ObjectCode] = $sql;
			//for sort
			if($sort)
			{
				$retval[0] = $sql;
			}
		}
		//echo $sql;die;
		if(!isset($retval[0]))
		{
			$retval[0] = $retval[$objectcode];
		}
		return $retval;
	}

    public function getApproverByStep($ifid, $stepNo)
    {
        $sql = sprintf('
            SELECT qsusers.*
            FROM qsstepapprover
            LEFT JOIN qsfapprover ON qsstepapprover.SAID = qsfapprover.SAID
            LEFT JOIN qsusers ON qsusers.UID = qsfapprover.UID
            WHERE qsfapprover.IFID = %1$d AND qsfapprover.StepNo = %2$d
            ORDER BY qsstepapprover.OrderNo
        ', $ifid, $stepNo);
        return $this->_o_DB->fetchAll($sql);
    }
	public function getRecordRights()
	{
		$sql = sprintf('select qsusers.*, qsrecordrights.IFID,qsrecordrights.Rights
						from qsusers
						left join qsrecordrights on qsrecordrights.UID = qsusers.UID and IFID = %1$d
						where qsusers.UID <> -1'
					, $this->i_IFID);
		return $this->_o_DB->fetchAll($sql);
	}
	public function saveRecordRights($C,$R,$U,$D)
	{
		$sql = sprintf('delete from qsrecordrights where IFID=%1$d'
				, $this->i_IFID);
		$this->_o_DB->execute($sql);
		$insertSQL = '';
		$sql = sprintf('select * from qsusers where isActive=1'
				, $this->i_IFID);
		$users = $this->_o_DB->fetchAll($sql);
		
		foreach ($users as $item)
		{
			$rights = bindec(sprintf('%1$d%2$d%3$d%4$d',
							(in_array($item->UID,$D))?1:0,
							(in_array($item->UID,$U))?1:0,
							(in_array($item->UID,$R))?1:0,
							(in_array($item->UID,$C))?1:0));
			if ( $rights)
			{
				$sql = sprintf('insert into qsrecordrights(UID,FormCode,IFID,Rights) 
					values(%1$d,%2$s,%3$d,%4$d)', /* */
					$item->UID, 
					$this->_o_DB->quote($this->FormCode), 
					$this->i_IFID,
					$rights|48);
				$this->_o_DB->execute($sql);
			}
		}
	}
	public function updateApproveCount()
	{
		$sql = sprintf('update qsusers 
					left join (select  qsstepapproverrights.UID, count(*) as TongSo
					from qsstepapprover
					inner join qsstepapproverrights on qsstepapproverrights.SAID = qsstepapprover.SAID
					inner join qsworkflowsteps on qsworkflowsteps.SID = qsstepapprover.SID
					inner join qsworkflows on qsworkflows.WFID = qsworkflowsteps.WFID
					inner join qsiforms on qsiforms.FormCode = qsworkflows.FormCode and qsiforms.Status = qsworkflowsteps.StepNo
					inner join qsforms on qsiforms.FormCode = qsforms.FormCode
					left join qsfapprover on qsfapprover.IFID = qsiforms.IFID and qsfapprover.UID = qsstepapproverrights.UID
					 and qsfapprover.StepNo = qsiforms.Status and qsfapprover.SAID = qsstepapprover.SAID
					where qsforms.Effected = 1 and qsiforms.deleted <> 1 and qsworkflows.Actived = 1 and (qsfapprover.IFID is null or RDate is not null)
					group by qsstepapproverrights.UID) as t on t.UID = qsusers.UID
					set qsusers.Message = ifnull(t.TongSo,0)');
		$this->_o_DB->execute($sql);
	}
}
?>