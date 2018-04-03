<?php
/**
 *
 * The system form management
 *
 * @author HuyBD
 *
 */
class System_CalendarController extends Qss_Lib_Controller
{
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		$this->i_SecurityLevel = 1;
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/system-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
	}
	public function indexAction()
	{
		$model = new Qss_Model_Calendar_Form();
		$this->html->list = $model->getAll();
	}
	/**
	 * Edit action
	 *
	 * @return void
	 */
	public function editAction()
	{
		$fid = $this->params->requests->getParam('fid',0);
		$model = new Qss_Model_Calendar_Form();
		$this->html->data = $model->get($fid);
		$this->html->forms = $model->getAllForm();
		$this->html->fields = $model->getAllFieldsByFID($fid);
		$this->html->fid = $fid;
	}

	public function saveAction()
	{
		$a_Params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax())
		{
			$service = $this->services->System->Calendar->Save($a_Params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 * Save action that call via ajax
	 *
	 * @return void
	 */
	public function deletection()
	{
		/* Use same name in databse */
		$fid = $this->params->requests->getParam('fid',0);
		if ( $this->params->requests->isAjax())
		{
			$service = $this->services->System->Calendar->Delete($fid);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

}
?>