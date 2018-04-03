<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Db_MySQLI extends Qss_Db_Abstract
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
		try 
		{
			$this->open();
		}
		catch(Exception $e)
		{
			
		}
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
		$this->_link = mysqli_connect('p:'.$host, $username, $password);
		else
		$this->_link = mysqli_connect($host, $username, $password);

		if ( !$this->_link )
		die(mysqli_error($this->_link));
		mysqli_set_charset( $this->_link,'utf8');
		/* Connect to the database */
		$db = mysqli_select_db($this->_link, $database);
		if ( !$db )
		{
			throw new Exception(mysqli_error($this->_link));
		}
		//mysqli_query("SET AUTOCOMMIT=1", $this->_link);
	}
	
	public function escape ($string)
	{
		return mysqli_real_escape_string($this->_link,$string);
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
		return mysqli_insert_id($this->_link);
	}

	public function execute ($sql)
	{
		//file_put_contents('D:\logs.txt', $sql."\n\r\n\r\n\r\n\r\n\r",FILE_APPEND);
		$this->_resource = mysqli_query($this->_link, $sql);
		if ( !$this->_resource )
		die('Error with the query: ' . $sql . '(' . mysqli_error($this->_link) . ')');
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
		$row = mysqli_fetch_assoc($this->_resource);
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
		while ( $row = mysqli_fetch_object($this->_resource) )
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
			mysqli_free_result($this->_resource);
		}
		mysqli_close($this->_link);
	}
	public function beginTransaction()
	{
		mysqli_query($this->_link,"SET AUTOCOMMIT=0");
		mysqli_query($this->_link,"START TRANSACTION");	
	}
	public function commit()
	{
		mysqli_query($this->_link,"COMMIT");
	}
	public function rollback()
	{
		mysqli_query($this->_link,"ROLLBACK");
	}
	public function isOpen()
	{
		return @mysqli_ping($this->_link);
	}
	public function tableExists($table)
	{
		if(mysqli_query($this->_link,"SHOW TABLES LIKE '".$table."'")->num_rows>=1) 
		{
			return true;
		}
		return false;
	}
	
	public function fieldExists($table,$field)
	{
		if(mysqli_query($this->_link,"SELECT * 
FROM information_schema.COLUMNS 
WHERE 
    TABLE_SCHEMA = '{$this->_config['database']}'
AND TABLE_NAME = '{$table}' 
AND COLUMN_NAME = '{$field}'")->num_rows>=1) 
		{
			return true;
		}
		return false;
	}
}
?>
