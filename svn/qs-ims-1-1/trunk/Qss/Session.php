<?php
/**
 * 
 * @author HuyBD
 *
 */
class Qss_Session
{

	protected static $_params;

	/* Session handler */
	protected $_handler = null;

	/* Session name, reverse of PHPSESSID */
	protected $_name = 'DISSESPHP';

	/* Defautl session lifetime is 2 hours */
	protected $_lifetime = 7200;

	/* Session has been started or not */
	protected $_started = null;

	public function __construct ()
	{
		if ( !is_array(self::$_params) )
		{
			session_start();
			global $_SESSION;
			self::$_params = $_SESSION;
		}
	}

	/**
	 * 
	 * @param $name
	 * @param $default
	 * @return unknown_type
	 */
	public static function get ($name, $default = null)
	{
		if ( key_exists($name, self::$_params) )
		{
			return self::$_params[$name];
		}
		
		return $default;
	}

	/**
	 * 
	 * @param $name
	 * @param $value
	 * @return unknown_type
	 */
	public static function set ($name, $value)
	{
		self::$_params[$name] = $value;
		$_SESSION[$name] = $value;
	}

	/**
	 * Start the session with the session (save) handler
	 * and configuration params
	 * 
	 * @author giangsondat@gmail.com
	 * @param null|Session_Handler_Interface $handler
	 * @param null|array $config
	 */
	public static function start (Qss_Session_Handler_Interface $handler = null, $config = null)
	{
		/* Return and do nothing if session is started before */
		if ( self::$_started )
			return;
			
		/* First time of using session start 
		 * without session handler and the php environment has auto start 
		 * do write close to avoid the handler initialization */
		if ( self::$_started === null && $handler === null && defined('SID') )
			session_write_close();
			
		/* Register the session handler */
		if ( $handler )
			session_set_save_handler(array(&$handler, 'open'), array(&$handler, 'close'), array(&$handler, 'read'), array(&$handler, 'write'), array(&$handler, 'destroy'), array(&$handler, 'gc'));
			
		/* Get configuration params */
		if ( $config !== null )
		{
			self::$_name = isset($config['name']) ? $config['name'] : self::$_name;
			self::$_lifetime = isset($config['lifetime']) ? $config['lifetime'] : self::$_lifetime;
		}
		
		/* Set the session name, and start the session */
		session_name(self::$_name);
		session_start();
		self::$_started = TRUE;
	}

	/**
	 * Set|Get|Generate the session id for|of|for the current session
	 * 
	 * @author giangsondat@gmail.com
	 * @param null|string $id
	 * @return string|false
	 */
	public static function id ($id = null)
	{
		if ( $id === null )
			return session_id();
		else
		{
			if ( !$id || !is_string($id) )
				throw new Exception('The id of session must be a string');
			
			if ( headers_sent($file, $line) )
				throw new Exception('Headers sent at file ' . $file . ' line ' . $line);
			
			return session_id($id);
		}
	}

	/**
	 * Re-generate the session id, determine to delete the old session or not
	 * 
	 * @author giangsondat@gmail.com
	 * @param bool $deleteOldSession
	 */
	public static function regenerate ($deleteOldSession = false)
	{
		if ( headers_sent($file, $line) )
			throw new Exception('Headers sent at file ' . $file . ' line ' . $line);
		
		session_regenerate_id($deleteOldSession);
	}

	/**
	 * Write and close the session, after that the user can run Session::start() once again
	 * 
	 * @author giangsondat@gmail.com
	 */
	public static function writeClose ()
	{
		self::$_started = FALSE;
		session_write_close();
	}

	/**
	 * Destroy the session data
	 * 
	 * @author giangsondat@gmail.com
	 */
	public static function destroy ()
	{
		session_destroy();
	}
}
?>