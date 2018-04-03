<?php
/**
 *
 * @author HuyBD
 *
 */
class User_MailController extends Qss_Lib_Controller
{
	protected $_model;
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		parent::init();
		$this->_model = new Qss_Model_Mail();
		$this->headScript($this->params->requests->getBasePath() . '/js/mail-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{
		$id = $this->params->requests->getParam('id');
		$ifid = $this->params->requests->getParam('ifid',0);
		$this->headScript($this->params->requests->getBasePath() . '/js/popup-select.js');
		$this->html->accounts = $this->_model->getAllAccount($this->_user->user_id);
		$cms = new Qss_Model_Admin_CMS();
		$this->html->cms = $cms->getCMSList($this->_user);
		if($id)
		{
			$event = new Qss_Model_Event();
			$this->html->event = $event->getByID($id);
		}
		if($ifid)
		{
			$form = new Qss_Model_Form();
			if($form->initData($ifid, $this->_user->user_dept_id))
			{
				$design = new Qss_Model_Admin_Design($this->_user->user_dept_id);
				$this->html->designs = $design->a_fGetDesignByForm($form->FormCode);
				$this->html->ifid = $form->i_IFID;
				$form->o_fGetMainObject()->initData($form->i_IFID, $form->i_DepartmentID, 0);
				$this->html->ioid = $form->o_fGetMainObject()->i_IOID;
				$this->html->fid = $form->FormCode;
			}
		}
		$this->html->lists = $this->_model->getAllList($this->_user->user_id);
	}
	/**
	 *
	 * @return void
	 */
	public function accountAction ()
	{
		$this->html->accounts = $this->_model->getAllAccount($this->_user->user_id);

	}
	public function accountEditAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$this->html->account = $this->_model->getAccountById($id);
	}
	public function accountSaveAction ()
	{
		$params = $this->params->requests->getParams();
		$id = $params['id'];
		$params['uid'] = $this->_user->user_id;
		$service = $this->services->Mail->Account->Check($this->_user->user_id,$id,1);
		if($service->getData())
		{
			$service = $this->services->Mail->Account->Save($params);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function accountDeleteAction ()
	{
		$id = $this->params->requests->getParam('id');
		$service = $this->services->Mail->Account->Check($this->_user->user_id,$id,1);
		if($service->getData())
		{
			$service = $this->_model->deleteAccount($id);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function cmsLoadAction ()
	{
		$cms_id = $this->params->requests->getParam('cms_id',0);
		$content_id = (int) $this->params->requests->getParam('content_id');
		$record_id = (int) $this->params->requests->getParam('record_id');
		$design_id = (int) $this->params->requests->getParam('design_id');
		$limit = (int) $this->params->requests->getParam('limit');
		$pageno = (int) $this->params->requests->getParam('pageno');
		$page = $this->params->requests->getParam('page');
		if(!$cms_id)
		{
			echo 'Không tìm thấy cms id!';
			exit();
		}
		$dp = new Qss_Model_Web_CMS();
		$dp->getData($cms_id);
		$content = $dp->getDisplay($content_id,$record_id,$design_id,$limit,$pageno,$page,false);
		preg_match('/<body>(.*)<\/body>/s', $content, $matches);
		$body = @$matches[1];
		echo @$body;
		$this->setLayoutRender(false);
		$this->setHtmlRender(false);
	}
	public function signatureLoadAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$data = $this->_model->getAccountById($id);
		echo @$data->Signature;
		$this->setLayoutRender(false);
		$this->setHtmlRender(false);
	}
	public function sendAction ()
	{
		$params = $this->params->requests->getParams();
		$params['createdid'] = $this->_user->user_id;
		$service = $this->services->Mail->Send($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function listAction ()
	{
		$this->html->lists = $this->_model->getAllList($this->_user->user_id);

	}
	public function listEditAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$this->html->list = $this->_model->getListById($id);
	}
	public function listSaveAction ()
	{
		$params = $this->params->requests->getParams();
		$id = $params['id'];
		$params['uid'] = $this->_user->user_id;
		$service = $this->services->Mail->List->Check($this->_user->user_id,$id,1);
		if($service->getData())
		{
			$service = $this->services->Mail->List->Save($params);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function listDeleteAction ()
	{
		$id = $this->params->requests->getParam('id');
		$service = $this->services->Mail->List->Check($this->_user->user_id,$id,1);
		if($service->getData())
		{
			$this->_model->deleteList($id);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function referAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$this->html->refers = $this->_model->getFormRefer($id);
		$this->html->id = $id;
	}
	public function objectAction()
	{
		$id = $this->params->requests->getParam('id',0);
		$this->html->list = $this->_model->getListByID($id);
		$this->html->referlist = $this->_model->getReferChoise($this->_user);
		$this->setLayoutRender(false);
	}
	public function importAction ()
	{
		$id = $this->params->requests->getParam('mlid', 0);
		$fid = $this->params->requests->getParam('fid', 0);
		$deptid = Qss_Register::get('userinfo')->user_dept_id;
		$objid = $this->params->requests->getParam('objid', 0);

		$currentpage = (int) $this->params->cookies->get('object_' . $objid . '_currentpage', 1);
		$currentpage = $currentpage ? $currentpage : 1;
		$limit = $this->params->cookies->get('object_' . $objid . '_limit', 20);
		$fieldorder = $this->params->cookies->get('object_' . $objid . '_fieldorder', '0');
		$ordertype = $this->params->cookies->get('object_' . $objid . '_ordertype', '0');
		$filter = $this->params->cookies->get('object_' . $objid . '_filter', 'a:0:{}');
		$filter = unserialize($filter);
		$i_GroupBy = $this->params->cookies->get('object_' . $objid . '_groupby', '0');
		$form = new Qss_Model_Form();
		$form->v_fInit($fid, $deptid, Qss_Register::get('userinfo')->user_id);
		$object = $form->o_fGetObjectById($objid);
		$sql = $object->sz_fGetSQL($id, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC', $filter,$i_GroupBy,1);
		$this->html->listview = $this->views->Instance->Object->GridImport($sql, $form, $object, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC',$i_GroupBy);
		$this->html->searchform = $this->views->Instance->Object->SearchImport($form, $object, $filter);
		$this->html->pager = $this->views->Instance->Object->ImportPager($sql, $object, $currentpage, $limit,$i_GroupBy);
		$this->html->form = $form;
		$this->html->id = $id;
		$this->html->object = $object;
	}
	public function importReloadAction ()
	{
		if ( $this->params->requests->isAjax() )
		{
			$user = $this->params->registers->get('userinfo', null);
			$this->params->responses->clearBody();
			$id = $this->params->requests->getParam('mlid', 0);
			$fid = $this->params->requests->getParam('fid', 0);
			$deptid = $user->user_dept_id;
			$objid = $this->params->requests->getParam('objid', 0);

			$currentpage = $this->params->requests->getParam('pageno', 1);
			$limit = $this->params->requests->getParam('perpage', 20);

			$fieldorder = $this->params->requests->getParam('fieldorder', 0);
			$ordertype = $this->params->cookies->get('object_' . $objid . '_ordertype', 0);

			$i_GroupBy = $this->params->requests->getParam('groupby', 0);

			$form = new Qss_Model_Form();
			$form->v_fInit($fid, $deptid, Qss_Register::get('userinfo')->user_id);
			$object = $form->o_fGetObjectById($objid);

			if ( $fieldorder != 0 )
			{
				$ordertype = !$ordertype;
			}
			else
			{
				$fieldorder = $fieldorder ? $fieldorder : $this->params->cookies->get('object_' . $objid . '_fieldorder', 0);
			}
			$filter = $this->a_fGetFilter();
			$this->params->cookies->set('object_' . $objid . '_currentpage', $currentpage);
			$this->params->cookies->set('object_' . $objid . '_limit', $limit);
			$this->params->cookies->set('object_' . $objid . '_filter', serialize($filter));
			$this->params->cookies->set('object_' . $objid . '_ordertype', $ordertype);
			$this->params->cookies->set('object_' . $objid . '_fieldorder', $fieldorder);
			$this->params->cookies->set('object_' . $objid . '_groupby', $i_GroupBy);
			$sql = $object->sz_fGetSQL($id, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC', $filter,$i_GroupBy,1);
			echo $this->views->Instance->Object->ImportPager($sql, $object, $currentpage, $limit,$i_GroupBy);
			echo $this->views->Instance->Object->GridImport($sql, $form, $object, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC',$i_GroupBy);
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function importImportAction ()
	{
		$arrIOID = $this->params->requests->getParam('ioidlist', '');
		$arrFID = $this->params->requests->getParam('fidlist', '');
		$arrIOID = explode(',', $arrIOID);
		$arrFID = explode(',', $arrFID);
		$id = $this->params->requests->getParam('mlid', 0);
		$service = $this->services->Mail->List->Check($this->_user->user_id,$id,1);
		if($service->getData())
		{
			$service = $this->services->Mail->Import($id, $arrIOID, $arrFID);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function referDeleteAction ()
	{
		$id = $this->params->requests->getParam('id', 0);
		$ifid = $this->params->requests->getParam('ifid', 0);
		$ioid = $this->params->requests->getParam('ioid', 0);
		$service = $this->services->Mail->List->Check($this->_user->user_id,$id,1);
		if($service->getData())
		{
			$this->_model->deleteRefer($id,$ifid,$ioid);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function referReloadAction ()
	{
		$id = $this->params->requests->getParam('id',0);
		$fid = $this->params->requests->getParam('fid',0);
		$objid = $this->params->requests->getParam('objid',0);
		echo $this->views->Instance->Object->GridMail($id,$fid,$objid);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function listLoadAction()
	{
		$mailmodel = new Qss_Model_Mail();
		$id = $this->params->requests->getParam('id',0);
		$this->html->type = $this->params->requests->getParam('type','to');
		$this->html->tolist = $mailmodel->getMailListById($id);
		//$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

}
?>