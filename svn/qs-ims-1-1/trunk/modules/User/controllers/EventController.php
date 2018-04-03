<?php
/**
 *
 * @author HuyBD
 *
 */
class User_EventController extends Qss_Lib_Controller
{
	protected $_model;
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		parent::init();
		$this->_model = new Qss_Model_Event();
		$this->headScript($this->params->requests->getBasePath() . '/js/event-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/popup-select.js');
		$this->headLink($this->params->requests->getBasePath() . '/css/calendar.css');
	}
	public function indexAction ()
	{
		$type = $this->params->requests->getParam('type','');
		if(!$type)
		{
			$type = $this->params->cookies->get('cal_event_type', 'day');
		}
		$day = $this->params->requests->getParam('day',date('d'));
		$week = $this->params->requests->getParam('week',date('W'));
		$month = $this->params->requests->getParam('month',date('m'));
		$year = $this->params->requests->getParam('year',date('Y'));
		$owner = $this->params->requests->getParam('user',array());
		$filter = $this->params->requests->getParam('filter',array());
		switch($type)
		{
			case 'day':
				$this->html->select = $this->views->Event->Select->Day($this->_user,$day,$month,$year);
				$this->html->content = $this->views->Event->Content->Day($this->_user,$day,$month,$year,$filter,$owner);
				break;
			case 'week':
				$this->html->select = $this->views->Event->Select->Week($this->_user,$week,$month,$year);
				$this->html->content = $this->views->Event->Content->Week($this->_user,$week,$month,$year,$filter,$owner);
				break;
			case 'month':
				$this->html->select = $this->views->Event->Select->Month($this->_user,$month,$year);
				$this->html->content = $this->views->Event->Content->Month($this->_user,$month,$year,$filter,$owner);
				break;
			case 'year':
				$this->html->select = $this->views->Event->Select->Year($this->_user,$year);
				$this->html->content = $this->views->Event->Content->Year($this->_user,$year,$filter,$owner);
				break;
		}
		$this->html->type = $type;
		$this->html->day = $day;
		$this->html->week = $week;
		$this->html->month = $month;
		$this->html->year = $year;
		$this->html->owner = $owner;
		$this->html->filter = $filter;
		$this->html->users = $this->_model->getUsers($this->_user);
		$this->html->filters = $this->_model->getAllType();

	}
	public function reloadAction ()
	{
		$type = $this->params->requests->getParam('type','day');
		$day = $this->params->requests->getParam('day',date('d'));
		$week = $this->params->requests->getParam('week',date('W'));
		//echo $week;die;
		$month = $this->params->requests->getParam('month',date('m'));
		$year = $this->params->requests->getParam('year',date('Y'));
		$owner = $this->params->requests->getParam('user',array());
		$filter = $this->params->requests->getParam('filter',array());
		switch($type)
		{
			case 'day':
				$this->html->select = $this->views->Event->Select->Day($this->_user,$day,$month,$year);
				$this->html->content = $this->views->Event->Content->Day($this->_user,$day,$month,$year,$filter,$owner);
				break;
			case 'week':
				$this->html->select = $this->views->Event->Select->Week($this->_user,$week,$month,$year);
				$this->html->content = $this->views->Event->Content->Week($this->_user,$week,$month,$year,$filter,$owner);
				break;
			case 'month':
				$this->html->select = $this->views->Event->Select->Month($this->_user,$month,$year);
				$this->html->content = $this->views->Event->Content->Month($this->_user,$month,$year,$filter,$owner);
				break;
			case 'year':
				$this->html->select = $this->views->Event->Select->Year($this->_user,$year);
				$this->html->content = $this->views->Event->Content->Year($this->_user,$year,$filter,$owner);
				break;
		}
		$this->html->type = $type;
		$this->html->day = $day;
		$this->html->week = $week;
		$this->html->month = $month;
		$this->html->year = $year;
		$this->html->owner = $owner;
		$this->html->filter = $filter;
		$this->html->users = $this->_model->getUsers($this->_user);
		$this->html->filters = $this->_model->getAllType();
		$this->params->cookies->set('cal_event_type', $type);
	}
	/**
	 *
	 * @return void
	 */
	public function listAction ()
	{
		$currentpage = (int) $this->params->cookies->get('form_event_currentpage', 1);
		$currentpage = $currentpage ? $currentpage : 1;
		$limit = $this->params->cookies->get('form_event_limit', 20);
		$fieldorder = $this->params->cookies->get('form_event_fieldorder', '0');
		$ordertype = $this->params->cookies->get('form_event_ordertype', '0');
		$filter = $this->params->cookies->get('form_event_filter', 'a:0:{}');
		$i_GroupBy = $this->params->cookies->get('form_event_groupby', '0');
		//		$filter ? $filter : 'a:0:{}';
		$filter = unserialize($filter);
		$this->html->groupby = $i_GroupBy;
		$this->html->orderid = $fieldorder;
		$this->html->ordertype = $ordertype ? 'ASC' : 'DESC';
		$this->html->limit = $limit?$limit:1;
		$this->html->pageno = $currentpage;
		$this->html->filters = $filter;
		$this->html->types = $this->_model->getAllType();
		$user = new Qss_Model_Admin_User();
		$this->html->users = $user->a_fGetAllNormal($this->_user->user_id);
		$this->html->recordcount = $this->_model->countAll($this->_user->user_id,$currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC',$filter);
		$this->html->events = $this->_model->getAll($this->_user->user_id,$currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC',$i_GroupBy,$filter);
	}
	public function editAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$user = new Qss_Model_Admin_User();
		$this->html->users = $user->a_fGetAllNormal($this->_user->user_id);
		$this->html->event = $this->_model->getByID($id);
		$this->html->types = $this->_model->getAllType();
		$this->html->times = $this->_model->getEventTimes($id);
		$this->html->members = $this->_model->getEventMembers($id);
	}
	public function referAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$this->html->refers = $this->_model->getFormRefer($id);
		$this->html->id = $id;
	}
	public function referReloadAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$fid = $this->params->requests->getParam('fid',0);
		$objid = $this->params->requests->getParam('objid',0);
		echo $this->views->Instance->Object->GridEvent($id,$fid,$objid);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @return void
	 */
	public function typeAction ()
	{
		//$this->html->types = $this->_model->getAllType();
		$this->html->listview = $this->views->Common->TreeView($this->_model,'/user/event/type');
		$this->html->pager = '';

	}
	public function saveAction ()
	{
		$params = $this->params->requests->getParams();
		$params['createdid'] = $this->_user->user_id;
		$service = $this->services->Event->Check($this->_user->user_id,$params['id'],1);
		if($service->getData())
		{
			$service = $this->services->Event->Save($params);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @return void
	 */
	public function typeEditAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$this->html->eventtype = $this->_model->getType($id);
		$this->html->types = $this->_model->getAllType($id);
	}
	public function typeSaveAction ()
	{
		$params = $this->params->requests->getParams();
		$service = $this->services->Event->Type->Save($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function typeDeleteAction ()
	{
		$id = $this->params->requests->getParam('id');
		$service = $this->services->Event->Type->Delete($id);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function createAction ()
	{
		$elid = $this->params->requests->getParam('elid', 0);
		$ifid = $this->params->requests->getParam('ifid', 0);
		$etid = $this->params->requests->getParam('etid', 0);
		$eventtype = $this->params->requests->getParam('eventtype', 0);
		$eventid = $this->params->requests->getParam('eventid', 0);
		$stepno = $this->params->requests->getParam('stepno', 0);
		$user = new Qss_Model_Admin_User();
		$this->html->users = $user->a_fGetAllNormal($this->_user->user_id);
		$this->html->activititype = $this->_model->getAllType();
		$this->html->elid  = $elid;
		$this->html->ifid = $ifid;
		$this->html->eventtype = $eventtype;
		$this->html->stepno = $stepno;
		$this->html->eventid = $eventid;
		$this->html->name = '';
		$this->html->date = date('d-m-Y');
		$this->html->stime = '7:30';
		$this->html->etime = '17:30';
		$this->html->status = 0;
		if($elid)
		{
			$dataSQL = $this->_model->getEventLogByID($elid);
			//print_r($dataSQL);die;
			if($dataSQL)
			{
				$this->html->ifid = $dataSQL->IFID;
				$this->html->eventtype = $dataSQL->ETID;
				$this->html->date = Qss_Lib_Date::mysqltodisplay($dataSQL->Date);
				$this->html->stime = $dataSQL->STime;
				$this->html->etime = $dataSQL->ETime;
				$this->html->name = $dataSQL->Note;
				$this->html->status = $dataSQL->Status;
				$this->html->stepno = $dataSQL->StepNo;
			}
		}
		elseif($eventid)
		{
			$dataSQL = $this->_model->getByID($eventid);
			$eventtime = $this->_model->getEventTimeByID($etid);
			//print_r($eventtime);die;
			if($dataSQL)
			{
				$this->html->eventtype = $dataSQL->EventType;
				$this->html->name = $dataSQL->Title;
				$this->html->date = date('d-m-Y');
				$this->html->stime = @$eventtime->STime;
				$this->html->etime = @$eventtime->ETime;
				$this->html->status = 0;
			}
		}
	}
	public function quickAction()
	{
		$params = $this->params->requests->getParams();
		$params['uid'] = $this->_user->user_id;
		$service = $this->services->Event->Log->Save($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function gridAction ()
	{
		if ( $this->params->requests->isAjax() )
		{
			$this->params->responses->clearBody();
			$i_CurrentPage = (int) $this->params->requests->getParam('pageno', 1);
			$i_PerPage = (int) $this->params->requests->getParam('perpage', 20);
			$i_FieldOrder = (int) $this->params->requests->getParam('fieldorder', 0);
			$i_OrderType = $this->params->cookies->get('form_event_ordertype', 0);
			$i_GroupBy = $this->params->requests->getParam('groupby', 0);
			if ( $i_FieldOrder != 0 )
			{
				$i_OrderType = !$i_OrderType;
			}
			else
			{
				$i_FieldOrder = $i_FieldOrder ? $i_FieldOrder : $this->params->cookies->get('form_event_fieldorder', 0);
			}
			$a_Filter = $this->a_fGetFilter();
			$this->params->cookies->set('form_event_currentpage', $i_CurrentPage);
			$this->params->cookies->set('form_event_limit', $i_PerPage);
			$this->params->cookies->set('form_event_filter', serialize($a_Filter));
			$this->params->cookies->set('form_event_ordertype', $i_OrderType);
			$this->params->cookies->set('form_event_fieldorder', $i_FieldOrder);
			$this->params->cookies->set('form_event_groupby', $i_GroupBy);
			$this->html->groupby = $i_GroupBy;
			$this->html->orderid = $i_FieldOrder;
			$this->html->ordertype = $i_OrderType ? 'ASC' : 'DESC';
			$this->html->limit = $i_PerPage?$i_PerPage:1;
			$this->html->pageno = $i_CurrentPage;
			$this->html->recordcount = $this->_model->countAll($this->_user->user_id,$i_CurrentPage, $i_PerPage, $i_FieldOrder, $i_OrderType ? 'ASC' : 'DESC',$a_Filter);
			$this->html->events = $this->_model->getAll($this->_user->user_id,$i_CurrentPage, $i_PerPage, $i_FieldOrder, $i_OrderType ? 'ASC' : 'DESC',$i_GroupBy,$a_Filter);
		}
		$this->setLayoutRender(false);
	}
	public function objectAction()
	{
		$id = $this->params->requests->getParam('id',0);
		$this->html->event = $this->_model->getByID($id);
		$this->setLayoutRender(false);
	}
	public function objectLoadAction()
	{
		$fid = $this->params->requests->getParam('fid',0);
		$object = new Qss_Model_System_Object();
		$objects = $object->a_fGetByForm($fid);
		$option = '';
		foreach($objects as $data)
		{
			$option .= '<option value="'.$data->ObjectCode.'">'.$data->ObjectName;
		}
		echo $option;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function importAction ()
	{
		$id = $this->params->requests->getParam('eventid', 0);
		$fid = $this->params->requests->getParam('fid', 0);
		$deptid = Qss_Register::get('userinfo')->user_dept_id;
		$objid = $this->params->requests->getParam('objid', 0);

		$currentpage = (int) $this->params->cookies->get('object_' . $objid . '_currentpage', 1);
		$currentpage = $currentpage ? $currentpage : 1;
		$limit = $this->params->cookies->get('object_' . $objid . '_limit', 20);
		$fieldorder = $this->params->cookies->get('object_' . $objid . '_fieldorder', '0');
		$ordertype = $this->params->cookies->get('object_' . $objid . '_ordertype', '0');
		$filter = $this->params->cookies->get('object_' . $objid . '_filter', 'a:0:{}');
		$filter = unserialize($filter);
		$i_GroupBy = $this->params->cookies->get('object_' . $objid . '_groupby', '0');
		$form = new Qss_Model_Form();
		$form->v_fInit($fid, $deptid, Qss_Register::get('userinfo')->user_id);
		$object = $form->o_fGetObjectById($objid);
		$sql = $object->sz_fGetSQL($id, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC', $filter,$i_GroupBy);
		$this->html->listview = $this->views->Instance->Object->GridImport($sql, $form, $object, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC',$i_GroupBy);
		$this->html->searchform = $this->views->Instance->Object->SearchImport($form, $object, $filter);
		$this->html->pager = $this->views->Instance->Object->ImportPager($sql, $object, $currentpage, $limit,$i_GroupBy);
		$this->html->form = $form;
		$this->html->id = $id;
		$this->html->object = $object;
	}
	public function importReloadAction ()
	{
		if ( $this->params->requests->isAjax() )
		{
			$user = $this->params->registers->get('userinfo', null);
			$this->params->responses->clearBody();
			$id = $this->params->requests->getParam('eventid', 0);
			$fid = $this->params->requests->getParam('fid', 0);
			$deptid = $user->user_dept_id;
			$objid = $this->params->requests->getParam('objid', 0);

			$currentpage = $this->params->requests->getParam('pageno', 1);
			$limit = $this->params->requests->getParam('perpage', 20);

			$fieldorder = $this->params->requests->getParam('fieldorder', 0);
			$ordertype = $this->params->cookies->get('object_' . $objid . '_ordertype', 0);

			$i_GroupBy = $this->params->requests->getParam('groupby', 0);

			$form = new Qss_Model_Form();
			$form->v_fInit($fid, $deptid, Qss_Register::get('userinfo')->user_id);
			$object = $form->o_fGetObjectById($objid);

			if ( $ordertype != 0 )
			{
				$ordertype = !$ordertype;
			}
			else
			{
				$ordertype = $ordertype ? $ordertype : $this->params->cookies->get('object_' . $objid . '_fieldorder', 0);
			}
			$filter = $this->a_fGetFilter();
			$this->params->cookies->set('object_' . $objid . '_currentpage', $currentpage);
			$this->params->cookies->set('object_' . $objid . '_limit', $limit);
			$this->params->cookies->set('object_' . $objid . '_filter', serialize($filter));
			$this->params->cookies->set('object_' . $objid . '_ordertype', $ordertype);
			$this->params->cookies->set('object_' . $objid . '_fieldorder', $fieldorder);
			$this->params->cookies->set('object_' . $objid . '_groupby', $i_GroupBy);
			$sql = $object->sz_fGetSQL($id, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC', $filter,$i_GroupBy);
			echo $this->views->Instance->Object->ImportPager($sql, $object, $currentpage, $limit,$i_GroupBy);
			echo $this->views->Instance->Object->GridImport($sql, $form, $object, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC',$i_GroupBy);
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function importImportAction ()
	{
		$arrIOID = $this->params->requests->getParam('ioidlist', '');
		$arrFID = $this->params->requests->getParam('fidlist', '');
		$arrIOID = explode(',', $arrIOID);
		$arrFID = explode(',', $arrFID);
		$id = $this->params->requests->getParam('eventid', 0);
		$service = $this->services->Event->Import($id, $arrIOID, $arrFID);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function actionAction ()
	{
		$id = $this->params->requests->getParam('id', 0);
		$action = $this->params->requests->getParam('action', 0);
		$service = $this->services->Event->Action($id,$action);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deleteAction ()
	{
		$id = $this->params->requests->getParam('id', 0);
		$service = $this->services->Event->Check($this->_user->user_id,$id,1);
		if($service->getData())
		{
			$this->_model->delete($id);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function referDeleteAction ()
	{
		$id = $this->params->requests->getParam('id', 0);
		$ifid = $this->params->requests->getParam('ifid', 0);
		$ioid = $this->params->requests->getParam('ioid', 0);
		$service = $this->services->Event->Check($this->_user->user_id,$id,1);
		if($service->getData())
		{
			$this->_model->deleteRefer($id,$ifid,$ioid);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
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
	public function noteSaveAction ()
	{
		$elid = $this->params->requests->getParam('elid', 0);
		$note = $this->params->requests->getParam('note', '');
		$id = $this->_model->getEventByLID($elid);
		$service = $this->services->Event->Check($this->_user->user_id,$id,1);
		if($service->getData())
		{
			$service = $this->services->Event->Log->Note($elid,$note);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function downloadAction ()
	{
		$id = $this->params->requests->getParam('id');
		$dataSQL  = $this->_model->getType($id);
		if($dataSQL)
		{
			$file = QSS_DATA_DIR . '/documents/event/' . $dataSQL->TypeID . '.' . $dataSQL->File;
			if ( file_exists($file) )
			{
				header("Content-type: application/force-download");
				header("Content-Transfer-Encoding: Binary");
				header("Content-length: " . filesize($file));
				header("Content-disposition: attachment; filename=\"" . $dataSQL->TypeName . '.' . $dataSQL->File ."\"");
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
		$dataSQL  = $this->_model->getType($id);
		if($dataSQL)
		{
			$file = QSS_DATA_DIR . '/documents/event/' . $dataSQL->TypeID . '.' . $dataSQL->File;
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