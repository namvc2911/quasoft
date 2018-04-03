<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Db_MSSQL extends Qss_Db_Abstract
{

	/**
	 *
	 * @var resource
	 */
	protected $_link;

	protected $_config;
	
	/**
	 *
	 * @var resource
	 */
	protected $_resource;

	public function __construct ($config)
	{
		$this->_config = $config;
		//$this->open();
	}

	public function open()
	{
		$host = $this->_config['host'];
		$username = $this->_config['username'];
		$password = $this->_config['password'];
		$database = $this->_config['database'];
		$persistent = $this->_config['persistent'];
		$connectionInfo = array( "Database"=>$database, "UID"=>$username, "PWD"=>$password,"CharacterSet" => "UTF-8");
		/* Create the connection with server */
		if ( $persistent )
		$this->_link = sqlsrv_connect($host, $connectionInfo);
		else
		$this->_link = sqlsrv_connect($host, $connectionInfo);

		if ( !$this->_link )
		{
			throw new Exception(print_r(sqlsrv_errors()));
		}
		//mysqli_set_charset('utf8', $this->_link);
		/* Connect to the database */
		//$db = mssql_select_db($this->_link, $database);
		//if ( !$db )
		//{
			//throw new Exception(sqlsrv_errors());
		//}
		//mysqli_query("SET AUTOCOMMIT=1", $this->_link);
	}
	
	public function escape ($string)
	{
		 if(is_numeric($string))
	        return $data;
	    $unpacked = unpack('H*hex', $string);
	    return '0x' . $unpacked['hex'];
	}

	public function insert ($table, $data)
	{
		$keys = '';
		$values = '';
		foreach ($data as $key => $value)
		{
			$keys .= '`' . $key . '`,';
			if ( $value === null )
			$values .= 'NULL, ';
			else
			$values .= '\'' . $this->escapse($value) . '\',';
		}
		$sql = 'INSERT INTO `' . $table . '` (' . trim($keys, ',') . ') VALUES (' . trim($values, ',') . ')';
		return $this->execute($sql);
	}

	public function insertedId ()
	{
		return sqlsrv_get_field($this->_resource,0);
	}

	public function execute ($sql)
	{
		$stm = sqlsrv_prepare($this->_link,$sql);
		$ret = sqlsrv_execute($stm);
		if ( $ret === false )
		{
			throw new Exception('Error with the query: ' . $sql . '(' . print_r(sqlsrv_errors()) . ')');
		}
		return $ret;
	}

	public function update ($table, $data, $where)
	{
		$sql = 'UPDATE `' . $table . '` SET ';
		foreach ($data as $key => $value)
		{
			if ( $value === NULL )
			$sql .= '`' . $key . '`=NULL,';
			else
			$sql .= '`' . $key . '`=\'' . $this->escape($value) . '\',';
		}
		$sql = rtrim($sql, ',');
		$sql .= ' WHERE ' . $where;
		return $this->execute($sql);
	}

	public function delete ($table, $where)
	{
		$sql = 'DELETE FROM `' . $table . '` WHERE ' . $where;
		return $this->execute($sql);
	}

	/**
	 *
	 * @param $select
	 * @param $arrayMode
	 * @return array|object
	 */
	public function fetchOne ($select, $arrayMode = false)
	{
		$sql = $this->_sql($select);
		$this->_resource = sqlsrv_query($this->_link,$sql);
		if ( !$this->_resource )
		{
			throw new Exception('Error with the query: ' . $sql . '(' . print_r(sqlsrv_errors()) . ')');
		}
		$row = sqlsrv_fetch_object($this->_resource);
		return $row;
	}

	/**
	 *
	 * @param $select
	 * @param $arrayMode
	 * @return array|object
	 */
	public function fetchAll ($select,$params = array())
	{
		$sql = $this->_sql($select);
		$this->_resource = sqlsrv_query($this->_link,$sql,$params);
		if ( !$this->_resource )
		{
			throw new Exception('Error with the query: ' . $sql . '(' . print_r(sqlsrv_errors()) . ')');
		}
		$rows = array();
		while ( $row = sqlsrv_fetch_object($this->_resource) )
		{
			$rows[] = $row;
		}
		/*if ( !$arrayMode )
		{
			$rows = Qss_Util::arrayToObject($rows);
		}*/
		return $rows ? $rows : array();
	}

	protected function _sql ($select)
	{
		return $select;
	}
	public function close ()
	{
		if(!is_bool($this->_resource) && is_resource($this->_resource))
		{
			sqlsrv_free_stmt($this->_resource);
		}
		if($this->_link)
		{
			sqlsrv_close($this->_link);
		}
	}
	public function beginTransaction()
	{
		sqlsrv_begin_transaction($this->_link);
	}
	public function commit()
	{
		sqlsrv_commit($this->_link);
	}
	public function rollback()
	{
		sqlsrv_rollback($this->_link);
	}
	public function isOpen()
	{
		return @sqlsrv_ping($this->_link);
	}
}
?>
