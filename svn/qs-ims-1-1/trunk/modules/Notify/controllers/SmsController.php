<?php
/**
 *
 * @author ThinhTuan
 * @todo: Chua cho xoa member tao lich
 */
class Notify_SmsController extends Qss_Lib_Controller
{
	public $_common;
	public $_notify;
	public $_params;

	public function init ()
	{
		$this->i_SecurityLevel = 15;
		parent::init();
		$this->_common = new Qss_Model_Extra_Extra();
		$this->_notify = new Qss_Model_Notify_Sms();
		$this->_params = $this->params->requests->getParams();
		$this->headScript($this->params->requests->getBasePath() . '/js/notify-sms.js');
	}
	public function indexAction()
	{
		$this->html->forms  = $this->getModules(true);
	}
	public function addAction()
	{

	}
	public function editAction()
	{
		$user                = new Qss_Model_Admin_User();
		$this->html->users   = $user->a_fGetAllNormal($this->_user->user_id);
		$this->html->times   = $this->_common->getTable(array('*'), 'qsfsmscalendars'
		, array('FormCode'=>$this->_params['fid']), array('FormCode'), 'NO_LIMIT');;
		$this->html->members = $this->_notify->getNotifyMembers($this->_params['fid']);
		$this->html->groups = $this->_notify->getNotifyAllGroups($this->_params['fid']);
		$this->html->sms = $this->_notify->getSMS($this->_params['fid']);
		$this->html->fid     = $this->_params['fid'];
		$this->html->action  = $this->_params['action'];
		$this->html->type  		= constant($this->_params['fid'].'::TYPE');
	}


	public function deleteAction()
	{
		$params = $this->params->requests->getParams();
		$service = $this->services->Notify->Sms->Delete($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function saveAction()
	{
		$params = $this->params->requests->getParams();
		$params['createdid'] = $this->_user->user_id;

		$service = $this->services->Notify->Sms->Save($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	private function getModules()
	{
		$filterArr = array();
		$existsArr = array();
		$allForms  = $this->_notify->getValidateModule();
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		foreach ($allForms as $form)
		{
			// check class exists in non-step module and report
			$folder = QSS_ROOT_DIR.'/bin/Notify/Sms/'.$form->FormCode;
			if(file_exists($folder))
			{
				$arrFile = scandir($folder);
				foreach($arrFile as $filename)
				{
					$file_parts = pathinfo($filename);
					if($file_parts['extension'] == 'php')
					{
						$classname = 'Qss_Bin_Notify_Sms_'.$form->FormCode.'_'.$file_parts['filename'];
						// add array element when has non-step module validation
						if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
						{
							$type = constant($classname.'::TYPE');
							$name = constant($classname.'::TITLE');
							$filterArr[] = array('FID'=>$classname,'Code'=>$form->FormCode,'Name'=>$form->{"Name".$lang}
							,'Type'=>$type,'Desc'=>$name,'FileName'=>$file_parts['filename']);
						}
					}
				}
			}
		}
		return $filterArr;
	}
	public function runAction ()
	{
		$fid = $this->params->requests->getParam('fid');
		$formcode = substr($fid,20,4);
		$form = new Qss_Model_Form();
		if($form->init($formcode,  $this->_user->user_dept_id,$this->_user->user_id))
		{
			//if($this->b_fCheckRightsOnForm($form,16))
			{
				$service = $this->services->Notify->Sms($form,fid);
				echo $service->getMessage();
			}
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function logAction ()
	{
		$pageno    = $this->params->requests->getParam('pageno',1);
		$deleteSel = $this->params->requests->getParam('delete_selected',0);
		$deleteAll = $this->params->requests->getParam('delete_all',0);
		$model     = new Qss_Model_Sms();


		if(!is_numeric($pageno))
		{
			$pageno = 1;
		}


		if($deleteSel)
		{
			$del = $this->params->requests->getParam('SMS');
			if(is_array($del) && count($del))
			{
				$model->deleteLog($del);
			}
		}

		if($deleteAll)
		{
			$model->deleteAllLog();
		}

		$this->html->list = $model->getLogs($pageno);
		$this->html->pagecount = ceil($model->countPage()->count);
		$this->html->pageno = $pageno;
	}
}
?>