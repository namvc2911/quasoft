<?php
class Qss_Model_IReport_Abstract extends Qss_Model_Abstract
{
	protected $_params;
	
	protected $_orderby;
	/**
	 * Build constructor
	 *
	 * @return void
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->_params = array();
		$this->_orderby = array();
	}
	public function getSQL($sql)
	{
		if(count($this->_orderby))
		{
			$sql .= sprintf(' order by %1$s',implode(',',$this->_orderby));
		}
		$sql .= sprintf(' limit 10000 ');
		return $sql;
	}
	public function setParams($params)
	{
		$this->_params = $params;
	}
	public function setOrder($name)
	{
		$this->_orderby[] = $name;
	}
    
    /**
     * 
     * @param string $filterCode name của filter vd: equip, location
     * @param string $column tên cột cần lọc
     * @param string $alias alias của bảng chứa cột cần lọc
     * @return string query filter
     */
	public function getNormalFilter($filterCode, $column, $alias = '')
	{
        $val   = $this->_params[$filterCode];
        $alias = $alias?$alias.'.':'';
		return $val?sprintf(' and %1$s%2$s = %3$s ', $alias, $column, $this->_o_DB->quote($val)):'';
	}
	public function getDateRangeFilter($column ,$startdate = 'startdate',$enddate = 'enddate', $alias = '')
	{
		$start = @$this->_params[$startdate];
        $end   = @$this->_params[$enddate];
        
        if($start && $end)
        {
            if(Qss_Lib_Date::compareTwoDate($end, $start) == -1)
            {
                return sprintf(' and 1 = 0 ');
            }
            else
            {
                $alias = $alias?$alias.'.':'';
                $start = Qss_Lib_Date::displaytomysql($start);
                $end   = Qss_Lib_Date::displaytomysql($end);
                return sprintf(' and (%1$s%2$s between %3$s and %4$s) ', $alias, $column, $this->_o_DB->quote($start), $this->_o_DB->quote($end));
            }            
        }
        return '';
	}
	public function getLocationFilter($name,$alias = '')
	{
		$retval = '';
		$alias = $alias?($alias.'.'):$alias;
		$makhuvuc = $this->_params[$name];
		if($makhuvuc)
		{
			$sql = sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $makhuvuc);
			$location    = $this->_o_DB->fetchOne($sql);
			if($location)
			{
				$retval = sprintf(' and %3$slft >= %1$d and  %3$srgt <= %2$d', 
						$location->lft, 
						$location->rgt,
						$alias);
			}
		}
		return $retval;
	}
	public function getTypeFilter($name,$alias = '')
	{
		$retval = '';
		$alias = $alias?($alias.'.'):$alias;
		$maloai = $this->_params[$name];
		if($maloai)
		{
			$sql = sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $maloai);
			$type    = $this->_o_DB->fetchOne($sql);
			if($type)
			{
				$retval = sprintf(' and %3$slft >= %1$d and  %3$srgt <= %2$d', 
						$type->lft, 
						$type->rgt,
						$alias);
			}
		}
		return $retval;
	}
}
?>