<?php

class Qss_Service
{

	protected $_Directories;

	public function __construct ()
	{
		$this->_Directories = array();
	}

	/**
	 * Add folder
	 * 
	 * @param $name
	 * @return unknown_type
	 */
	public function __get ($name)
	{
		$this->_Directories[] = $name;
		return $this;
	}

	/* Call service module */
	public function __call ($name, $agrs)
	{
		$className = 'Qss_Service_' . implode('_', $this->_Directories) . '_' . $name;
		/* Init the service */
		try
		{
			$service = new $className();
			$service->run($agrs, $this->_Directories, $name);
			return $service;
		}
		catch ( Exception $e )
		{
			throw new Qss_Exception($e->getTraceAsString().'No service name ' . $name . ' in folder ' . implode('_', $this->_Directories));
		}
	}
}
?>