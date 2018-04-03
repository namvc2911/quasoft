<?php
/**
 *
 * @author HuyBD
 *
 */
class System_BashController extends Qss_Lib_Controller
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
		$this->_model = new Qss_Model_Bash();
		$this->headScript($this->params->requests->getBasePath() . '/js/bash-list.js');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{
		$currentpage = (int) $this->params->cookies->get('bash_currentpage', 1);
		$currentpage = $currentpage ? $currentpage : 1;
		$limit = $this->params->cookies->get('bash_limit', 20);
		$fieldorder = $this->params->cookies->get('bash_fieldorder', '0');
		$ordertype = $this->params->cookies->get('bash_ordertype', '0');
		$filter = $this->params->cookies->get('bash_filter', 'a:0:{}');
		$i_GroupBy = $this->params->cookies->get('bash_groupby', '0');
		//		$filter ? $filter : 'a:0:{}';
		$filter = unserialize($filter);
		$this->html->filters = $filter;
		$form = new Qss_Model_System_Form();
		$this->html->forms = $form->a_fGetAll();
		$this->html->listview= $this->views->Bash->Grid($this->_user,$currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC',$i_GroupBy,$filter);
	}
	public function gridAction ()
	{
		$currentpage = (int) $this->params->requests->getParam('pageno', 1);
		$limit = $this->params->requests->getParam('perpage', 20);
		$fieldorder = $this->params->requests->getParam('fieldorder', '0');
		$ordertype = $this->params->cookies->get('bash_ordertype', '0');
		$filter = $this->a_fGetFilter();
		$i_GroupBy = $this->params->requests->getParam('groupby', '0');
		//		$filter ? $filter : 'a:0:{}';
		if ( $fieldorder != 0 )
		{
			$ordertype = !$ordertype;
		}
		else
		{
			$fieldorder = $fieldorder ? $fieldorder : $this->params->cookies->get('bash_fieldorder', 0);
		}
		$this->params->cookies->set('bash_currentpage', $currentpage);
		$this->params->cookies->set('bash_limit', $limit);
		$this->params->cookies->set('bash_filter', serialize($filter));
		$this->params->cookies->set('bash_ordertype', $ordertype);
		$this->params->cookies->set('bash_fieldorder', $fieldorder);
		$this->params->cookies->set('bash_groupby', $i_GroupBy);
		echo $this->views->Bash->Grid($this->_user,$currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC',$i_GroupBy,$filter);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function editAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$this->html->bash = $this->_model->getByID($id);
		$form = new Qss_Model_Form();
		$this->html->forms = $form->a_fGetAll();
		$fid = @$this->html->bash->FormCode;
		$lang = new Qss_Model_System_Language();
		$this->html->languages = $lang->getAll(1);
		if($fid)
		{
			if($form->init($fid, $this->_user->user_dept_id, $this->_user->user_id))
			{
				$this->html->objid = $form->o_fGetMainObject()->ObjectCode;
			}
		}
	}
	public function saveAction ()
	{
		$params = $this->params->requests->getParams();
		$params['uid'] = $this->_user->user_id;
		$id = $this->params->requests->getParam('id', 0);
		$service = $this->services->Bash->Save($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deleteAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$this->_model->delete($id);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function fieldsAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$this->html->bashid = $id;
		$this->html->fields = $this->_model->getFields($id);
	}
	public function fieldEditAction ()
	{
		$bashid = $this->params->requests->getParam('bashid',0);
		$id = $this->params->requests->getParam('id',0);
		$bash = $this->_model->getByID($bashid);
		$this->html->field = $this->_model->getFieldByID($id);
		if($bash->FID && $bash->ToFID )
		{
			$fieldmodel = new Qss_Model_System_Field();
			$formmodel = new Qss_Model_Form();
			$formmodel->v_fInit($bash->FID, $this->_user->user_dept_id, $this->_user->user_id);
			$this->html->fields = $fieldmodel->a_fGetAllByFID($bash->FID);
			$this->html->objid = $formmodel->o_fGetMainObject()->ObjectCode;
			$formmodel = new Qss_Model_Form();
			$formmodel->v_fInit($bash->ToFID, $this->_user->user_dept_id, $this->_user->user_id);
			$this->html->tofields = $fieldmodel->a_fGetAllByFID($bash->ToFID);
		}
		$this->html->bashid = $bashid;
	}
	public function fieldSaveAction ()
	{
		$params = $this->params->requests->getParams();
		$id = $this->params->requests->getParam('bashid', 0);
		$service = $this->services->Bash->Field->Save($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function fieldDeleteAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$this->_model->deleteField($id);
		echo Qss_Json::encode(array('error' => false));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function historyAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$currentpage = (int) $this->params->cookies->get('bash_history_currentpage', 1);
		$currentpage = $currentpage ? $currentpage : 1;
		$limit = $this->params->cookies->get('bash_history_limit', 20);
		$ordertype = $this->params->cookies->get('bash_history_ordertype', '0');
		$this->html->listview= $this->views->Bash->History->Grid($id,$currentpage, $limit,  $ordertype ? 'ASC' : 'DESC');
		$this->html->bashid = $id;
	}
	public function historyGridAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$currentpage = (int) $this->params->requests->getParam('pageno', 1);
		$limit = $this->params->requests->getParam('perpage', 20);
		$fieldorder = $this->params->requests->getParam('fieldorder', '0');
		$ordertype = $this->params->cookies->get('bash_history_ordertype', '0');
		if ( $fieldorder )
		{
			$ordertype = !$ordertype;
		}
		$this->params->cookies->set('bash_history_currentpage', $currentpage);
		$this->params->cookies->set('bash_history_limit', $limit);
		$this->params->cookies->set('bash_history_ordertype', $ordertype);
		echo $this->views->Bash->History->Grid($id,$currentpage, $limit,  $ordertype ? 'ASC' : 'DESC');
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function runAction ()
	{
		$id = $this->params->requests->getParam('id', 0);
		$ifid = $this->params->requests->getParam('ifid', 0);
		$form = new Qss_Model_Form();
		if($form->initData($ifid, $this->_user->user_dept_id))
		{
			$form->o_fGetMainObject()->initData($ifid, $this->_user->user_dept_id, $form->o_fGetMainObject()->i_IOID);
			$service = $this->services->Bash->Execute($form,$id);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>