<?php
/**
 *
 * @author HuyBD
 *
 */
class User_ProcessController extends Qss_Lib_Controller
{
	protected $_model;
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		parent::init();
		$this->_model = new Qss_Model_Process();
		$this->headScript($this->params->requests->getBasePath() . '/js/process-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{
		$ifid = $this->params->requests->getParam('ifid', 0);
		$deptid = $this->params->requests->getParam('deptid', 0);

		$currentpage = (int) $this->params->cookies->get('process_currentpage', 1);
		$currentpage = $currentpage ? $currentpage : 1;
		$limit = $this->params->cookies->get('process_limit', 20);
		$fieldorder = $this->params->cookies->get('process_fieldorder', '0');
		$ordertype = $this->params->cookies->get('process_ordertype', '0');
		$filter = $this->params->cookies->get('process_filter', 'a:0:{}');
		$i_GroupBy = $this->params->cookies->get('process_groupby', '0');
		$filter = unserialize($filter);
		$this->html->filters = $filter;
		$form = new Qss_Model_Form();
		$form->initData($ifid, $deptid);
		if($this->b_fCheckRightsOnForm($form,4))
		{
			$user = new Qss_Model_Admin_User();
			$this->html->users = $user->a_fGetAllNormal();
			$this->html->count = $form->countProcessLog();
			$this->html->listview= $this->views->Process->Log->Grid($ifid,$currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC',$i_GroupBy,$filter);
			$this->html->form = $form;
			//$form->read($this->_user->user_id);
		}
	}
	public function gridAction ()
	{
		$ifid = (int) $this->params->requests->getParam('ifid', 0);
		$form = new Qss_Model_Form();
		$form->initData($ifid, $this->_user->user_dept_id);
		if($this->b_fCheckRightsOnForm($form,4))
		{
			$currentpage = (int) $this->params->requests->getParam('pageno', 1);
			$limit = $this->params->requests->getParam('perpage', 20);
			$fieldorder = $this->params->requests->getParam('fieldorder', '0');
			$ordertype = $this->params->cookies->get('process_ordertype', '0');
			$filter = $this->a_fGetFilter();
			$i_GroupBy = $this->params->requests->getParam('groupby', '0');
			if ( $fieldorder != 0 )
			{
				$ordertype = !$ordertype;
			}
			else
			{
				$fieldorder = $fieldorder ? $fieldorder : $this->params->cookies->get('process_fieldorder', 0);
			}
			//		$filter ? $filter : 'a:0:{}';
			$this->params->cookies->set('process_currentpage', $currentpage);
			$this->params->cookies->set('process_limit', $limit);
			$this->params->cookies->set('process_filter', serialize($filter));
			$this->params->cookies->set('process_ordertype', $ordertype);
			$this->params->cookies->set('process_fieldorder', $fieldorder);
			$this->params->cookies->set('process_groupby', $i_GroupBy);
			echo $this->views->Process->Log->Grid($ifid,$currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC',$i_GroupBy,$filter);
				
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function calendarAction ()
	{
		$ifid = $this->params->requests->getParam('ifid');
		$this->html->ifid = $ifid;
		$this->html->times = $this->_model->getCalendars($ifid);
	}

	public function logDeleteAction ()
	{
		$elid = $this->params->requests->getParam('elid', 0);
		$id = $this->_model->getEventByLID($elid);
		$service = $this->services->Event->Check($this->_user->user_id,$id,1);
		if($service->getData())
		{
			$this->_model->deleteLog($id,$elid);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function calendarSaveAction ()
	{
		$params = $this->params->requests->getParams();
		$ifid = $params['ifid'];
		$form = new Qss_Model_Form();
		if($form->initData($ifid,  $this->_user->user_dept_id))
		{
			if($this->b_fCheckRightsOnForm($form,2))
			{
				$service = $this->services->Process->Calendar->Save($params);
				echo $service->getMessage();
			}
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function runAction ()
	{
		$ifid = $this->params->requests->getParam('ifid');
		$form = new Qss_Model_Form();
		if($form->initData($ifid,  $this->_user->user_dept_id))
		{
			if($this->b_fCheckRightsOnForm($form,4))
			{
				$service = $this->services->Form->Validate($form);
				if(!$service->isError())
				{
					$service = $this->services->Process->Run($form);
				}
				echo $service->getMessage();
			}
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

}
?>