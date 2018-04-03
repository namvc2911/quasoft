<?php
class Qss_Model_Admin_Document extends Qss_Model_Abstract
{
	public $dataField = 'DTID';
	public $arrField = array(1=>'Code',2=>'Type');
	public $arrFieldName = array(1=>'Mã tài liệu',2=>'Tên tài liệu');
	public $arrFieldType = array(1=>1,2=>1);

	//-----------------------------------------------------------------------
	/**
	* construct a department
	*
	* @access  public
	*/
	function __construct ()
	{
		parent::__construct();
	}

	public function getAll($dtid = 0)
	{
		$sql = sprintf('select * from qsdocumenttype where DTID!=%1$d',$dtid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getById($dtid)
	{
		$sql = sprintf('select * from qsdocumenttype where DTID=%1$d',$dtid);
		return $this->_o_DB->fetchOne($sql);
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
		$id = $params['dtid'];
		$data = array('Code'=>$params['szCode'],
						'Type'=>$params['szType'],
						'ParentID'=>$params['intParentID'],
						'File'=>$params['szFile']);
		if ( $id )
		{
			$sql = sprintf('update qsdocumenttype set %1$s where DTID = %2$d', /* */
				$this->arrayToUpdate($data),$id);
			$this->_o_DB->execute($sql);
		}
		else
		{
			$sql = sprintf('insert into qsdocumenttype%1$s',/* */ 
				$this->arrayToInsert($data));
			$id = $this->_o_DB->execute($sql);
		}
		return $id;
	}

	//-----------------------------------------------------------------------
	/**
	* Delete department
	*
	* @author HuyBD
	*
	* @return void
	*/
	function delete ($id)
	{
		$sql = sprintf('delete from qsdocumenttype where DTID = %1$d',/* */ 
				$id);
		$this->_o_DB->execute($sql);
	}

	
	function getByParent($id)
	{
		$sql = sprintf('select * from qsdocumenttype where ParentID=%1$d order by ParentID',/* */
			$id);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getAllByFormCode($FormCode,$status)
	{
		$sql = sprintf('select qsdocumenttype.* from qsdocumenttype 
					left join qsfrecords on qsfrecords.DTID = qsdocumenttype.DTID and FormCode="%1$s" and StepNo=%2$d
					order by FormCode',$FormCode,$status);
		return $this->_o_DB->fetchAll($sql);
	}
}
?>