<?php
/**
 *
 * @author HuyBD
 *
 */
class User_StatisticController extends Qss_Lib_Controller
{

	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/statistic-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{

	}
	public function userAction ()
	{
		$deptid = $this->params->requests->getParam('detpid', 0);
		$user = new Qss_Model_Admin_User();
		$users = $user->a_fGetAllByDeptID($deptid);
		$ret = "<option value='0'>--Chọn tất cả---";
		foreach ($users as $user)
		{
			$ret .= "<option value='{$user->UID}'>{$user->UserName}";
		}
		echo $ret;
		$this->setHTMLRender(false);
		$this->setLayoutRender(false);
	}

	public function stepAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$fid = $this->params->requests->getParam('fid', 0);
		$form = new Qss_Model_Form();
		$form->v_fInit($fid, $userinfo->user_dept_id, $userinfo->user_id);
		$step = new Qss_Model_System_Step($form->i_WorkFlowID);
		$steps = $step->getAll();
		$ret = "<option value='0'>--Chọn tất cả---";
		foreach ($steps as $step)
		{
			$ret .= "<option value='{$step->SID}'>{$step->Name}";
		}
		echo $ret;
		$this->setHTMLRender(false);
		$this->setLayoutRender(false);
	}
	public function notifyAction()
	{
		$notify = new Qss_Bin_Notify_Notify();
		$this->html->forms = $notify->showNotify($this->_user);
		//$this->setHtmlRender(false);
		//$this->setLayoutRender(false)
	}
	public function warningAction()
	{
		$notify = new Qss_Bin_Notify_Notify();
		$this->html->forms = $notify->showMessage($this->_user);
	}
	public function warning1Action()
	{
		$pageno = $this->params->requests->getParam('pageno');
		$pageno = (int)$pageno;
		$this->html->forms = $this->_user->getWarning($pageno);
		$this->html->user_id = $user->user_id;
		$this->setLayoutRender(false);
	}
	public function eventAction()
	{
		$notify = new Qss_Bin_Notify_Notify();
		$this->html->forms = $notify->showEvent($this->_user);
	}
	public function tagUserAction()
	{
		$retval = array();
		$tag = $this->params->requests->getParam('tag');
		$user = new Qss_Model_Admin_User();
		$dataSQL = $user->a_fGetAllNormal(-1,$tag);
		foreach ($dataSQL as $item)
		{
			$retval[] = array('id'=>$item->UID,'value'=>$item->UserName . ' (' . $item->EMail . ')');
		}
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function tagFormAction()
	{
		$retval = array();
		$tag = $this->params->requests->getParam('tag');
		$modules = $this->_user->a_fGetAllModule();
		if($tag)
		{
			foreach ($modules as $item)
			{
				if(stripos($item->Name, $tag) === false || $item->class)
				{
					continue;
				}
				$retval[] = array('id'=>$item->FID,'value'=>$item->Name);
			}
		}
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function tagModuleAction()
	{
		$retval = array();
		$tag = $this->params->requests->getParam('tag');
		$modules = Qss_Session::get('modules');
		if(!$modules)
		{
			$modules = $this->_user->a_fGetAllModule();
			$modules = Qss_Session::set('modules',$modules);
		}
		if($tag)
		{
			foreach ($modules as $item)
			{
				if(mb_stripos($item->Code, $tag) === false && mb_stripos($item->Name, $tag) === false)
				{
					continue;
				}
				if($item->class && $item->Type != Qss_Lib_Const::FORM_TYPE_PROCESS)
				{
					$id = $item->class;
				}
				else
				{
					$id = '/user/form?fid='.$item->FID;
				}
				$retval[] = array('id'=>$id,'value'=>$item->Code . '-' . $item->Name);
			}
		}
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function quickaccessAction()
	{
		$dashboadmodel = new Qss_Model_Dashboad();
		$userinfo = $this->_user;
		$this->html->userinfo = $userinfo;
		$this->html->blocklist = $dashboadmodel->getQuickAccess($this->_user);
	}
	public function detailuserquickaccessAction()
	{
		$dashboadmodel = new Qss_Model_Dashboad();
		echo $this->views->Common->Quick($this->_user);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function settingquickaccessAction()
	{
		$dashboadmodel = new Qss_Model_Dashboad();
		$this->html->blocklist =$dashboadmodel->getAllModule($this->_user);
	}
	public function savedashboadAction()
	{
		$BlockID = $this->params->requests->getParam('BlockID');
		$check = (bool)$this->params->requests->getParam('checked');
		$model = new Qss_Model_Dashboad();
		$model->saveDashboad($BlockID,$this->_user->user_id, $check);
		$retval = array('error'=>false,'message'=>'');
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function savequickaccessAction ()
	{
		$fid = $this->params->requests->getParam('fid');
		$check = (bool)$this->params->requests->getParam('checked');
		$model = new Qss_Model_Dashboad();
		$model->saveQuickAccess($fid,$this->_user->user_id, $check);
		$retval = array('error'=>false,'message'=>'');
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function saveallquickaccessAction ()
	{
		$check = (bool)$this->params->requests->getParam('checked');
		$model = new Qss_Model_Dashboad();
		$model->saveAllQuickAccess($this->_user->user_id, $check);
		$retval = array('error'=>false,'message'=>'');
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function savequickreportAction ()
	{
		$urid = $this->params->requests->getParam('urid');
		$check = (bool)$this->params->requests->getParam('checked');
		$model = new Qss_Model_Dashboad();
		$model->saveQuickReport($urid,$this->_user->user_id, $check);
		$retval = array('error'=>false,'message'=>'');
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function quickreportsAction()
	{
		$report = new Qss_Model_Report();
		$reports = $report->getReportsByUser($this->_user->user_id,$this->_mobile);
		$this->html->reports = $reports;
	}
	public function detailuserquickreportsAction()
	{
		$urid = $this->params->requests->getParam('urid',0);
		$report = new Qss_Model_Report();
		$data = $report->getParams($this->_user->user_id,$urid);
		if($data)
		{
			$form = new Qss_Model_Form();
			$form->v_fInit($data->FID, $this->_user->user_dept_id, $this->_user->user_id);
			$params = unserialize($data->Params);
			$this->html->form = $form;
			$table_display =  $params['table_display'];
			$chart_display =  $params['chart_display'];
			$chart =  $params['chart'];
			$tructung =  $params['tructung'];
			$status =  $params['status'];
			$filter = array();
			$agr = array();
			$order = array();
			foreach($params as $key=>$value)
			{
				if(substr($key,0,7) == 'filter_' && $value != '')
				{
					$filter[substr($key,7,strlen($key))] = $value;
				}
				if(substr($key,0,4) == 'agr_' && $value != '')
				{
					$agr[substr($key,4,strlen($key))] = $value;
				}
				if(substr($key,0,6) == 'order_' && $value != '')
				{
					$order[substr($key,6,strlen($key))] = $value;
				}
			}
			$tructung = $params['tructung'];
			$limit = $params['limit'];
			$dataSQL = $form->getReport($table_display,$chart_display,$filter,$agr,$order,$limit,$status);
			$this->html->list = $dataSQL;
			$this->html->table_display = is_array($table_display)?$table_display:array();
			$this->html->chart_display = $chart_display;
			$this->html->chart = $chart;
			$this->html->agr = $agr;
			$this->html->tructung = $tructung;
				
			$this->html->params = $params;
			$this->html->data = $data;
			$this->html->urid = $urid;
			$this->html->fid = $data->FID;
			if($this->_user->user_lang == 'vn')
			{
				$this->html->reportname = $params['reportname'];
			}
			else
			{
				$this->html->reportname = $params['reportname_'.$this->_user->user_lang];
			}
			$this->html->showtable = $params['showtable'];
			$this->html->fcode = $form->FormCode;
		}
		$this->setLayoutRender(false);
	}
	public function settingquickreportsAction()
	{
		$dashboadmodel = new Qss_Model_Dashboad();
		$this->html->blocklist =$dashboadmodel->getReportModule($this->_user);
	}
}
?>