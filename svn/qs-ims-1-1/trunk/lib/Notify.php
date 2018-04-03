<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_Notify extends Qss_Lib_Bin
{
	
	protected $_maillist;
	
	public function init()
	{
		parent::init();
		$this->_maillist = array();
	}
	protected function _genPDF($code,$params = array())
	{
		//Get action (1)
		$model = new Qss_Model_Report();
		Qss_Params::getInstance()->requests->setParams($params);
		$report = $model->getByCode($code);
		$path = $report->class;
		$arrPath = explode('/', trim($path , '/'));
		$module = array_shift($arrPath);
		$module = ucfirst($module);
 
		$controller = @array_shift($arrPath);
		
		$options = Qss_Register::get('configs');
		if ( !$controller )
		{
			$controller = 'Index';
		}

		$controller = ucfirst($controller);
		$action = '';
		$actionPath = '';
		foreach ($arrPath as $value)
		{
			$action .= ucfirst($value);
			$actionPath .= '/' . $value;
		}
		if ( !$action )
		{
			$action = 'Index';
			$actionPath = '/index';
		}
		$action .= '1';
		$actionPath .= '1';
		if(isset($options->module->path_ext))
		{
			$include = $options->module->path_ext . $module . '/controllers/' . $controller . 'Controller.php';
			if ( !@include_once $include )
			{
				$include = $options->module->path . $module . '/controllers/' . $controller . 'Controller.php';
				if ( !@include_once $include )
				{
					throw new Qss_Exception('File not found ' . $include);
				}
			}
		}
		else
		{
			$include = $options->module->path . $module . '/controllers/' . $controller . 'Controller.php';
			if ( !@include_once $include )
			{
				die($clasName);
				throw new Qss_Exception('File not found ' . $include);
			}
		}
		$clasName = $module . '_' . $controller . 'Controller';
		if ( $controller == 'Index' )
		{
			if(isset($options->module->path_ext) && file_exists($options->module->path_ext . $module . '/html/' . $actionPath . '.phtml'))
			{
				$include = $options->module->path_ext . $module . '/html/' . $actionPath . '.phtml';
			}
			else 
			{
				$include = $options->module->path . $module . '/html/' . $actionPath . '.phtml';
			}
		}
		else
		{
			if(isset($options->module->path_ext) && file_exists($options->module->path_ext . $module . '/html/' . strtolower($controller) . $actionPath . '.phtml'))
			{
				$include = $options->module->path_ext . $module . '/html/' . strtolower($controller) . $actionPath . '.phtml';
			}
			else 
			{
				$include = $options->module->path . $module . '/html/' . strtolower($controller) . $actionPath . '.phtml';
			}
		}
		$controller = new $clasName();
		$controller->setHtml($include);
		$controller->setAction($action);
		if ( @$options->layout )
		{
			$controller->layout = $options->layout->path . '/' . $options->layout->name . '.php';
		}
		try
		{
			$controller->init();
		}
		catch (Exception $e)
		{
			
		}
		$controller->setLayoutRender(false);
		ob_start();
		$controller->run();
		ob_get_clean();
		$response = Qss_Params::getInstance();
		$content = $response->responses->getContent();
		//get html content from action

		/*$postdata = http_build_query($params);

		$opts = array('http' =>
		array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => $postdata
		)
		);
		$context  = stream_context_create($opts);
		$content = file_get_contents(QSS_BASE_URL.$action.'1'. "?" . session_name() . "=". session_id());
		echo $context;*/
		//gen pdf from content to file
		$fn = QSS_DATA_BASE.'/tmp/' . uniqid().'.pdf';
		$html2pdf = new Qss_Model_Html2PDF();
		$doc = new DOMDocument('1.0', 'UTF-8');
		@$doc->loadHTML($content);
		$html2pdf->render($doc,$fn);
		return $fn;
	}
	protected function _genHTML($code,$params = array())
	{
		//Get action (1)
		$model = new Qss_Model_Report();
		Qss_Params::getInstance()->requests->setParams($params);
		$report = $model->getByCode($code);
		$path = $report->class;
		$arrPath = explode('/', trim($path , '/'));
		$module = array_shift($arrPath);
		$module = ucfirst($module);
 
		$controller = @array_shift($arrPath);
		
		$options = Qss_Register::get('configs');
		if ( !$controller )
		{
			$controller = 'Index';
		}

		$controller = ucfirst($controller);
		$action = '';
		$actionPath = '';
		foreach ($arrPath as $value)
		{
			$action .= ucfirst($value);
			$actionPath .= '/' . $value;
		}
		if ( !$action )
		{
			$action = 'Index';
			$actionPath = '/index';
		}
		$action .= '1';
		$actionPath .= '1';
		if(isset($options->module->path_ext))
		{
			$include = $options->module->path_ext . $module . '/controllers/' . $controller . 'Controller.php';
			if ( !@include_once $include )
			{
				$include = $options->module->path . $module . '/controllers/' . $controller . 'Controller.php';
				if ( !@include_once $include )
				{
					throw new Qss_Exception('File not found ' . $include);
				}
			}
			else
			{
				$options->module->path = $options->module->path_ext;
			}
		}
		else
		{
			$include = $options->module->path . $module . '/controllers/' . $controller . 'Controller.php';
			if ( !@include_once $include )
			{
				die($clasName);
				throw new Qss_Exception('File not found ' . $include);
			}
		}
		$clasName = $module . '_' . $controller . 'Controller';
		if ( $controller == 'Index' )
		{
			$include = $options->module->path . $module . '/html/' . $actionPath . '.phtml';
		}
		else
		{
			$include = $options->module->path . $module . '/html/' . strtolower($controller) . $actionPath . '.phtml';
		}
		$controller = new $clasName();
		$controller->setHtml($include);
		$controller->setAction($action);
		if ( @$options->layout )
		{
			$controller->layout = $options->layout->path . '/' . $options->layout->name . '.php';
		}
			
		try
		{
			$controller->init();
		}
		catch (Exception $e)
		{
			
		}
		$controller->setLayoutRender(false);
		ob_start();
		$controller->run();
		ob_get_clean();
		$response = Qss_Params::getInstance();
		return $response->responses->getContent();
	}
	protected function _genExcel($code,$params = array())
	{
		//Get action (1)
		$model = new Qss_Model_Report();
		Qss_Params::getInstance()->requests->setParams($params);
		$report = $model->getByCode($code);
		$path = $report->class;
		$arrPath = explode('/', trim($path , '/'));
		$module = array_shift($arrPath);
		$module = ucfirst($module);
 
		$controller = @array_shift($arrPath);
		
		$options = Qss_Register::get('configs');
		if ( !$controller )
		{
			$controller = 'Index';
		}

		$controller = ucfirst($controller);
		$action = '';
		$actionPath = '';
		foreach ($arrPath as $value)
		{
			$action .= ucfirst($value);
			$actionPath .= '/' . $value;
		}
		if ( !$action )
		{
			$action = 'Index';
			$actionPath = '/index';
		}
		$action .= '1';
		$actionPath .= '1';
		if(isset($options->module->path_ext))
		{
			$include = $options->module->path_ext . $module . '/controllers/' . $controller . 'Controller.php';
			if ( !@include_once $include )
			{
				$include = $options->module->path . $module . '/controllers/' . $controller . 'Controller.php';
				if ( !@include_once $include )
				{
					throw new Qss_Exception('File not found ' . $include);
				}
			}
			else
			{
				$options->module->path = $options->module->path_ext;
			}
		}
		else
		{
			$include = $options->module->path . $module . '/controllers/' . $controller . 'Controller.php';
			if ( !@include_once $include )
			{
				die($clasName);
				throw new Qss_Exception('File not found ' . $include);
			}
		}
		$clasName = $module . '_' . $controller . 'Controller';
		if ( $controller == 'Index' )
		{
			$include = $options->module->path . $module . '/html/' . $actionPath . '.phtml';
		}
		else
		{
			$include = $options->module->path . $module . '/html/' . strtolower($controller) . $actionPath . '.phtml';
		}
		$controller = new $clasName();
		$controller->setHtml($include);
		$controller->setAction($action);
		if ( @$options->layout )
		{
			$controller->layout = $options->layout->path . '/' . $options->layout->name . '.php';
		}
		try
		{
			$controller->init();
		}
		catch (Exception $e)
		{
			
		}
		$controller->setLayoutRender(false);
		ob_start();
		$controller->run();
		ob_get_clean();
		$response = Qss_Params::getInstance();
		$content = $response->responses->getContent();
		
		$fn = QSS_DATA_BASE.'/tmp/' . uniqid().'.xls';
		$html2excel = new Qss_Model_Html2Excel();
		$html2excel->init();
		$doc = new DOMDocument('1.0', 'UTF-8');
		@$doc->loadHTML($content);
		$html2excel->render($doc,$fn);
		return $fn;
	}
}
?>