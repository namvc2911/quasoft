<?php

class Qss_Request
{

	protected static $_instance;

	/**
	 * Scheme for http
	 *
	 */
	const SCHEME_HTTP = 'http';

	/**
	 * Scheme for https
	 *
	 */
	const SCHEME_HTTPS = 'https';

	/**
	 * The parameters' holder
	 * @var array
	 */
	protected $_params = array();

	/**
	 * Base URL of request
	 * @var string
	 */
	protected $_baseUrl = null;

	/**
	 * Base Path of request
	 * @var string
	 */
	protected $_basePath;
	
	
	protected $_requestUri;

	/**
	 * Construction: init the request params list
	 * 
	 * @param array $params
	 */
	public function __construct ($params = array())
	{
		/* Have to use array as param of request construction */
		$this->_params = array_merge($_GET, $_POST);
		foreach ($this->_params as $k=>$v) 
		{
			if((ctype_digit($v) && substr($v,0,1) !== '0' && $v == (int)$v) || $v === '0')//không chơi với bigint
			{
				$this->_params[$k] = (int)$v;
			}
		}
	}

	/**
	 * 
	 * @return Qss_Request
	 */
	public static function getInstance ()
	{
		if ( null === self::$_instance )
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Get parameter of the request
	 * 
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getParam ($key, $default = null)
	{
		return $this->hasParam($key) ? $this->_params[$key] : $default;
	}

	/**
	 * Get all parameters of this request
	 * 
	 * @return array
	 */
	public function getParams ()
	{
		return $this->_params;
	}

	/**
	 * Check that parameter is provided by the key
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function hasParam ($key)
	{
		return is_string($key) && array_key_exists($key, $this->_params);
	}

	/**
	 * Set the parameter to this request
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function setParam ($key, $value = null)
	{
		$this->_params[$key] = $value;
	}

	/**
	 * Set a batch of params 
	 * 
	 * @param array $values
	 */
	public function setParams ($values)
	{
		$this->_params = array_merge($this->_params, $values);
	}

	/**
	 * Check that the request is xml http request or not
	 * 
	 * @return bool
	 */
	public function isAjax ()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
	}

	/**
	 * Check that the request is cli call
	 * 
	 * @return bool
	 */
	public function isCli ()
	{
		return 0 == strncasecmp(PHP_SAPI, 'cli', 3);
	}

	/**
	 * Set the REQUEST_URI on which the instance operates
	 *
	 * If no request URI is passed, uses the value in $_SERVER['REQUEST_URI'],
	 * $_SERVER['HTTP_X_REWRITE_URL'], or $_SERVER['ORIG_PATH_INFO'] + $_SERVER['QUERY_STRING'].
	 *
	 * @param string $requestUri
	 * @return Zend_Controller_Request_Http
	 */
	public function setRequestUri ($requestUri = null)
	{
		if ( $requestUri === null )
		{
			if ( isset($_SERVER['HTTP_X_REWRITE_URL']) )
			{ // check this first so IIS will catch
				$requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
			}
			elseif ( isset($_SERVER['REQUEST_URI']) )
			{
				$requestUri = $_SERVER['REQUEST_URI'];
				// Http proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
				$schemeAndHttpHost = $this->getScheme() . '://' . $this->getHttpHost();
				if ( strpos($requestUri, $schemeAndHttpHost) === 0 )
				{
					$requestUri = substr($requestUri, strlen($schemeAndHttpHost));
				}
			}
			elseif ( isset($_SERVER['ORIG_PATH_INFO']) )
			{ // IIS 5.0, PHP as CGI
				$requestUri = $_SERVER['ORIG_PATH_INFO'];
				if ( !empty($_SERVER['QUERY_STRING']) )
				{
					$requestUri .= '?' . $_SERVER['QUERY_STRING'];
				}
			}
			else
			{
				return $this;
			}
		}
		elseif ( !is_string($requestUri) )
		{
			return $this;
		}
		else
		{
			// Set GET items, if available
			if ( false !== ($pos = strpos($requestUri, '?')) )
			{
				// Get key => value pairs and set $_GET
				$query = substr($requestUri, $pos + 1);
				parse_str($query, $vars);
				$this->setQuery($vars);
			}
		}
		
		$this->_requestUri = $requestUri;
		return $this;
	}

