<?php
class Qss_Model_Extra_Common extends Qss_Model_Abstract
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
     
	// $extWhere = '', // custom where 
	public function getTable($field  = array(), //'*', 'field', 'field as alias'
                                $table  = '', //example_table
                                $where  = array(), // 'field'=>'value', field = value and field = value 
                                $order  = array(), // 'field, field asc, field desc'
                                $limit  = 100, // or 'NO_LIMIT'
                                
                                $returnType = 2
                                )
	{
		$field  = count($field)?implode(',', $field):' * '; // fields
		$sWhere = ''; // where
		$order  = count($order)?sprintf(' order by %1$s ', implode(',', $order)):''; // order by 
		$limit  = (is_numeric($limit))?" limit {$limit}":''; // limit
		
		// add condition to filter records
		foreach ($where as $col=>$val)
		{
			$sWhere .= $sWhere?" and ":" ";	
			$sWhere .= is_numeric($val)?" {$col} = {$val}":"{$col} = '{$val}'"; 
		}
                
//                $sWhere .= $extWhere?$extWhere:'';
		
		$sWhere  = $sWhere?" where {$sWhere}":''; // add condition
		$sql     = sprintf(' select %1$s from %2$s %3$s %4$s %5$s ', $field, $table, $sWhere, $order, $limit);
		
                if($returnType == 2)
                {
                    return $this->_o_DB->fetchAll($sql);
                }
                else 
                {
                    return $this->_o_DB->fetchOne($sql);
                }
	}
}