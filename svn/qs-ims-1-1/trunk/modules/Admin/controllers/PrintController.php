<?php
/**
 *
 * @author HuyBD
 *
 */
class Admin_PrintController extends Qss_Lib_Controller
{

	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		$this->i_SecurityLevel = 4;
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/admin-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
	}

	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function indexAction ()
	{
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/design.php';
		$userinfo = $this->params->registers->get('userinfo');
		$fid = $this->params->requests->getParam('fid',0);
		$this->html->fid = $fid;
		$design = new Qss_Model_Admin_Print();
		$this->html->forms = $design->getPrintForms($this->_user);

	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function reloadAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$fid = $this->params->requests->getParam('fid',0);
		$design = new Qss_Model_Admin_Print();
		$designs = $design->getByFID($fid);
		echo $this->views->Common->List($designs, 'FPID', 'Name');
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function editAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$fid = $this->params->requests->getParam('fid',0);
		$designid = $this->params->requests->getParam('designid');
		$design = new Qss_Model_Admin_Print();
		$this->html->fid = $fid;
		$this->html->design = $design->getById($designid);
		$lang = new Qss_Model_System_Language();
		$this->html->languages = $lang->getAll(1);
		$this->html->data = $design->getById($designid);
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function saveAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Admin->Print->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function deleteAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$designid = $this->params->requests->getParam('designid',0);
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Admin->Print->Delete($designid);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}
}
?>