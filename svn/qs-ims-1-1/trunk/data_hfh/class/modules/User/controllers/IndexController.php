<?php
/**
 *
 * @author HuyBD
 *
 */
class User_IndexController extends Qss_Controller
{

	public function init ()
	{
		$lang = $this->params->cookies->get('lang', 'vn');
		Qss_Translation::getInstance()->setLanguage($lang);
		$this->params->responses->setHeader('', 'Content-Type: text/html; charset=utf-8');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{
		$userinfo = $this->params->sessions->get('userinfo');
		$userid = $this->params->cookies->get('user_id', '');
		$pass = $this->params->cookies->get('pass_md5', '');
		$dept = $this->params->cookies->get('dept_id', 0);
		if ( (!$userinfo || !($userinfo instanceof Qss_Model_UserInfo)) && (!$userid || !$pass || !$dept))
		{
			$this->redirect('/user/index/loginform');
		}
		if ( !$userinfo || !($userinfo instanceof Qss_Model_UserInfo))
		{
			$userinfo = new Qss_Model_UserInfo();
			$userinfo->user_name = $userid;
			$userinfo->user_password = $pass;
			$userinfo->user_dept_id = $dept;
		}
		$loginservice = $this->services->Security->UserLogin($userinfo->user_name, $userinfo->user_password, $userinfo->user_dept_id,15);
		$this->params->registers->set('userinfo', $loginservice->getData());
		$loginret = $loginservice->getStatus();
		$this->redirectLogin($loginret);
		$this->setHtmlRender(false);
		$this->setLayoutRender(true);
	}

	/**
	 *
	 * @return void
	 */
	public function loginAction ()
	{
		$userinfo = $this->params->sessions->get('userinfo');
		if ( $userinfo )
		{
			$userinfo = new Qss_Model_UserInfo();
		}
		$savepass = $this->params->requests->getParam('epSavePassword', 0);
		$userid = $this->params->requests->getParam('epUserID', 0);
		$pass = $this->params->requests->getParam('epPassWord', '');
		$dept = $this->params->requests->getParam('epDepartmentID', '');
		$lang = $this->params->requests->getParam('epLanguage', '');
		if ( !$userid )
		$userid = $userinfo ? $userinfo->user_name : "";
		if ( !$userid )
		$userid = $this->params->cookies->get('user_id', 0);
		if ( !$dept )
		$dept = $userinfo ? $userinfo->user_dept_id : 0;
		if ( !$dept )
		$dept = $this->params->cookies->get('dept_id', 0);
		if ( !$lang )
		$lang = $this->params->cookies->get('lang', 'vn');
		if ( !$pass )
		$pass = $userinfo->user_password;
		else
		{
			$pass = Qss_Util::hmac_md5($pass, 'EP');
		}
		if ( !$pass )
		$pass = $this->params->cookies->get('pass_md5', '');
		$loginservice = $this->services->Security->UserLogin($userid, $pass, $dept,15);
		$this->params->sessions->set('userinfo', $loginservice->getData());
		$this->params->registers->set('userinfo', $loginservice->getData());
		$loginret = $loginservice->getStatus();
		//		if(!($loginret & 1))
		{
			$loginservice->getData()->user_lang = $lang;
		}
		if($savepass && $loginret)
		{
			$this->params->cookies->set('user_id', $userid);
			$this->params->cookies->set('pass_md5', $pass);
		}
		$this->params->cookies->set('dept_id', $dept);
		$this->params->cookies->set('lang', $lang);
		$this->redirectLogin($loginret);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function loginformAction ()
	{
		$this->services->Security->UserLogout();
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/login.php';
		$dept = new Qss_Model_Admin_Department();
		$arr = array();
		$depts = $dept->getAll();
		foreach ($depts as $item)
		{
			if($item->LegalEntity)
			{
				$new = new stdClass();
				$new->ID = $item->DepartmentID;
				$new->Value = $item->Name;
				$new->Parent = $item->ParentID;
				$arr[] = $new;
			}
		}
		$this->html->departments = $arr;
		$lang = new Qss_Model_System_Language();
		$this->html->languages = $lang->getAll(1);
		$this->html->deptid = $this->params->cookies->get('dept_id', 0);
		$this->html->lang = $this->params->cookies->get('lang', 'vn');
		$this->html->err = $this->params->requests->getParam('err','');
	}

	public function logoutAction ()
	{
		$this->services->Security->UserLogout();
		$this->redirect('/user/index/loginform');
	}

	/**
	 *
	 * @return void
	 */
	public function redirectLogin ($loginret)
	{
		if ( $loginret )
		{

			$lasturl = $this->params->cookies->get('url');
			if ( $lasturl )
			{
				$this->params->cookies->set('url', null);
				$this->redirect($lasturl);
			}
			elseif ( $loginret & 1 )
			{
				$this->redirect('/system'); // header('Location: sysAdmin/Forms.php?type=1');
			}
			else
			{
				$this->redirect('/user/dashboard');
			}
		}
		else
		{
			$this->params->cookies->set('user_id', '');
			$this->params->cookies->set('pass_md5', '');
			if(count($_POST))
			{
				$this->redirect('/user/index/loginform?err=ERR_AUTHENTICATE');
			}
			else
			{
				$this->redirect('/user/index/loginform');
			}
		}
	}
}
?>