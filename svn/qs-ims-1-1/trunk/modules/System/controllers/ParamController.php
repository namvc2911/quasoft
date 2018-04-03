<?php
/**
 *
 * @author HuyBD
 *
 */
class System_ParamController extends Qss_Lib_Controller
{
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		$this->i_SecurityLevel = 1;
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/system-list.js');
	}
	public function indexAction()
	{
		$paramModel = new Qss_Model_System_Param();
		$this->html->params = $paramModel->getAllParams();
	}
	public function editAction()
	{
		$id = $this->params->requests->getParam('id','');
		$paramModel = new Qss_Model_System_Param();
		$this->html->param = $paramModel->getById($id, 0);
	}

	/**
	 * Save action that call via ajax
	 *
	 * @return void
	 */
	public function saveAction()
	{
		/* Use same name in databse */
		$a_Params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax())
		{
			$service = $this->services->System->Param->Save($a_Params,0);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deleteAction()
	{
		$id = $this->params->requests->getParam('PID','');
		$paramModel = new Qss_Model_System_Param();
		$paramModel->delete($id, 0);
		$retval = array('error'=>0);
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>