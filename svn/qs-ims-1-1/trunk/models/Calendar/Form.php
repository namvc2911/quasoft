<?php
/**
 * System form model
 *
 * @author HuyBD
 *
 */
class Qss_Model_Calendar_Form extends Qss_Model_Form
{
	
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
	 * Get all system form
	 *
	 * @return array
	 */
	public function get ($fid)
	{
		$sql = sprintf('select qsfcalendar.* from qsfcalendar
					inner join qsforms on qsfcalendar.FormCode = qsforms.FormCode
					where qsfcalendar.FormCode = "%1$s"',/* */
					$fid);
		return $this->_o_DB->fetchOne($sql);
	}
	public function getAll ()
	{
		$sql = sprintf('select * from qsfcalendar
					inner join qsforms on qsfcalendar.FormCode = qsforms.FormCode');
		return $this->_o_DB->fetchAll($sql);
	}
	public function getAllForm ()
	{
		$sql = sprintf('select * from qsforms where FormCode in 
					(select FormCode from qsfobjects
						inner join qsfields on qsfields.ObjectCode = qsfobjects.ObjectCode 
						where FieldType=10 or FieldType=4)');
		return $this->_o_DB->fetchAll($sql);
	}
	public function getAllFieldsByFID ($fid)
	{
		$sql = sprintf('select * from qsfields
						inner join qsobjects on qsobjects.ObjectCode = qsfields.ObjectCode
						inner join qsfobjects on qsfobjects.ObjectCode = qsobjects.ObjectCode
						where qsfobjects.FormCode = "%1$s"
						order by qsfobjects.ObjNo,qsfields.FieldNo',$fid);
		return $this->_o_DB->fetchAll($sql);
	}
	/**
	 * Save system form
	 *
	 * @return boolean
	 */
	public function save ($params)
	{
		$data = array('FormCode'=>$params['fid'],
					'DisplayFieldCode1'=>$params['displayfield1'],
					'DisplayFieldCode2'=>$params['displayfield2'],
					'DisplayFieldCode3'=>$params['displayfield3'],
					'SDate'=>$params['sdate'],
					'EDate'=>$params['edate'],
					'STime'=>$params['stime'],
					'ETime'=>$params['etime'],
					'ADate'=>$params['adate'],
					'ATime'=>$params['atime'],
					'FilterFieldCode'=>$params['filterfield'],
					'GroupFieldCode'=>$params['groupfield']
		);
		$sql=sprintf('replace into qsfcalendar%1$s',/* */
			$this->arrayToInsert($data));
		$this->_o_DB->execute($sql);
	}
	public function delete ($fid)
	{
		$sql = sprintf('delete from qsfcalendar FormCode = "%1$s"',$fid);
		$this->_o_DB->execute($sql);
	}
	public function getCalendar($user,$startdate,$enddate,$filter,$owner,$limit = 1000)
	{
		$limit = $limit?$limit:1000;
		$arrCheck = array();
		$select = ' 1 ';
		$where = '';
		$joinstatus = sprintf('inner join qsiforms on qsiforms.IFID = tbl.IFID_%1$s',$this->FormCode);
		$configs = $this->get($this->FormCode);
		$arrSelectedObject = array();
		$order = '';
		if(count($owner))
		{
			$where .= sprintf(' and qsiforms.UID in(%1$s)',
								implode(',',$owner));
		}
		foreach($configs as $key=>$value)
		{	
			if($key !== 'FormCode' && $key !== 'ObjectCode')
			{
				if($value)
				{
					//$field = $this->o_fGetMainObject()->getFieldByCode($value);
					//$object = $this->o_fGetMainObject();
					//$arrSelectedObject[] = $field->ObjectCode;
					//$fieldname = 'tbl_' . $field->ObjectCode . '.'.$object->getFieldByCode($value)->FieldCode;
					//$reffieldname = 'tbl_' . $field->ObjectCode . '.Ref_'.$object->getFieldByCode($value)->FieldCode;
					$arrSelectedObject[] = substr($value,0,strpos($value,'.'));
					$fieldname = $value;
					$reffieldname = str_ireplace('.','.Ref_', $value);
					//$fieldalias = str_ireplace('.','_', $value);
					if($key == 'SDate')
					{
						$where .= sprintf(' and %3$s <= %2$s',// or %3$s >= %1$s)
								$this->_o_DB->quote($startdate),$this->_o_DB->quote($enddate),$fieldname);
						if($order !='')
						{
							$order .= ',';
						}
						$order .= sprintf(' %1$s',// or %3$s >= %1$s)
								$fieldname);
					}
					if($key == 'EDate')
					{
						$where .= sprintf(' and ((%3$s is null) or (%3$s = \'\') or (%3$s >= %1$s))',
								$this->_o_DB->quote($startdate),$this->_o_DB->quote($enddate),$fieldname);
					}
					if($key == 'ADate')
					{
						$where .= sprintf(' and ( (%3$s is null) or (%3$s = \'\') or (%3$s >= %1$s))',
								$this->_o_DB->quote($startdate),$this->_o_DB->quote($enddate),$fieldname);
					}
					if($key == 'FilterFieldCode')
					{
						if(count($filter))
						{
							$where .= sprintf(' and %1$s in(%2$s)',
												$reffieldname,implode(',',$filter));
						}
					}
					if($key == 'GroupFieldCode')
					{
						if($order !='')
						{
							$order .= ',';
						}
						$order = sprintf(' %1$s %2$s',
												$reffieldname,$order);
						$select .= sprintf(' ,%1$s as Ref%2$s',$reffieldname,$key); 
					}
				}
				else
				{
					$fieldname = '0';
				}
				$select .= sprintf(' ,%1$s as %2$s',$fieldname,$key); 
			}
		}
		$join = '';
		//print_r($arrSelectedObject);die;
		foreach($this->o_fGetMainObjects() as $object)
		{
			if(in_array($object->ObjectCode,$arrSelectedObject))
			{
				$join .= sprintf(' inner join %1$s as %2$s on %2$s.IFID_%3$s = qsiforms.IFID',$object->ObjectCode,$object->ObjectCode,$this->FormCode);
			}
		}
		foreach($this->a_fGetSubObjects() as $object)
		{
			if(in_array($object->ObjectCode,$arrSelectedObject))
			{
				$join .= sprintf(' inner join %1$s as %2$s on %2$s.IFID_%3$s = qsiforms.IFID',$object->ObjectCode,$object->ObjectCode,$this->FormCode);
			}
		}
		if(!$join)
		{
			return array();
		}
		$sql = sprintf('select distinct %6$s, qsiforms.IFID,qsiforms.DepartmentID, Error, 
						ErrorMessage,qsworkflowsteps.color
                        from qsiforms
                        inner join qsforms on qsforms.FormCode=qsiforms.FormCode
                   		inner join qsusers on qsiforms.UID=qsusers.UID
						left join qsworkflows on qsworkflows.FormCode = qsforms.FormCode and Actived=1
                        left join qsworkflowsteps on qsworkflowsteps.StepNo=qsiforms.Status and qsworkflowsteps.WFID=qsworkflows.WFID
						%4$s
                        where qsforms.FormCode="%3$s"
						and (qsiforms.UID = %1$d or (qsiforms.IFID in(Select IFID from qsftrace where UID=%1$d)) 
						or qsiforms.GroupID in(%2$s) or %9$d&16) and qsiforms.deleted<>1
						%7$s
						order by %8$s
						limit %5$d', 
						$user->user_id, 
						$user->user_group_list,
						$this->FormCode,
						$join,
						$limit,
						$select,
						$where,
						$order,
						$this->i_Rights);
					//	echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
	public function getFilters()
	{
		$configs = $this->get($this->FormCode);
		if($configs->FilterFieldCode)
		{
			$arr = explode('.',$configs->FilterFieldCode);
			if(count($arr) == 2)
			{
				$field = $this->getFieldByCode($arr[0], $arr[1]);
				$object = $this->o_fGetObjectByCode($field->ObjectCode);
				$sql = sprintf('select distinct %1$s as Value, Ref_%1$s as ID
							from %2$s
							where Ref_%1$s is not null', 
							$field->FieldCode, 
							$object->ObjectCode);
				return $this->_o_DB->fetchAll($sql);
			}
		}
		return array();
	}
}
?>