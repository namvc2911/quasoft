<?php
/**
 *
 * The system form management
 *
 * @author HuyBD
 *
 */
class System_LanguageController extends Qss_Lib_Controller
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
		$model = new Qss_Model_System_Language();
		$this->html->languages = $model->getAll();
	}
	/**
	 * Edit action
	 *
	 * @return void
	 */
	public function editAction()
	{
		$code = $this->params->requests->getParam('code','');
		$model = new Qss_Model_System_Language();
		$model->init($code);
		$this->html->language = $model;
	}
	public function translateAction()
	{
		$id = $this->params->requests->getParam('id',0);
		$model = new Qss_Model_System_Language();
		$this->html->list = $model->getAllTranslation();
		$data = array();
		$ret = $model->getTranslation($id);
		foreach ($ret as $item)
		{
			$data[$item->Language] = $item->Text;
		}
		$this->html->languages = $model->getAll(1);
		$this->html->data = $data;
		$this->html->id = $id;
	}
	//	public function translateEditAction()
	//	{
	//		$type = $this->params->requests->getParam('type',0);
	//		$fid = $this->params->requests->getParam('fid',0);
	//		$a_Params = $this->params->requests->getParams();
	//		$form = new Qss_Model_System_Form($type);
	//		$form->b_fInit($fid);
	//		if ( $this->params->requests->isAjax())
	//		{
	//			$service = $this->services->System->Form->Duplicate($form,$a_Params);
	//			echo $service->getMessage();
	//		}
	//		$this->setHtmlRender(false);
	//		$this->setLayoutRender(false);
	//	}
	/**
	 * Save action that call via ajax
	 *
	 * @return void
	 */
	public function saveAction()
	{
		/* Use same name in databse */
		$a_Params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax())
		{
			$service = $this->services->System->Language->Save($a_Params);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function translateSaveAction()
	{
		$params = $this->params->requests->getParams();
		$service = $this->services->System->Translate->Save($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function translateDeleteAction()
	{
		$id = $this->params->requests->getParam('id',0);
		$model = new Qss_Model_System_Language();
		$model->deleteTranslate($id);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>