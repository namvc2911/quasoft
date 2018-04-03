<?php
/**
 * 
 * @author HuyBD
 *
 */
class Qss_Html
{

	protected $_params;

	protected $_html;
	
	protected $_language;
	
	public $_path;

	/**
	 * 
	 * @return void
	 */
	public function __construct ()
	{
		$this->_params = array();
	}

	/**
	 * Set html
	 * 
	 * @param $html
	 * @return void
	 */
	public function setHtml ($html)
	{
		$this->_html = $html;
		if(!count($this->_language))
		{
			$lang = Qss_Translation::getInstance()->getLanguage();
			if($this->_path)
			{
				$path = str_ireplace('.phtml', '_'.$lang.'.ini', $this->_path);
			}
			else
			{
				$path = str_ireplace('.phtml', '_'.$lang.'.ini', $this->_html);
			}
			$this->_language = Qss_Translation::getInstance()->getTranslation($path);
		}
	}

	/**
	 * Generate html content
	 * 
	 * @return html content
	 */
	public function generate ()
	{
		ob_start();
		if ( !include $this->_html )
		{
			throw new Qss_Exception('File not found ' . $this->_html);
		}
		return ob_get_clean();
	}

	/**
	 * 
	 * @param $name
	 * @param $value
	 * @return unknown_type
	 */
	public function __set ($name, $value)
	{
		$this->_params[$name] = $value;
	}

	/**
	 * 
	 * @param $name
	 * @return unknown_type
	 */
	public function __get ($name)
	{
		if ( $name == 'views' )
		{
			return new Qss_View();
		}
		else
		{
			return @$this->_params[$name];
		}
	}
	public function __call($name, $agrs)
	{
		if($name == '_translate')
		{
			return isset($this->_language[$agrs[0]])?$this->_language[$agrs[0]]:"UNKOWN_".$agrs[0];
		}
	}
	/**
	 * 
	 * @param $name
	 * @return boolean
	 */
	public function __isset ($name)
	{
		return isset($this->_params[$name]);
	
	}
}
?>