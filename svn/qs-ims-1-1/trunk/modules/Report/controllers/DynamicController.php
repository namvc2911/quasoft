<?php
/**
 b
 */
class Report_DynamicController extends Qss_Lib_Controller
{
	protected $_model;
	public function init ()
	{
		parent::init();
		$this->_model = new Qss_Model_IReport();
		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
	}
	public function indexAction()
	{
		$this->html->reports = $this->_model->getAll();
	}
	public function detailAction()
	{
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		$reportid = $this->params->requests->getParam('reportid', 0);
		$report = $this->_model->getById($reportid);
		$code = $report->Code;
		$datasetname = 'Qss_Model_IReport_' . $code;
		if($code && class_exists($datasetname) )
		{
			$dataset = new $datasetname();
			$view = $dataset->view;
			$viewname = 'Qss_View_IReport_' . $view;
			if(class_exists($viewname))
			{
				$this->html->content = $this->views->IReport->{$code}();
				$this->html->reportid = $reportid;
				$this->html->report = $report;
			}
		}
	}
	public function showAction()
	{
		$reportid = $this->params->requests->getParam('reportid', 0);
		$report = $this->_model->getById($reportid);
		$code = $report->Code;

		$datasetname = 'Qss_Model_IReport_' . $code;
		$dataset = new $datasetname();
		if($report->GroupBy)
		{
			$dataset->setOrder($report->GroupBy);
		}
		if($report->OrderBy)
		{
			$dataset->setOrder($report->OrderBy);
		}
		$dataset->setParams($this->params->requests->getParams());
		$this->html->columns = $this->_model->getColumns($reportid);
		$this->html->fieldtypes = $dataset->fieldtypes;
		$this->html->report = $report;
		$this->html->datasource = $dataset->__doExecute();
		$this->html->width = $dataset->widths;
		$this->html->groupby = $report->GroupBy;
        $this->html->tableWidth = $report->TableWidth;
	}
	public function editAction()
	{
		$reportid = $this->params->requests->getParam('reportid', 0);
		$newcode = $this->params->requests->getParam('report', '');
		$name = '';
		$customcolumns = array();
		if($reportid)
		{
			$report = $this->_model->getById($reportid);
			if($report && ($newcode == $report->Code || $newcode === ''))
			{
				$code = $report->Code;
				$customcolumns = $this->_model->getColumns($reportid);
			}
			$this->html->report = $report;
		}
		$datasetname = 'Qss_Model_IReport_' . (($newcode !== '')?$newcode:$code);
		$columns = array();
		if(class_exists($datasetname))
		{
			$dataset = new $datasetname();
			$columns = $dataset->columns;
			$name = $dataset->name;
			$this->html->orgcolumns = $columns;
		}
		$begincolumns = array();
		$endcolumns = array();
		foreach ($customcolumns as $item)
		{
			if(array_key_exists($item->Code, $columns))
			{
				$item->Selected = true;
				$begincolumns[$item->Code] = $item;
			}
		}
		foreach ($columns as $key=>$val)
		{
			if(!array_key_exists($key, $begincolumns))
			{
				$obj = new stdClass;
				$obj->Code = $key;
				$obj->Name = $val;
				$obj->Class = '';
				$obj->OrderNo  = '';
				$obj->Selected = false;
				$endcolumns[$key] = $obj;
			}
		}
		$this->html->columns = array_merge($begincolumns,$endcolumns);
		$path = QSS_ROOT_DIR . '/models/IReport';
		$myDirectory = opendir($path);
		// get each entry
		$allreports = array();
		while($entryName = readdir($myDirectory)) {
			$rptname = basename($entryName,".php");
			$classname = 'Qss_Model_IReport_'.$rptname;
			if(class_exists($classname))
			{

				$rpt = new $classname();
				$interfaces = class_implements($rpt);
				if(count($interfaces))
				{
					$allreports[$rptname] = $rpt->name;
				}
			}
		}
		// close directory
		closedir($myDirectory);
		$this->html->allreports = $allreports;
		$this->html->newcode = ($newcode !== '')?$newcode:$code;
		$this->html->name = $name;
		$this->html->width = $dataset->widths;
		$this->html->dcolumns = $dataset->columns;

	}
	public function saveAction ()
	{
		$params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->IReport->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deleteAction ()
	{
		$id  = $this->params->requests->getParam('id');
		$this->_model->delete($id);
		echo Qss_Json::encode(array('error'=>false));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}