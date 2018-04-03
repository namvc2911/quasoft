<?php
/**
 *
 * @author HuyBD
 *
 */
class Admin_ParamController extends Qss_Lib_Controller
{
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		$this->i_SecurityLevel = 15;
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/admin-list.js');
	}
	public function indexAction()
	{
		$user = $this->params->registers->get('userinfo', null);
		$paramModel = new Qss_Model_System_Param();
		$this->html->params = $paramModel->getAllParams($user->user_dept_id);
	}
	public function editAction()
	{
		$user = $this->params->registers->get('userinfo', null);
		$id = $this->params->requests->getParam('id','');
		$paramModel = new Qss_Model_System_Param();
		$this->html->param = $paramModel->getById($id, $user->user_dept_id);
	}

	/**
	 * Save action that call via ajax
	 *
	 * @return void
	 */
	public function saveAction()
	{
		$user = $this->params->registers->get('userinfo', null);
		/* Use same name in databse */
		$a_Params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax())
		{
			$service = $this->services->System->Param->Save($a_Params,$user->user_dept_id);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>