<?php
class Qss_Model_System_Workflow extends Qss_Model_Abstract
{

	var $intWorkFlowID = 0;

	var $intGroupID = 0;

	var $szName;

	var $FormCode;

	var $intType;

	var $bActived;

	var $szDescription;

	//-----------------------------------------------------------------------
	/**
	* construct a group
	*
	* @access  public
	*/
	function __construct ($fid)
	{
		parent::__construct();
		$this->FormCode = $fid;
		$sql = sprintf('select * from qsforms where FormCode = "%1$s"', $this->FormCode);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL)
		{
			$this->intType = $dataSQL->Type;
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
	function init ($wfid)
	{
		$this->intWorkFlowID = $this->intWorkFlowID ? $this->intWorkFlowID : $wfid;
		if ( !$this->intWorkFlowID )
		return;
		$sql = "select * from qsworkflows where WFID=" . $this->intWorkFlowID;
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL)
		{
			$this->szName = $dataSQL->Name;
			$this->szDescription = $dataSQL->Description;
			$this->intFID = $dataSQL->FormCode;
			$this->bActived = $dataSQL->Actived;
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
		$sql = sprintf('select * from qsworkflows
                        where FormCode = "%1$s"', $this->FormCode);
		return $this->_o_DB->fetchAll($sql);
	}

	function save ($params)
	{
		$this->szName = $params['szName'];
		$this->szDescription = $params['szDesc'];
		$this->bActived = @$params['bActived'];
		if ( $this->intWorkFlowID )
		$sql = sprintf('update qsworkflows set Name=%1$s,Description=%2$s,Actived=%3$d
                                        where WFID = %4$d', $this->_o_DB->quote($this->szName), $this->_o_DB->quote($this->szDescription), $this->bActived, $this->intWorkFlowID);
		else
		$sql = sprintf('insert into qsworkflows(Name,Description,Actived,FormCode)
                                        values(%1$s,%2$s,%3$d,"%4$s")', 
				$this->_o_DB->quote($this->szName), 
				$this->_o_DB->quote($this->szDescription), 
				$this->bActived, 
				$this->FormCode);
		$this->_o_DB->execute($sql);
		return true;
	}

	function delete ()
	{
		$sql = sprintf('delete from qsworkflows where WFID = %1$d', $this->intWorkFlowID);
		$this->_o_DB->execute($sql);
		return true;
	}
	public function duplicate($fromfid,$tofid)
	{
//		$sql = sprintf('insert into qsuserforms(FID, GroupID,Rights) select %2$d, GroupID,Rights from qsuserforms where FID=%1$d',
//				$fromfid,$tofid);
//		$this->_o_DB->execute($sql);
	}
}
?>
