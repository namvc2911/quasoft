<?php

class Qss_Controller
{

	protected $_action;

	protected $html;

	protected $_html;

	protected $_isHtmlRender = true;

	protected $_isLayoutRender = true;

	protected $headScript = array();

	protected $headLink = array();

	protected $headStyle = array();

	protected $content;

	public $_title = 'Quasoft CMMS';

	public $layout;

	public $params;

	/**
	 * Quick access to controller element (view, service  ...)
	 * inside controller, we can use: $this->services->...->..->serviceclassname($param1,...);
	 *
	 */
	public function __construct ()
	{
		$this->params = Qss_Params::getInstance();
		$this->html = new Qss_Html();
	}

	public function init ()
	{

	}

	/**
	 * 
	 * @param string|array $scripts
	 * @return string
	 */
	public function headScript ($scripts = null, $layout = false)
	{
		if ( $scripts )
		{
			if ( !is_array($scripts) )
			{
				$scripts = array($scripts);
			}
			if ( $layout )
			{
				$this->headScript = array_merge($scripts, $this->headScript);
			}
			else
			{
				$this->headScript = array_merge($this->headScript, $scripts);
			}
		}
		$retval = '';
		foreach ($this->headScript as $script)
		{
			$retval .= "<script src='$script' type='text/javascript'></script>\n";
		}
		return $retval;
	}

	/**
	 * 
	 * @param string|array $links
	 * @return string
	 */
	public function headLink ($links = null)
	{
		if ( $links )
		{
			if ( !is_array($links) )
			{
				$links = array($links);
			}
			$this->headLink = array_merge($this->headLink, $links);
		}
		$retval = '';
		foreach ($this->headLink as $link)
		{
			$retval .= "<link rel='stylesheet' href='$link' type='text/css'>\n";
		}
		return $retval;
	}

	/**
	 * 
	 * @param string $style
	 * @return string
	 */
	public function headStyle ($style = null)
	{
		if ( $style )
		{
			$this->headStyle[] = $style;
		}
		$retval = '';
		foreach ($this->headStyle as $style)
		{
			$retval .= "<style>\n";
			$retval .= "$style\n";
			$retval .= "</style>\n";
		}
		return $retval;
	}

	/**
	 * Set action 
	 * @param $action
	 * @return void
	 */
	public function setAction ($action)
	{
		$this->_action = $action;
	}

	/**
	 * Set html
	 *  
	 * @param $action
	 * @return void
	 */
	public function setHtml ($html)
	{
		$this->_html = $html;
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
			case 'views':
				return new Qss_View();
				break;
		}
	}

	/**
	 * No render
	 * @return unknown_type
	 */
	public function setHtmlRender ($render)
	{
		$this->_isHtmlRender = $render;
	}

	/**
	 * No render
	 * @return unknown_type
	 */
	public function setLayoutRender ($render)
	{
		$this->_isLayoutRender = $render;
	}

	/**
	 * 
	 * 
	 * @param array $action
	 * @param $controller
	 * @param $module
	 * @param $params
	 * @return void
	 */
	public function forward ($action, $controller = null, $module = null, array $params = null)
	{
		$baseUrl = $this->params->requests->getBaseURL();
		header('Location: ' . $baseUrl . '/' . $module . '/' . $controller . '/' . $action);
	}

	/**
	 * 
	 * @param $url
	 * @param $options
	 * @return void
	 */
	public function redirect ($url, $options = array())
	{
		$params = '';
		if ( sizeof($options) )
		{
			$params = '?' . implode('/', $options);
		}
		header('Location: ' . $this->params->requests->getBaseUrl() . $url . $params);
	}

	/**
	 * 
	 * 
	 * @return void
	 */
	public function run ()
	{
		/* Generate content */
		if ( !$this->_action )
		{
			$this->_action = 'Index';
		}
		$action = $this->_action . 'Action';
		if ( !method_exists($this, $action) )
		{
			//throw new Qss_Exception('No action name ' . $this->_action);
			header( "Location: /" );
		}
		if ( $this->_isHtmlRender )
		{
			$this->html->setHtml($this->_html);
		}
		ob_start();
		$this->$action();
		$this->content = ob_get_clean();
		if ( $this->_isHtmlRender )
		{
			$this->content = $this->html->generate();
		}
		if ( $this->_isLayoutRender )
		{
			ob_start();
			include_once $this->layout;
			$this->content = ob_get_clean();
		}
		$this->params->responses->setContent($this->content);
		$this->params->responses->send();
	}

}
?>