<?php
/**
 *
 * @author HuyBD
 *
 */
class User_ImportController extends Qss_Lib_Controller
{

	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/import-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{
		$fid = $this->params->requests->getParam('fid', 0);
		$ifid = $this->params->requests->getParam('ifid', 0);
		$deptid = $this->params->requests->getParam('deptid', 0);
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
		if ( $ifid )
		{
			$form->initData($ifid, $deptid);
			$object = new Qss_Model_Object();
			$object->v_fInit($objid, $form->FormCode);
			$object->initData($ifid, $deptid, 0);
		}
		else
		{
			$form->v_fInit($fid, $deptid, Qss_Register::get('userinfo')->user_id);
			$object = $form->o_fGetMainObject();
		}
		$sql = $object->sz_fGetInheritedSQL(Qss_Register::get('userinfo'), $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC', $filter,$i_GroupBy);
		$this->html->listview = $this->views->Instance->Object->GridImport($sql, $form, $object, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC',$i_GroupBy);
		$this->html->searchform = $this->views->Instance->Object->SearchImport($form, $object, $filter);
		$this->html->pager = $this->views->Instance->Object->ImportPager($sql, $object, $currentpage, $limit,$i_GroupBy);
		$this->html->form = $form;
		$this->html->object = $object;
	}

	/**
	 *
	 * @return void
	 */
	public function gridAction ()
	{
		if ( $this->params->requests->isAjax() )
		{
			$user = $this->params->registers->get('userinfo', null);
			$this->params->responses->clearBody();
			$fid = $this->params->requests->getParam('fid', 0);
			$ifid = $this->params->requests->getParam('ifid', 0);
			$deptid = $this->params->requests->getParam('deptid', 0);
			$objid = $this->params->requests->getParam('objid', 0);
				
			$currentpage = $this->params->requests->getParam('pageno', 1);
			$limit = $this->params->requests->getParam('perpage', 20);
				
			$fieldorder = $this->params->requests->getParam('fieldorder', 0);
			$ordertype = $this->params->cookies->get('object_' . $objid . '_ordertype', 0);
				
			$i_GroupBy = $this->params->requests->getParam('groupby', 0);
				
			$form = new Qss_Model_Form();
			if ( $ifid )
			{
				$form->initData($ifid, $deptid);
				$object = new Qss_Model_Object();
				$object->v_fInit($objid, $form->FormCode);
				$object->initData($ifid, $deptid, 0);
			}
			else
			{
				$form->v_fInit($fid, $deptid, Qss_Register::get('userinfo')->user_id);
				$object = $form->o_fGetMainObject();
			}
				
			if ( $ordertype != 0 )
			{
				$ordertype = !$ordertype;
			}
			else
			{
				$ordertype = $ordertype ? $ordertype : $this->params->cookies->get('object_' . $objid . '_fieldorder', 0);
			}
			$filter = $this->a_fGetFilter();
			$this->params->cookies->set('object_' . $objid . '_currentpage', $currentpage);
			$this->params->cookies->set('object_' . $objid . '_limit', $limit);
			$this->params->cookies->set('object_' . $objid . '_filter', serialize($filter));
			$this->params->cookies->set('object_' . $objid . '_ordertype', $ordertype);
			$this->params->cookies->set('object_' . $objid . '_fieldorder', $fieldorder);
			$this->params->cookies->set('object_' . $objid . '_groupby', $i_GroupBy);
			$sql = $object->sz_fGetInheritedSQL(Qss_Register::get('userinfo'), $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC', $filter,$i_GroupBy);
			echo $this->views->Instance->Object->ImportPager($sql, $object, $currentpage, $limit,$i_GroupBy);
			echo $this->views->Instance->Object->GridImport($sql, $form, $object, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC',$i_GroupBy);
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function importAction ()
	{
		$arrIOID = $this->params->requests->getParam('ioidlist', '');
		$arrFID = $this->params->requests->getParam('fidlist', '');
		$arrIOID = explode(',', $arrIOID);
		$arrFID = explode(',', $arrFID);
		$fid = $this->params->requests->getParam('fid', 0);
		$ifid = $this->params->requests->getParam('ifid', 0);
		$deptid = $this->params->requests->getParam('deptid', 0);
		$objid = $this->params->requests->getParam('objid', 0);

		$form = new Qss_Model_Form();
		if ( $ifid )
		{
			$form->initData($ifid, $deptid);
			$object = new Qss_Model_Object();
			$object->v_fInit($objid, $form->FormCode);
			$object->initData($ifid, $deptid, 0);
		}
		else
		{
			$form->v_fInit($fid, $deptid, Qss_Register::get('userinfo')->user_id);
			$object = $form->o_fGetMainObject();
		}
		$service = $this->services->Object->Import($form,$object, $arrIOID, $arrFID);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Excel import page
	 *
	 * @return void
	 */
	public function excelAction ()
	{
		$ifid = $this->params->requests->getParam('ifid', 0);
		$deptid = $this->params->requests->getParam('deptid', 0);
		$objid = $this->params->requests->getParam('objid', 0);
		$fid = $this->params->requests->getParam('fid', 0);
		$form = new Qss_Model_Form();
		if ( $ifid )
		{
			$form->initData($ifid, $deptid);
			$object = new Qss_Model_Object();
			$object->v_fInit($objid, $form->FormCode);
			$object->initData($ifid, $deptid, 0);
			$this->html->form = $form;
			$this->html->object = $object;
		}
		$this->html->fid = $fid;
		$this->html->user = Qss_Register::get('userinfo');
	}
	/**
	 * Excel import page only for sub object
	 *
	 * @return void
	 */
	public function formAction ()
	{
		$fid    = $this->params->requests->getParam('fid', '');
		$deptid = $this->params->requests->getParam('deptid', 0);
		$form   = Qss_Lib_System::getFormByCode($fid);
        
		$this->html->deptid = $deptid;
		$this->html->form   = $form;
		$this->html->user   = Qss_Register::get('userinfo');
	}
	
	/**
	 * Excel import page only for sub object
	 *
	 * @return void
	 */
	public function objectAction ()
	{
	    $ifid = $this->params->requests->getParam('ifid', 0);
	    $deptid = $this->params->requests->getParam('deptid', 0);
	    $objid = $this->params->requests->getParam('objid', 0);
	    $form = new Qss_Model_Form();
	    $form->initData($ifid, $deptid);
	    $object = new Qss_Model_Object();
	    $object->v_fInit($objid, $form->FormCode);
	    $object->initData($ifid, $deptid, 0);
	    $this->html->form = $form;
	    $this->html->object = $object;
	    $this->html->user = Qss_Register::get('userinfo');
	}	
	
	/**
	 * Call excel import
	 *
	 * @return unknown_type
	 */
	public function excelImportAction ()
	{
		$params = $this->params->requests->getParams();
		$service = $this->services->Form->Import($params);
		echo $service->getMessage(Qss_Service_Abstract::TYPE_HTML);
		echo $service->getData();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Call excel import
	 *
	 * @return unknown_type
	 */
	public function downloadAction ()
	{
		$params = $this->params->requests->getParams();
		$service = $this->services->Form->Export($params);
		$fn = $service->getData();
		if ( file_exists($fn[0]) )
		{
			$file = $fn[0];
			header("Content-type: application/force-download");
			header("Content-Transfer-Encoding: Binary");
			header("Content-length: " . filesize($file));
			header("Content-disposition: attachment; filename=\"" . $fn[1] . '.' . pathinfo($fn[0], PATHINFO_EXTENSION) . "\"");
				
			readfile("$file");
			unlink($fn[0]);
			die();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>