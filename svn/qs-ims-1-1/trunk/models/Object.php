<?php

function sum ($arr)
{
	return array_sum($arr);
}

function avg ($arr)
{
	return array_sum($arr) / count($arr);
}

class Qss_Model_Object extends Qss_Model_System_Object
{

	public $FormCode;

	public $i_IFID = 0;

	public $i_IOID = 0;

	public $intDepartmentID;

	public $i_UserID;

	public $ParentObjectCode;
	
	public $RefFormCode;

	public $code;

	public $title;

	public $b_Main = 0;

	public $a_Fields;

	public $intWorkFlowID = 0;

	public $intStatus = 0;

	public $intRights = 0;

	public $arrInherit = array();

	public $intFieldOrder = 0;

	public $bOrder = true;

	public $intType;

	public $bText = false;

	public $bPublic = 0;//1 ẩn trên desktop, 2. Ẩn trên mobile 4. Cho hiện custom object trên mobile

	public $bEditing = true;

	public $bDeleting = true;

	public $bInsert = true;

	public $bTrack = false;

	public $i_RecordCount = 0;

	public $i_PageCount = 0;

	public $gridFieldCount;
	
	public $oldData;
	
	public $currentData;

	/**
	 * Build constructor
	 *
	 * @return void
	 */
	public function __construct ()
	{
		parent::__construct();
	}