	/**
	 * Returns the REQUEST_URI taking into account
	 * platform differences between Apache and IIS
	 *
	 * @return string
	 */
	public function getRequestUri ()
	{
		if ( empty($this->_requestUri) )
		{
			$this->setRequestUri();
		}
		
		return $this->_requestUri;
	}

	/**
	 * Set the base URL of the request; i.e., the segment leading to the script name
	 *
	 * E.g.:
	 * - /admin
	 * - /myapp
	 * - /subdir/index.php
	 *
	 * Do not use the full URI when providing the base. The following are
	 * examples of what not to use:
	 * - http://example.com/admin (should be just /admin)
	 * - http://example.com/subdir/index.php (should be just /subdir/index.php)
	 *
	 * If no $baseUrl is provided, attempts to determine the base URL from the
	 * environment, using SCRIPT_FILENAME, SCRIPT_NAME, PHP_SELF, and
	 * ORIG_SCRIPT_NAME in its determination.
	 *
	 * @param mixed $baseUrl
	 * @return Zend_Controller_Request_Http
	 */
	public function setBaseUrl ($baseUrl = null)
	{
		if ( (null !== $baseUrl) && !is_string($baseUrl) )
		{
			return $this;
		}
		
		if ( $baseUrl === null )
		{
			$filename = (isset($_SERVER['SCRIPT_FILENAME'])) ? basename($_SERVER['SCRIPT_FILENAME']) : '';
			
			if ( isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === $filename )
			{
				$baseUrl = $_SERVER['SCRIPT_NAME'];
			}
			elseif ( isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) === $filename )
			{
				$baseUrl = $_SERVER['PHP_SELF'];
			}
			elseif ( isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename )
			{
				$baseUrl = $_SERVER['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
			}
			else
			{
				// Backtrack up the script_filename to find the portion matching
				// php_self
				$path = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
				$file = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
				$segs = explode('/', trim($file, '/'));
				$segs = array_reverse($segs);
				$index = 0;
				$last = count($segs);
				$baseUrl = '';
				do
				{
					$seg = $segs[$index];
					$baseUrl = '/' . $seg . $baseUrl;
					++ $index;
				}
				while ( ($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos) );
			}
			
			// Does the baseUrl have anything in common with the request_uri?
			$requestUri = $this->getRequestUri();
			
			if ( 0 === strpos($requestUri, $baseUrl) )
			{
				// full $baseUrl matches
				$this->_baseUrl = $baseUrl;
				return $this;
			}
			
			if ( 0 === strpos($requestUri, dirname($baseUrl)) )
			{
				// directory portion of $baseUrl matches
				$this->_baseUrl = rtrim(dirname($baseUrl), '/');
				return $this;
			}
			
			if ( !strpos($requestUri, basename($baseUrl)) )
			{
				// no match whatsoever; set it blank
				$this->_baseUrl = '';
				return $this;
			}
			
			// If using mod_rewrite or ISAPI_Rewrite strip the script filename
			// out of baseUrl. $pos !== 0 makes sure it is not matching a value
			// from PATH_INFO or QUERY_STRING
			if ( (strlen($requestUri) >= strlen($baseUrl)) && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0)) )
			{
				$baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
			}
		}
		
		$this->_baseUrl = rtrim($baseUrl, '/');
		return $this;
	}

	/**
	 * Everything in REQUEST_URI before PATH_INFO
	 * <form action="<?=$baseUrl?>/news/submit" method="POST"/>
	 *
	 * @return string
	 */
	public function getBaseUrl ()
	{
		if ( null === $this->_baseUrl )
		{
			$this->setBaseUrl();
		}
		
		return $this->_baseUrl;
	}

	/**
	 * Set the base path for the URL
	 *
	 * @param string|null $basePath
	 * @return Zend_Controller_Request_Http
	 */
	public function setBasePath ($basePath = null)
	{
		if ( $basePath === null )
		{
			$filename = basename($_SERVER['SCRIPT_FILENAME']);
			
			$baseUrl = $this->getBaseUrl();
			if ( empty($baseUrl) )
			{
				$this->_basePath = '';
				return $this;
			}
			
			if ( basename($baseUrl) === $filename )
			{
				$basePath = dirname($baseUrl);
			}
			else
			{
				$basePath = $baseUrl;
			}
		}
		
		if ( substr(PHP_OS, 0, 3) === 'WIN' )
		{
			$basePath = str_replace('\\', '/', $basePath);
		}
		
		$this->_basePath = rtrim($basePath, '/');
		return $this;
	}

	/**
	 * Everything in REQUEST_URI before PATH_INFO not including the filename
	 * <img src="<?=$basePath?>/images/zend.png"/>
	 *
	 * @return string
	 */
	public function getBasePath ()
	{
		if ( null === $this->_basePath )
		{
			$this->setBasePath();
		}
		
		return $this->_basePath;
	}

	/**
	 * Set the PATH_INFO string
	 *
	 * @param string|null $pathInfo
	 * @return Zend_Controller_Request_Http
	 */
	public function setPathInfo ($pathInfo = null)
	{
		if ( $pathInfo === null )
		{
			$baseUrl = $this->getBaseUrl();
			
			if ( null === ($requestUri = $this->getRequestUri()) )
			{
				return $this;
			}
			
			// Remove the query string from REQUEST_URI
			if ( $pos = strpos($requestUri, '?') )
			{
				$requestUri = substr($requestUri, 0, $pos);
			}
			
			if ( (null !== $baseUrl) && (false === ($pathInfo = substr($requestUri, strlen($baseUrl)))) )
			{
				// If substr() returns false then PATH_INFO is set to an empty string
				$pathInfo = '';
			}
			elseif ( null === $baseUrl )
			{
				$pathInfo = $requestUri;
			}
		}
		
		$this->_pathInfo = (string) $pathInfo;
		return $this;
	}

	/**
	 * Returns everything between the BaseUrl and QueryString.
	 * This value is calculated instead of reading PATH_INFO
	 * directly from $_SERVER due to cross-platform differences.
	 *
	 * @return string
	 */
	public function getPathInfo ()
	{
		if ( empty($this->_pathInfo) )
		{
			$this->setPathInfo();
		}
		
		return $this->_pathInfo;
	}

	/**
	 * Get the request URI scheme
	 *
	 * @return string
	 */
	public function getScheme ()
	{
		return ($this->getServer('HTTPS') == 'on') ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
	}

	/**
	 * Retrieve a member of the $_SERVER superglobal
	 *
	 * If no $key is passed, returns the entire $_SERVER array.
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getServer ($key = null, $default = null)
	{
		if ( null === $key )
		{
			return $_SERVER;
		}
		
		return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
	}

	/**
	 * Get the HTTP host.
	 *
	 * "Host" ":" host [ ":" port ] ; Section 3.2.2
	 * Note the HTTP Host header is not the same as the URI host.
	 * It includes the port while the URI host doesn't.
	 *
	 * @return string
	 */
	public function getHttpHost ()
	{
		$host = $this->getServer('HTTP_HOST');
		if ( !empty($host) )
		{
			return $host;
		}
		
		$scheme = $this->getScheme();
		$name = $this->getServer('SERVER_NAME');
		$port = $this->getServer('SERVER_PORT');
		
		if ( ($scheme == self::SCHEME_HTTP && $port == 80) || ($scheme == self::SCHEME_HTTPS && $port == 443) )
		{
			return $name;
		}
		else
		{
			return $name . ':' . $port;
		}
	}
}
?>