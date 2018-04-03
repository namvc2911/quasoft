<?php
/**
 *
 * @author HuyBD
 *
 */
class Static_M005Controller extends Qss_Lib_Controller {

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
	 *
	 * @return void
	 */
	public function indexAction() {
		$userinfo = $this->params->registers->get('userinfo');

		$szSearch = $this->params->requests->getParam('search', 0);
		$user = new Qss_Model_Admin_User();

		$total = Qss_Model_Db::Table('qsusers');
		$total->select('count(1) as total');
		$total->where('UID != -1');
		$total->where('UID != 1');
		$total = $total->fetchOne();
		// print_r($total);die;
		$active = Qss_Model_Db::Table('qsusers');
		$active->select('count(1) as total');
		$active->where('UID != -1');
		$active->where('UID != 1');
		$active->where('IFNULL(isActive, 0) = 1');
		$active = $active->fetchOne();
		// print_r($active);die;
		$inactive = Qss_Model_Db::Table('qsusers');
		$inactive->select('count(1) as total');
		$inactive->where('UID != -1');
		$inactive->where('UID != 1');
		$inactive->where('IFNULL(isActive, 0) = 0');
		$inactive = $inactive->fetchOne();

		$this->html->total = $total ? $total->total : 0;
		$this->html->active = $active ? $active->total : 0;
		$this->html->inactive = $inactive ? $inactive->total : 0;
	}

	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function reloadAction() {
		$userinfo = $this->params->registers->get('userinfo');
		// print_r($userinfo);die;
		$szSearch = $this->params->requests->getParam('search', 0);
		$user = new Qss_Model_Admin_User();
		$this->html->user = $user->a_fGetAllNormal(0, $szSearch);
		// print_r($szSearch);die;
	}

	/**
	 * Edit page
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function editAction() {
		$userinfo = $this->params->registers->get('userinfo');
		// print_r($userinfo);die;
		$userid = $this->params->requests->getParam('userid', 0);
		$form = new Qss_Model_System_Form();
		$user = new Qss_Model_Admin_User();
		$menu = new Qss_Model_Menu();
		$user->init($userid);
		$alldept = $user->getGroupDept($userinfo->user_dept_id);
		$temp = array();
		foreach ($alldept as $dept) {
			$deptgroup = $user->getGroupByDept($dept->DepartmentID);
			$temp[$dept->Name] = $deptgroup;
		}
		//for ERP onlue
		$this->html->secure = $form->getSecureForm();
		//$this->html->workcenters = $user->getWorkCenters();
		$this->html->menus = $menu->getMainMenu();
		$this->html->user = $user;
		$this->html->deptgroup = $temp;
		$this->html->approverRigths = $user->getApproveRights();

	}

	/**
	 * Save user account
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function saveAction() {
		$userid = $this->params->requests->getParam('userid', 0);
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax()) {
			$service = $this->services->Admin->User->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deleteAction() {
		$userid = $this->params->requests->getParam('userid', 0);
		if ($this->params->requests->isAjax()) {
			$service = $this->services->Admin->User->Delete($userid);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>