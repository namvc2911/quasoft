<?php
/**
 *
 * @author HuyBD
 *
 */
class System_ObjectController extends Qss_Lib_Controller
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
	}
	public function indexAction()
	{
		$o_ObjectModel = new Qss_Model_System_Object();
		$this->html->ObjectList = $o_ObjectModel->a_fGetAll();
	}
	public function reloadAction()
	{
		$i_Type        = $this->params->requests->getParam('type',0);
		$sz_Search     = $this->params->requests->getParam('search','');
        $o_ObjectModel = new Qss_Model_System_Object();
        $this->html->ObjectList = $o_ObjectModel->a_fGetAll($sz_Search);
	}
	public function editAction()
	{
		$objid = $this->params->requests->getParam('objid',0);
		$object = new Qss_Model_System_Object();
		$object->v_fInit($objid);
		$this->html->fields = array();
		$lang = new Qss_Model_System_Language();
		$this->html->languages = $lang->getAll(1);
		$this->html->object = $object;
		$this->html->data = $object->getByCode($objid);
	}
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
			$service = $this->services->System->Object->Save($a_Params);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>