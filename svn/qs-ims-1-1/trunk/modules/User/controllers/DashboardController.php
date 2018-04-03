<?php
/**
 *
 * @author HuyBD
 *
 */
class User_DashboardController extends Qss_Lib_Controller {

	/**
	 *
	 * @return unknown_type
	 */
	public function init() {
		parent::init();
		//$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->headLink($this->params->requests->getBasePath() . '/css/dashboad.css');
		$dashboadmodel = new Qss_Model_Dashboad();
		$userinfo = $this->_user;
		// var_dump($userinfo);die;
		$this->html->userinfo = $userinfo;
	}
}
?>