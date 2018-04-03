<?php
/**
 *
 * @author HuyBD
 *
 */
class Static_M006Controller extends Qss_Lib_Controller {
	/**
	 *
	 * @return unknown_type
	 */
	public function init() {
		$this->i_SecurityLevel = 2;
		$this->_model = new Qss_Model_Admin_Group();
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
		/*$userinfo = $this->params->registers->get('userinfo');
			 $user = new Qss_Model_Admin_Group();
		*/
		$this->html->data = $this->_model->getAll();
		// print_r($this->html->data);die;
	}

	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function reloadAction() {
		/*$userinfo = $this->params->registers->get('userinfo');
			 $szSearch = $this->params->requests->getParam('search', 0);
			 $group = new Qss_Model_Admin_Group();
			 echo $this->views->Common->List($group->getAll($userinfo->user_dept_id, $szSearch), 'GroupID', 'GroupName');
			 $this->setHtmlRender(false);
		*/
	}

	/**
	 * Edit page
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function editAction() {
		$userinfo = $this->params->registers->get('userinfo');
		$groupid = $this->params->requests->getParam('groupid', 0);
		// print_r($this->params->requests->getParam('groupid', 0));die;
		$group = new Qss_Model_Admin_Group();
		$form = new Qss_Model_System_Form();
		$group->init($groupid);
		$module = $group->getRightEle();
		$moduleStep = $group->getStepRights();
		$moduleInActive = array();

		// Không muốn đổi query
		foreach ($module as $key => $item) {
			if ((int) $item->Effected == 0) {
				unset($module[$key]);
				$moduleInActive[] = $item->FormCode;
			}
		}

		// Không muốn đổi query
		foreach ($moduleStep as $key => $item) {
			if ((int) $item->Effected == 0) {
				unset($moduleStep[$key]);
			}

			if (in_array($item->FormCode, $moduleInActive) && isset($moduleStep[$key])) {
				unset($moduleStep[$key]);
			}
		}

		$this->html->group = $group;
		$this->html->module = $module;
		$this->html->moduleStep = $moduleStep;
		$this->html->users = $group->getUsers();
		//$this->html->recordRights = $group->getRecordRights();
		$this->html->users = $group->getUsers();
		$dept = new Qss_Model_Admin_Department();
		$this->html->depts = $dept->getAll();
		// print_r($this->html->depts);die;
	}

	/**
	 * Save department
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function saveAction() {
		$userinfo = $this->params->registers->get('userinfo');
		$groupd = $this->params->requests->getParam('groupd', 0);
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax()) {
			//$params['intDepartmentID'] = $userinfo->user_dept_id;
			$service = $this->services->Admin->Group->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>