<?php
/**
 *
 * The system form management
 *
 * @author HuyBD
 *
 */
class System_WorkflowController extends Qss_Lib_Controller
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
		$fid = $this->params->requests->getParam('fid',0);
		$workflow = new Qss_Model_System_Workflow($fid);
		$this->html->workflow = $workflow->getAll();
		$this->html->type = $workflow->intType;
		$this->html->fid = $workflow->FormCode;
	}
	/**
	 * Edit action
	 *
	 * @return void
	 */
	public function editAction()
	{
		$fid = $this->params->requests->getParam('fid',0);
		$wfid = $this->params->requests->getParam('wfid',0);
		$workflow = new Qss_Model_System_Workflow($fid);
		if($wfid)
		{
			$workflow->init($wfid);
		}
		$this->html->workflow = $workflow;
		$this->html->type = $workflow->intType;
		$this->html->fid = $workflow->FormCode;
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
			$service = $this->services->System->Workflow->Save($a_Params);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deleteAction()
	{
		/* Use same name in databse */
		$wfid = $this->params->requests->getParam('wfid');
		$fid = $this->params->requests->getParam('fid');
		if ( $this->params->requests->isAjax())
		{
			$workflow = new Qss_Model_System_Workflow($fid);
			$workflow->init($wfid);
			$workflow->delete();
			$step = new Qss_Model_System_Step($wfid);
			$step->deleteAll();
			echo Qss_Json::encode(array('error'=>0));
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function stepAction()
	{
		$wfid 						= $this->params->requests->getParam('wfid',0);
		$step 						= new Qss_Model_System_Step($wfid);
		$this->html->steps 			= $step->getAll();
		$this->html->fid 			= $step->FormCode;
		$this->html->wfid 			= $step->intWorkFlowID;
	}
	public function stepEditAction()
	{
		$this->headScript($this->params->requests->getBasePath() . '/js/tag.js');
		$sid 						= $this->params->requests->getParam('sid',0);
		$wfid 						= $this->params->requests->getParam('wfid',0);
		$step 						= new Qss_Model_System_Step($wfid);
		$document					= new Qss_Model_Admin_Document();
		$activity					= new Qss_Model_Event();
		if($sid)
		{
			$step->v_fInit($sid);
		}
		$form 						= new Qss_Model_Form();
		$form->init($step->FormCode, 1,0);
		$this->html->objid 			= $form->o_fgetMainObject()->ObjectCode;
		$this->html->step 			= $step;
		$this->html->fields 		= $step->getRights();
//		print_r($this->html->fields);die;
		$this->html->wfid 			= $wfid;
		$lang 						= new Qss_Model_System_Language();
		$this->html->groups = $step->getGroups();
		$this->html->languages 		= $lang->getAll(1);
		$this->html->data 			= $step->getById($sid);
		$this->html->form 			= $form;
		$this->html->documenttypes  = $document->getAll();
		$this->html->activitytype   = $activity->getAllType();
		$this->html->documents      = $step->getStepDocuments();
		$this->html->activities     = $step->getStepActivities();
		$this->html->objectrights	= $step->getObjectRights();
		$this->html->approvers 		= $step->getApprovers();
		//echo '<pre>';
		//print_r($this->html->objectrights);die;
	}
	public function stepSaveAction()
	{
		/* Use same name in databse */
		$a_Params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax())
		{
			$service = $this->services->System->Workflow->Step->Save($a_Params);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function stepDeleteAction()
	{
		/* Use same name in databse */
		$wfid = $this->params->requests->getParam('wfid');
		$sid = $this->params->requests->getParam('sid');
		if ( $this->params->requests->isAjax())
		{
			$step = new Qss_Model_System_Step($wfid);
			$step->v_fInit($sid);
			$step->delete();
			echo Qss_Json::encode(array('error'=>0));
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function stepApproverAction()
	{
		$wfid = $this->params->requests->getParam('wfid');
		$said = $this->params->requests->getParam('said',0);
		$sid = $this->params->requests->getParam('sid');
		$model = new Qss_Model_System_Step($wfid);
		$model->v_fInit($sid);
		$fid = $model->FormCode;
		$data = $model->getApprover($said);
		$form = new Qss_Model_Form();
		$form->init($fid, $this->_user->user_dept_id, $this->_user->user_id);
		$this->html->form = $form;
		$this->html->wfid= $wfid;
		$this->html->said = $said;
		$this->html->sid = $sid;
		$this->html->orderno = (int)@$data->OrderNo;
		$this->html->name = @$data->Name;
		$group = new Qss_Model_Admin_Group();
		$condition = @$data->Condition;
		$arrCondition =array();
		if($condition)
		{
			$arrCondition = (array)Qss_Json::decode($condition);
		}
		$this->html->condition = $arrCondition;
		$this->setLayoutRender(false);
	}
	public function stepSaveApproverAction()
	{
		/* Use same name in databse */
		$params = $this->params->requests->getParams();
		$service = $this->services->System->Workflow->Approver->Save($params);
		echo $service->getMessage();
		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function stepDeleteApproverAction()
	{
		$wfid = $this->params->requests->getParam('wfid');
		$said = $this->params->requests->getParam('said');
		$sid = $this->params->requests->getParam('sid');
		$name = $this->params->requests->getParam('name',0);
		$model = new Qss_Model_System_Step($wfid);
		$model->v_fInit($sid);
		$model->deleteApprover($said);
		echo Qss_Json::encode(array('error'=>false));
		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function stepCustomAction()
	{
		$sid 						= $this->params->requests->getParam('sid',0);
		$wfid 						= $this->params->requests->getParam('wfid',0);
		$groupid 					= $this->params->requests->getParam('groupid',0);
		$step 						= new Qss_Model_System_Step($wfid);
		$document					= new Qss_Model_Admin_Document();
		$activity					= new Qss_Model_Event();
		if($sid)
		{
			$step->v_fInit($sid);
		}
		$form 						= new Qss_Model_Form();
		$this->html->step 			= $step;
		$this->html->fields 		= $step->getRights($groupid);
//		print_r($this->html->fields);die;
		$this->html->wfid 			= $wfid;
		$this->html->groupid 		= $groupid;
	}
	public function stepSaveCustomAction()
	{
		/* Use same name in databse */
		$params = $this->params->requests->getParams();
		$service = $this->services->System->Workflow->Custom->Save($params);
		echo $service->getMessage();
		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function stepDeleteCustomAction()
	{
		$wfid = $this->params->requests->getParam('WFID');
		$groupid = $this->params->requests->getParam('GroupID');
		$sid = $this->params->requests->getParam('SID');
		$model = new Qss_Model_System_Step($wfid);
		$model->v_fInit($sid);
		$model->deleteCustom($groupid);
		echo Qss_Json::encode(array('error'=>false));
		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}