<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Db_MySQL extends Qss_Db_Abstract
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
		$this->open();
	}
	
	public function open()
	{
		$host = $this->_config['host'];
		$username = $this->_config['username'];
		$password = $this->_config['password'];
		$database = $this->_config['database'];
		$persistent = $this->_config['persistent'];

		/* Create the connection with server */
		if ( $persistent )
		$this->_link = mysql_pconnect($host, $username, $password);
		else
		$this->_link = mysql_connect($host, $username, $password);

		if ( !$this->_link )
		throw new Exception(mysql_error());
		//mysql_set_charset('utf8', $this->_link);
		/* Connect to the database */
		$db = mysql_select_db($database, $this->_link);
		if ( !$db )
		{
			throw new Exception(mysql_error($this->_link));
		}
		//mysql_query("SET AUTOCOMMIT=1", $this->_link);	
	}
	
	public function escape ($string)
	{
		return mysql_real_escape_string($string, $this->_link);
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
		return mysql_insert_id($this->_link);
	}

	public function execute ($sql)
	{
		$t = time();
		$this->_resource = mysql_query($sql, $this->_link);
		$t1 = time();
		//echo ($t1-$t).': '.$sql.'<hr>';
		if ( !$this->_resource )
		throw new Exception('Error with the query: ' . $sql . '(' . mysql_error($this->_link) . ')');
		return $this->insertedId();
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
		$this->execute($sql);
		$row = mysql_fetch_assoc($this->_resource);
		if ( !$arrayMode )
		{
			$row = Qss_Util::arrayToObject($row);
		}
		return $row;
	}

	/**
	 *
	 * @param $select
	 * @param $arrayMode
	 * @return array|object
	 */
	public function fetchAll ($select)
	{
		$sql = $this->_sql($select);
		$this->execute($sql);
		$rows = array();
		while ( $row = mysql_fetch_object($this->_resource) )
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
			mysql_free_result($this->_resource);
		}
		mysql_close($this->_link);
	}
	public function beginTransaction()
	{
		mysql_query("SET AUTOCOMMIT=0", $this->_link);
		mysql_query("START TRANSACTION", $this->_link);	
	}
	public function commit()
	{
		mysql_query("COMMIT", $this->_link);
	}
	public function rollback()
	{
		mysql_query("ROLLBACK", $this->_link);
	}
	public function isOpen()
	{
		return @mysql_ping($this->_link);
	}
	public function tableExists($table)
	{
		if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$table."'",$this->_link))==1) 
		{
			return true;
		}
		return false;
	}
	public function fieldExists($table,$field)
	{
		if(mysql_num_rows(mysql_query("SELECT * 
FROM information_schema.COLUMNS 
WHERE 
    TABLE_SCHEMA = '{$this->_config['database']}' 
AND TABLE_NAME = '{$table}' 
AND COLUMN_NAME = '{$field}'",$this->_link))==1) 
		{
			return true;
		}
		return false;
	}
}
?>
