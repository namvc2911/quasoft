<?php
class Qss_Model_Query extends Qss_Model_Abstract
{
    var $bMain;
    protected $szName;
    protected $arrChilds;
    protected $arrRecords;
    protected $arrData;
    protected $arrParam;
    protected $szSQL;
    
    function __construct()
    {
        parent::__construct();
		$this->arrParam = array();
		$this->arrChilds = array();
		$this->sysTime();
		$this->sysDate();
		$this->sysUserData();
    }
    function init($sql)
    {
        $this->szSQL = $sql;
    }
    function addParam($params = array())
    {
        $this->arrParam = array_merge($this->arrParam,$params);    
    }
	function getParam()
	{
		return $this->arrParam;
	}
    function getRecords()
    {
        $ret = array();
		$template = new Qss_Lib_Template();
		$sql = $template->parseContent($this->szSQL,$this->arrParam);
        $dataSQL = $this->_o_DB->fetchAll($sql);
        foreach($dataSQL as $data)
        {
			$arrRec = array();
			foreach($data as $key=>$val)
			{
				if(!is_numeric($key))
				{
					$arrRec[$this->getName().":".$key] = $val;
				}
			}
			$ret[] = $arrRec;
        }
		if($this->bMain && sizeof($ret))
        	return $ret[0];
		else
			return $ret;
    }    
    function getName()
    {
        return $this->szName;
    }
    function setName($name)
    {
        $this->szName = $name;
    }
    function setChild($child)
    {
        $this->arrChilds[] = $child;
    }
    function getChild()
    {
        return $this->arrChilds;
    }
//-----------------------------------------------------------------------	
	 /**
	 * Add sys param
     *'
     * @access  public
     */	
	private function sysTime()
	{
	 	$this->arrParam['sys:Time'] = date('h:i');
	}	
//-----------------------------------------------------------------------	
	 /**
	 * Add sys param
     *'
     * @access  public
     */	
	private function sysDate()
	{
	 	$this->arrParam['sys:Date'] = date('d-m-Y');
	}		
//-----------------------------------------------------------------------	
	 /**
	 * Add user param
     *'
     * @access  public
     */	
	private function sysUserData()
	{
		$user = Qss_Register::get('userinfo');
	 	$this->arrParam['sys:UID'] = $user->user_id;
		$this->arrParam['sys:UserID'] = $user->user_name;
		$this->arrParam['sys:DepartmentID'] = $user->user_dept_id;
	}
	public function getViewData($module,$object,$deptid=0,$order = null)
	{
		$sql = sprintf('select * from %1$s limit 100',$object);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getViewByField($module, $object ,$where , $deptid=0,$order = array())
	{
		$sql = sprintf('select * from %1$s where',$object);
		$tmp = '';
		foreach ($where as $key=>$number)
		{
			if($number)
			{
				$sql .= $tmp?sprintf(' AND  %1$s = %2$d', $key, $number):sprintf(' %1$s = %2$d', $key, $number);
			}
			else 
			{
				$sql .= $tmp?sprintf(' AND  %1$s = 0 or %1$s is null', $key):sprintf(' %1$s = 0 or %1$s is null', $key);
			}
		}
		if(count($order))
		{
			$sql .= ' ORDER BY ';
			$tmp  = '';
			foreach ($order as $field=>$sortType)
			{
				$sql .= ($tmp)?' , '.$field.' '.$sortType: $field. ' ' . $sortType;
			}
		}
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getRecordByField($module, $object ,$where , $deptid=0)
	{
		$sql = sprintf('select * from %1$s where',$object);
		$tmp = '';
		foreach ($where as $key=>$number)
		{
			if($number)
			{
				$sql .= $tmp?sprintf(' AND  %1$s = %2$d', $key, $number):sprintf(' %1$s = %2$d', $key, $number);
			}
			else 
			{
				$sql .= $tmp?sprintf(' AND  %1$s = 0 or %1$s is null', $key):sprintf(' %1$s = 0 or %1$s is null', $key);
			}
		}
		return $this->_o_DB->fetchOne($sql);
	}
}