<?php
class Qss_Model_System_Param extends Qss_Model_Abstract
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

	//-----------------------------------------------------------------------
	/**
	* Get all
	*
	* @return array
	*/
	function getAllParams ($deptid = 0)
	{
		$sql = sprintf('select * from qsparams as t1
			where DepartmentID in (select max(DepartmentID) from qsparams where (DepartmentID = %1$d or DepartmentID=0) and t1.PID = PID)',$deptid);
		return $this->_o_DB->fetchAll($sql);
		//group by PID
	}
	//-----------------------------------------------------------------------
	/**
	* Get all by deptid
	*
	* @return array
	*/
	public function getById ($id,$deptid=0)
	{
		$sql = sprintf('select * from qsparams where PID = %1$s and (DepartmentID = %2$d or DepartmentID = 0) order by DepartmentID desc limit 1',
		$this->_o_DB->quote($id),$deptid);
		return $this->_o_DB->fetchOne($sql);
	}
	/**
	 * Save
	 *
	 * @return array
	 */
	public function save($params,$deptid)
	{

		$sql = sprintf('select 1 from qsparams where PID = %1$s and DepartmentID = %2$d',$this->_o_DB->quote($params['PID']),$deptid);
		if($this->_o_DB->fetchOne($sql))
		{
			$sql = sprintf('update qsparams set Value = %3$s, Description = %4$s where PID = %1$s and DepartmentID = %2$d'
			,$this->_o_DB->quote($params['PID']),$deptid,$this->_o_DB->quote($params['Value']),$this->_o_DB->quote($params['Description']));
		}
		else
		{
			$sql = sprintf('insert into qsparams values(%1$s,%2$s,%3$s,%4$d)'
			,$this->_o_DB->quote($params['PID']),$this->_o_DB->quote($params['Value']),$this->_o_DB->quote($params['Description']),$deptid);
		}
		$this->_o_DB->execute($sql);
		return true;
	}
	public function delete($id,$deptid=0)
	{
		$sql = sprintf('delete from qsparams where PID = %1$s and DepartmentID = %2$d',$this->_o_DB->quote($id),$deptid);
		return $this->_o_DB->execute($sql);
	}

}
?>