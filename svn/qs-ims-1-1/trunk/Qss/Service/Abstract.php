<?php
/**
 * Abstract class for service
 *
 * @author HuyBD
 *
 */
abstract class Qss_Service_Abstract
{

	//const for export type json
	const TYPE_JSON = 0;

	//const for export type html
	const TYPE_HTML = 1;

	//const for export type text
	const TYPE_TEXT = 2;

	protected $_data;

	protected $_message = array();

	protected $_status;
	
	protected $_language;
	
	static $_redirect;

	/**
	 *
	 * @var error
	 */
	protected $_error = false;

	public function __construct ()
	{

	}

	/**
	 *
	 * @param $type
	 * @return string
	 */
	public function mergeMessage ($type = self::TYPE_JSON)
	{
		if ( $type == self::TYPE_TEXT)
		{
			$retval = implode("\n", $this->_message);
		}
		else
		{
			$retval = implode("<br/>", $this->_message);
		}
		return $retval;
	}

	/**
	 *
	 * @param $type
	 * @return message
	 */
	public function getMessage ($type = self::TYPE_JSON)
	{
		/* Check the export type mode */
		switch ( $type )
		{
			/* Json */
			case (self::TYPE_JSON):
				/* Merge message , and then */
				/* Encode this message collection as json reponse */
				$data = array();
				$data['message'] = $this->mergeMessage($type);
				$data['error'] = $this->_error;
				$data['status'] = $this->_status;
				$data['redirect'] = self::$_redirect;
				return Qss_Json::encode($data);
				break;
				/* Html */
			case (self::TYPE_HTML):
				/* Merge message, and then */
				/* Return the message */
				return $this->mergeMessage($type);
				break;
				/* Text */
			case (self::TYPE_TEXT):
				/* Merge message as text */
				return $this->mergeMessage($type);
				break;
				/* Other case */
			default:
				/* Return message merged */
				return $this->mergeMessage($type);
		}
		/* Error case */
		/* Return nothing */
		return '';
	}

	/**
	 * Add message
	 *
	 * @param $message
	 * @return void
	 */
	public function setMessage ($message)
	{
		if($message)
		{
			$this->_message[] = $message;
		}
	}

	/**
	 * Get status
	 *
	 * @return status
	 */
	public function getStatus ()
	{
		return @$this->_status;
	}

	/**
	 *
	 * @param $status
	 * @return void
	 */
	public function setStatus ($status)
	{
		$this->_status = $status;
	}

	/**
	 *
	 * @return return of __doExecute function
	 */
	public function getData ()
	{
		return $this->_data;
	}

	/**
	 * Quick access to controller element (view, service  ...)
	 * inside controller, we can use: $this->services->...->..->serviceclassname($param1,...);
	 *
	 */
	public function __get ($name)
	{
		switch ( $name )
		{
			case 'services':
				return new Qss_Service();
				break;
		}
	}

	/**
	 * call service
	 *
	 * @param $agrs
	 * @return this
	 */
	final public function run ($agrs, $directories, $name)
	{
		$iniFile = '';
		if(isset(Qss_Register::get('configs')->service->path_ext))
		{
			$iniFile = Qss_Register::get('configs')->service->path_ext . implode('/', $directories) . '/' . $name . '.ini';
		}
		if(!file_exists($iniFile))
		{
			$iniFile = Qss_Register::get('configs')->service->path . implode('/', $directories) . '/' . $name . '.ini';
		}
		//load translation
		$path = '';
		$lang = Qss_Translation::getInstance()->getLanguage();
		if(isset(Qss_Register::get('configs')->service->path_ext))
		{
			$path = Qss_Register::get('configs')->service->path_ext . implode('/', $directories) . '/' . $name . '_' . $lang.  '.ini';
		}
		if(!file_exists($path))
		{
			$path = Qss_Register::get('configs')->service->path . implode('/', $directories) . '/' . $name . '_' . $lang. '.ini';
		}
		$this->_language = Qss_Translation::getInstance()->getTranslation($path);
		/* Validate params of _doExecute function */
		if ( file_exists($iniFile) )
		{
			$reflection = new ReflectionMethod($this, '__doExecute');
			$config = Qss_Config::loadIniFile($iniFile);
			$params = array();
			$i = 0;
			foreach ($reflection->getParameters() as $param)
			{
				$params[$param->name] = @$agrs[$i];
				$i ++;
			}
			$this->validate($params, $config);
		}
		if ( !$this->isError() )
		{
			$this->_data = call_user_func_array(array($this, '__doExecute'), $agrs);
		}
		return $this;
	}

	/**
	 *
	 * @return void
	 */
	public function setError ($error = true)
	{
		$this->_error = $error;
	}

	/**
	 *
	 * @return boolean
	 */
	public function isError ()
	{
		return $this->_error;
	}

	/**
	 * Validate params
	 *
	 * @param $params
	 * @param $config
	 * @return void
	 */

	private function validate ($params, $configs)
	{
		foreach ($configs as $key => $config)
		{
			if ( in_array($key, array_keys($params)) )
			{
				if ( $this->isMultiLevel($config) )
				{
					$this->check($config, $params[$key]);
				}
				else
				{
					$validate = new Qss_Validation($config);
					if ( !$validate->isValid($params[$key], $message) )
					{
						if(is_numeric($message))
						{
							$message = $this->_translate($message);
						}
						$this->setMessage($message);
						$this->setError();
					}
				}
			}
		}
	}

	/**
	 *
	 * @param array $config
	 * @param $value
	 * @return boolean
	 */
	private function check (array $configs, $value)
	{
		$i = 0;
		foreach ($configs as $key => $config)
		{
			if ( $this->isMultiLevel($config) )
			{
				$this->check($config, $value[$key]);
				$i ++;
			}
			else
			{
				$validate = new Qss_Validation($config);
				if ( !$validate->isValid($value[$key], $message) )
				{
					if(is_numeric($message))
					{
						$message = $this->_translate($message);
					}
					$this->setMessage($message);
					$this->setError();
				}
			}
		}
	}

	/**
	 * Check whether array is multi level or not
	 *
	 * @param $array
	 * @return boolean
	 */
	private function isMultiLevel ($array)
	{
		foreach ($array as $item)
		{
			if ( !is_array($item) )
			{
				return false;
			}
		}
		return true;
	}
	public function __call($name, $agrs)
	{
		if($name == '_translate')
		{
			return isset($this->_language[$agrs[0]])?$this->_language[$agrs[0]]:"UNKOWN_".$agrs[0];
		}
	}
	public  function reset()
	{
		$this->_error = false;
		$this->_message = array();
		$this->_redirect = null;
	}
	public  function setRedirect($url)
	{
		self::$_redirect = $url;
	}
}
?>