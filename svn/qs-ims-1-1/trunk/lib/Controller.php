<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_Controller extends Qss_Controller
{

	/**
	 *
	 * @var int
	 */
	protected $i_SecurityLevel = 15;
	
	protected $_user;
	
	protected $_mobile = false;
	
	/**
	 *
	 * @return void
	 */
	public function init ()
	{
		$this->params->responses->setHeader('', 'Content-Type: text/html; charset=utf-8');
		$userinfo = $this->params->sessions->get('userinfo', null);
		$userid = $this->params->cookies->get('user_id', '');
		$pass = $this->params->cookies->get('pass_md5', '');
		$popup = $this->params->requests->getParam('popup');
		if ( (!$userinfo || !($userinfo instanceof Qss_Model_UserInfo)) && (!$userid || !$pass))
		{
			if ( !$this->params->requests->isAjax())
			{
				if(!$popup)
				{
					$this->params->cookies->set('url', $this->params->requests->getRequestUri());
				}
				$this->redirect('/user/index/loginform');
			}
			else
			{
				$retval = array('redirect'=>$this->params->requests->getBaseUrl() . '/user/index/loginform');
				echo Qss_Json::encode($retval); 
			}
			die;
		}
		if ( !$userinfo || !($userinfo instanceof Qss_Model_UserInfo))
		{
			$dept = $this->params->cookies->get('dept_id', 0);
			$lang = $this->params->cookies->get('lang', 'vn');
			$userinfo = new Qss_Model_UserInfo();
			$userinfo->user_name = $userid;
			$userinfo->user_password = $pass;
			$userinfo->user_dept_id = $dept;
			$userinfo->user_lang = $lang;
		}
		$service = $this->services->Security->UserLogin($userinfo->user_name, $userinfo->user_password, $userinfo->user_dept_id,$this->i_SecurityLevel,(php_sapi_name() != 'cli')?$userinfo->user_session_id:0);
		if ( !$service->getStatus() )
		{
			if ( !$this->params->requests->isAjax())
			{
				$this->redirect('/user');
			}
			else
			{
				$retval = array('redirect'=>$this->params->requests->getBaseUrl() . '/user/index/loginform');
				echo Qss_Json::encode($retval); 
			}
			die;
		}
		$service->getData()->user_lang = $userinfo->user_lang;
		Qss_Register::set('userinfo', $service->getData());
		Qss_Translation::getInstance()->setLanguage($userinfo->user_lang);
		if ( !$this->params->requests->isAjax())
		{
			$userinfo = $service->getData();
			$userinfo->logAccess($this->params->requests->getServer('REMOTE_ADDR',0), $this->params->requests->getRequestUri());
		}
		else
		{
			$this->setLayoutRender(false);
		}
		$this->_user = $service->getData();
		if($this->_user->user_session_id != Qss_Session::id() && $this->_user->user_name != 'sysAdmin' 
					&& strtolower($this->_user->user_name) != 'admin'
					&& strtolower($this->_user->user_name) != 'huybd')
		{
			$this->services->Security->UserLogout();
			if ( !$this->params->requests->isAjax())
			{
				if(!$popup)
				{
					$this->params->cookies->set('url', $this->params->requests->getRequestUri());
				}
				$this->redirect('/user/index/loginform?err=ERR_SESSION');
			}
			else
			{
				$retval = array('redirect'=>$this->params->requests->getBaseUrl() . '/user/index/loginform?err=ERR_SESSION');
				echo Qss_Json::encode($retval); 
			}
			die;
		}
		$this->html->baseURL = $this->params->requests->getBaseUrl();
		//print_r($_COOKIE);die;
		//check url and set cookie
		if ( !$this->params->requests->isAjax())
		{
			$url = $this->params->requests->getRequestUri();
			if($url == '/user/dashboard')
			{
				$code = 'M000';
			}
			else 
			{
				$util = new Qss_Model_Extra_Extra();
				$fid = $this->params->requests->getParam('fid');
				$ifid = $this->params->requests->getParam('ifid');
				$code = '';
				$lastpage = $this->params->cookies->get('lastActiveModule');
				if(($fid || $ifid) && strpos($url,'/user/import') !== 0 && strpos($url,'/admin/print') !== 0 && strpos($url,'/extra/print') !== 0)
				{
					if($ifid)
					{
						$iform = $util->getTable(array('*'),'qsiforms',array('IFID'=>$ifid),array(),100,1);
						if($iform)
						{
							$fid = $iform->FormCode; 
						}
					}
					if($fid)
					{
						$form = $util->getTable(array('*'),'qsforms',array('FormCode'=>$fid),array(),100,1);
						if($form)
						{
							$code = $form->FormCode;
                            $formName = ($userinfo->user_lang == 'vn')?'Name':'Name_'.$userinfo->user_lang;
                            $this->_title = $form->{$formName}  . ' - ' . $form->FormCode;
						}
					}
				}
				else
				{
					//$form = $util->getTable(array('*'),'qsforms',array(),array(),100,sprintf('class like %1$s','\'%' . mysql_real_escape_string($url.'%') . '\''),1);
					$db = Qss_Db::getAdapter('main');
                    $url_format = $db->escape(trim($url)) ;
                    $form = $util->getTable(
                    	array('*') // select
                        ,'qsforms' // from
                        ,"INSTR('{$url_format}',class) = 1 and ifnull(class, '') != ''"// ext where
                        ,array('length(class) DESC') // order"class <> '{$url_format}'", 
                        ,100 //limit
                        
                        ,1    );// return fetchone
					if($form)
					{
						$code = $form->FormCode;
                        $formName = ($userinfo->user_lang == 'vn')?'Name':'Name_'.$userinfo->user_lang;
                        $this->_title = $form->{$formName}  . ' - ' . $form->FormCode;
					}
				}
			}
			if($code && !$popup)
			{
				$this->params->cookies->set('lastActiveModule',$code);
				$this->params->cookies->set($code,$url);
				//print_r($_COOKIE);die;
			}
		}
		$this->_mobile = $userinfo->user_mobile;
		//$this->_mobile = 1;
		if($this->_mobile)
		{
			$this->headLink($this->params->requests->getBasePath() . '/css/template.css');
			$this->headLink($this->params->requests->getBasePath() . '/css/form.css');
			$this->headLink($this->params->requests->getBasePath() . '/css/layout.css');
			$this->headScript($this->params->requests->getBasePath() . '/js/common.js');
			$this->headScript($this->params->requests->getBasePath() . '/js/hightchart/highcharts.js');
			$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/mobile.php';
		}
	}
	public function a_fGetFilter()
	{
		$params = $this->params->requests->getParams();
		$retval = array();
		foreach($params as $key=>$value)
		{
			if(substr($key,0,7) == 'filter_' && $value !== '')
			{
				$retval[substr($key,7,strlen($key))] = $value;
			}
		}
		return $retval;
	}
	public function b_fCheckRightsOnForm($form, $rights = 0,$status = 0)
	{
		$formrigths = $this->getFormRights($form, $status);
		if(!($formrigths & $rights) || !$form->FormCode)
		{
			$message = sprintf('Bạn không có quyền truy cập dữ liệu "%1$s" này.',$form->sz_Name);
			if($this->params->requests->isAjax())
			{
				$datatype = $this->params->requests->getParam('datatype');
				if($datatype == 'HTML')
				{
					$this->html->setHtml(QSS_DATA_BASE.'/access.phtml');
					$this->html->message = $message;
				}
				else
				{
					echo Qss_Json::encode(array('error' => true,'message'=>$message));
				}
			}
			else
			{
				$this->html->setHtml(QSS_DATA_BASE.'/access.phtml');
				$this->html->message = $message;
			}
			return false;
		}
		return true;
	}
	public function b_fCheckFormByUrl($url)
	{
		$form = new Qss_Model_Form();
		$dataSQL = $form->getByUrl($url);
		if($dataSQL)
		{
			$form->init($dataSQL->FormCode,$this->_user->user_dept_id,$this->_user->user_id);	
			 return $this->b_fCheckRightsOnForm($form, 4);
		}
		return true;
	}
	public function getFormRights($form, $status = 0)
	{
		$formright = $form->i_fGetRights($this->_user->user_group_list, $status);
		return $formright;
	}
	public function getFIDRights($fid)
	{
		$form = new Qss_Model_Form();
		$form->init($fid, $this->_user->user_dept_id, $this->_user->user_id);
		return $this->getFormRights($form);
	}
	public function checkApproveRights($form,$stepno)
	{
		$step = new Qss_Model_System_Step($form->i_WorkFlowID);
		$step->v_fInitByStepNumber($stepno);
		return $step->checkApproveRights($this->_user->user_id);
	}
}
?>