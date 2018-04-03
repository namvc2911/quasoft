<?php
class Qss_Model_Admin_Group extends Qss_Model_Abstract
{
	var $intGroupID=0;
	var $szGroupName;
	var $szDescription;
	//-----------------------------------------------------------------------
	/**
	* construct a group
	*
	* @access  public
	*/
	function __construct()
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
	function init($gid)
	{
		$this->intGroupID = $gid;
		if(!$this->intGroupID)
		return;
		$sql="select * from qsgroups where GroupID=".$this->intGroupID;
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if( $dataSQL )
		{
			$this->szGroupName=$dataSQL->GroupName;
			$this->szDescription=$dataSQL->Description;
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
	function getAll($search='')
	{
		$like = '';
		if ( $search )
		{
			$like .= ' and GroupName like ' . $this->_o_DB->quote("%{$search}%");
		}
		$sql="select * from qsgroups";
		$sql.=$like;
		$sql.=" order by qsgroups.GroupID";
		return $this->_o_DB->fetchAll($sql);
	}
	//-----------------------------------------------------------------------
	/**
	* Return textbox element of department name
	*
	* @access  public
	* @todo  	call form update form of department
	* @param   variable have default value is 200
	*/
	function save($params)
	{
		$this->szGroupName=$params["szName"];
		$this->szDescription=$params["szDesc"];
		if($this->intGroupID)
		{
			$sql=sprintf('update qsgroups set GroupName=%1$s, Description=%2$s
					where GroupID = %3$d'
				,$this->_o_DB->quote($this->szGroupName),
				$this->_o_DB->quote($this->szDescription),
				$this->intGroupID);
			$this->_o_DB->execute($sql);
		}
		else
		{
			$sql=sprintf('insert into qsgroups(GroupName,Description)
					values(%1$s,%2$s)',
					$this->_o_DB->quote($this->szGroupName),
					$this->_o_DB->quote($this->szDescription));
			$this->intGroupID=$this->_o_DB->execute($sql);
		}
		$sql="select qsforms.FormCode,qsforms.name,qsuserforms.rights,qsuserforms.FUID from qsforms
					left join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
					and qsuserforms.groupid=".$this->intGroupID;
		$dataSQL = $this->_o_DB->fetchAll($sql);

		foreach($dataSQL as $data)
		{
			if($data->FUID)
			{
				$sql=sprintf('update qsuserforms set Rights=%2$d where FUID=%1$d',
				$data->FUID,
				bindec(sprintf('%1$d%2$d%3$d%4$d%5$d',
				(@$params['S_'.$data->FormCode])?1:0,
				(@$params['D_'.$data->FormCode])?1:0,
				(@$params['U_'.$data->FormCode])?1:0,
				(@$params['R_'.$data->FormCode])?1:0,
				(@$params['C_'.$data->FormCode])?1:0)));
			}
			else
			{
				$sql=sprintf('insert into qsuserforms(FormCode,GroupID,Rights) values("%1$s",%2$d,%3$d)',
				$data->FormCode,$this->intGroupID,bindec(sprintf('%1$d%2$d%3$d%4$d%5$d',
				(@$params['S_'.$data->FormCode])?1:0,
				(@$params['D_'.$data->FormCode])?1:0,
				(@$params['U_'.$data->FormCode])?1:0,
				(@$params['R_'.$data->FormCode])?1:0,
				(@$params['C_'.$data->FormCode])?1:0)));
			}
			$this->_o_DB->execute($sql);
		}
		
		$sql = sprintf('delete from qsstepgroups where GroupID=%1$d'
				, $this->intGroupID);
		$this->_o_DB->execute($sql);
		$stepRights = $this->getStepRights();
		foreach($stepRights as $data)
		{
			$rights = bindec(sprintf('%1$d%2$d%3$d%4$d%5$d%6$d',
				(@$params['B_Step_'.$data->SID])?1:0,
				(@$params['S_Step_'.$data->SID])?1:0,
				(@$params['D_Step_'.$data->SID])?1:0,
				(@$params['U_Step_'.$data->SID])?1:0,
				(@$params['R_Step_'.$data->SID])?1:0,
				(@$params['C_Step_'.$data->SID])?1:0));
			if($rights)
			{
				$sql=sprintf('insert into qsstepgroups(SID,GroupID,Rights) values(%1$d,%2$d,%3$d)',
				$data->SID,$this->intGroupID,$rights);
				//echo $sql;die;
				$this->_o_DB->execute($sql);
			}
		}
	}

	/**
	 * Return textbox element of department name
	 *
	 * @access  public
	 * @todo  	call form update form of department
	 * @param   variable have default value is 200
	 */
	function getRightEle()
	{
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$sql=sprintf('select qsforms.FormCode,qsmenu.MenuName,p.MenuName as Parent,qsforms.Type,ifnull(Name%2$s,Name) as name,
				qsuserforms.rights,case when qsfobjects.FormCode is null then 0 else 1 end as actived, qsforms.Effected
				from qsforms
				left join qsuserforms on qsuserforms.FormCode = qsforms.FormCode and qsuserforms.groupid=%1$d 
				left join qsfobjects on qsforms.FormCode = qsfobjects.FormCode
				left join qsmenulink on qsmenulink.FormCode = qsforms.FormCode
				left join (select qsmenu.* from qsmenu where MID in (select ID from qsmenus where `Default`=1)) as qsmenu 
						on qsmenu.MenuID = qsmenulink.MenuID   
				left join qsmenu as p on p.MenuID = qsmenu.ParentID 
				where qsforms.Effected = 1 
				group by qsforms.FormCode,qsforms.name,qsuserforms.rights,qsuserforms.FormCode
				order by ifnull(qsmenu.MenuID,1000),MenuLinkOrder',$this->intGroupID,$lang);
		return $this->_o_DB->fetchAll($sql);
	}
	//-----------------------------------------------------------------------
	/**
	* Return textbox element of department name
	*
	* @access  public
	* @todo  	call form update form of department
	* @param   variable have default value is 200
	*/
	function getDescEle($width=55)
	{
		return parent::Editor("szDesc",$this->szDescription,$width);
	}
	//-----------------------------------------------------------------------
	/**
	* Return textbox element of department name
	*
	* @access  public
	* @todo  	call form update form of department
	* @param   variable have default value is 200
	*/
	function getDeptEle($width=100)
	{
		$this->intParentID=$this->intDepartmentID;
		return parent::getParentEle($width);
	}
	//-----------------------------------------------------------------------
	/**
	* Return textbox element of department name
	*
	* @access  public
	* @todo  	call form update form of department
	* @param   variable have default value is 200
	*/
	function getGDeptEle($width=100)
	{
		global $db;
		$ret="";
		$sql="select * from qsdepartments";
		$array=array();
		$db->query($sql);
		$array["0"]="---select department---";
		while($db->next_record())
		{
			$array[$db->f("DepartmentID")]=$db->f("Name");
		}
		return parent::ComboBox("intDepartmentID",$array,$this->intDepartmentID,$width);
	}
	//-----------------------------------------------------------------------
	function getGroup()
	{
		global $db,$sysfolder;
		$fn="../$sysfolder/designs/admin/group.html";
		$this->data=array();
		$this->data["name"]= $this->getNameEle();
		$this->data["ID"]= $this->intGroupID;
		$this->data["desc"]= $this->getDescEle();
		$this->data["rights"]= $this->getRightEle();
		$this->data["js"]= $this->getJS();
		$this->data['banner']=TMenu::getMenu();
		return parent::parseTemplateFile($fn,$this->data);
	}
	//-----------------------------------------------------------------------
	/**
	* Delete one group
	*
	* @access  public
	* @todo  	call form delete group
	* @return string
	*/
	function doDelete()
	{
		global $db,$sysfolder;
		$fn="../$sysfolder/designs/admin/group_update.html";
		$this->data=array();

		if($this->intGroupID)
		{
			/* Delete all property first */
			$sql = sprintf('delete from qsuserforms where GroupID = %1$d', $this->intGroupID);
			$db->query($sql);
			$db->close();
			/* Delete group */
			$sql = sprintf('delete from qsgroups where GroupID = %1$d',$this->intGroupID);
			$db->query($sql);
			$db->close();
			$message=sprintf('%1$s đã được xóa',$this->szGroupName);
		}
		else
		{
			$message = sprintf('Nhóm đã bị xóa hoặc không tồn tại.');
		}
		$this->data['message']=$message;
		$this->data['js']=$this->getJS();
		$this->data['banner']=TMenu::getMenu();
		return parent::parseTemplateFile($fn,$this->data);
	}
	public function duplicate($fromfid,$tofid)
	{
		$sql = sprintf('insert into qsuserforms(FormCode, GroupID,Rights) select "%2$s", GroupID,Rights from qsuserforms where FormCode = "%1$s"',
				$fromfid,$tofid);
		$this->_o_DB->execute($sql);
	}
	public function getUsers()
	{
		$sql = sprintf('select distinct qsusers.* from qsusergroups
						inner join qsusers on qsusers.UID = qsusergroups.UID
						where GroupID = %1$d',
				$this->intGroupID);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getStepRights()
	{
		$sql = sprintf('select qsforms.*,
						step.SID,step.Name as StepName,step.StepNo,
						qsstepgroups.Rights 
						from qsworkflowsteps as step
						inner join qsworkflows as wf on wf.WFID = step.WFID
						inner join qsforms on qsforms.FormCode = wf.FormCode
						left join qsstepgroups on qsstepgroups.SID = step.SID and qsstepgroups.GroupID = %1$d
						where qsforms.Effected =1
						order by qsforms.FormCode, step.StepNo'
					, $this->intGroupID);
		return $this->_o_DB->fetchAll($sql);
	}
}
?>