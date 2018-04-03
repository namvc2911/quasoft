<?php

class Qss_View
{

	protected $_Directories;

	public function __construct ()
	{
		$this->_Directories = array();
	}

	/*Add folder*/
	public function __get ($name)
	{
		$this->_Directories[] = $name;
		return $this;
	}

	/* Call view module */
	public function __call ($name, $agrs)
	{
		$className = 'Qss_View_' . implode('_', $this->_Directories) . '_' . $name;
		/* Init the view */
		try
		{
			$view = new $className();
			$path = '';
			if(isset(Qss_Register::get('configs')->view->path_ext))
			{
				$path = Qss_Register::get('configs')->view->path_ext . implode('/', $this->_Directories) . '/' . $name . '.phtml';
			}
			if(!file_exists($path))
			{
				$path = Qss_Register::get('configs')->view->path . implode('/', $this->_Directories) . '/' . $name . '.phtml';
			}
			$view->setHtml($path);
			return $view->generate($agrs);
		}
		catch ( Exception $e )
		{
			//throw new Qss_Exception('No class name ' . $className . ' in folder ' . implode('/', $this->_Directories));
			throw new Qss_Exception($e->getTraceAsString());
				
		}
	}
}
?>