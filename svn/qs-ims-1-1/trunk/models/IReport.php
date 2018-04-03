<?php
class Qss_Model_IReport extends Qss_Model_Abstract
{
	var $arrQuery;
	 /**
	 * Working with all design of form that user acess via module management
     *'
     * @access  public
     */	
	public function __construct()
	{
	 	parent::__construct(); 
	}	

	function getAll()
	{
		$sql = sprintf('select *  
					from qsreports');
		return $this->_o_DB->fetchAll($sql);
	}
	function getById($id)
	{
		$sql = sprintf('select *  
					from qsreports where RID = %1$d'
					,$id);
		return $this->_o_DB->fetchOne($sql);
	}
	function getColumns($id)
	{
		$sql = sprintf('select *  
					from qsreportcolumns where RID = %1$d
					order by OrderNo',$id);
		return $this->_o_DB->fetchAll($sql);
	}
	function save($params)
	{
        $checkFieldWidthExists = $this->_o_DB->fetchOne('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = \'qsreports\' AND column_name = \'TableWidth\'');

        if(!$checkFieldWidthExists)
        {
            $this->_o_DB->execute('ALTER TABLE qsreports ADD COLUMN `TableWidth` INT AFTER `Name`;');
        }

		//save qsreports
		$data = array(
		    'Code'=>$params['report'],
            'Name'=>$params['name'],
            'GroupBy'=>$params['groupby'],
            'OrderBy'=>$params['orderby'],
            'TableWidth'=>$params['width']
        );
		$id = $params['reportid'];
		if($id)
		{
			$sql = sprintf('update qsreports set %1$s where RID = %2$d',
			$this->arrayToUpdate($data),$id);
			$this->_o_DB->execute($sql);
		}
		else
		{
			$sql = sprintf('insert into qsreports%1$s',$this->arrayToInsert($data));
			$id = $this->_o_DB->execute($sql);
		}
		//update columns
		$sql = sprintf('delete from qsreportcolumns where RID = %1$d',$id);
		$this->_o_DB->execute($sql);
		if(isset($params['column_code']) && is_array($params['column_code']))
		{
            $iOrder = 1;
			foreach ($params['column_code'] as $code)
			{
				if(@$params['display_'.$code])
				{
					$sql = sprintf('insert into qsreportcolumns(RID,Code,Name,Class,OrderNo)
							values(%5$d,%1$s,%2$s,%3$s,%4$d)',
							$this->_o_DB->quote($code),
							$this->_o_DB->quote($params['name_'.$code]),
							$this->_o_DB->quote($params['class_'.$code]),
							//$params['order_'.$code],
                            $iOrder++,
							$id);
					$this->_o_DB->execute($sql);
				}
			}	
		}
	}
	function delete($id)
	{
		$sql = sprintf('delete from 
					qsreports where RID = %1$d'
					,$id);
		return $this->_o_DB->execute($sql);
	}
}	
?>