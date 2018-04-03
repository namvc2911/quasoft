<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Model_UserInfo extends Qss_Model_Abstract
{

	public $user_id;

	public $user_name;

	public $user_type;

	public $user_dept_id = 0;

	public $user_group_id;

	public $user_password;

	public $user_desc;

	public $user_dept_name;

	public $user_group_list;

	public $user_dept_list;

	public $user_email;

	public $user_lang;

	public $user_is_active;

	public $user_register_date;

	public $user_form_list;

	public $user_menu_id;
	
	public $user_session_id;
	
	public $user_mobile;
	
	public $user_notify;
	
	public $user_message;
	
	public $user_event;

	function __construct ()
	{
		parent::__construct();
		$this->setDefaultValue();
	}

	function setDefaultValue ()
	{
		$this->user_id = 0;
		$this->user_name = "";
		$this->user_type = 1;
		$this->user_dept_id = 0;
		$this->user_group_id = 0;
		$this->user_password = "";
		$this->user_desc = "";
		$this->user_dept_name = "";
		$this->user_group_list = "0";
		$this->user_dept_list = "0";
		$this->user_form_list = array();
		$this->user_is_active = false;
		$this->user_lang = 'vn';
		$this->user_menu_id = 0;
		$this->user_mobile = null;
	}

	/**
	 * Check login
	 *
	 * @return int security level
	 */
	function checkLogin ($sercurity_level = 0,$session_id = null)
	{
		$ret = false;
		if ( $this->user_name == 'sysAdmin' )
		{
			$sql = sprintf('select * from qsusers
				where LCASE(UserID)=LCASE(%1$s) and PassWord=%2$s and isActive = 1', 
			$this->_o_DB->quote($this->user_name), $this->_o_DB->quote($this->user_password));
		}
		else
		{
			$sql = sprintf('select qsusergroups.*,qsusers.*,qsdepartments.Name from qsusers
				inner join qsusergroups on qsusergroups.UID=qsusers.UID
				inner join qsdepartments on qsdepartments.DepartmentID=qsusergroups.DepartmentID
				where LCASE(UserID)=LCASE(%1$s) and PassWord=%2$s and qsusergroups.DepartmentID=%3$d and isActive = 1', 
			$this->_o_DB->quote($this->user_name), $this->_o_DB->quote($this->user_password), $this->user_dept_id);
		}
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL )
		{
			$ret = true;
			$this->user_id = $dataSQL->UID;
			$this->user_type = $dataSQL->Type;
			$this->user_desc = $dataSQL->UserName;
			$this->user_email = $dataSQL->EMail;
			if ( $this->user_name != 'sysAdmin' )
			{
				$this->user_dept_name = $dataSQL->Name;
				$this->user_dept_list = $this->getSubDepartments($dataSQL->DepartmentID, $dataSQL->UID);
				$this->user_group_list = $this->getGroups($dataSQL->DepartmentID, $dataSQL->UID);
				$this->user_dept_id = $dataSQL->DepartmentID;
				$this->user_group_id = $dataSQL->GroupID;
				$this->user_menu_id = $dataSQL->MenuID;
				$this->user_notify = $dataSQL->Notify;
				$this->user_message = $dataSQL->Message;
				$this->user_event = $dataSQL->Event;
			}
		}
		if ( $ret )
		{
			if($session_id === null)
			{
				//save it
				$sql = sprintf('update qsusers
						set SessionID = %1$s
						where UID = %2$d', 
						$this->_o_DB->quote(Qss_Session::id()), 
						$this->user_id);
				$this->_o_DB->execute($sql);
				$this->user_session_id = Qss_Session::id();
			}
			else 
			{
				$this->user_session_id = $session_id?$dataSQL->SessionID:Qss_Session::id();
			}
			return $this->user_type & $sercurity_level;
		}
	}

	//-----------------------------------------------------------------------
	/**
	* Return list of sub department
	*
	* @access  public
	* @todo
	* @param   departmentid
	*/
	function getSubDepartments ($deptid, $uerid)
	{
		$sql = sprintf('select group_concat( distinct qsdepartments.DepartmentID) as Department from qsdepartments
					inner join qsusergroups on qsusergroups.DepartmentID=qsdepartments.DepartmentID
					where qsusergroups.UID=%2$d and (qsdepartments.ParentID=%1$d or qsdepartments.DepartmentID=%1$d) Order By qsdepartments.ParentID', $deptid, $uerid);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return @$dataSQL->Department ? $dataSQL->Department : 0;
	}

	//-----------------------------------------------------------------------
	/**
	* Return list of group
	*
	* @access  public
	* @todo
	* @param   departmentid
	*/
	//-----------------------------------------------------------------------
	function getGroups ($deptid, $uerid)
	{
		$sql = sprintf('select group_concat(GroupID) as GroupList from qsusergroups
				where DepartmentID=%1$d and UID=%2$d', $deptid, $uerid);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return @$dataSQL->GroupList ? $dataSQL->GroupList : 0;

	}

	//-----------------------------------------------------------------------
	/**
	* Get last working url of lastest login of current user
	*
	* @return  string
	*/
	function getLastUrl ()
	{
		$sql = sprintf('select Url from qslogaccess
				where UserID = %1$s and Url NOT LIKE \'%%login.php%%\' order by Date Desc limit 1', $db2->tosql($this->user_name, TEXT));
		$o_Query = $this->o_DB->fetchOne($sql);
		if ( $o_Query )
		$ret = $o_Query->Url;
		return $ret;
	}
	public function a_fGetListMenu ($parent = 0)
	{
		$lang = ($this->user_lang == 'vn')?'':'_'.$this->user_lang;
		$menuid = $this->getMenuID(); 
		$sz_SQL = sprintf('select MenuID,ifnull(MenuName%3$s,MenuName) as MenuName,ParentID,Icon
							from qsmenu where qsmenu.MID = %4$d and qsmenu.ParentID = %1$d
							order by MenuOrder',
					$parent,
					$this->user_group_list,
					$lang,
					$menuid);
		return $this->_o_DB->fetchAll($sz_SQL);
	}
	public function a_fGetListByMenu ($i_MenuId)
	{
		$lang = ($this->user_lang == 'vn')?'':'_'.$this->user_lang;
		$sz_SQL = sprintf('select qsforms.FormCode,ifnull(Name%3$s,Name) as Name,class,Type,classMobile
							from qsforms
							inner join qsmenulink on qsmenulink.FormCode=qsforms.FormCode
							inner join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
							where MenuID=%1$d and GroupID in (%2$s) and Rights&63<>0 and qsforms.Effected = 1
							group by qsforms.FormCode
							order by MenuLinkOrder', $i_MenuId, $this->user_group_list,$lang);
		return $this->_o_DB->fetchAll($sz_SQL);
	}
	public function hasChild ($i_MenuId)
	{
		$menuid = $this->getMenuID(); 			
		$sz_SQL = sprintf('select 1
							from qsforms
							inner join qsmenulink on qsmenulink.FormCode=qsforms.FormCode
							inner join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
							where GroupID in (%2$s) and Rights&63<>0 and qsforms.Effected = 1
							and (MenuID = %1$d or MenuID in 
							(SELECT GROUP_CONCAT(lv SEPARATOR ",") FROM (
							SELECT @pv:=(SELECT GROUP_CONCAT(MenuID SEPARATOR ",") FROM qsmenu WHERE ParentID IN (@pv)) AS lv FROM qsmenu
							JOIN
							(SELECT @pv:=%1$d)tmp
							WHERE ParentID IN (@pv)) a))', 
		$i_MenuId, $this->user_group_list,$menuid);
		return $this->_o_DB->fetchOne($sz_SQL);
	}
	public function a_fGetAllModule ()
	{
		$lang = ($this->user_lang == 'vn')?'':'_'.$this->user_lang;
		if(!count($this->user_form_list))
		{
                        $sz_SQL = sprintf('select qsforms.FormCode,ifnull(Name%2$s,Name) as Name, Type, class,bit_or(Rights) as Rights,ExcelImport,classMobile 
						from qsforms
						inner join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
						where GroupID in(%1$s) 
                        and Rights&63<>0
                        and qsforms.Effected = 1
                        group by qsforms.FormCode
                        order by  Name', 
				$this->user_group_list?$this->user_group_list:0
                ,$lang);
			$this->user_form_list = $this->_o_DB->fetchAll($sz_SQL);
		}
		return $this->user_form_list;
	}
	
	public function getOpenModules ($orderby = 'Name')
	{
		$lang = ($this->user_lang == 'vn')?'':'_'.$this->user_lang;
		$sz_SQL = sprintf('select distinct qsforms.FormCode,ifnull(Name%2$s,Name) as Name, class
				from qsforms
				inner join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
	            where GroupID in(%1$s) 
	            and Rights&63<>0
	            and qsforms.Effected = 1
	            order by  %3$s
	            limit 7', 
            $this->user_group_list,
			$lang,
            $orderby);
		return $this->_o_DB->fetchAll($sz_SQL);
	}
	
	public function logAccess($ip,$url)
	{
		return;
		$sql=sprintf('insert into qslogaccess(IP,UserID,Url) values(%1$s,%2$s,%3$s)'
		,$this->_o_DB->quote($ip),$this->_o_DB->quote($this->user_name),$this->_o_DB->quote($url));
		$this->_o_DB->execute($sql);
	}
	/*public function countUnRead()
	{
		$retval = 0;
		if($this->user_group_list)
		{
			$where = '';
			//loc yeu cau bao tri
			$rights = Qss_Lib_System::getFormRights('M715', $this->user_group_list);
			if($rights && !($rights & 16))
			{
				$where .= sprintf(' and (qsforms.Code != \'M715\' or qsiforms.IFID in
					(select IFID_M715 from OYeuCauBaoTri 
					inner join ODanhSachThietBi on OYeuCauBaoTri.Ref_MaMayMoc = ODanhSachThietBi.IOID
					inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=ODanhSachThietBi.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					where ODanhSachNhanVien.Ref_TenTruyCap = %1$d))',$this->user_id);
			}
			//loc phieu bao tri dk
			$rights = Qss_Lib_System::getFormRights('M706', $this->user_group_list);
			if($rights && !($rights & 16))
			{
				$where .= sprintf(' and (qsforms.Code != \'M706\' or qsiforms.IFID in
					(select IFID_M706 from OPhieuBTDK
					inner join ODanhSachThietBi on OPhieuBTDK.Ref_MaThietBi = ODanhSachThietBi.IOID
					inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=ODanhSachThietBi.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					where ODanhSachNhanVien.Ref_TenTruyCap = %1$d))',$this->user_id);
			}
			//loc bao tri su co
			$rights = Qss_Lib_System::getFormRights('M715', $this->user_group_list);
			if($rights && !($rights & 16))
			{
				$where .= sprintf(' and (qsforms.Code != \'M759\' or qsiforms.IFID in
					(select IFID_M759 from OPhieuBaoTri 
					inner join ODanhSachThietBi on OPhieuBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
					inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=ODanhSachThietBi.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					where ODanhSachNhanVien.Ref_TenTruyCap = %1$d))',$this->user_id);
			}
			$where = '';
			$sql = sprintf('select count(distinct qsiforms.IFID) as count 
				from qsftrace
				inner join qsiforms on qsiforms.IFID = qsftrace.IFID
				inner join qsforms on qsforms.FormCode = qsiforms.FormCode
				inner join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
				inner join qsusers on qsusers.UID = qsftrace.UID
				where (LastModify >= (UNIX_TIMESTAMP() - 604800) or SDate >= (UNIX_TIMESTAMP() - 604800)) 
				and qsuserforms.GroupID in(%1$s) and qsuserforms.Rights&63<>0
				and (qsuserforms.Rights&8<>0 or (qsiforms.Status = 0 and qsforms.Type <> 3) 
				or qsiforms.UID = %2$d)
				and qsiforms.IFID in (select IFID from qsfreader where IFID not in 
				(select IFID from qsfreader where UID = %2$d)) %3$s',
			$this->user_group_list,
			$this->user_id,
			$where);
			$retval = $this->_o_DB->fetchOne($sql);
		}
		return $retval?$retval->count:0;
	}
	public function getUnRead()
	{
		$lang = ($this->user_lang == 'vn')?'':'_'.$this->user_lang;
		$retval = array();
		if($this->user_group_list)
		{
			$where = '';
			//loc yeu cau bao tri
			$rights = Qss_Lib_System::getFormRights('M715', $this->user_group_list);
			if($rights && !($rights & 16))
			{
				$where .= sprintf(' and (qsforms.Code != \'M715\' or qsiforms.IFID in
					(select IFID_M715 from OYeuCauBaoTri 
					inner join ODanhSachThietBi on OYeuCauBaoTri.Ref_MaMayMoc = ODanhSachThietBi.IOID
					inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=ODanhSachThietBi.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					where ODanhSachNhanVien.Ref_TenTruyCap = %1$d))',$this->user_id);
			}
			//loc phieu bao tri dk
			$rights = Qss_Lib_System::getFormRights('M706', $this->user_group_list);
			if($rights && !($rights & 16))
			{
				$where .= sprintf(' and (qsforms.Code != \'M706\' or qsiforms.IFID in
					(select IFID_M706 from OPhieuBTDK
					inner join ODanhSachThietBi on OPhieuBTDK.Ref_MaThietBi = ODanhSachThietBi.IOID
					inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=ODanhSachThietBi.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					where ODanhSachNhanVien.Ref_TenTruyCap = %1$d))',$this->user_id);
			}
			//loc bao tri su co
			$rights = Qss_Lib_System::getFormRights('M715', $this->user_group_list);
			if($rights && !($rights & 16))
			{
				$where .= sprintf(' and (qsforms.Code != \'M759\' or qsiforms.IFID in
					(select IFID_M759 from OPhieuBaoTri 
					inner join ODanhSachThietBi on OPhieuBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
					inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=ODanhSachThietBi.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					where ODanhSachNhanVien.Ref_TenTruyCap = %1$d))',$this->user_id);
			}
			$where = '';
			$sql = sprintf('select distinct qsiforms.*,qsusers.UID, qsusers.UserName,qsforms.Name%3$s as Name,
				qsforms.Code as FCode,qsftrace.Logs
				from qsftrace
				inner join qsiforms on qsiforms.IFID = qsftrace.IFID
				inner join qsforms on qsforms.FormCode = qsiforms.FormCode
				inner join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
				inner join qsusers on qsusers.UID = qsftrace.UID
                where (LastModify >= (UNIX_TIMESTAMP() - 604800) or SDate >= (UNIX_TIMESTAMP() - 604800))
				and qsuserforms.GroupID in(%1$s) and qsuserforms.Rights&63<>0
				and (qsuserforms.Rights&8<>0 or (qsiforms.Status = 0 and qsforms.Type <> 3) 
				or qsiforms.UID = %2$d)
				and qsiforms.IFID in (select IFID from qsfreader where IFID not in 
				(select IFID from qsfreader where UID = %2$d))
				%4$s
				group by qsiforms.IFID
				order by LastModify desc,SDate desc
				limit 10',
				$this->user_group_list,
				$this->user_id,
				$lang,
				$where);
			$retval = $this->_o_DB->fetchAll($sql);
		}
		return $retval;
	}
	public function countWarning()
	{
		$retval = 0;
		if($this->user_group_list)
		{
			$where = '';
			//loc yeu cau bao tri
			$rights = Qss_Lib_System::getFormRights('M715', $this->user_group_list);
			if($rights && !($rights & 16))
			{
				$where .= sprintf(' and (qsforms.FormCode != \'M715\' or qsiforms.IFID in
					(select IFID_M715 from OYeuCauBaoTri 
					inner join ODanhSachThietBi on OYeuCauBaoTri.Ref_MaMayMoc = ODanhSachThietBi.IOID
					inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=ODanhSachThietBi.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					where ODanhSachNhanVien.Ref_TenTruyCap = %1$d))',$this->user_id);
			}
			//loc phieu bao tri dk
			$rights = Qss_Lib_System::getFormRights('M706', $this->user_group_list);
			if($rights && !($rights & 16))
			{
				$where .= sprintf(' and (qsforms.FormCode != \'M706\' or qsiforms.IFID in
					(select IFID_M706 from OPhieuBTDK
					inner join ODanhSachThietBi on OPhieuBTDK.Ref_MaThietBi = ODanhSachThietBi.IOID
					inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=ODanhSachThietBi.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					where ODanhSachNhanVien.Ref_TenTruyCap = %1$d))',$this->user_id);
			}
			//loc bao tri su co
			$rights = Qss_Lib_System::getFormRights('M715', $this->user_group_list);
			if($rights && !($rights & 16))
			{
				$where .= sprintf(' and (qsforms.FormCode != \'M759\' or qsiforms.IFID in
					(select IFID_M759 from OPhieuBaoTri 
					inner join ODanhSachThietBi on OPhieuBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
					inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=ODanhSachThietBi.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					where ODanhSachNhanVien.Ref_TenTruyCap = %1$d))',$this->user_id);
			}
			$where = '';
			$sql = sprintf('select count(distinct qsiforms.IFID) as count from qsiforms
				inner join qsforms on qsforms.FormCode = qsiforms.FormCode
				inner join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
				where EDate > DATE_ADD(EDate,INTERVAL -7 DAY) 
				and qsuserforms.GroupID in(%1$s) and Rights&63<>0
				and Error = 1 and deleted = 0 %3$s',
			$this->user_group_list,
			$this->user_id,
			$where);
			$retval = $this->_o_DB->fetchOne($sql);
		}
		return $retval?$retval->count:0;
	}
	public function getWarning($pageno = 0)
	{
		$lang = ($this->user_lang == 'vn')?'':'_'.$this->user_lang;
		$retval = array();
		if($this->user_group_list)
		{
			$where = '';
			//loc yeu cau bao tri
			$rights = Qss_Lib_System::getFormRights('M715', $this->user_group_list);
			if($rights && !($rights & 16))
			{
				$where .= sprintf(' and (qsforms.Code != \'M715\' or qsiforms.IFID in
					(select IFID_M715 from OYeuCauBaoTri 
					inner join ODanhSachThietBi on OYeuCauBaoTri.Ref_MaMayMoc = ODanhSachThietBi.IOID
					inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=ODanhSachThietBi.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					where ODanhSachNhanVien.Ref_TenTruyCap = %1$d))',$this->user_id);
			}
			//loc phieu bao tri dk
			$rights = Qss_Lib_System::getFormRights('M706', $this->user_group_list);
			if($rights && !($rights & 16))
			{
				$where .= sprintf(' and (qsforms.Code != \'M706\' or qsiforms.IFID in
					(select IFID_M706 from OPhieuBTDK
					inner join ODanhSachThietBi on OPhieuBTDK.Ref_MaThietBi = ODanhSachThietBi.IOID
					inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=ODanhSachThietBi.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					where ODanhSachNhanVien.Ref_TenTruyCap = %1$d))',$this->user_id);
			}
			//loc bao tri su co
			$rights = Qss_Lib_System::getFormRights('M715', $this->user_group_list);
			if($rights && !($rights & 16))
			{
				$where .= sprintf(' and (qsforms.Code != \'M759\' or qsiforms.IFID in
					(select IFID_M759 from OPhieuBaoTri 
					inner join ODanhSachThietBi on OPhieuBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
					inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=ODanhSachThietBi.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					where ODanhSachNhanVien.Ref_TenTruyCap = %1$d))',$this->user_id);
			}
			$where = '';
			$sql = sprintf('select distinct qsiforms.*,qsusers.UID, qsusers.UserName,qsforms.Name%3$s as Name
                                , qsforms.FormCode as FCode
				from qsiforms
				inner join qsforms on qsforms.FormCode = qsiforms.FormCode
				inner join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
				inner join qsusers on qsusers.UID = qsiforms.UID
				where EDate > DATE_ADD(EDate,INTERVAL -7 DAY) 
				and qsuserforms.GroupID in(%1$s) and Rights&63<>0
				and Error = 1 and deleted = 0
				%4$s
				order by LastModify desc,SDate desc
				limit %5$d,10',
				$this->user_group_list,
				$this->user_id,
				$lang,
				$where,
				$pageno*10);
			$retval = $this->_o_DB->fetchAll($sql);
		}
		return $retval;
	}
	public function countEvents()
	{
		$retval = 0;
		if($this->user_group_list)
		{
			$sql = sprintf('select 
					(select count(distinct	qseventlogs.ELID)
					from qseventlogs 
					inner join qsusers on qsusers.UID = qseventlogs.UID
					inner join qseventtype on qseventtype.TypeID = qseventlogs.ETID
					left join qsiforms on qsiforms.IFID = qseventlogs.IFID
					left join qsforms on qsforms.FormCode = qsiforms.FormCode
					left join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
					where 
					qseventlogs.Status = 0 and qseventlogs.Date <= CURDATE() 
					and	(qseventlogs.UID = %1$d or (qsuserforms.GroupID in(%2$s) and Rights&63<>0)))
					+
					(select count(distinct qsevents.EventID)
					from qseventtimes
					inner join qsevents on qsevents.EventID = qseventtimes.EventID
					inner join qseventtype on qseventtype.TypeID = qsevents.EventType
					inner join qsusers on qsusers.UID = qsevents.CreatedID
					left join qseventlogs on qseventlogs.EventID = qsevents.EventID
					left join qseventmembers on qseventmembers.EventID = qsevents.EventID
					where qseventlogs.EventID is null and 
					(
					(qseventtimes.Type = 0 and (CURDATE() between SDate and EDate or CURDATE() = SDate))
					or
					(qseventtimes.Type = 1)
					or
					(qseventtimes.Type = 2 and weekday(CURDATE())=WDay)
					or
					(qseventtimes.Type = 3 and day(CURDATE())=Day)
					or
					(qseventtimes.Type = 4 and day(CURDATE())=Day and month(CURDATE())=Month)
					)
					and (qsevents.UID = %1$d or qseventmembers.UID = %1$d or qsevents.CreatedID = %1$d))
					 as count
				',
			$this->user_id,$this->user_group_list);
			$retval = $this->_o_DB->fetchOne($sql);
		}
		return $retval?$retval->count:0;
	}
	public function getEvents()
	{
		$retval = array();
		if($this->user_group_list)
		{
			$sql = sprintf('select distinct
					0 as ETID, qseventlogs.ELID,qseventlogs.EventID,qseventlogs.IFID,qseventlogs.ETID as EventType,
					ifnull(qsforms.Name,qseventlogs.Note) as Title,
					qseventlogs.Date,qseventlogs.STime,qseventlogs.ETime,
					qseventtype.TypeName,qsusers.UserName,
					case when qseventlogs.UID = %1$d then 1 else 2 end as Rights,
					qseventlogs.Status,
					qseventlogs.StepNo
					from qseventlogs 
					inner join qsusers on qsusers.UID = qseventlogs.UID
					inner join qseventtype on qseventtype.TypeID = qseventlogs.ETID
					left join qsiforms on qsiforms.IFID = qseventlogs.IFID
					left join qsforms on qsforms.FormCode = qsiforms.FormCode
					left join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
					where 
					qseventlogs.Status = 0 and qseventlogs.Date <= CURDATE() 
					and	(qseventlogs.UID = %1$d or (qsuserforms.GroupID in(%2$s) and Rights&63<>0))
					union all
					select distinct
					qseventtimes.ETID, 0 as ELID,qsevents.EventID,0 as IFID,qsevents.EventType,
					qsevents.Title, CURDATE() as Date,qseventtimes.STime as STime,qseventtimes.ETime as ETime,
					qseventtype.TypeName,qsusers.UserName,
					case when qsevents.CreatedID = %1$d then 1 else 2 end as Rights,
					0 as Status,
					qseventlogs.StepNo
					from qseventtimes
					inner join qsevents on qsevents.EventID = qseventtimes.EventID
					inner join qseventtype on qseventtype.TypeID = qsevents.EventType
					inner join qsusers on qsusers.UID = qsevents.CreatedID
					left join qseventlogs on qseventlogs.EventID = qsevents.EventID
					left join qseventmembers on qseventmembers.EventID = qsevents.EventID
					where qseventlogs.EventID is null and 
					(
					(qseventtimes.Type = 0 and (CURDATE() between SDate and EDate or CURDATE() = SDate))
					or
					(qseventtimes.Type = 1)
					or
					(qseventtimes.Type = 2 and weekday(CURDATE())=WDay)
					or
					(qseventtimes.Type = 3 and day(CURDATE())=Day)
					or
					(qseventtimes.Type = 4 and day(CURDATE())=Day and month(CURDATE())=Month)
					)
					and (qsevents.UID = %1$d or qseventmembers.UID = %1$d or qsevents.CreatedID = %1$d)
					limit 10
					',//or (qseventlogs.Status = 0 and qseventlogs.Date <= CURDATE())
					$this->user_id,$this->user_group_list);
			//echo $sql;die;
			$retval = $this->_o_DB->fetchAll($sql);
		}
		return $retval;
	}
	*/
	public function getMenuID()
	{
		if($this->user_menu_id)
		{
			return $this->user_menu_id;
		}
		$sql = sprintf('select * from qsmenus where `Default` = 1 Limit 1');
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->ID:0; 
	}
	public function getParentMenu()
	{
		$lang = ($this->user_lang == 'vn')?'':'_'.$this->user_lang;
		$menuid = $this->getMenuID(); 
		$sz_SQL = sprintf('select MenuID,ifnull(MenuName%3$s,MenuName) as MenuName,ParentID,Icon,
						(select qsforms.FormCode
							from qsforms
							inner join qsmenulink on qsmenulink.FormCode=qsforms.FormCode
							inner join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
							where MenuID=qsmenu.MenuID and GroupID in (%2$s) and Rights&63<>0 and qsforms.Effected = 1
							group by qsforms.FormCode
							order by MenuLinkOrder
							limit 1) as FormCode,
							(select qsforms.FormCode
							from qsforms
							inner join qsmenulink on qsmenulink.FormCode=qsforms.FormCode
							inner join qsmenu as mn on mn.MenuID = qsmenulink.MenuID
							inner join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
							where qsmenulink.MenuID in (select MenuID from qsmenu as b where ParentID = qsmenu.MenuID) and GroupID in (%2$s) and Rights&63<>0 and qsforms.Effected = 1
							group by qsforms.FormCode
							order by MenuOrder, MenuLinkOrder
							limit 1) as FormCode1
							from qsmenu 
							where qsmenu.MID = %4$d and ifnull(qsmenu.ParentID,0) = %1$d
							order by MenuOrder',
					0,
					$this->user_group_list?$this->user_group_list:0,
					$lang,
					$menuid);
					//echo '<pre>';
					//echo $sz_SQL;die;
		return $this->_o_DB->fetchAll($sz_SQL);
	}
	public function getRootByFormCode($formcode)
	{
		$lang = ($this->user_lang == 'vn')?'':'_'.$this->user_lang;
		$menuid = $this->getMenuID(); 
		$sz_SQL = sprintf('select MenuID
							from qsmenu
							where ifnull(qsmenu.ParentID,0) = 0
							and 
								(MenuID in (select MenuID from qsmenulink where FormCode = %1$s)
								or MenuID in (select ParentID from qsmenu
										inner join qsmenulink on qsmenu.MenuID = qsmenulink.MenuID
										where FormCode = %1$s))
							and MID = %2$d
							limit 1',
					$this->_o_DB->quote($formcode)
					,$this->user_menu_id);
					//echo $sz_SQL;die;
		return $this->_o_DB->fetchOne($sz_SQL);
	}
	public function readChatLogOfUser($uid)
	{
		$sql = sprintf('update qschats set Status = 1 where Status = 0 and Sender = %2$s and Receiver = %1$d',
					$this->user_id
					,$uid);
		$this->_o_DB->execute($sql);
	}
}
?>