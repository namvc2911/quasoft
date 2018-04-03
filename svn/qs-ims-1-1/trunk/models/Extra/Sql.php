<?php
class Qss_Model_Extra_Sql extends Qss_Model_Abstract
{
	public $filter = array();

	public $limit  = '';
	
	public function __construct()
	{
		parent::__construct();
	}
	public static function createInstance()
	{
		return new self();
	}	
	
	public function setCond($sql)
	{
		$this->filter[] = $sql;
	}
	
	/**
	 * 
	 * @param string $fieldWithAlias
	 * @param string $operator ">, <, >=, <=, <>"
	 * @param int $val 
	 */
	public function setCondWithCustomOperatorAndIntValRequire($fieldWithAlias, $operator, $val)
	{
		$this->filter[] = sprintf(' ifnull(%1$s, 0) %3$s %2$d ', $fieldWithAlias, $val, $operator);
	}
	
	/**
	 * 
	 * @param string $fieldWithAlias
	 * @param string $operator ">, <, >=, <=, <>"
	 * @param int $val 
	 */
	public function getCondWithCustomOperatorAndIntValRequire($fieldWithAlias, $operator, $val)
	{
		return sprintf(' ifnull(%1$s, 0) %3$s %2$d ', $fieldWithAlias, $val, $operator);
	}	
	
	/**
	 * 
	 * @param string $fieldWithAlias
	 * @param string $operator ">, <, >=, <=, <>"
	 * @param string $val 
	 */
	public function setCondWithCustomOperatorAndStrValRequire($fieldWithAlias, $operator, $val)
	{
		$this->filter[] = sprintf(' ifnull(%1$s, \'\') %3$s %2$s '
			, $fieldWithAlias, $this->_o_DB->quote($val), $operator);
	}
	
	/**
	 * 
	 * @param string $fieldWithAlias
	 * @param string $operator ">, <, >=, <=, <>"
	 * @param string $val 
	 */
	public function getCondWithCustomOperatorAndStrValRequire($fieldWithAlias, $operator, $val)
	{
		return sprintf(' ifnull(%1$s, \'\') %3$s %2$s '
			, $fieldWithAlias, $this->_o_DB->quote($val), $operator);
	}		
	
	/**
	 * @param string $fieldWithAlias
	 * @param int $val <number>
	 */
	public function setEqualCond($fieldWithAlias, $val)
	{
		if($val)
		$this->filter[] = sprintf(' ifnull(%1$s, 0) = %2$d ', $fieldWithAlias, $val);
	}
	
	/**
	 * @param string $fieldWithAlias
	 * @param int $val <number>
	 */	
	public function getEqualCondStr($fieldWithAlias, $val)
	{
		return ($val)?sprintf(' ifnull(%1$s, 0) = %2$d ', $fieldWithAlias, $val):'';
	}	
	
	/**
	 * @param string $fieldWithAlias
	 * @param string $val 
	 */
	public function setEqualCondWithValIsString($fieldWithAlias, $val)
	{
		if($val)
		$this->filter[] = sprintf(' ifnull(%1$s, \'\') = %2$s ', $fieldWithAlias, $this->_o_DB->quote($val));
	}
	
	/**
	 * @param string $fieldWithAlias
	 * @param string $val 
	 */	
	public function getEqualCondWithValIsString($fieldWithAlias, $val)
	{
		return ($val)?sprintf(' ifnull(%1$s, \'\') = %2$s ', $fieldWithAlias, $this->_o_DB->quote($val)):'';
	}	
	
	/**
	 * @param string $fieldWithAlias
	 * @param null $val 
	 */		
	public function setEqualCondWithValIsNull($fieldWithAlias)
	{
		$this->filter[] = sprintf(' %1$s is null ', $fieldWithAlias);
	}	
	
	/**
	 * @param string $fieldWithAlias
	 * @param null $val 
	 */			
	public function getEqualCondWithValIsNull($fieldWithAlias)
	{
		return sprintf(' %1$s is null ', $fieldWithAlias);
	}		
	
	/**
	 * @param string $fieldWithAlias
	 * @param int $val  = 0 
	 */		
	public function setEqualCondWithValIsZero($fieldWithAlias)
	{
		$this->filter[] = sprintf(' ifnull(%1$s, 0) = 0 ', $fieldWithAlias);
	}	
	
	/**
	 * @param string $fieldWithAlias
	 * @param int $val  = 0
	 */			
	public function getEqualCondWithValIsZero($fieldWithAlias)
	{
		return sprintf(' ifnull(%1$s, 0) = 0 ', $fieldWithAlias);
	}		
	
