<?php
/**
 *
 * The system form management
 *
 * @author HuyBD
 *
 */
class System_UiController extends Qss_Lib_Controller
{
	protected $_model;
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		$this->i_SecurityLevel = 1;
		parent::init();
		$this->_model = new Qss_Model_System_UI();
		$this->headScript($this->params->requests->getBasePath() . '/js/system-list.js');
	}
	public function groupAction()
	{
		$objid = $this->params->requests->getParam('objid',0);
		$this->html->objid = $objid;
		$this->html->data = $this->_model->getGroupByObjID($objid);
	}
	/**
	 * Edit action
	 *
	 * @return void
	 */
	public function groupEditAction()
	{
		$objid = $this->params->requests->getParam('objid',0);
		$uigid = $this->params->requests->getParam('uigid',0);
		$lang 						= new Qss_Model_System_Language();
		$this->html->languages 		= $lang->getAll(1);
		$this->html->objid = $objid;
		$this->html->data = $this->_model->getGroupByID($uigid);
	}
	/**
	 * Save action that call via ajax
	 *
	 * @return void
	 */
	public function groupSaveAction()
	{
		/* Use same name in databse */
		$a_Params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax())
		{
			$service = $this->services->System->UI->Group->Save($a_Params);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function groupDeleteAction()
	{
		/* Use same name in databse */
		$gid = $this->params->requests->getParam('gid');
		if ( $this->params->requests->isAjax())
		{
			$model = new Qss_Model_System_UI();
			$model->deleteGroup($gid);
			echo Qss_Json::encode(array('error'=>0));
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function boxAction()
	{
		$objid = $this->params->requests->getParam('objid',0);
		$gid = $this->params->requests->getParam('gid',0);
		$this->html->objid = $objid;
		$this->html->gid = $gid;
		$this->html->data = $this->_model->getBoxByGID($gid);
	}
	public function boxEditAction()
	{
		$objid = $this->params->requests->getParam('objid',0);
		$gid = $this->params->requests->getParam('gid',0);
		$bid = $this->params->requests->getParam('bid',0);
		$lang 						= new Qss_Model_System_Language();
		$this->html->languages 		= $lang->getAll(1);
		$this->html->objid = $objid;
		$this->html->gid = $gid;
		$this->html->bid = $bid;
		$this->html->data = $this->_model->getBoxByID($bid);
		$this->html->fields  = $this->_model->getFieldByBox($objid,$bid);
		$this->html->uigroups = $this->_model->getGroupByObjID($objid);
	}
	public function boxSaveAction()
	{
		/* Use same name in databse */
		$a_Params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax())
		{
			$service = $this->services->System->UI->Box->Save($a_Params);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

}