	/**
	 *
	 * @param $i_OID
	 * @return void
	 */
	public function v_fInit ($ObjectCode, $FormCode)
	{
		parent::v_fInit($ObjectCode);
		$this->FormCode = $FormCode;
	}
	/**
	 *
	 * @param $ifid
	 * @param $fdeptid
	 * @param $ioid
	 * @param $deptid
	 * @return void
	 */
	public function initData ($i_IFID, $i_FDeptID, $i_IOID)
	{
		$ret = true;
		if ( !$i_IFID || !$i_FDeptID )
		{
			return false;
		}
		if ( !$i_IOID && !$this->ObjectCode )
		{
			return false;
		}
		$this->intDepartmentID = $i_FDeptID;
		$this->i_IFID = $i_IFID;

		if ( $this->b_Main )
		{
			$sql = "select v.*,qsiforms.Status ,qsiforms.FormCode
										from {$this->ObjectCode} as v
                                        inner join qsiforms on v.IFID_{$this->FormCode}=qsiforms.IFID  
                                        where qsiforms.ifid={$i_IFID}";
		}
		else
		{
			$sql = "select *,qsiforms.UID as OUID
					from qsiforms
					inner join {$this->ObjectCode} as v on v.IFID_{$this->FormCode}=qsiforms.IFID 
					where qsiforms.IFID = {$i_IFID}  
					and v.IOID = {$i_IOID}";
		}
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL)
		{
			$this->oldData = $dataSQL;
			$this->fetchData($dataSQL);
			return true;
		}
		else
		{
			return true;
		}
	}
	/**
	 *
	 * gan ban ghi du lieu
	 * @param unknown_type $dataSQL
	 */

	public function fetchData($dataSQL)
	{
		$this->FormCode = $dataSQL->FormCode;
		$this->intStatus = $dataSQL->Status;
		$this->i_IOID = $dataSQL->IOID;
		$this->i_IFID = $dataSQL->{'IFID_'.$this->FormCode};
		$fields = $this->loadFields();
		foreach ($fields as $field)
		{
			$field->intIFID = $this->i_IFID;
			$field->intIOID = $dataSQL->IOID;
			if($field->intFieldType == 11)
			{
				$field->setValue($dataSQL->{$field->FieldCode}/1000);
			}
			elseif(property_exists($dataSQL,$field->FieldCode))
			{
				$field->setValue($dataSQL->{$field->FieldCode});
			}
			if(property_exists($dataSQL,'Ref_'.$field->FieldCode))
			{
				$field->setRefIOID($dataSQL->{'Ref_'.$field->FieldCode});
			}
			$field->bEditStatus = false;
		}
	}
	/**
	 *
	 * @return int
	 */
	function i_fGetIFID ()
	{
		return $this->i_IFID;
	}

	/**
	 * Get all ioid of instance form
	 *
	 * @return array
	 */
	public function a_fGetAllIOIDByIFID ()
	{
		$sql = sprintf('select v.IOID
					from %2$s where IFID_%3$s = %1$d ', /* */
		$this->i_IFID, $this->ObjectCode,$this->FormCode);
		return $this->_o_DB->fetchAll($sql);
	}

	function sz_fGetSQLByFormAndUser (Qss_Model_Form $o_Form,
	Qss_Model_UserInfo $o_User,
	$i_CurrentPage = 1,
	$i_Limit = 50,
	$fieldorder = array(),
	$a_Condition = array(),
	$groupbys = array(),
	$selectall = false/*Only for subobject*/)
	{
		$params = $a_Condition;
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$where = '';
		$treeselect = '';
		$order = array();
		$sort = false;
		$join = "";
		if($this->b_Tree)
		{
			$treeselect = sprintf(' ,v.lft,v.rgt,(SELECT count(*) FROM %1$s as u WHERE u.lft<=v.lft and u.rgt >= v.rgt and IFID_%2$s = %3$d) as level ',
			$this->ObjectCode,
			$this->FormCode,
			$this->i_IFID);
			$join = sprintf(' left join %1$s as t on v.IOID = t.IOID ',$this->ObjectCode);
			$order[] = 'v.lft';
			//exclude close tree
			$treecookie = Qss_Cookie::get('object_'. $o_Form->i_IFID . '_' .$this->ObjectCode.'_tree', 'a:0:{}');
			$treecookie = unserialize($treecookie);
			//print_r($treecookie);die;
			foreach ($treecookie as $closeioid)
			{
				$where .= sprintf(' and not (v.lft > ifnull((SELECT lft FROM %1$s WHERE IOID = %3$d),0) and v.rgt < ifnull((SELECT rgt FROM %1$s WHERE IOID = %3$d),0)) ',
				$this->ObjectCode,
				$this->FormCode,
				$closeioid);
					
			}
			$treeselect .= sprintf(' , case when v.IOID in (%1$s) then 1 else 0 end as close ',implode(',', count($treecookie)?$treecookie:array(-1)));
			//echo $where;die;
		}
		if(count($groupbys) && is_array($groupbys))
		{
			foreach ($groupbys as $groupby)
			{
				$fgroupby = $this->getFieldByCode($groupby);
				if($fgroupby->ObjectCode == $this->ObjectCode
				&& $fgroupby->intFieldType != 4
				&& $fgroupby->intFieldType != 9
				&& $fgroupby->intFieldType != 8
				&& $fgroupby->intFieldType != 17 )
				{
					if(isset($fieldorder[$groupby]))
					{
						$order[] = sprintf('%1$s %2$s',$fgroupby->FieldCode,$fieldorder[$groupby]);
					}
					else
					{
						$order[] = sprintf('%1$s',$fgroupby->FieldCode);
					}
					$sort = true;
				}
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
				$foder = $this->getFieldByCode($k);
				if ( $foder->ObjectCode == $this->ObjectCode
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
		if(!count($fieldorder) && $this->orderField)//nếu ko order by thì lấy mặc định
		{
			$foder = $this->getFieldByCode($this->orderField);
			if ( $foder->ObjectCode == $this->ObjectCode
			&& $foder->intFieldType != 4
			&& $foder->intFieldType != 9
			&& $foder->intFieldType != 8
			&& $foder->intFieldType != 17 )
			{
				$jsonData = $this->getJsonData();
				if(is_array($jsonData))
				{
					$order[] = sprintf('FIELD(%2$s,%1$s) DESC', "'".implode(',', array_keys($jsonData))."'",$foder->FieldCode);
				}
				else
				{
					$order[] = sprintf('%2$s %1$s', $this->orderType,$foder->FieldCode);
				}
				$sort = true;
			}
		}
		//add filter join and where
		$classname = 'Qss_Bin_Filter_'.$this->ObjectCode;
		if(class_exists($classname))
		{
			$exfilter = new $classname($o_User,$params);
			$join .= $exfilter->getJoin();
			$where .= $exfilter->getWhere();
		}
		if(count($order))
		{
			$where .= sprintf(' order by %1$s',implode(',', $order));
		}
		if ( !$this->b_Main )
		{
			if($selectall)
			{
				$sql = sprintf('select qsiforms.*,v.*,qsusers.UserName,
								IFNULL((Select case when UID=%7$d then 1 else 0 end From qsfreader Where IFID=qsiforms.IFID order by qsfreader.UID!=%7$d limit 1),1) as Accepted,
								qsworkflowsteps.SID,
                                ifnull(qsworkflowsteps.Name%8$s,qsworkflowsteps.Name) as Name ,qsworkflowsteps.StepNo,
								qsworkflowsteps.Color
								%9$s
								from %2$s as v
                                inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
                                inner join qsusers on qsiforms.UID=qsusers.UID
                                left join qsworkflowsteps on qsworkflowsteps.StepNo=qsiforms.Status and qsworkflowsteps.WFID=%1$d
                                %5$s
                                where qsiforms.Deleted<>1 and qsiforms.DepartmentID=%4$d %6$s', /* */
				$o_Form->i_WorkFlowID,
				$this->ObjectCode,
				$this->FormCode,
				$this->intDepartmentID,
				$join,
				$where,
				$o_User->user_id,
				$lang,
				$treeselect);
			}
			else
			{
				$sql = sprintf('select qsiforms.*,v.*,qsusers.UserName,
								IFNULL((Select case when UID=%7$d then 1 else 0 end From qsfreader Where IFID=qsiforms.IFID order by qsfreader.UID!=%7$d limit 1),1) as Accepted
								%8$s
								from %2$s as v
                                inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
                                inner join qsusers on qsiforms.UID=qsusers.UID
                                %5$s
                                where qsiforms.Deleted<>1 and qsiforms.IFID=%1$d and qsiforms.DepartmentID=%4$d %6$s', /* */
				$this->i_IFID,
				$this->ObjectCode,
				$this->FormCode,
				$this->intDepartmentID,
				$join,
				$where,
				$o_User->user_id,
				$treeselect);
			}

		}
		elseif ( $o_Form->i_Type == 1 || $o_Form->i_Type == Qss_Lib_Const::FORM_TYPE_PROCESS)
		{
			$sql = sprintf('select v.*,qsusers.*,qsiforms.*,
								IFNULL((Select case when UID=%5$d then 1 else 0 end From qsfreader Where IFID=qsiforms.IFID order by qsfreader.UID!=%5$d limit 1),1) as Accepted
								%6$s
								from %2$s as v
                                inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
                                %3$s
                                inner join qsusers on qsiforms.UID=qsusers.UID
                                where qsiforms.Deleted<>1 %4$s', /* */
			$this->ObjectCode,
			$this->FormCode,
			$join,
			$where,
			$o_User->user_id,
			$treeselect);
		}
		elseif ( $o_Form->i_Type == 2 )
		{
			$sql = sprintf('select v.*,qsusers.*,qsiforms.*,
								IFNULL((Select case when UID=%6$d then 1 else 0 end From qsfreader Where IFID=qsiforms.IFID order by qsfreader.UID!=%6$d limit 1),1) as Accepted
								%7$s
								from %1$s as v
                                inner join qsiforms on qsiforms.IFID = v.IFID_%2$s
                                %4$s
                                inner join qsusers on qsiforms.UID=qsusers.UID
                                where qsiforms.Deleted<>1 and qsiforms.DepartmentID in(%3$d) %5$s', 
			$this->ObjectCode,
			$this->FormCode,
			$o_User->user_dept_id . ',' . $o_User->user_dept_list,
			$join,
			$where,
			$o_User->user_id,
			$treeselect);
		}
		else
		{
			if($selectall)
			{
				$sql = sprintf('select distinct v.*,qsusers.UserName,qsiforms.*,qsworkflowsteps.SID,
                                ifnull(qsworkflowsteps.Name%10$s,qsworkflowsteps.Name) as Name ,qsworkflowsteps.StepNo,
								qsworkflowsteps.Color,
                                IFNULL((Select case when UID=%2$d then 1 else 0 end From qsfreader Where IFID=qsiforms.IFID order by qsfreader.UID!=%2$d limit 1),1) as Accepted
                                %11$s
                                from %1$s as v
                                inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
                                %5$s
                                inner join qsusers on qsiforms.UID=qsusers.UID
                                inner join qsworkflowsteps on qsworkflowsteps.StepNo=qsiforms.Status and qsworkflowsteps.WFID=%4$d
                                left join (SELECT * FROM qsftrace WHERE qsftrace.UID = %2$d LIMIT 1) as qsftrace on qsftrace.IFID =  qsiforms.IFID
                                where qsiforms.UID!=0 
                                and qsiforms.deleted<>1 %6$s', /* */
				$this->ObjectCode,
				$o_User->user_id,
				$this->FormCode,
				$o_Form->i_WorkFlowID,
				$join,
				$where,
				$this->intRights,
				$o_User->user_dept_id . ',' . $o_User->user_dept_list,
				$o_User->user_group_list,
				$lang,
				$treeselect);
			}
			else
			{
				$sql = sprintf('select distinct qsrecforms.*,qsusers.UserName, qsiforms.*,qsworkflowsteps.SID,
                                ifnull(qsworkflowsteps.Name%10$s,qsworkflowsteps.Name) as Name ,qsworkflowsteps.StepNo,
								qsworkflowsteps.Color,
                                IFNULL((Select case when UID=%2$d then 1 else 0 end From qsfreader Where IFID=qsiforms.IFID Order By qsfreader.UID!=%2$d Limit 1),1) as Accepted
                                %11$s
                                from %1$s as v
                                inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
                                %5$s
                                left join qsusers on qsiforms.UID=qsusers.UID
                                inner join qsworkflowsteps on qsworkflowsteps.StepNo=qsiforms.Status and qsworkflowsteps.WFID=%4$d
                                left join (SELECT * FROM qsftrace WHERE qsftrace.UID = %2$d LIMIT 1) as qsftrace on qsftrace.IFID =  qsiforms.IFID
                                where qsiforms.UID!=0 
                                and (qsftrace.IFID is not null
                                or qsiforms.UID=%2$d or qsiforms.GroupID in(%9$s) or (%7$d&18 and qsiforms.DepartmentID in(%8$s))) 
                                and qsiforms.deleted<>1 %6$s', /* */
				$this->ObjectCode,
				$o_User->user_id,
				$this->FormCode,
				$o_Form->i_WorkFlowID,
				$join,
				$where,
				$this->intRights,
				$o_User->user_dept_id . ',' . $o_User->user_dept_list,
				$o_User->user_group_list,
				$lang,
				$treeselect);
			}
		}
		//echo $sql;die;
		return $sql;
	}

	public function a_fGetIOIDBySQL ($sql, $i_CurrentPage = 1, $i_Limit = 50)
	{
		if($i_Limit)
		{
			$sql .= sprintf(' LIMIT %1$d,%2$d', ($i_CurrentPage - 1) * $i_Limit, $i_Limit);
		}
		//echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
	/**
	 * Get next back record
	 */
	public function getNextPrevRecord ($sql, $ifid, $edit = false)
	{
		$sub = '';
		$field = 'IFID';
		if(!$this->b_Main)
		{
			$sub = ', v.IOID';
			$field = 'IOID';
		}
		$order = array("\r\n", "\n", "\r", "\t");
		$sql1 = preg_replace("/(select) .* (from)/s", "$1 qsiforms.IFID,qsiforms.DepartmentID {$sub} from ", str_replace($order, ' ', $sql), 1);
		$sql1 = preg_replace("/(from) .* (where)/s", "$0 where", $sql1, 1);
		$sql1 = preg_replace('(where)', ' ', $sql1,1);
		$sql1 = sprintf('select t.*,@curRow := @curRow + 1 AS row_number from (%1$s LIMIT 100) as t JOIN (SELECT @curRow := 0) r ',$sql1);//18446744073709551615
		if($ifid)
		{
			$sql = sprintf('select * from (%1$s) as m where %3$s = %2$d',$sql1,$ifid,$field);
			$dataSQL = $this->_o_DB->fetchOne($sql);
			if($dataSQL)
			{
				$sql = sprintf('select *,case when row_number = %2$d then 0 else 1 end as type
						from (%1$s) as t where row_number = %2$d or row_number = %3$d',
				$sql1,
				$dataSQL->row_number-1,
				$dataSQL->row_number+1);
				return  $this->_o_DB->fetchAll($sql);
			}
		}
		else
		{
			$sql = sprintf('select *, 1 as type
						from (%1$s) as t where row_number = 1',
			$sql1);
			return  $this->_o_DB->fetchAll($sql);
		}
		return array();
	}
	public function getRowNumber($sql, $ifid)
	{
		$sub = '';
		$field = 'IFID';
		if(!$this->b_Main)
		{
			$sub = ', v.IOID';
			$field = 'IOID';
		}
		$order = array("\r\n", "\n", "\r", "\t");
		$sql1 = preg_replace("/(select) .* (from)/s", "$1 qsiforms.IFID,qsiforms.DepartmentID {$sub} from ", str_replace($order, ' ', $sql), 1);
		$sql1 = preg_replace("/(from) .* (where)/s", "$0 where", $sql1, 1);
		if($this->b_Tree)
		{
			$sql1 = preg_replace("/LIMIT(.*)/s", " ", $sql1, 1);
		}
		else
		{
			$sql1 = preg_replace("/(order by).*/s", " ", $sql1, 1);
		}
		$sql1 = preg_replace('(where)', ' ', $sql1,1);
		$sql1 = sprintf('select t.*,@curRow := @curRow + 1 AS row_number from (%1$s LIMIT 18446744073709551615) as t JOIN (SELECT @curRow := 0) r ',$sql1);
		$sql = sprintf('select * from (%1$s) as m  where %3$s = %2$d',$sql1,$ifid,$field);

		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL)
		{
			return $dataSQL->row_number;
		}
		return 0;
	}
	public function i_fGetPageCount ($sql, $i_CurrentPage = 1, $i_Limit = 50)
	{
		if ( $this->i_PageCount )
		{
			return $this->i_PageCount;
		}
		$order = array("\r\n", "\n", "\r", "\t");
		if($this->b_Main)
		{
			$sql1 = preg_replace("/(select) .* (from)/s", "$1 count(distinct qsiforms.IFID) as recordcounts from ", str_replace($order, ' ', $sql), 1);
		}
		else
		{
			$sql1 = preg_replace("/(select) .* (from)/s", "$1 count(distinct v.IOID) as recordcounts from ", str_replace($order, ' ', $sql), 1);
		}
		$sql1 = preg_replace("/(order by).*/s", " ", $sql1, 1);
		//echo $sql1;die;
		$o_Count = $this->_o_DB->fetchOne($sql1);
		if ( $o_Count )
		{
			$this->i_RecordCount = $o_Count->recordcounts ? $o_Count->recordcounts : 1;
		}
		$this->i_PageCount = ceil($this->i_RecordCount / $i_Limit);
		return $this->i_PageCount;
	}

	/**
	 * Delete instance of object
	 *
	 * @return boolean
	 */
	function b_fDelete ()
	{
		if ( $this->i_IOID)
		{
			if($this->b_Tree)
			{
				$old = $this->getTreeRecord($this->i_IOID);
			}
			$sql = sprintf('delete from %1$s where IOID = %2$d',
			$this->ObjectCode,
			$this->i_IOID);
			$this->_o_DB->execute($sql);
			$sql = sprintf('delete from qsioidlink where ToIOID = %1$d and ToIFID = %1$d',
			$this->i_IOID,
			$this->i_IFID);
			$this->_o_DB->execute($sql);
			if($this->b_Tree)
			{
				$new = $this->getTreeRecord($this->i_IOID);
				$this->updateTemp($this->ObjectCode,$this->b_Tree, $this->i_IOID,$this->i_IFID,$old,$new);
			}
		}
	}
	public function getTreeRecord($ioid)
	{
		$sql = sprintf('select child.*,parent.lft as plft,parent.rgt as prgt
					from %2$s as child
					left join  %2$s as parent on parent.IOID = child.%3$s
					where child.IOID = %1$d',
		$ioid,$this->ObjectCode,$this->b_Tree);
		return $this->_o_DB->fetchOne($sql);
	}
	/**
	 * Load field config and (or) field's data
	 *
	 * @return void
	 */
	function loadFields($user = null)
	{
		if(is_null($user))
		{
			$user = Qss_Register::get('userinfo');
		}
		if ( is_array($this->a_Fields) && sizeof($this->a_Fields) )
		{
			return $this->a_Fields;
		}
		$this->a_Fields = array();
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$join = '';
		$select = ',7 as Rights';
		if ( !$this->intStatus && !$this->i_IFID )
		{
			$this->intStatus = 1;
		}
		if ( ($this->intStatus && $this->intWorkFlowID ) || ($this->intType < 2 && $this->intWorkFlowID ))
		{
			$join = sprintf(' left join (select bit_or(qssteprights.Rights) as Rights,FieldCode,ObjectCode ,qssteprights.SID
						from qssteprights
						inner join qsworkflowsteps on qssteprights.SID = qsworkflowsteps.SID 
						where qsworkflowsteps.WFID=%1$d and qsworkflowsteps.StepNo=%2$d and GroupID in (%3$s)
						group by qssteprights.SID, ObjectCode,FieldCode) as steprights 
						on steprights.FieldCode = qsfields.FieldCode and steprights.ObjectCode = qsfields.ObjectCode
						left join (select qssteprights.Rights,FieldCode,ObjectCode ,qssteprights.SID
						from qssteprights
						inner join qsworkflowsteps on qssteprights.SID = qsworkflowsteps.SID 
						where qsworkflowsteps.WFID=%1$d and qsworkflowsteps.StepNo=%2$d and GroupID = 0) as customesteprights 
						on customesteprights.FieldCode = qsfields.FieldCode and customesteprights.ObjectCode = qsfields.ObjectCode'
						, $this->intWorkFlowID
						, $this->intStatus?$this->intStatus:1
						, $this->_user->user_group_list);//in (%3$s)
						$select = ',if(steprights.FieldCode is not null,steprights.Rights,customesteprights.Rights) as Rights ';
		}
		$sql = sprintf('select FieldNo ,qsfields.ObjectCode ,qsfields.FieldCode ,
				ifnull(FieldName%6$s,FieldName) as FieldName ,FieldType,RefFieldCode,RefDisplayCode,DefaultVal , `Unique`,
				RefLabel,DataType , ReadOnly,isRefresh,RefFormCode,RefObjectCode,InputType, FieldWidth,Search,TValue,Style,
				FValue,Grid, Required,AFunction, Pattern,PatternMessage,Regx, Effect %5$s
				from qsfields
                %4$s
                where qsfields.Effect=1 and qsfields.ObjectCode=%3$s order by qsfields.FieldNo', 
		1,
		$this->intDepartmentID,
		$this->_o_DB->quote($this->ObjectCode),
		$join,
		$select,
		$lang);
		$dataSQL = $this->_o_DB->fetchAll($sql);
		$tmpForm = array();
		foreach ($dataSQL as $result)
		{
			$fieldtype =  $result->FieldType;
			if($fieldtype == 14)
			{
				$f = new Qss_Model_DField();
			}
			elseif($fieldtype == 15)
			{
				$f = new Qss_Model_CField();
			}
			elseif($fieldtype == 16)
			{
				$f = new Qss_Model_UField();
			}
			else
			{
				$f = new Qss_Model_Field();
			}
			$f->FieldCode = $result->FieldCode;
			if ( $result->FieldType == 4 )
			{
				$this->bText = true;
			}
			$f->FormCode = $this->FormCode;
			$f->ObjectCode = $result->ObjectCode;
			$f->szDefaultVal = $result->DefaultVal;
			$f->bUnique = $result->Unique;
			$f->szTValue = $result->TValue;
			$f->szFValue = $result->FValue;
			$f->intDepartmentID = $this->intDepartmentID;
			$f->szRegx = $result->Regx;
			$f->szFieldName = trim($result->FieldName);
			$f->szPattern = $result->Pattern;
			$f->szPatternMessage = $result->PatternMessage;
			$f->bEffect = $result->Effect;
			$f->szRefLabel = $result->RefLabel;
			//$f->szFilter = $result->Filter;
			$f->intInputType = $result->InputType;
			$f->intFieldType = $fieldtype;
			$f->bGrid = $result->Grid;
			$f->isRefresh = $result->isRefresh;
			$f->bSearch = $result->Search;
			if ( ($this->intStatus && $this->intWorkFlowID) || ($this->intType < 2 && $this->intWorkFlowID ) ) //&&$this->intIFID
			{
				$f->bVisible = (($this->intRights & 16) || (bool) $result->Rights);
				$f->bReadOnly = $result->ReadOnly ? $result->ReadOnly : !($result->Rights & 6);
				$f->bRequired = $result->Required ? $result->Required : ($result->Rights & 4);
			}
			else
			{
				$f->bReadOnly = $result->ReadOnly;
				$f->bRequired = $result->Required;
			}
			//for custom button
			if($result->FieldType == 17)
			{
				if(!$f->bReadOnly)
				{
					$f->szFValue = $f->szFValue?$f->szFValue:'custom-button-field';
				}
				//$f->bReadOnly = $this->i_IOID?$f->bReadOnly:true;
			}
			$f->AFunction = $result->AFunction;
			$f->RefFormCode = $result->RefFormCode;
			$f->RefObjectCode = $result->RefObjectCode;
			$f->RefFieldCode= $result->RefFieldCode;
			$f->RefDisplayCode = $result->RefDisplayCode;
			$f->intFieldWidth = $result->FieldWidth;
			$f->szStyle = $result->Style;
				
			if ( !$this->i_IFID && !$this->i_IOID )
			{
				if($f->szDefaultVal == 'KEEP')
				{
					$keepvalue = Qss_Session::get($f->FieldCode . '_' . $this->ObjectCode, '');
					$f->setValue($keepvalue);
				}
			}
			if($f->RefFormCode && in_array($f->RefFormCode, $tmpForm))
			{
				$f->lookupFilter = 0;
			}
			$this->a_Fields[] = $f;
			$tmpForm[] = $f->RefFormCode;
		}
		return $this->a_Fields;
	}

	public function a_fGetSubObjectsOfForm ($FormCode)
	{
		$sql = sprintf('select *
			from qsfobjects 
			where (qsfobjects.main<>1 or qsfobjects.main is null) and qsfobjects.FormCode="%1$s"
			order by ObjNo',
		$FormCode);
		return $this->_o_DB->fetchAll($sql);
	}

	public function a_fGetRefObjectsOfForm ($FormCode)
	{
		$sql = "select qsfields.* from qsfields
				inner join qsfobjects on qsfobjects.ObjectCode=qsfields.RefObjectCode and qsfobjects.Main=1
                inner join qsforms on qsforms.FormCode=qsfobjects.FormCode 
                where qsfields.Regx != 'auto' and qsforms.FormCode=\"" . $FormCode . "\" group by qsfields.ObjectCode";
		return $this->_o_DB->fetchAll($sql);

	}
	public function sz_fGetNameByCode ($i_ObjID)
	{
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$sql = sprintf('select ObjectName%2$s as Name from qsobjects where ObjectCode = "%1$s"',
		$i_ObjID,
		$lang);
		return @$this->_o_DB->fetchOne($sql)->Name;
	}
	/**
	 * @param bool $getIFID check to replace
	 */
	function b_fCheckDuplicate ($arrDuplicate,$ioid)
	{
		$ret;
		$where = '';
		foreach ($arrDuplicate as $f)
		{
			if($where)
			{
				$where .= ' and ';
			}
			if ( (($f->intInputType == 3
			|| $f->intInputType == 4
			|| $f->intInputType == 11
			|| $f->intInputType == 12)
			&& $f->RefFieldCode
			)
			||$f->intInputType == 4
			||$f->intInputType == 5
			||$f->intInputType == 6
			||$f->intInputType == 7
			)
			{
				$where .= sprintf('ifnull(Ref_%1$s,0) = %2$d',$f->FieldCode,$f->getRefIOID());
			}
			elseif($f->intFieldType <= 3)
			{
				$where .= sprintf(' ifnull(%1$s,\'\') = %2$s %3$s'
				,$f->FieldCode
				,$this->_o_DB->quote($f->getValue(false))
				,(($f->bUnique == 1)?(Qss_Lib_Const::MYSQL_COLLATION):''));
			}
			else
			{
				$where .= sprintf(' ifnull(%1$s,\'\') = %2$s',$f->FieldCode,$this->_o_DB->quote($f->getValue(false)));
			}
		}
		$condition = '';
		$sql = sprintf('select *
								from %1$s as v
								inner join qsiforms on qsiforms.IFID = v.IFID_%2$s
								where deleted = 0 and %3$s and IOID <> %4$d',
		$this->ObjectCode,
		$this->FormCode,
		$where,
		$this->i_IOID);
		if ( !$this->b_Main )
		{
			$condition .= " and (qsiforms.IFID = {$this->i_IFID} and qsiforms.DepartmentID = {$this->intDepartmentID})";
		}
		//echo $sql.$condition;die;
		return $this->_o_DB->fetchOne($sql.$condition);
	}



	function checkObjInForm ($objid)
	{
		$sql = "select * from qsfobjects
                where ObjectCode = '{$objid}' and FormCode = '{$this->FormCode}'";
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL )
		{
			if ( $dataSQL->Main )
			{
				/*In and Main*/
				return 1;
			}
			else
			{
				/*In and Sub*/
				return 2;
			}
		}
		/*Not in*/
		return 0;
	}

	function getFieldValue ($objid, $ioid, $field,$lookup = false)
	{
		$check = $this->checkObjInForm($objid);
		if (($this->ObjectCode == $objid && $ioid == -1))
		{
			foreach ($this->a_Fields as $key => $f)
			{
				if ( $field->FieldCode == $f->FieldCode )
				{
					$field = clone ($f);
				}
			}
		}
		elseif ( $check == 2 )
		{
			$ret = array();
			$sql = sprintf('select
					 %2$s as data 
					from %3$s as v
                    inner join qsiforms on qsiforms.IFID=v.IFID_%4$s
                    where qsiforms.IFID=%1$d and deleted= 0', /* */
			$this->i_IFID,
			$field->FieldCode,
			$objid,
			$this->FormCode);
			$dataSQL = $this->_o_DB->fetchAll($sql);
			foreach ($dataSQL as $data)
			{
				if($field->intFieldType == 11)
				$ret[] = $data->data / 1000;
				else
				$ret[] = $data->data;
			}
			return $ret;
		}
		else
		{
			$sql = sprintf('select
					*
					from %3$s as v
                    where v.IOID=%1$d', 
			$ioid,
			$field->FormCode);
			$dataSQL = $this->_o_DB->fetchOne($sql);
			if ( $dataSQL )
			{
				switch($field->intFieldType)
				{
					case 14:
					case 15:
					case 16:
						$field->setValue($dataSQL->{$field->FieldCode});
						break;
					case 11:
						$field->setValue($dataSQL->{$field->FieldCode}/1000);
						break;
					default:
						$field->setValue($dataSQL->{$field->FieldCode});
						break;
				}
				if(isset($dataSQL->{'Ref_'.$field->FieldCode}))
				{
					$field->setRefIOID($dataSQL->{'Ref_'.$field->FieldCode});
				}
				$field->intIOID = $ioid;
			}
		}
		return $lookup?$field:$field->getValue(false);
	}

	public function b_fSave ($upper = 0)
	{
		$bUpdate = true;
		if ( $this->i_IFID == 0 )
		{
			$this->doInsertForm();
			$bUpdate = false;
		}
		if ( $this->i_IFID == 0 )
		{
			return false;
		}
		if($this->b_Tree)
		{
			$old = $this->getTreeRecord($this->i_IOID);
		}
		if ( $this->i_IOID == 0 )
		{
			$this->insertData();
		}
		else
		{
			$this->updateData();
		}
		if($this->b_Tree)
		{
			$new = $this->getTreeRecord($this->i_IOID);
			$this->updateTemp($this->ObjectCode, $this->b_Tree,$this->i_IOID,$this->i_IFID,$old,$new);
			if($upper)
			{
				$this->downOrder($upper);
			}
		}
		if ( $this->i_IOID == 0 )
		{
			return false;
		}
		$bSaved = true;
		if($bSaved)
		{
			if($bUpdate)
			{
				$logs = Qss_Register::get('Logs');
				$this->v_fTrace($logs);
			}
			if($bUpdate)
			{
				$this->doUpdateForm();
			}
		}
		Qss_Register::set('Logs',null);
		return $bSaved;
	}
	public function downOrder($ioid)
	{
		$sqlTree = '';
		if(!$this->b_Main)
		{
			$sqlTree = sprintf(' and IFID_%1$s = %2$d',$this->FormCode,$this->i_IFID);
		}
		//get current
		$sql = sprintf('select * from %1$s where IOID = %2$d limit 1',
		$this->ObjectCode,
		$this->i_IOID);
		$curr = $this->_o_DB->fetchOne($sql);
		
		$sql = sprintf('select * from %1$s where IOID = %2$d limit 1',
		$this->ObjectCode,
		$ioid);
		$next = $this->_o_DB->fetchOne($sql);
		
		if($curr->{$this->b_Tree} != $next->{$this->b_Tree})
		{
			return;
		}
		
		//mở rộng lft những cái bên dưới khoảng cách = hiện tại
		$sql = sprintf('update %1$s set lft = lft + %2$d
				where lft > %3$d',
		$this->ObjectCode,
		($curr->rgt - $curr->lft + 1),//khoang cach
		$next->rgt);
		$sql .= $sqlTree;
		$this->_o_DB->execute($sql);
		
		//mở rộng rgt những cái bên dưới khoảng cách = hiện tại
		$sql = sprintf('update %1$s set rgt = rgt + %2$d
				where rgt > %3$d',
		$this->ObjectCode,
		($curr->rgt - $curr->lft + 1),//khoang cach
		$next->rgt);
		$sql .= $sqlTree;
		$this->_o_DB->execute($sql);
		
		//get current
		$sql = sprintf('select * from %1$s where IOID = %2$d limit 1',
		$this->ObjectCode,
		$this->i_IOID);
		$curr = $this->_o_DB->fetchOne($sql);
		
		//đút cái hiện tại vào lỗ hổng đã tạo
		$sql = sprintf('update %1$s set lft = lft + %2$d,
				rgt = rgt + %2$d
				where lft between %3$d and %4$d and rgt between %3$d and %4$d',
		$this->ObjectCode,
		($next->rgt - $curr->lft + 1),
		$curr->lft,
		$curr->rgt);
		$sql .= $sqlTree;
		$this->_o_DB->execute($sql);
		
		//Xóa lỗ hổng của cái chuyển
		$sql = sprintf('update %1$s set lft = lft - %2$d
				where lft > %3$d',
		$this->ObjectCode,
		($curr->rgt - $curr->lft + 1),
		$curr->lft);
		$sql .= $sqlTree;
		$this->_o_DB->execute($sql);
		
		$sql = sprintf('update %1$s set 
				rgt = rgt - %2$d
				where rgt > %3$d',
		$this->ObjectCode,
		($curr->rgt - $curr->lft + 1),
		$curr->lft);
		$sql .= $sqlTree;
		$this->_o_DB->execute($sql);
	}

	function doInsertForm ()
	{
		if ( $this->i_IFID == 0 )
		{
			$step = new Qss_Model_System_Step($this->intWorkFlowID);
			$step->v_fInitByStepNumber(0);
			$sql = sprintf('insert into qsiforms(FormCode,UID,Status,DepartmentID,SDate,GroupID)
                                values("%1$s",%2$d,%5$d,%3$d,%4$d,%7$d)', /* */
			$this->FormCode,
			$this->i_UserID,
			$this->intDepartmentID,
			time(),
			($this->getType($this->FormCode) != Qss_Lib_Const::FORM_TYPE_MODULE) ? 0 : $this->intStatus ? $this->intStatus : (($this->intType == 3) ? 1 : 0),
			$this->_o_DB->quote($this->code . "-" . time()),
			0);
			$this->_o_DB->execute($sql);
			$this->i_IFID = @$this->_o_DB->insertedId();
		}
	}

	/**
	 *
	 * @return void
	 */
	function doUpdateForm ()
	{
		//$user = Qss_Register::get('userinfo');
		if ( $this->i_IFID )
		{
			$step = new Qss_Model_System_Step($this->intWorkFlowID);
			$step->v_fInitByStepNumber(0);
			$sql = sprintf('update qsiforms set LastModify=%2$d
                                where IFID=%1$d', $this->i_IFID,time());
			$this->_o_DB->execute($sql);
		}
	}

	function getType ($fid)
	{
		$sql = sprintf('select * from qsforms where FormCode = "%1$s"', $fid);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL )
		{
			return $dataSQL->Type;
		}
	}

	/*public function a_fGetInherit ()
	 {
		if ( !sizeof($this->arrInherit) )
		{
		$sql = sprintf('select qsfinheritable.* from qsfinheritable
		inner join qsfobjects on qsfobjects.ObjectCode = qsfinheritable.ObjectCode and qsfobjects.FormCode=qsfinheritable.ToFormCode
		where qsfinheritable.ToFormCode=%1$s and qsfinheritable.ObjectCode=%2$s',
		$this->_o_DB->quote($this->FormCode),
		$this->_o_DB->quote($this->ObjectCode));
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach ($dataSQL as $data)
		{
		$this->arrInherit[$data->FromFormCode] = $data->Type;
		}
		}
		return $this->arrInherit;
		}*/


	public function sz_fGetReferenceSQL ($fieldid, $recordid, $i_CurrentPage = 1, $i_Limit = 50, $i_FieldOrder = 0, $sz_OrderType = 'ASC', $a_Condition = array(),$groupby=0)
	{
		$order = "";
		$join = "";
		if ( is_array($a_Condition) && sizeof($a_Condition) )
		{
			if ( $this->b_Main )
			{
				$order = sprintf(' and qsiforms.IFID in (%1$s)', sizeof($a_Condition) ? implode(',', $a_Condition) : '0');
			}
			else
			{
				$order = sprintf(' and qsiobjects.IOID in (%1$s)', sizeof($a_Condition) ? implode(',', $a_Condition) : '0');
			}
		}
		if($groupby)
		{
			if($groupby == -2)
			{
				$order .= sprintf('order by qsusers.UID %1$s', $sz_OrderType);
			}
			elseif($groupby == -1 && $o_Form->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE)
			{
				$order .= sprintf('order by qsworkflowsteps.StepNo %1$s', $sz_OrderType);
			}
			else
			{
				$order .= sprintf('order by %1$s DESC',$groupby);
			}
		}
		elseif ( $i_FieldOrder !== -1 )
		{
			$order .= sprintf('order by qsworkflowsteps.StepNo %1$s', $sz_OrderType);
		}
		elseif ( $i_FieldOrder)
		{
			$order .= sprintf('order by %2$s %1$s', $sz_OrderType,$i_FieldOrder);
		}
		else
		{
			$order .= sprintf('order by qsiforms.SDate DESC');
		}
		$sql = sprintf('select distinct qsiforms.*,qsforms.Code,  qsusers.UserName
							from %1$s as v
							inner join qsiforms on  qsiforms.IFID = v.IFID_%2$s
							inner join qsusers on qsiforms.UID=qsusers.UID
							inner join qsforms on  qsiforms.FormCode = qsforms.FormCode
							where deleted = 0 %3$s',
		$this->ObjectCode,
		$this->FormCode,
		$order);
		return $sql;

	}
	/**
	 * Clean field use for import
	 *
	 * @return void
	 */
	public function v_fResetFields ()
	{
		$this->a_Fields = array();
	}

	public function setRefValue (Qss_Model_Field &$fa)
	{
		foreach ($this->a_Fields as $key => $f)
		{
			if ( $fa->FieldCode == $f->FieldCode )
			{
				break;
			}
			if ( $fa->RefObjectCode == $f->RefObjectCode && $f->szRegx != Qss_Lib_Const::FIELD_REG_AUTO)
			{
				if ( $fa->intInputType == 3 
					|| $fa->intInputType == 4 
					|| $fa->intInputType == 11 
					||$fa->intInputType == 12)
				{
					//$fa->intRefIOID = $f->intRefIOID;
					//$fa->setValue('');
					//$fa->setRefIOID(0);
					if(in_array($fa->intFieldType,array(14,15,16)))
					{
						$value = $f->getDataByRef($f);
						$fa->setValue($value->{$f->FieldCode});
						$fa->setRefIOID($value->{'Ref_'.$f->FieldCode});
					}
					else
					{
						if($fa->getRefIOID() != $f->getRefIOID())
						{
							$fa->setRefIOID($f->getRefIOID());
							$fa->setValue('');
						}

					}
				}
			}
		}
	}
	function getArrayOfIOID()
	{
		$retval = array();
		$sql = sprintf('select IOID from %2$s as v where IFID_%3$s = %1$d' ,/* */
		$this->i_IFID, $this->ObjectCode,$this->FormCode);
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach($dataSQL as $data)
		{
			$retval[] = $data->IOID;
		}
		return $retval;
	}
	public function sz_fGetFormFieldElement ($FieldCode,Qss_Model_UserInfo $user = null,$dialog = false)
	{
		$o_Field = $this->getFieldByCode($FieldCode);
		$add = '';
		$elename = sprintf('%1$s_%2$s', $o_Field->ObjectCode, $o_Field->FieldCode);
		$limit = 0;
		switch ($o_Field->intFieldType)
		{
			case 1: 
				$limit = 100;
			break;
			case 2: 
				$limit = 255;
			break;
		}
		switch ( $o_Field->intInputType )
		{
			case 1: //if($o_Field->RefFormCode)
				$namespce = '';
				if($dialog)
				{
					$namespce = sprintf('dialog_%1$s.',$this->ObjectCode);
				}
				$onchangefunction = sprintf('onkeyup="%1$s%2$s()"',//this,%2$s
				$namespce,
				$this->b_Main?'onKeyupRefresh':'onObjectKeyupRefresh');
				if ( $o_Field->intFieldType == 11 )
				{
					$add = Qss_Lib_Template::MoneyBox($elename, $o_Field->getValue(), $o_Field->intFieldWidth,$o_Field->bReadOnly,$o_Field->isRefresh?$onchangefunction:'',$o_Field->getRefIOID()); //Monney
				}
				elseif ( $o_Field->intFieldType == 5 )
				{
					$add = Qss_Lib_Template::IntegerBox($elename, $o_Field->getValue(), $o_Field->intFieldWidth,$o_Field->bReadOnly,$o_Field->isRefresh?$onchangefunction:'',$o_Field->szTValue); //Monney
				}
				elseif ( $o_Field->intFieldType == 6 )
				{
					$add = Qss_Lib_Template::DecimalBox($elename, $o_Field->getValue(), $o_Field->intFieldWidth,$o_Field->bReadOnly,$o_Field->isRefresh?$onchangefunction:'',$o_Field->szTValue); //Monney
				}
				elseif ( $o_Field->intFieldType == 13 )
				{
					$add = Qss_Lib_Template::TextBox($elename, $o_Field->szValue, $o_Field->intFieldWidth,$o_Field->bReadOnly,$o_Field->isRefresh?$onchangefunction:''); //Moeny
				}
				elseif ( $o_Field->intFieldType == 4 )
				{
					$add = Qss_Lib_Template::Timmer($elename, $o_Field->getValue(), $o_Field->intFieldWidth,$o_Field->bReadOnly,($o_Field->isRefresh?$onchangefunction:'')) . '<span onclick = "setDefaultTimmer(this)" for="'.$elename.'" class="icon-timmer">&nbsp;</span>'; //Textbox
				}
				else
				{
					$add = Qss_Lib_Template::TextBox($elename, $o_Field->getValue(), $o_Field->intFieldWidth,$o_Field->bReadOnly,$o_Field->isRefresh?$onchangefunction:'',$limit) . $o_Field->szTValue; //Textbox
				}
				break;
			case 2:
				$add = Qss_Lib_Template::Editor($elename, $o_Field->getValue(), $o_Field->intFieldWidth,'',$o_Field->bReadOnly,$limit); //Editor
				break;
			case 3: //Combobox
				if($o_Field->szRegx == Qss_Lib_Const::FIELD_REG_AUTO)
				{
					if($o_Field->intFieldType == 7)
					{
						$add = Qss_Lib_Template::CheckBox($elename, ($o_Field->getValue()) ? 1 : 0,'',$o_Field->bReadOnly);
					}
					elseif($o_Field->intFieldType == 11)
					{
						$add = Qss_Lib_Template::MoneyBox($elename, $o_Field->getValue(), $o_Field->intFieldWidth,$o_Field->bReadOnly,'',''); //Monney
					}
					else
					{
						$add = Qss_Lib_Template::TextBox($elename, $o_Field->getValue(), $o_Field->intFieldWidth,$o_Field->bReadOnly,'',$limit);
					}
				}
				else
				{
					$jsonData = array();
					$json = $o_Field->getJsonRegx();
					if($json)//kiểu list tĩnh
					{
						$add = $o_Field->ComboBox( $user,$this->b_Main,$dialog,$json);
					}
					else
					{
						$add = $o_Field->DBComboBox( $user,$this->b_Main,$dialog);
					}
					/*if($json) kiểu này chơi onchange đây
					 {
						foreach ($json as $key=>$value)
						{
						$fieldParam = $this->getFieldByCode($value)->getValue();
						if($fieldParam)
						{
						$jsonData[$key] = $fieldParam;
						}
						}
						}
						$o_Field->json = Qss_Json::encode($jsonData);
						*/
						
				}
				break;
			case 4: //Listbox
				$add = Qss_Lib_Template::ListBox($o_Field,$this->b_Main?$this->FormCode:0,$dialog);
				break;
			case 5:
				$namespce = '';
				if($dialog)
				{
					$namespce = sprintf('dialog_%1$s.',$this->ObjectCode);
				}
				$onchangefunction = sprintf('onchange="%1$s%3$s(this,%2$s)"',
				$namespce,
				$o_Field->isRefresh,
				$this->b_Main?'rowEditRefresh':'rowObjectEditRefresh');
				$add = Qss_Lib_Template::Radio($elename, $o_Field->getValue(), $o_Field->getJsonRegx(),$o_Field->bReadOnly,$onchangefunction);
				break;
			case 6: //Picture
				$namespce = '';
				if($dialog)
				{
					if($this->b_Main)
					{
						$namespce = sprintf('dialog_%1$s.',$this->FormCode);
					}
					else
					{
						$namespce = sprintf('dialog_%1$s.',$this->ObjectCode);
					}
				}
				$add .= Qss_Lib_Template::Picture($o_Field->ObjectCode,$o_Field->FieldCode,$o_Field->getValue(),$o_Field->bReadOnly,$o_Field->intFieldWidth,$namespce);
				break;
			case 7: //File
				$add .= Qss_Lib_Template::File($elename,0, $o_Field->getValue(),$o_Field->bReadOnly);
				$add .= Qss_Lib_Template::FileDown($o_Field->getValue(), $o_Field);
				break;
			case 8: //Date
				$namespce = '';
				if($dialog)
				{
					if($this->b_Main)
					{
						$namespce = sprintf('dialog_%1$s.',$this->FormCode);
					}
					else
					{
						$namespce = sprintf('dialog_%1$s.',$this->ObjectCode);
					}
				}
				$onchangefunction = sprintf('onchange="%1$s%3$s(this,%2$s)"',
				$namespce,
				$o_Field->isRefresh,
				$this->b_Main?'rowEditRefresh':'rowObjectEditRefresh');
				$add .= Qss_Lib_Template::DateBox($elename, $o_Field->getValue(),$o_Field->bReadOnly,$onchangefunction);
				break;
			case 9: //Checkbox
				$json = $o_Field->getJsonRegx();
				if($json)//kiểu list tĩnh
				{
					$add = $o_Field->CheckBox( $user,$this->b_Main,$dialog,$json);
				}
				else
				{
					$namespce = '';
					if($dialog)
					{
						if($this->b_Main)
						{
							$namespce = sprintf('dialog_%1$s.',$this->FormCode);
						}
						else
						{
							$namespce = sprintf('dialog_%1$s.',$this->ObjectCode);
						}
					}
					$onchangefunction = sprintf('onchange="%1$s%3$s(this,%2$s)"',
					$namespce,
					$o_Field->isRefresh,
					$this->b_Main?'rowEditRefresh':'rowObjectEditRefresh');
					$add .= Qss_Lib_Template::CheckBox($elename, ($o_Field->getValue()) ? 1 : 0,$onchangefunction,$o_Field->bReadOnly);//== $o_Field->szTValue
				}
				break;
			case 11: //Selectbox
				$add .= $o_Field->getSelectBox();
				break;
			case 12: //(calculate)
				if($user)//nếu ẩn trong form sửa thì không gọi khỏi tốn performance
				{
					if((($o_Field->bGrid & 4) && !$user->user_mobile) || (($o_Field->bGrid & 8) && $user->user_mobile))
					{
						break;
					}
				}
				$classname = 'Qss_Bin_Calculate_'.$this->ObjectCode.'_'.$o_Field->FieldCode;
				if(class_exists($classname))
				{
					$autocal = new $classname($this);
					$add .= $autocal->__doExecute();
				}
				break;
			case 13: //(Display)
				$classname = 'Qss_Bin_Display_'.$this->ObjectCode.'_'.$o_Field->FieldCode;
				if(class_exists($classname))
				{
					$autocal = new $classname($this->currentData);
					$add .= $autocal->__doExecute($this->currentData);
				}
				break;
			case 14: //Date
				$add = Qss_Lib_Template::TextBox($elename, $o_Field->szValue, $o_Field->intFieldWidth,$o_Field->bReadOnly); //datetime
				break;
		}
		$add = (($o_Field->bRequired && $o_Field->intInputType != 11 && $o_Field->intInputType != 12 && !$o_Field->bReadOnly) ? '<div class="required">'.$add.'</div>' : $add);
		return $add;
	}
	public function getGridFieldCount()
	{
		if(!$this->gridFieldCount)
		{
			$retval = 0;
			$fields = $this->loadFields();
			foreach ($fields as $field)
			{
				if($field->bGrid & 1)//Chỉ dùng cho desktop
				{
					$retval++;
				}
			}
			$this->gridFieldCount = $retval;
		}
		return $this->gridFieldCount;
	}
	public function getAgregrate(Qss_Model_Field $field,$sql)
	{

		$retval = '';
		switch ($field->AFunction) {
			case 'SUM':
				$retval = sprintf(' sum(%1$s) as adata ',$field->FieldCode);
				break;
			case 'COUNT':
				$retval = sprintf(' count(*) as adata ');
				break;
			case 'AVG':
				$retval = sprintf(' avg(%1$s) as adata ',$field->FieldCode);
				break;
			default:
				return $field->AFunction;
				break;
		}
		if($retval)
		{
			$order = array("\r\n", "\n", "\r", "\t");
			$sql1 = preg_replace("/(select) .* (from)/s", "$1 {$retval} from ", str_replace($order, ' ', $sql), 1);
			$sql1 = preg_replace("/(from) .* (where)/s", "$0  where", $sql1, 1);
			$sql1 = preg_replace('(where)', ' ', $sql1,1);
			$sql1 = preg_replace("/order by .*/", "$1 ", $sql1, 1);
			$dataSQL = $this->_o_DB->fetchOne($sql1);
			if($dataSQL)
			{
				if($field->intFieldType == 11 && is_numeric($dataSQL->adata))
				$retval = $dataSQL->adata / 1000;
				else
				$retval = $dataSQL->adata;
			}
		}
		return $retval;
	}
	public function getEvents()
	{
		$sql = sprintf('select * from qseventlogs
				inner join qsevents on qsevents.EventID = qseventlogs.EventID
				inner join qsusers on qsevents.CreatedID = qsusers.UID
				where qseventlogs.EventID in (select EventID from qseventrefer where IFID = %1$d or IOID = %2$d)
				order by Date desc, STime' ,$this->i_IFID,$this->i_IOID);
		return $this->_o_DB->fetchAll($sql);
	}
	public function countAll()
	{
		$sql = sprintf('select count(IOID) as count
				from %2$s
				where IFID_%3$s = %1$d' ,
		$this->i_IFID,
		$this->ObjectCode,
		$this->FormCode);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->count:0;
	}
	function getSelectSQL(Qss_Model_Form $o_Form,
	Qss_Model_UserInfo $o_User,
	$i_CurrentPage = 1,
	$i_Limit = 50,
	$i_FieldOrder = 0,
	$sz_OrderType = 'ASC',
	$a_Condition = array(),
	$groupby=0)
	{
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$filter = array();
		if ( count($a_Condition) )
		{
			if($this->b_Main)
			{
				$filter = array_merge($filter,$this->a_fSearchIFID($a_Condition));
			}
			else
			{
				$filter = array_merge($filter,$this->a_fSearchIOID($a_Condition));
			}
			if ( is_array($filter) && !count($filter) )
			{
				$filter = array(0);
			}
		}
		$a_Condition = $filter;
		$order = "";
		$join = "";
		if ( is_array($a_Condition) && sizeof($a_Condition) )
		{
			if ( $this->b_Main )
			{
				$order = sprintf(' and qsiforms.IFID in (%1$s)', sizeof($a_Condition) ? implode(',', $a_Condition) : '0');
			}
			else
			{
				$order = sprintf(' and qsiobjects.IOID in (%1$s)', sizeof($a_Condition) ? implode(',', $a_Condition) : '0');
			}
		}
		if($groupby)
		{
			if($groupby > 0)
			{
				$fgroupby = new Qss_Model_Field();
				$fgroupby->b_fInit($this->ObjectCode,$groupby);
				$order .= sprintf('order by %1$s.%2$s DESC',Qss_Lib_Const::$DATABASE_TABLES[$fgroupby->intFieldType - 1],Qss_Lib_Const::getDataField($fgroupby->intFieldType));
			}
			elseif($groupby == -1 && $o_Form->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE)
			{
				$order .= sprintf('order by qsworkflowsteps.StepNo %1$s', $sz_OrderType);
			}
			elseif($groupby == -2)
			{
				$order .= sprintf('order by qsusers.UID %1$s', $sz_OrderType);
			}
		}
		elseif ( $i_FieldOrder == -1 )
		{
			$order .= sprintf('order by qsworkflowsteps.StepNo %1$s', $sz_OrderType);
		}
		elseif ( $i_FieldOrder )
		{
			$order .= sprintf('order by %2$s %1$s', $sz_OrderType,$i_FieldOrder);
		}
		else
		{
			$order .= sprintf('order by qsiforms.SDate DESC');
		}
		if ( !$this->b_Main )
		{
			$sql = sprintf('select qsiforms.*,v.*,qsusers.UserName,
							IFNULL((Select case when UID=%7$d then 1 else 0 end From qsfreader Where IFID=qsiforms.IFID order by qsfreader.UID!=%7$d limit 1),1) as Accepted,
							qsworkflowsteps.SID,
							ifnull(qsworkflowsteps.Name%8$s,qsworkflowsteps.Name) as Name ,qsworkflowsteps.StepNo,
							qsworkflowsteps.Color
							from %2$s as v
                            inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
							inner join qsusers on qsiobjects.UID=qsusers.UID
							left join qsworkflowsteps on qsworkflowsteps.StepNo=qsiforms.Status and qsworkflowsteps.WFID=%1$d
							%5$s
							where qsrecforms.ObjectCode="%2$s" and qsiforms.Deleted<>1 and qsiforms.FormCode="%3$s" and qsiforms.DepartmentID=%4$d %6$s', /* */
			$o_Form->i_WorkFlowID,
			$this->ObjectCode,
			$this->FormCode,
			$this->intDepartmentID,
			$join,
			$order,
			$o_User->user_id,
			$lang);
		}
		elseif ( $o_Form->i_Type == 1 || $o_Form->i_Type == Qss_Lib_Const::FORM_TYPE_PROCESS)
		{
			$sql = sprintf('select v.*,qsusers.*,qsiforms.*,
								IFNULL((Select case when UID=%5$d then 1 else 0 end From qsfreader Where IFID=qsiforms.IFID order by qsfreader.UID!=%5$d limit 1),1) as Accepted
								from %1$s as v
                            	inner join qsiforms on qsiforms.IFID = v.IFID_%2$s
                                %3$s
                                inner join qsusers on qsiforms.UID=qsusers.UID
                                where qsiforms.Deleted<>1 %4$s', /* */
			$this->ObjectCode,
			$this->FormCode,
			$join,
			$order,
			$o_User->user_id);
		}
		elseif ( $o_Form->i_Type == 2 )
		{
			$sql = sprintf('select v.*,qsusers.*,qsiforms.*,
								IFNULL((Select case when UID=%6$d then 1 else 0 end From qsfreader Where IFID=qsiforms.IFID order by qsfreader.UID!=%6$d limit 1),1) as Accepted
								from %1$s as v
                            	inner join qsiforms on qsiforms.IFID = v.IFID_%2$s
                                %4$s
                                inner join qsusers on qsiforms.UID=qsusers.UID
                                where qsiforms.Deleted<>1  
                                and qsiforms.DepartmentID in(%3$d) %5$s', 
			$this->ObjectCode,
			$this->FormCode,
			$o_User->user_dept_id . ',' . $o_User->user_dept_list,
			$join,
			$order,
			$o_User->user_id);
		}
		else
		{
			$sql = sprintf('select distinct v.*,qsusers.UserName,qsiforms.*,qsworkflowsteps.SID,
							ifnull(qsworkflowsteps.Name%10$s,qsworkflowsteps.Name) as Name ,qsworkflowsteps.StepNo,
							qsworkflowsteps.Color,
							IFNULL((Select case when UID=%2$d then 1 else 0 end From qsfreader Where IFID=qsiforms.IFID order by qsfreader.UID!=%2$d limit 1),1) as Accepted
							from %1$s as v
                           	inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
							%5$s
							inner join qsusers on qsiforms.UID=qsusers.UID
							inner join qsworkflowsteps on qsworkflowsteps.StepNo=qsiforms.Status and qsworkflowsteps.WFID=%4$d
							left join (SELECT * FROM qsftrace WHERE qsftrace.UID = %2$d LIMIT 1) as qsftrace on qsftrace.IFID =  qsiforms.IFID
							where qsiforms.UID!=0 
							and qsiforms.deleted<>1 %6$s', /* */
			$this->ObjectCode,
			$o_User->user_id,
			$this->FormCode,
			$o_Form->i_WorkFlowID,
			$join,
			$order,
			$this->intRights,
			$o_User->user_dept_id . ',' . $o_User->user_dept_list,
			$o_User->user_group_list,
			$lang);
		}
		return $sql;
	}
	function getFieldByCode ($code)
	{
		foreach ($this->loadFields() as $key => $value)
		{
			if ( $value->FieldCode == $code )
			return $value;
		}
		return new Qss_Model_Field();
	}
	public function getCrossLinks()
	{
		//xử lý tất cả lookup
		$exists = array();
		$sql = sprintf('select qsforms.Name,qsfields.FieldCode,qsfields.ObjectCode as object,qsfields.RefObjectCode as curobject
					from qsfields 
					inner join qsfobjects on qsfobjects.ObjectCode = qsfields.ObjectCode
					inner join qsforms on qsforms.FormCode = qsfobjects.FormCode
					where qsforms.Effected = 1 and qsfields.RefObjectCode = %1$s and qsfields.RefFormCode = %2$s and qsfields.RefFieldCode != \'\'
					order by FieldNo'
					,$this->_o_DB->quote($this->ObjectCode)
					,$this->_o_DB->quote($this->FormCode));
					$dataSQL = $this->_o_DB->fetchAll($sql);
					foreach ($dataSQL as $item)
					{
						$sql = sprintf('select 1 from %1$s a
						inner join %2$s as b on a.Ref_%3$s = b.IOID
						where b.IOID = %4$d
						limit 1',
						$item->object,
						$item->curobject,
						$item->FieldCode,
						$this->i_IOID);
						$checkSQL = $this->_o_DB->fetchOne($sql);
						if($checkSQL)
						{
							$exists[] = $item;
						}
					}
					return $exists;
	}
	/*public function getParentID()
	 {
		return $this->o_Parent?$this->o_Parent->ObjectCode:0;
		}
		public function getIParentID()
		{
		return $this->o_Parent?$this->o_Parent->i_IOID:0;
		}
		public function getParent()
		{
		return $this->o_Parent;
		}*/
	public function doUpDown($type)
	{
		if($this->b_Tree)
		{
			$sqlTree = '';
			if(!$this->b_Main)
			{
				$sqlTree = sprintf(' and IFID_%1$s = %2$d',$this->FormCode,$this->i_IFID);
			}
			//get current
			$sql = sprintf('select * from %1$s where IOID = %2$d limit 1',
			$this->ObjectCode,
			$this->i_IOID);
			$curr = $this->_o_DB->fetchOne($sql);
			if($curr)
			{
				if($type)
				{
					//up, get above
					$sql = sprintf('select * from %1$s where rgt = %2$d-1',
					$this->ObjectCode,
					$curr->lft);
					$sql .= $sqlTree;
					$pre = $this->_o_DB->fetchOne($sql);
					if($pre)
					{
						//save temp table to store ioid
						$sql = sprintf('CREATE TEMPORARY TABLE TREE(IOID int)');
						$this->_o_DB->execute($sql);
						//
						$sql = sprintf('insert into TREE select IOID from %1$s
								where lft between %2$d and %3$d and rgt between %2$d and %3$d',
						$this->ObjectCode,
						$curr->lft,
						$curr->rgt);
						$sql .= $sqlTree;
						$this->_o_DB->execute($sql);
						$sql = sprintf('update %1$s set lft = lft - %2$d,
								rgt = rgt - %2$d
								where lft between %3$d and %4$d and rgt between %3$d and %4$d',
						$this->ObjectCode,
						($pre->rgt - $pre->lft + 1),
						$curr->lft,
						$curr->rgt);
						$sql .= $sqlTree;
						$this->_o_DB->execute($sql);
						//update pre
						$sql = sprintf('update %1$s set lft = lft + %2$d,
								rgt = rgt + %2$d
								where lft between %3$d and %4$d and rgt between %3$d and %4$d and IOID not in (select IOID from TREE)',
						$this->ObjectCode,
						($curr->rgt - $curr->lft + 1),
						$pre->lft,
						$pre->rgt);
						$sql .= $sqlTree;
						$this->_o_DB->execute($sql);
						//drop
						$sql = sprintf('DROP TABLE TREE');
						$this->_o_DB->execute($sql);
					}
				}
				else
				{
					//down, get below
					$sql = sprintf('select * from %1$s where lft = %2$d+1',
					$this->ObjectCode,
					$curr->rgt);
					$sql .= $sqlTree;
					$next = $this->_o_DB->fetchOne($sql);
					if($next)
					{
						//save temp table to store ioid
						$sql = sprintf('CREATE TEMPORARY TABLE TREE(IOID int)');
						$this->_o_DB->execute($sql);
						//
						$sql = sprintf('insert into TREE select IOID from %1$s
								where lft between %2$d and %3$d and rgt between %2$d and %3$d',
						$this->ObjectCode,
						$curr->lft,
						$curr->rgt);
						$sql .= $sqlTree;
						$this->_o_DB->execute($sql);
						$sql = sprintf('update %1$s set lft = lft + %2$d,
								rgt = rgt + %2$d
								where lft between %3$d and %4$d and rgt between %3$d and %4$d',
						$this->ObjectCode,
						($next->rgt - $next->lft + 1),
						$curr->lft,
						$curr->rgt);
						$sql .= $sqlTree;
						$this->_o_DB->execute($sql);
						$sql = sprintf('update %1$s set lft = lft - %2$d,
								rgt = rgt - %2$d
								where lft between %3$d and %4$d and rgt between %3$d and %4$d and IOID not in (select IOID from TREE)',
						$this->ObjectCode,
						($curr->rgt - $curr->lft + 1),
						$next->lft,
						$next->rgt);
						$sql .= $sqlTree;
						$this->_o_DB->execute($sql);
					}
				}
			}
		}
	}
	public function reorderTree($newIOID,$up)
	{
		if($this->b_Tree)
		{
			$sqlTree = '';
			if(!$this->b_Main)
			{
				$sqlTree = sprintf(' and IFID_%1$s = %2$d',$this->FormCode,$this->i_IFID);
			}
			//get current
			$sql = sprintf('select * from %1$s where IOID = %2$d limit 1',
			$this->ObjectCode,
			$this->i_IOID);
			$curr = $this->_o_DB->fetchOne($sql);

			if($curr)
			{
					
				//down, get below
				$sql = sprintf('select * from %1$s where IOID = %2$d',
				$this->ObjectCode,
				$newIOID);
				$sql .= $sqlTree;
				$next = $this->_o_DB->fetchOne($sql);
				if($next)
				{
					//save temp table to store ioid
					//$sql = sprintf('CREATE TEMPORARY TABLE TREE(IOID int)');
					//$this->_o_DB->execute($sql);
					//copy current node to temp
					$sql = sprintf('CREATE TEMPORARY TABLE TREE select * from %1$s
							where lft between %2$d and %3$d and rgt between %2$d and %3$d',
					$this->ObjectCode,
					$curr->lft,
					$curr->rgt);
					$sql .= $sqlTree;
					$this->_o_DB->execute($sql);
					//delete current gap
					$sql = sprintf('delete from %1$s
							where lft between %2$d and %3$d and rgt between %2$d and %3$d',
					$this->ObjectCode,
					$curr->lft,
					$curr->rgt);
					$sql .= $sqlTree;
					$this->_o_DB->execute($sql);
					//update lft rgt bellow
					$sql = sprintf('update %1$s set lft = lft - %2$d,
							rgt = rgt - %2$d
							where lft > %3$d and rgt > %4$d',
					$this->ObjectCode,
					($curr->rgt - $curr->lft + 1),
					$curr->lft,
					$curr->rgt);
					$sql .= $sqlTree;
					$this->_o_DB->execute($sql);
					//get new target
					$sql = sprintf('select * from %1$s where IOID = %2$d',
					$this->ObjectCode,
					$newIOID);
					$sql .= $sqlTree;
					$target = $this->_o_DB->fetchOne($sql);
						
					if($up)//up
					{
						//add current gap to new position
						$sql = sprintf('update %1$s set lft = lft + %2$d,
								rgt = rgt + %2$d
								where lft >= %3$d',
						$this->ObjectCode,
						($curr->rgt - $curr->lft+1),
						$target->lft,
						$target->rgt);
						$sql .= $sqlTree;
						$this->_o_DB->execute($sql);
						//update TEMP
						$sql = sprintf('update TREE set lft = %1$d + lft - %2$d,
								rgt = %1$d + rgt - %2$d',
						$target->lft,
						$curr->lft);
						$this->_o_DB->execute($sql);
					}
					else//down
					{
						//add current gap to new position
						$sql = sprintf('update %1$s set lft = lft + %2$d,
								rgt = rgt + %2$d
								where lft > %3$d and rgt > %4$d',
						$this->ObjectCode,
						($curr->rgt - $curr->lft+1),
						$target->lft,
						$target->rgt);
						$sql .= $sqlTree;
						$this->_o_DB->execute($sql);
						//update TEMP
						$sql = sprintf('update TREE set lft = %1$d + lft - %2$d,
								rgt = %1$d + rgt - %2$d',
						$target->rgt + 1,
						$curr->lft);
						$this->_o_DB->execute($sql);
					}
					//copy from temp
					$sql = sprintf('insert into %1$s select * from TREE',
					$this->ObjectCode);
					$this->_o_DB->execute($sql);
				}
			}
		}
	}
	public function v_fTrace ($logs)
	{
		if(!$logs)
		{
			return;
		}
		$userinfo = Qss_Register::get('userinfo');
		if($userinfo)
		{
			$uid = $userinfo->user_id;
		}
		else
		{
			$uid = $this->i_UserID;
		}
		$content = '';
		$select = '';
		foreach ($logs as $key=>$val)
		{
			if($select != '')
			{
				$select .= ',';
			}
			$select .= $this->_o_DB->quote($val.': ').',IFNULL('.$key.",''),'<br>'";
		}
		if($select != '')
		{
			$sql = sprintf('select concat(%1$s) as Content from %2$s where IOID = %3$d',
			$select,
			$this->ObjectCode,
			$this->i_IOID);
			$dataSQL = $this->_o_DB->fetchOne($sql);
			if($dataSQL)
			{
				$content .= 'Dữ liệu cũ: ' . $dataSQL->Content;
			}
		}
		if($content)
		{
			$data = array('IFID'		=>	$this->i_IFID,
						'UID'		=>	$uid,
						'Date'		=>	time(),
						'Logs'		=>	$content);
			$sql = sprintf('insert into qsftrace%1$s',
			$this->arrayToInsert($data));
			$this->_o_DB->execute($sql);
		}
	}
	public function insertData()
	{
		$data = array();
		$data['IFID_'.$this->FormCode] = $this->i_IFID;
		$data['DeptID'] = $this->intDepartmentID;
		foreach ($this->a_Fields as $field)
		{
			if($field->bEditStatus)
			{
				if($field->intFieldType == 8)
				{
					$data[$field->FieldCode] = $field->moveFile();
				}
				elseif($field->intFieldType == 9)
				{
					$data[$field->FieldCode] = $field->moveFile();
				}
				elseif($field->intFieldType == 10)
				{
					$data[$field->FieldCode] = $field->getValue()?Qss_Lib_Date::displaytomysql($field->getValue()):null;
				}
				elseif($field->intFieldType == 11)
				{
					$data[$field->FieldCode] = $field->getValue(false)*1000;
				}
				elseif($field->intFieldType == 17) // custom field
				{
					// bo qua
				}
				else
				{
					$data[$field->FieldCode] = $field->getValue();
				}
				if($field->intFieldType == 11
				|| $field->RefFieldCode
				|| $field->intFieldType == 14
				|| $field->intFieldType == 16)
				{
					$data['Ref_'.$field->FieldCode] = $field->getRefIOID();
				}
				if(($field->intInputType == 5 || $field->intInputType == 3) && $field->getJsonRegx())
				{
					$json = $field->getJsonRegx();
					$data['Ref_'.$field->FieldCode] = @$json[$field->getValue()];
				}
			}
		}
		$sql = sprintf('insert into %1$s%2$s',
		$this->ObjectCode,
		$this->arrayToInsert($data));
		//echo '<pre>'; print_r($sql); die;
		$this->i_IOID = $this->_o_DB->execute($sql);
	}
	public function updateData()
	{
		$data = array();
		$data['IFID_'.$this->FormCode] = $this->i_IFID;
		foreach ($this->a_Fields as $field)
		{
			if($field->bEditStatus)
			{
				if($field->intFieldType == 8)
				{
					$data[$field->FieldCode] = $field->moveFile();
				}
				elseif($field->intFieldType == 9)
				{
					$data[$field->FieldCode] = $field->moveFile();
				}
				elseif($field->intFieldType == 10)
				{
					$data[$field->FieldCode] = $field->getValue()?Qss_Lib_Date::displaytomysql($field->getValue()):null;
				}
				elseif($field->intFieldType == 11)
				{
					$data[$field->FieldCode] = $field->getValue(false)*1000;
				}
				elseif($field->intFieldType == 17) // custom field
				{
					// bo qua
				}
				elseif($field->intFieldType == 4)
				{
					if($field->getValue())
					{
						$data[$field->FieldCode] = $field->getValue();
					}
					else
					{
						$data[$field->FieldCode] = null;
					}
				}
				else
				{
					$data[$field->FieldCode] = $field->getValue();
				}
				if($field->intFieldType == 11
				|| $field->RefFieldCode
				|| $field->intFieldType == 14
				|| $field->intFieldType == 16)
				{
					$data['Ref_'.$field->FieldCode] = $field->getRefIOID();
				}
				elseif($field->intFieldType != 17) //update các lookup
				{
					$this->updateLookUp($field->ObjectCode,$field->FieldCode,$this->i_IOID,$data[$field->FieldCode],'');
				}
				if(($field->intInputType == 5  || $field->intInputType == 3) && $field->getJsonRegx())
				{
					$json = $field->getJsonRegx();
					$data['Ref_'.$field->FieldCode] = @$json[$field->getValue()];
				}
			}
		}
		$sql = sprintf('update %1$s set %2$s where IOID=%3$s',
		$this->ObjectCode,
		$this->arrayToUpdate($data),
		$this->i_IOID);
		$this->_o_DB->execute($sql);
	}
	private function updateLookUp($ObjectCode,$FieldCode,$ioid,$value,$where)
	{
		$sqlLookup = sprintf('select distinct FieldCode,qsfields.ObjectCode
								from qsfields 
								inner join qsobjects on qsobjects.ObjectCode = qsfields.ObjectCode
								inner join qsfobjects on qsfobjects.ObjectCode = qsobjects.ObjectCode
								inner join qsforms on qsforms.FormCode = qsfobjects.FormCode
								where qsforms.Effected = 1 and RefObjectCode = %2$s and RefFieldCode = %1$s',
		$this->_o_DB->quote($FieldCode)
		,$this->_o_DB->quote($ObjectCode));
		$dataLookup = $this->_o_DB->fetchAll($sqlLookup);
		foreach ($dataLookup as $lookup)
		{
			if($this->_o_DB->tableExists($lookup->ObjectCode))
			{
				if($ioid)
				{
					$where = '';
					$updateWhere = sprintf('Ref_%1$s = %2$d',$lookup->FieldCode,$ioid);
				}
				elseif($lookup->ObjectCode == $ObjectCode)
				{
					//create temp
					$temp = sprintf('CREATE TEMPORARY TABLE IF NOT EXISTS %1$s_tmp AS (%2$s)',
					$ObjectCode,
					$where);
					$this->_o_DB->execute($temp);
					$updateWhere = sprintf('Ref_%1$s in(select IOID from %2$s_tmp)'
					,$lookup->FieldCode
					,$ObjectCode);
				}
				else
				{
					$updateWhere = sprintf('Ref_%1$s in(%2$s)',$lookup->FieldCode,$where);
				}
				$sqlUpdateLookup = sprintf('update %1$s set %2$s = %3$s where %4$s'
				,$lookup->ObjectCode
				,$lookup->FieldCode
				,$this->_o_DB->quote($value)
				,$updateWhere);
				$newioid 	= $this->_o_DB->execute($sqlUpdateLookup);
				if(!$where)
				{
					$newwhere = sprintf('select IOID from %1$s where Ref_%2$s = %4$d'
					,$lookup->ObjectCode
					,$lookup->FieldCode
					,$this->_o_DB->quote($value)
					,$ioid);
				}
				else
				{
					$newwhere = sprintf('select IOID from %1$s where Ref_%2$s in(%4$s)'
					,$lookup->ObjectCode
					,$lookup->FieldCode
					,$this->_o_DB->quote($value)
					,$where);
				}
				$this->updateLookUp($lookup->ObjectCode,$lookup->FieldCode,$newioid,$value,$newwhere);
			}
		}
	}
	public function getMultiEditableFields()
	{
		$retval = array();
		$fields = $this->loadFields();
		foreach ($fields as $f)
		{
			if($f->bGrid & 16)
			{
				$retval[] = $f;
			}
		}
		return $retval;
	}
	public function autoCalculate()
	{
		foreach($this->a_Fields as $f)
		{
			if((!$this->i_IFID && !$this->i_IOID && $f->bUnique) || (($f->szRegx == Qss_Lib_Const::FIELD_REG_RECALCULATE || ($f->getValue(false) === '' || $f->getValue(false) == null) && !$this->i_IOID)))
			{
				$classname = 'Qss_Bin_Calculate_'.$this->ObjectCode.'_'.$f->FieldCode;
				if($f->szDefaultVal != 'KEEP' && class_exists($classname))
				{
					$autocal = new $classname($this);
					$retval = $autocal->__doExecute();
					if ( $retval !== null )
					{
						$f->setValue($retval);
					}
				}
			}
		}
	}
	public function countGroupBySQL ($sql, $groupby)
	{
		$retval = array();
		$select = '';
		$group = '';
		foreach ($groupby as $item)
		{
			if($select != '')
			{
				$select .= ',';
				$group .= ',';
			}
			$select .= 'v.'.$item;
			$group .= 'v.'.$item;
		}
		$select .= ', count(*) as TongSo';
		$order = array("\r\n", "\n", "\r", "\t");
		$sql1 = preg_replace("/(select) .* (from)/s", "$1 {$select} from ", str_replace($order, ' ', $sql), 1);
		$sql1 = preg_replace("/(order by).*/s", " ", $sql1, 1);
		$sql1 .= ' group by ' . $group; 
		return $this->_o_DB->fetchAll($sql1);
		
	}
	/**
	 * 
	 * Chuyển dữ liệu sang dạng giống table
	 */
	public function setTableData()
	{
		$retval = new stdClass();
		$retval->{'IFID_'.$this->FormCode} = $this->i_IFID; 
		$retval->IOID = $this->i_IOID;
		$retval->DeptID = $this->intDepartmentID;
		foreach ($this->a_Fields as $f)
		{
			$retval->{$f->FieldCode} = $f->getValue();
			$retval->{'Ref_'.$f->FieldCode} = $f->getRefIOID(); 
		}
		$this->currentData = $retval;
	} 
}
?>