	/**
	 * @param string $fieldWithAlias
	 * @param int $val <number>
	 */
	public function setEqualCondWithFieldPossiveEqualZero($fieldWithAlias, $val)
	{
		if($val)
		$this->filter[] = sprintf(' ((ifnull(%1$s, 0) = 0) or (ifnull(%1$s, 0) = %2$d)) '
			, $fieldWithAlias, $val);
	}	
	
	/**
	 * @param string $fieldWithAlias
	 * @param int $val <number>
	 */
	public function getEqualCondWithFieldPossiveEqualZero($fieldWithAlias, $val)
	{
		return ($val)?sprintf(' ((ifnull(%1$s, 0) = 0) or (ifnull(%1$s, 0) = %2$d)) '
			, $fieldWithAlias, $val):'';
	}	
	
	/**
	 * @param string $fieldWithAlias
	 * @param array $valArr <array(int)>
	 */
	public function setEqualCondWithValIsArr($fieldWithAlias, $valArr)
	{
		if(count($valArr))
		$this->filter[] = sprintf(' %1$s in (%2$s) ', $fieldWithAlias, implode(', ', $valArr));
	}
	
	/**
	 * @param string $fieldWithAlias
	 * @param array $valArr <array(int)>
	 */	
	public function getEqualCondWithValIsArrStr($fieldWithAlias, $valArr)
	{
		return (count($valArr))?
		sprintf(' %1$s in (%2$s) ', $fieldWithAlias, implode(', ', $valArr)):'';
	}	
	
	/**
	 * @param string $fieldWithAlias
	 * @param date $start <YYYY-mm-dd>
	 * @param date $end <YYYY-mm-dd>
	 */
	public function setDateRangeCond($fieldWithAlias, $start, $end) 
	{
		if($start && $end)
		$this->filter[] = sprintf(' %1$s between %2$s and %3$s '
			, $fieldWithAlias
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end));
	}
	
	/**
	 * @param string $fieldWithAlias
	 * @param date $start <YYYY-mm-dd>
	 * @param date $end <YYYY-mm-dd>
	 */
	public function getDateRangeCondStr($fieldWithAlias, $start, $end) 
	{
		return ($start && $end)?sprintf(' %1$s between %2$s and %3$s '
			, $fieldWithAlias
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end)):'';
	}
	
	/**
	 * 
	 * @param string $startDateField with alias
	 * @param string $endDateField with alias
	 * @param date $start <YYYY-mm-dd>
	 * @param date $end <YYYY-mm-dd>
	 */
	public function setInDateRangeCond($startDateField, $endDateField, $start, $end)
	{
		if($start && $end)
		{
			$this->filter[] = sprintf(' (%4$s >= %1$s AND %3$s <= %2$s )'
				, $startDateField
				, $endDateField
				, $this->_o_DB->quote($start)
				, $this->_o_DB->quote($end));
		}
	}
	
	/**
	 * 
	 * @param string $startDateField with alias
	 * @param string $endDateField with alias
	 * @param date $start <YYYY-mm-dd>
	 * @param date $end <YYYY-mm-dd>
	 * @return string filter sql
	 */
	public function getInDateRangeCond($startDateField, $endDateField, $start, $end)
	{
		return  ($start && $end)?sprintf(' (%4$s >= %1$s AND %3$s <= %2$s )'
			, $startDateField
			, $endDateField
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end)):'';
	}	
	
	/**
	 * @return string condtion
	 */
	public function getCond()
	{
		return implode(' and ', $this->filter);
	}
	
	/**
	 * @return string condtion with "where"
	 */	
	public function getCondWithWhere()
	{
		return count($this->filter)?
		sprintf(' where %1$s ' ,implode(' and ', $this->filter)):'';
	}	
	
	/**
	 * Reset filter
	 */
	public function resetCond()
	{
		$this->filter = array();
	}
	
	/**
	 * @param int $display number of record 
	 * @param int $page page
	 */
	public function setPagination($display, $page)
	{
		$limitTo     = ceil(($page - 1) * $display);
		$this->limit = " limit {$limitTo}, {$display}";		
	}	
	
	/**
	 * @param int $display number of record 
	 * @param int $page page
	 */	
	public function getPaginationStr($display, $page)
	{
		$limitTo     = ceil(($page - 1) * $display);
		return " limit {$limitTo}, {$display}";		
	}	
	
	/**
	 * @param int $display number of record 
	 */
	public function setLimit($display = 100000)
	{
		$this->limit   = " limit {$display}";		
	}		
	
	/**
	 * @return string sql limit
	 */
	public function getLimit()
	{
		return $this->limit;
	}
}