<?php
/**
 *
 * @author HuyBD
 *
 */
class Static_M012Controller extends Qss_Lib_Controller {

	/**
	 *
	 * @return unknown_type
	 */
	public function init() {
		$this->i_SecurityLevel = 15;
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/admin-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
	}
	/**
	 * Change user
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function indexAction() {
		$userinfo = $this->params->registers->get('userinfo');
		$user = new Qss_Model_Admin_User();
		$menu = new Qss_Model_Menu();
		$user->init($userinfo->user_id);
		$this->html->user = $user;
		$this->html->menus = $menu->getMainMenu();
		$dashboadmodel = new Qss_Model_Dashboad();
		// print_r($dashboadmodel);die;
		$this->html->blocklist = $dashboadmodel->getReportModule($this->_user);
	}
	/**
	 * Change user
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function changeAction() {
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax()) {
			$service = $this->services->Admin->User->Change($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>