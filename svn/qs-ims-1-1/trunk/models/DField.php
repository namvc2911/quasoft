<?php
class Qss_Model_DField extends Qss_Model_Field
{

	
	//-----------------------------------------------------------------------
	/**
	* Build constructor
	*'
	* @return void
	*/
	public function __construct ()
	{
		parent::__construct();
	}
	
	function getValue ($repBr = true)
	{
		$ret = $this->szValue;
		if(!$ret && $this->intRefIOID)
		{
			$dat = $this->getDataById($this->intRefIOID);
			if($dat)
			{
				$this->setValue($dat->Name);
				$ret = $this->szValue;
			}
		}
		if(!$ret && !$this->intIOID 
			&& ($this->szDefaultVal !== '') 
			&& $this->szDefaultVal!='KEEP')
		{
			if($this->szDefaultVal == 'AUTO')
			{
				$ret = $this->intDepartmentID;
			}
			else
			{
				$ret = $this->LookupValue($this->szDefaultVal);
			}
		}
		return $ret?$ret:'';
	}

	function sz_fGetDisplay ($special = false,$width = 0)
	{
		$ret = $this->szValue;
		if(!$ret && $this->intRefIOID)
		{
			$dat = $this->getDataById($this->intRefIOID);
			if($dat)
			{
				$this->setValue($dat->UserName);
				$ret = $this->szValue;
			}
		}
		if(!$ret && !$this->intIOID && ($this->szDefaultVal !== '') && $this->szDefaultVal!='KEEP')
		{
			if($this->szDefaultVal == 'AUTO')
			{
				$ret = Qss_Register::get('userinfo')->user_dept_name;
			}
			else
			{
				$ret = (string) $this->szDefaultVal;
			}
		}
		return $ret;
	}
	public function getRefIOID($user = null)
	{
		if(is_null($user))
		{
			$user = Qss_Register::get('userinfo');
		}
		if(!$this->intRefIOID && $this->szValue)
		{
			$this->setRefIOID($this->LookupValue($this->szValue));
		}
		return $this->intRefIOID;
	}	
	public function getDataById ($id)
	{
		$ret = '';
		$sql = sprintf('select * from qsdepartments where DepartmentID = %1$d', /* */
				$id);
		return $this->_o_DB->fetchOne($sql);
	}

	/**
	 *
	 * @param Qss_Model_UserInfo $user
	 * @return array
	 */
	public function a_fGetReference ()
	{
		$sql = sprintf('select * from qsdepartments');
		return $this->_o_DB->fetchAll($sql);
	}

	function b_fSave ($objectcode)
	{
		if($this->bEditStatus)
		{
			$logs = Qss_Register::get('Logs');
			$logs[$this->FieldCode] = $this->szFieldName;
			Qss_Register::set('Logs',$logs);
		}
		return $this->bEditStatus ;
	}

	function LookupValue ($val)
	{
		$sql = sprintf('select * from qsdepartments where Name = %1$s ', $this->_o_DB->quote($val));
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->DepartmentID:0;
	}

	
	function DBComboBox (Qss_Model_UserInfo $user = null,$bMain = true)
	{
		$onchangefunction = $bMain?'rowEditRefresh(this)':'rowObjectEditRefresh(this)';
		$elename = sprintf('%1$s_%2$s', $this->ObjectCode, $this->FieldCode);
		$sql = sprintf('select * from qsdepartments');
		$dataSQL = $this->_o_DB->fetchAll($sql);
		$a_Combo = array('' => '');
		foreach ($dataSQL as $data)
		{
			$a_Combo[$data->DepartmentID] = $data->Name;
		}
		return Qss_Lib_Template::ComboBox($elename, $a_Combo, $this->getRefIOID(), $this->intFieldWidth,$this->isRefresh?$onchangefunction:"");
	}
	function getSelectBox ()
	{
		$elename = sprintf('%1$s_%2$d', $this->ObjectCode, $this->FieldCode);
		$value = $this->intRefIOID;
		return sprintf('<div class="select-popup"><span onclick="popupSelect(%5$d,this,%6$d)" refid="%1$s">%2$s</span></div><input id="%1$s" name="%1$s" type="hidden" value="%3$s">',
		$elename,(!$this->sz_fGetDisplay())?'Bấm để chọn':$this->sz_fGetDisplay(),$value,$this->RefFormCode,$this->FieldCode,$this->isRefresh,$this->RefObjectCode);
	}
	
	public function getLookUp ($search = '')
	{
		$where = '';
		if($search)
		{
			$where = sprintf(' and Name like %1$s',$this->_o_DB->quote('%'.$search.'%'));
		}
		$sql = sprintf('select * from qsdepartments where 1=1 %1$s
						order by Name limit 100',$where); 
		return $this->_o_DB->fetchAll($sql);
	}

}
?>