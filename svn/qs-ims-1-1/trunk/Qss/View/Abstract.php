<?php
/**
 * Abstract class for view
 * 
 * @author HuyBD
 *
 */
abstract class Qss_View_Abstract
{

	/**
	 * 
	 * @var Html object
	 */
	protected $html;

	/**
	 * 
	 * @var content file
	 */
	protected $_html;

	/**
	 * Buid constructor
	 * 
	 * @return void
	 */
	public function __construct ()
	{
		$this->html = new Qss_Html();
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
		$this->html->setHtml($this->_html);
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
	 * call view
	 *  
	 * @param $agrs
	 * @return this
	 */
	final public function generate ($agrs)
	{
		$reflection = new ReflectionMethod($this, '__doExecute');
		$i = 0;
		foreach ($reflection->getParameters() as $params)
		{
			$paramName = $params->name;
			if(isset($agrs[$i]))
			{
				$this->html->{$paramName} = $agrs[$i];
			}
			else 
			{
				$this->html->{$paramName} = $params->getDefaultValue();
			}
			$i ++;
		}
		$this->html->setHtml($this->_html);
		call_user_func_array(array(&$this, '__doExecute'), $agrs);
		return $this->html->generate();
	}
}
?>