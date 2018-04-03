<?php
/**
 *
 * @author HuyBD
 *
 */
class Admin_DocumentController extends Qss_Lib_Controller
{
	protected $_model;
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		$this->i_SecurityLevel = 2;
		$this->_model = new Qss_Model_Admin_Document();
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/admin-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/popup-select.js');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{
		$this->html->listview = $this->views->Common->TreeView($this->_model,'/admin/document');
		$this->html->pager = '';

	}


	/**
	 * Edit page
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function editAction ()
	{
		$dtid = $this->params->requests->getParam('dtid', 0);
		$this->html->doctype = $this->_model->getById($dtid);
		$this->html->doctypes = $this->_model->getAll($dtid);
	}

	/**
	 * Save department
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function saveAction ()
	{
		$params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Admin->Document->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deleteAction ()
	{
		$id = $this->params->requests->getParam('dtid');
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Admin->Document->Delete($id);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function downloadAction ()
	{
		$id = $this->params->requests->getParam('id');
		$dataSQL  = $this->_model->getById($id);
		if($dataSQL)
		{
			$file = QSS_DATA_DIR . '/documents/template/' . $dataSQL->DTID . '.' . $dataSQL->File;
			if ( file_exists($file) )
			{
				header("Content-type: application/force-download");
				header("Content-Transfer-Encoding: Binary");
				header("Content-length: " . filesize($file));
				header("Content-disposition: attachment; filename=\"" . $dataSQL->Code . '.' . $dataSQL->File ."\"");
				readfile("$file");
				die();
			}
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deleteTemplateAction ()
	{
		$id = $this->params->requests->getParam('id');
		$dataSQL  = $this->_model->getById($id);
		if($dataSQL)
		{
			$file = QSS_DATA_DIR . '/documents/template/' . $dataSQL->DTID . '.' . $dataSQL->File;
			if ( file_exists($file) )
			{
				unlink($file);
			}
		}
		echo Qss_Json::encode(array('error'=>false));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>