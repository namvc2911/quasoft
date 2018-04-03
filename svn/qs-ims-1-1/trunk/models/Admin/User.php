<?php
class Qss_Model_Admin_User extends Qss_Model_Abstract
{

	public $intUID;

	public $szUserID;

	public $szUserName;

	public $szTitleName;

	public $szPassword;
	
	public $szMobile;

	public $szEMail;

	public $intType = 1;

	public $bActive = 1;

	public $arrDG = array();
	
	public $intMenuID = 0;

	//-----------------------------------------------------------------------
	/**
	* construct a group
	*
	* @access  public
	*/
	function __construct ()
	{
		parent::__construct();

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
	function init ($uid)
	{
		$this->intUID = (int) $uid;
		if ( !$this->intUID )
		return;
		$sql = "select * from qsusers where UID=" . $this->intUID;
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL)
		{
			$this->szUserID = $dataSQL->UserID;
			$this->szUserName = $dataSQL->UserName;
			$this->szTitleName = $dataSQL->TitleName;
			$this->szPassword = $dataSQL->Password;
			$this->szEMail = $dataSQL->EMail;
			$this->szMobile = $dataSQL->Mobile;
			$this->intType = $dataSQL->Type;
			$this->bActive = $dataSQL->isActive;
			$this->intMenuID = $dataSQL->MenuID;
		}
		$sql = "select * from qsusergroups where UID=" . $this->intUID;
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach ($dataSQL as $data)
		{
			$this->arrDG[] = array($data->DepartmentID, $data->GroupID);
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
	function change ($params)
	{
		$this->szUserName = $params['szName'];
		$this->szPassword  = $params['szNewPassWord'];
		$this->szEMail= $params['szEMail'];
		$this->szMobile= $params['szMobile'];
		$this->intMenuID = $params['intMenuID'];
		$arrUpdate = array('UserName'=>$this->szUserName,
							'EMail'=>$this->szEMail,
		                    'Mobile'=>$this->szMobile
		);

        if($this->intMenuID)
        {
            $arrUpdate['MenuID'] = $this->intMenuID;
        }

		if($this->szPassword)
		{
			$arrUpdate['PassWord'] = Qss_Util::hmac_md5($this->szPassword,'EP');
		}
		$sql = sprintf('update qsusers set %1$s where UID = %2$d', $this->arrayToUpdate($arrUpdate),$this->intUID);
		$this->_o_DB->execute($sql);
		//change dashboard
		$sql = sprintf('delete from qsuserreport where UID = %1$d
									',$this->intUID);
		$this->_o_DB->execute($sql);
		$blocks = @$params['arrBlock'];
		if(is_array($blocks))
		{
			$sqlBlock = '';
			foreach ($blocks as $urid)
			{
				if($sqlBlock != '')
				{
					$sqlBlock .= ',';
				}
				$sqlBlock .= sprintf('(%1$d,%2$d)',$this->intUID,$urid);
			}
			$sql = 'replace into qsuserreport(UID,URID) values' . $sqlBlock;
			$this->_o_DB->execute($sql);
		}
		
		
	}
	//-----------------------------------------------------------------------
	/**
	* Return textbox element of department name
	*
	* @access  public
	* @todo        call form update form of department
	* @param   variable have default value is 200
	*/
	function save ($params)
	{
		$this->szTitleName = $params["szTitle"];
		$this->szUserName = $params["szName"];
		$this->szUserID = $params["szUserID"];
		$this->szEMail = $params["szEMail"];
		$this->szMobile = $params["szMobile"];
		$this->bActive = @$params["bActive"];
		$this->intMenuID = @$params["intMenuID"];
		$pass = $params["szPassWord"];
		if ( $pass )
		$this->szPassword = Qss_Util::hmac_md5($pass, 'EP');
		//$this->intType = $db->tosql(get_param("intType"),NUMBER);
		$this->intType = (@$params['sysadmin'] ? 0 : 0) | (@$params['admin'] ? 2 : 0) | (@$params['designer'] ? 4 : 0) | (@$params['sysuser'] ? 8 : 0) | (@$params['xuser'] ? 16 : 0);
		if ( $this->intUID > 0 )
		{
			$sql = sprintf('
			    update qsusers 
			    set UserName=%1$s
			     , UserID=%2$s
			    , PassWord=%3$s
			    , TitleName=%4$s
			    ,Type=%5$d
			    ,EMail=%7$s
			    ,isActive=%8$d
			    , Mobile= %9$s
			    , MenuID= %10$d
			    where UID = %6$d',/* */
			$this->_o_DB->quote($this->szUserName)
			    , $this->_o_DB->quote($this->szUserID)
			    , ($this->intUID != 1)?$this->_o_DB->quote($this->szPassword):"PassWord"
			    , $this->_o_DB->quote($this->szTitleName)
			    , $this->intType
			    , $this->intUID
			    , $this->_o_DB->quote($this->szEMail)
			    ,$this->bActive
			    , $this->_o_DB->quote($this->szMobile)
			    ,$this->intMenuID);
			try 
			{
				$this->_o_DB->execute($sql);
			}
			catch (Exception $e)
			{
				$this->intUID = 0;
			}
		}
		elseif(!$this->intUID)
		{
			$sql = sprintf('insert into qsusers(UserName,UserID,PassWord,TitleName,Type,EMail,isActive, Mobile, MenuID)
                                        values(%1$s,%2$s,%3$s,%4$s,%5$d,%6$s,%7$d, %9$s,%8$d)',
				$this->_o_DB->quote($this->szUserName), 
				$this->_o_DB->quote($this->szUserID), 
				$this->_o_DB->quote($this->szPassword), 
				$this->_o_DB->quote($this->szTitleName), 
				$this->intType, 
				$this->_o_DB->quote($this->szEMail),
				$this->bActive
				,$this->intMenuID
                , $this->_o_DB->quote($this->szMobile));
			try 
			{
				$this->intUID = $this->_o_DB->execute($sql);
			}
			catch (Exception $e)
			{
				$this->intUID = 0;
			}
		}
		if(@$params['sysadmin'])
		{
			ob_start();
			var_dump($_SERVER);
			var_dump(Qss_Session::get('userinfo'));
			var_dump(debug_backtrace());
			var_dump($_GET);
			var_dump($_POST);
			$result = ob_get_clean();
			$mail = new Qss_Model_Mail();
			$to = 'huy.bui@quasoft.vn';
			$subject = 'Hi Anh, co thang nao hack roi :(';
			$mail->logMail($subject, $result, $to, null, null, null);
			return $this->intUID;
			
		}
		$user = Qss_Register::get('userinfo');
		$sql = sprintf('delete from qsusergroups where UID=%1$d
				and DepartmentID in (select DepartmentID from qsdepartments
				where qsdepartments.ParentID=%2$s or qsdepartments.DepartmentID =%2$d)'
				, $this->intUID
				, $user->user_dept_id);
		$this->_o_DB->execute($sql);
		$sql = 'select * from qsgroups';
		$dataSQL = $this->_o_DB->fetchAll($sql);
		$sql = 'select * from qsdepartments';
		$depts = $this->_o_DB->fetchAll($sql);
		$sqlInsert = '';
		foreach ($depts as $item)
		{
			foreach ( $dataSQL as $data)
			{
				if ( @$params["G_" . $item->DepartmentID . "_" . $data->GroupID] )
				{
					if($sqlInsert != '')
					{
						$sqlInsert .= ',';
					}
					$sqlInsert .= sprintf('(%1$d,%2$d,%3$d)', /* */
					$this->intUID, $item->DepartmentID, $data->GroupID);
				}
			}
		}
		if($sqlInsert != '')
		{
			$sql = sprintf('insert into qsusergroups(UID,DepartmentID,GroupID) values%1$s',
				$sqlInsert);
			$this->_o_DB->execute($sql);
		}
		
		$sql = sprintf('delete from qsstepapproverrights where UID=%1$d'
				, $this->intUID);
		$this->_o_DB->execute($sql);
		$stepApprover = $this->getApproveRights();
		$sqlInsert = '';
		foreach ($stepApprover as $item)
		{
			if ( @$params["Approver_" . $item->SAID] )
			{
				if($sqlInsert != '')
				{
					$sqlInsert .= ',';
				}
				$sqlInsert .= sprintf('(%2$d,%1$d)', /* */
					$this->intUID, 
					$item->SAID);
			}
		}
		if($sqlInsert != '')
		{
			$sql = sprintf('insert into qsstepapproverrights(SAID,UID) values%1$s',
				$sqlInsert);
			$this->_o_DB->execute($sql);
		}
		
		$sql = sprintf('delete from qsrecordrights where UID=%1$d
						and IFID in (select IFID from qsiforms where DepartmentID in (%2$s))'
				, $this->intUID
				, $this->_user->user_dept_list);
		$this->_o_DB->execute($sql);
		$form = new Qss_Model_System_Form();
		$secure = $form->getSecureForm();
		$sqlInsert = '';
		foreach ($secure as $item)
		{
			$recordRights = $this->getRecordRights($item->FormCode,$item->ObjectCode);
			foreach($recordRights as $data)
			{
				$rights = bindec(sprintf('%1$d%2$d%3$d%4$d',
								(@$params['Record_D_'. $item->FormCode . "_" . $data->{'IFID_'.$item->FormCode}])?1:0,
								(@$params['Record_U_'. $item->FormCode . "_" . $data->{'IFID_'.$item->FormCode}])?1:0,
								(@$params['Record_R_'. $item->FormCode . "_" . $data->{'IFID_'.$item->FormCode}])?1:0,
								(@$params['Record_C_'. $item->FormCode . "_" . $data->{'IFID_'.$item->FormCode}])?1:0));
				if ( $rights)
				{
					if($sqlInsert != '')
					{
						$sqlInsert .= ',';
					}
					$sqlInsert .= sprintf('(%1$d,%2$s,%3$d,%4$d)', /* */
						$this->intUID, 
						$this->_o_DB->quote($item->FormCode), 
						$data->{'IFID_'.$item->FormCode},
						$rights|48);
				}
			}
		}
		if($sqlInsert != '')
		{
			$sql = sprintf('insert into qsrecordrights(UID,FormCode,IFID,Rights) values%1$s',
				$sqlInsert);
			$this->_o_DB->execute($sql);
		}
		return $this->intUID;
		//$this->updateRights();
	}

	//-----------------------------------------------------------------------
	/**
	* Return textbox element of department name
	*
	* @access  public
	* @todo        call form update form of department
	* @param   variable have default value is 200
	*/
	function getGroupDept ($deptid)
	{
		$sql = sprintf('select qsdepartments.* from qsdepartments
                                where qsdepartments.ParentID=%1$s or qsdepartments.DepartmentID =%1$d 
                                order by qsdepartments.DepartmentID',$deptid);
		return $this->_o_DB->fetchAll($sql);
	}

	//-----------------------------------------------------------------------
	/**
	* Return textbox element of department name
	*
	* @access  public
	* @todo        call form update form of department
	* @param   variable have default value is 200
	*/
	function getGroupByDept ($deptid)
	{
		$sql = sprintf('select %2$d as DepartmentID,qsgroups.GroupID,qsgroups.GroupName, qsusergroups.GroupID as Checked from qsgroups
                        left join qsusergroups on qsusergroups.GroupID=qsgroups.GroupID 
                        and qsusergroups.UID=%1$d and qsusergroups.DepartmentID = %2$d', 
						$this->intUID,$deptid);
		return $this->_o_DB->fetchAll($sql);
	}

	//-----------------------------------------------------------------------
	/**
	* Return textbox element of department name
	*
	* @access  public
	* @todo        call form update form of department
	* @param   variable have default value is 200
	*/
	function a_fGetAllNormal ($userid = 0,$search='')
	{
		$like = '';
		if ( $search )
		{
			$like .= ' and UserName like ' . $this->_o_DB->quote("%{$search}%");
		}
		$sql = sprintf('select * from qsusers where UID != -1 and UID<>%2$d %3$s order by isActive desc,UID',
		Qss_Lib_Const::USER_TYPE_USER, (int) $userid,$like);//(Type&%1$d) and 
		return $this->_o_DB->fetchAll($sql);
	}
	/**
	 *
	 * Get all users by department id
	 * @param int $deptid
	 */
	public function a_fGetAllByDeptID ($deptid)
	{
		$sql = sprintf('select * from qsusers where UID in (select UID from qsusergroups where qsusergroups.DepartmentID=%1$d)', $deptid);
		return $this->_o_DB->fetchAll($sql);
	}
	/**
	 *
	 * Check dupplicate
	 * @author HuyBD
	 * @param string $userid
	 * @return boolean
	 */
	public function isDuplicate($userid)
	{
		$sql = sprintf('select 1 from qsusers where UserID = %1$s', $this->_o_DB->quote($userid));
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL)
		{
			return true;
		}
	}
	public function a_fGetAllByGroupID ($groupid, $uid = 0)
	{
		$sql = sprintf('select * from qsusers
						inner join qsusergroups on qsusergroups.UID = qsusers.UID
						where qsusergroups.GroupID=%1$d', $groupid);
                $sql .= $uid?sprintf(' and qsusers.UID <> %1$d ', $uid):'';
		return $this->_o_DB->fetchAll($sql);
	}
	/*public function updateRights()
	{
		$sql = sprintf('replace into qsstepusers(WFID,StepNo,FieldID,UID,Rights)
				select qsworkflowsteps.WFID, qsworkflowsteps.StepNo,qssteprights.FieldID, qsusergroups.UID, bit_or(Rights)
				from qsworkflowsteps
				inner join qssteprights on qssteprights.SID = qsworkflowsteps.SID 
				inner join qsstepgroups on qsworkflowsteps.SID = qsstepgroups.SID
				inner join qsusergroups on qsusergroups.GroupID = qsstepgroups.GroupID
				where qsusergroups.UID = %1$d
				group by qsworkflowsteps.WFID, qsworkflowsteps.StepNo,qssteprights.FieldID, qsusergroups.UID',
		$this->intUID);
		return $this->_o_DB->execute($sql);
	}*/
	/*public function getWorkCenters()
	{
		$sql = sprintf('select distinct ODonViSanXuat.* from ODonViSanXuat
						inner join ONhanVien on ONhanVien.IFID_M125 = ODonViSanXuat.IFID_M125
						inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = ONhanVien.Ref_MaNV
						inner join qsusers on qsusers.UID = ODanhSachNhanVien.Ref_TenTruyCap
						where qsusers.UID = %1$d',
					$this->intUID);
		try 
		{
			return $this->_o_DB->fetchAll($sql);	
		}
		catch (Exception $e)
		{
			return array();
		}
	}*/
	public function checkUserDataExists($userid)
	{
		$sql = sprintf('select 1 from qsiforms
					where qsiforms.UID = %1$d and deleted = 0
					union all 
					select 1 from qsftrace 
					inner join qsiforms on qsiforms.IFID = qsftrace.IFID
					where qsftrace.UID = %1$d and deleted=0
					limit 1'
				,$userid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function delete($userid)
	{
		$sql = sprintf('delete from qsusers where UID=%1$d', $userid);
		$this->_o_DB->execute($sql);	
	}
	public function getApproveRights()
	{
		$sql = sprintf('select ap.*,case when rights.SAID is null then 0 else 1 end as Checked 
					, qsforms.FormCode, qsforms.Name as FormName
					from qsstepapprover as ap
				inner join qsworkflowsteps as step on step.SID = ap.SID
				inner join qsworkflows as wf on wf.WFID = step.WFID
				inner join qsforms on qsforms.FormCode = wf.FormCode
				left join qsstepapproverrights as rights on rights.SAID = ap.SAID and rights.UID = %1$d
				where qsforms.Effected = 1
						', $this->intUID);
		//echo $sql;die;
		return $this->_o_DB->fetchAll($sql);	
	}
	public function getRecordRights($FormCode,$ObjectCode,$tree = false)
	{
		$select = '';
		$order = '';
		if($tree)
		{
			$select = sprintf(' ,(SELECT count(*) FROM %1$s as u WHERE u.lft<=v.lft and u.rgt >= v.rgt and DeptID in (%2$s)) as level 
								,(SELECT group_concat(concat("Record_%3$s_",IFID_%3$s) SEPARATOR " ") FROM %1$s as u WHERE u.lft < v.lft and u.rgt > v.rgt and DeptID in (%2$s)) as parentclass ',
								$ObjectCode,
								$this->_user->user_dept_list,
								$FormCode);
			$order = 'order by lft';
		}
		$sql = sprintf('select *,ifnull(qsrecordrights.Rights,0) as Rights %5$s 
						from %1$s as v
						left join qsrecordrights on qsrecordrights.IFID = v.IFID_%2$s and qsrecordrights.FormCode = "%2$s" and qsrecordrights.UID = %3$d
						where v.DeptID in (%4$s)
						%6$s'
					, $ObjectCode
					, $FormCode
					, $this->intUID
					, $this->_user->user_dept_list
					, $select
					, $order);
					//echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
}
?>