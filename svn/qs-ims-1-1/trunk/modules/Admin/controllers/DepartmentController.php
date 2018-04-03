<?php
/**
 *
 * @author HuyBD
 *
 */
class Admin_DepartmentController extends Qss_Lib_Controller
{
	protected $_model;
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		$this->i_SecurityLevel = 2;
		$this->_model = new Qss_Model_Admin_Department();
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/admin-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{
		/*$user = $this->params->registers->get('userinfo');
		 $o_DepartmentModel = new Qss_Model_Admin_Department();
		 $this->html->Department = $o_DepartmentModel->getSubDepartments($user->user_dept_id);*/
		//$this->headScript($this->params->requests->getBasePath() . '/js/popup-select.js');
		//$fid = $this->params->requests->getParam('fid', 0);
		//		$filter ? $filter : 'a:0:{}';
		//if ( $fid )
		{
			//$o_Form = new Qss_Model_Form();
			//$o_Form->v_fInit($fid, $user->user_dept_id, $user->user_id);
			//if($this->b_fCheckRightsOnForm($o_Form,4))
			{
				$this->html->listview = $this->views->Common->TreeView($this->_model,'/user/department');
				$this->html->pager = '';
			}
		}
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function reloadAction ()
	{
		/*$user = $this->params->registers->get('userinfo');
		 $szSearch = $this->params->requests->getParam('search', 0);
		 $o_DepartmentModel = new Qss_Model_Admin_Department();
		 echo $this->views->Common->List($o_DepartmentModel->getSubDepartments($user->user_dept_id, $szSearch), 'DepartmentID', 'Name');
		 $this->setHtmlRender(false);
		 $this->setLayoutRender(false);*/
	}

	/**
	 * Edit page
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function editAction ()
	{
		$deptid = $this->params->requests->getParam('deptid', 0);
		$dept = new Qss_Model_Admin_Department();
		$dept->init($deptid);
		$this->html->dept = $dept;
		$this->html->depts = $dept->getAll($deptid);
	}

	/**
	 * Save department
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function saveAction ()
	{
		//$user = $this->params->registers->get('userinfo');
		$deptid = $this->params->requests->getParam('deptid', 0);
		$params = $this->params->requests->getParams();
		//$params['intParentID'] = $user->user_dept_id;
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Admin->Department->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>