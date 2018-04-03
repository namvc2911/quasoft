<?php
/**
 *
 * @author HuyBD
 *
 */
class User_ObjectController extends Qss_Lib_Controller
{

	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/object-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{
		$this->headScript($this->params->requests->getBasePath() . '/js/popup-select.js');
		$ifid = $this->params->requests->getParam('ifid', 0);
		$deptid = $this->params->requests->getParam('deptid', 0);
		$objid = $this->params->requests->getParam('objid', 0);

		$currentpage = (int) $this->params->cookies->get('object_' . $objid . '_currentpage', 1);
		$currentpage = $currentpage ? $currentpage : 1;
		$limit = $this->params->cookies->get('object_' . $objid . '_limit', Qss_Lib_Const_Display::OBJECT_LIMIT_DEFAULT);
		$filter = $this->params->cookies->get('object_' . $objid . '_filter', 'a:0:{}');
		$filter = unserialize($filter);
		$form = new Qss_Model_Form();
		$form->initData($ifid, $deptid);
		//$this->_title = $form->sz_Name;
		$fieldorder = $this->params->cookies->get('object_' . $objid . '_fieldorder', 'a:0:{}');
		$fieldorder = unserialize($fieldorder);
		$groupbys = $this->params->cookies->get('object_' . $objid . '_groupby', 'a:0:{}');
		$groupbys = unserialize($groupbys);
		if($this->b_fCheckRightsOnForm($form,15))
		{
			$object = $form->o_fGetObjectById($objid);
			$this->_title .= ' - ' . $object->sz_Name;
			$object instanceof Qss_Model_Object;
			$sql = $object->sz_fGetSQLByFormAndUser($form, Qss_Register::get('userinfo'), $currentpage, $limit, $fieldorder, $filter,$i_GroupBy);
			$this->html->listview = $this->views->Instance->Object->GridEdit($sql, $form, $object, $currentpage, $limit, $fieldorder,$i_GroupBy);
			$this->html->searchform = $this->views->Instance->Object->Search($form,$object, $filter);
			$this->html->pager = $this->views->Instance->Object->Pager($sql, $object, $currentpage, $limit,$i_GroupBy);
			$this->html->form = $form;
			$this->html->object = $object;
			//$form->read(Qss_Register::get('userinfo')->user_id);
			//$rights = $this->getFormRights($form);
			//echo $object->intRights;
			$rights = $form->i_fGetRights($this->_user->user_group_list);
			$this->html->Rights = $rights & $object->intRights & (($rights & 4)?15:0);
			if(($rights & 4))
			{
				$bash = new Qss_Model_Bash();
				$this->html->bashes = $bash->getManualByObjID($form->FormCode,$objid);
			}
			else
			{
				$this->html->bashes = array();
			}
		}
	}

	/**
	 *
	 * @return void
	 */
	public function deletedAction ()
	{}

	/**
	 * Edit action
	 *
	 * @return void
	 */
	public function editAction ()
	{
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
		$ifid = (int) $this->params->requests->getParam('ifid', 0);
		$deptid = (int) $this->params->requests->getParam('deptid', 0);
		$ioid = (int) $this->params->requests->getParam('ioid', 0);
		$objid = (int) $this->params->requests->getParam('objid', 0);
		$form = new Qss_Model_Form();
		//$form->initData($ifid, $deptid);
		if ( $form->initData($ifid, $deptid) )
		{
			//$this->_title = $form->sz_Name;
			if($this->b_fCheckRightsOnForm($form,15))
			{
				$object = $form->o_fGetObjectById($objid);
				$this->_title .= ' - ' . $object->sz_Name;
				//$object->v_fInit($objid, $form->FormCode);
				$object->initData($ifid, $deptid, $ioid);

				$currentpage = (int) $this->params->cookies->get('object_' . $objid . '_currentpage', 1);
				$currentpage = $currentpage ? $currentpage : 1;
				$limit = $this->params->cookies->get('object_' . $objid . '_limit', Qss_Lib_Const_Display::OBJECT_LIMIT_DEFAULT);
				$fieldorder = $this->params->cookies->get('object_' . $objid . '_fieldorder', '0');
				$ordertype = $this->params->cookies->get('object_' . $objid . '_ordertype', '0');
				$filter = $this->params->cookies->get('object_' . $objid . '_filter', 'a:0:{}');
				$i_GroupBy = $this->params->cookies->get('object_' . $objid . '_groupby', '');
				$filter = unserialize($filter);

				$sql = $object->sz_fGetSQLByFormAndUser($form, Qss_Register::get('userinfo'), $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC', $filter,$i_GroupBy);
				$navigator = $object->getNextPrevRecord($sql,$ioid,true);
				$this->html->prev = null;
				$this->html->next = null;
				foreach ($navigator as $item)
				{
					if($item->type == 0)
					{
						$this->html->prev = $item;
					}
					elseif($item->type == 1)
					{
						$this->html->next = $item;
					}
				}
				$rights = $form->i_fGetRights($this->_user->user_group_list);
				if(($rights & 48) && !$form->lockid)
				{
					$bash = new Qss_Model_Bash();
					$this->html->bashes = $bash->getManualByObjID($form->FormCode,$objid);
				}
				else
				{
					$this->html->bashes = array();
				}
				$this->html->form = $form;
				$this->html->object = $object;
				
				$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
				if(!class_exists($classname))
				{
					$classname = 'Qss_Lib_Onload';
				}
				$onload = new $classname(null,$object);
				$onload->__doExecute();
				$fields = $object->loadFields();
				foreach ($fields as $key => $f)
				{
					$onload->{$f->FieldCode}();
				}
				$this->html->objectedit = $this->views->Instance->Object->Edit($object, Qss_Register::get('userinfo'));
			}
		}
	}
	/**
	 * Edit multi rows, should open popup
	 *
	 * @return void
	 */
	public function meditAction ()
	{
		/*if($this->_mobile)
		{
			$this->html->setHtml(dirname(dirname(__FILE__)).'/html/form/edit_m.phtml');
		}*/
		$ioids = $this->params->requests->getParam('ioid', array());
		$ifid = (int) $this->params->requests->getParam('ifid', 0);
		$deptid = (int) $this->params->requests->getParam('deptid', 0);
		$objid = $this->params->requests->getParam('objid', 0);
		$form = new Qss_Model_Form();
		$form->initData($ifid, $deptid);
		if($this->b_fCheckRightsOnForm($form,15))
		{
			//load multi editable fields
			$object = $form->o_fGetObjectByCode($objid);
			$this->html->form = $form;
			$this->html->object = $object;
			$this->html->user = $this->_user;
			$this->html->ioids = implode(',',$ioids);
			$this->html->deptids = implode(',',$deptids);
			$this->html->mobile = $this->_mobile;
		}
		$this->setLayoutRender(false);
	}
	public function msaveAction ()
	{
		$params = $this->params->requests->getParams();
		$service = $this->services->Object->MSave($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @return void
	 */
	public function deleteAction ()
	{
		$ifid = $this->params->requests->getParam('ifid', 0);
		$deptid = $this->params->requests->getParam('deptid', 0);
		$ioid = $this->params->requests->getParam('ioid', array());
		$objid = $this->params->requests->getParam('objid', 0);
		$service = $this->services->Object->Delete($ifid, $deptid, $ioid, $objid);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function saveAction ()
	{
		$params = $this->params->requests->getParams();
		$service = $this->services->Object->Save($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function refreshAction ()
	{
		$params = $this->params->requests->getParams();
		$service = $this->services->Object->Refresh($params);
		if($service->isError())
		{
			echo $service->getMessage();
			$this->setHtmlRender(false);
		}
		else 
		{
			$object = $service->getData();
			$this->html->object = $object;
			$this->html->mobile = $this->_mobile;
			$this->html->content = $this->views->Instance->Object->Edit($object, $this->_user,true);
		}
		$this->setLayoutRender(false);
	}
	public function reloadAction ()
	{
		$params = $this->params->requests->getParams();
		$service = $this->services->Object->Refresh($params);;
		$object = $service->getData();
		$fields = $object->loadFields();
		$data = array();
		foreach ($fields as $field)
		{
			$display = ($field->intFieldType != 7)?$field->getValue():'';
			if(in_array($field->intInputType,array(3,4,11))  && $field->intFieldType != 14 && $field->intFieldType != 15 && $field->intFieldType != 16)
			{
				$value = $field->getRefIOID();
			}
			elseif($field->intFieldType == 17)
			{
				$field->bReadOnly = $object->i_IOID?$field->bReadOnly:true;
				$value = $this->views->Instance->Field->CustomButton($object,$field);
				$display = $value;
			}
			else
			{
				$value = $field->getValue();
			}
			if($value === true) $value = 1;
			if($value === false) $value = 0;
			$data[$field->ObjectCode .'_'. $field->FieldCode]['value'] =  $value;
			$data[$field->ObjectCode .'_'. $field->FieldCode]['readonly'] =  $field->bReadOnly?true:false;
			$data[$field->ObjectCode .'_'. $field->FieldCode]['display'] =  $display;
			//$data[$field->ObjectCode .'_'. $field->FieldCode]['filter'] =  $object->getFilter($field);
		}
		echo Qss_Json::encode($data);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @return void
	 */
	public function gridAction ()
	{
		if ( $this->params->requests->isAjax() )
		{
			if($this->_mobile)
			{
				$this->html->setHtml(dirname(dirname(__FILE__)).'/html/object/grid_m.phtml');
			}
			$user = $this->params->registers->get('userinfo', null);
			$this->params->responses->clearBody();
			$ifid = $this->params->requests->getParam('ifid', 0);
			$deptid = $this->params->requests->getParam('deptid', 0);
			$objid = $this->params->requests->getParam('objid', 0);
			$form = new Qss_Model_Form();
			$form->initData($ifid, $deptid);
			if(!$objid)
			{
				$subobject = $form->a_fGetSubObjects();
				foreach($subobject as $item)
				{
					if((!($item->bPublic & 1) && !$this->_mobile) || (!($item->bPublic & 2) && $this->_mobile))
					{
						$objid = $item->ObjectCode;
						break;
					}
				}
			}
			if(!$objid)
			{
				$this->setHtmlRender(false);
				$this->setLayoutRender(false);
				return;
			}
			if($this->params->requests->getParam('pageno')=='undefined')//lần đầu
			{
				$currentpage = (int) $this->params->cookies->get('object_' . $objid . '_currentpage', 1);
				$currentpage = $currentpage ? $currentpage : 1;
				$limit = $this->params->cookies->get('object_' . $objid . '_limit', Qss_Lib_Const_Display::OBJECT_LIMIT_DEFAULT);
				$fieldorder = $this->params->cookies->get('object_' . $objid . '_fieldorder', 'a:0:{}');
				$fieldorder = unserialize($fieldorder);
				$groupbys = $this->params->cookies->get('object_' . $objid . '_groupby', 'a:0:{}');
				$groupbys = unserialize($groupbys);
			}
			else
			{
				$currentpage = $this->params->requests->getParam('pageno', 1);
				$limit = $this->params->requests->getParam('perpage', Qss_Lib_Const_Display::OBJECT_LIMIT_DEFAULT);
				$fieldorder = $this->params->cookies->get('object_' . $objid . '_fieldorder', 'a:0:{}');
				$fieldorder = unserialize($fieldorder);
				$groupbys = $this->params->cookies->get('object_' . $objid . '_groupby', 'a:0:{}');
				$groupbys = unserialize($groupbys);
				$i_FieldOrder = $this->params->requests->getParam('fieldorder', 0);
				$i_GroupBy = $this->params->requests->getParam('groupby',0);
				if($i_GroupBy)
				{
					if (($key = array_search($i_GroupBy, $groupbys)) !== false)
					{
						unset($groupbys[$key]);
					}
					else
					{
						$groupbys[] = $i_GroupBy;
					}
					$this->params->cookies->set('object_' . $objid . '_groupby', serialize($groupbys));
				}
				if($i_FieldOrder)
				{
					if($i_FieldOrder === ' ')
					{
						$fieldorder = array();
					}
					else
					{
						if (array_key_exists($i_FieldOrder, $fieldorder))
						{
							if($fieldorder[$i_FieldOrder] == 'ASC')
							{
								$fieldorder[$i_FieldOrder] = 'DESC';
							}
							else
							{
								//$fieldorder[$i_FieldOrder] = 'ASC';
								unset($fieldorder[$i_FieldOrder]);
							}
						}
						else
						{
							$fieldorder[$i_FieldOrder] = 'ASC';
						}
					}
					$this->params->cookies->set('object_' . $objid . '_fieldorder', serialize($fieldorder));
				}
			}
			if($this->b_fCheckRightsOnForm($form,15))
			{
				$this->params->cookies->set('form_' . $form->FormCode. '_object_selected', $objid);
				$tree = (int) $this->params->requests->getParam('tree', 0);
				if($tree)
				{
					$treecookie = $this->params->cookies->get('object_' . $form->i_IFID . '_' . $objid . '_tree', 'a:0:{}');
					$treecookie = unserialize($treecookie);
						
					if (($key = array_search($tree, $treecookie)) !== false)
					{
						unset($treecookie[$key]);
					}
					else
					{
						$treecookie[] = $tree;
					}
					$this->params->cookies->set('object_' . $form->i_IFID . '_' . $objid . '_tree', serialize($treecookie));
				}
				$object = $form->o_fGetObjectByCode($objid);
				$object instanceof Qss_Model_Object;
				$filter = array();
				//$filter = $this->a_fGetFilter();
				$this->params->cookies->set('object_' . $objid . '_currentpage', $currentpage);
				$this->params->cookies->set('object_' . $objid . '_limit', $limit);
				//$this->params->cookies->set('object_' . $objid . '_filter', serialize($filter));				
				$sql = $object->sz_fGetSQLByFormAndUser($form, Qss_Register::get('userinfo'), $currentpage, $limit, $fieldorder, $filter,$groupbys);
				//change current page if has 
				$gotoioid = $this->params->requests->getParam('gotoioid', 0);
				if($gotoioid)
				{
					$rowno = $object->getRowNumber($sql,$gotoioid);
					if($rowno)
					{
						$currentpage = ceil($rowno / $limit);
					}
				}
				$classname = 'Qss_View_Object_' . $object->ObjectCode;
				if((!$this->_mobile || $object->bPublic & 4) && class_exists($classname))
				{
					echo $this->views->Object->{$object->ObjectCode}($sql, $form, $object, $currentpage, $limit, $fieldorder,$groupbys);
					$this->setHtmlRender(false);
				}
				else
				{
					if($this->_mobile)
					{
						$this->html->pager = $this->views->Mobile->Object->Pager($sql, $object, $currentpage,$limit,$groupbys);
						$this->html->grid = $this->views->Mobile->Object->GridEdit($sql, $form, $object, $currentpage, $limit, $fieldorder,$groupbys);
					}
					else
					{
						$this->html->pager = $this->views->Instance->Object->Pager($sql, $object, $currentpage,$limit,$groupbys);
						$this->html->grid = $this->views->Instance->Object->GridEdit($sql, $form, $object, $currentpage, $limit, $fieldorder, $groupbys);
					}
					$this->html->form = $form;
					$this->html->object = $object;
					$rights = $form->i_fGetRights($this->_user->user_group_list);
					$this->html->Rights = $rights & $object->intRights & (($rights & 4)?15:0);
					if($rights & 4)
					{
						$bash = new Qss_Model_Bash();
						$this->html->bashes = $bash->getManualByObjID($form->FormCode,$objid);
					}
					else
					{
						$this->html->bashes = array();
					}
				}
			}
		}
		$this->setLayoutRender(false);
	}
	public function traceAction()
	{
		$ifid = $this->params->requests->getParam('ifid', 0);
		$deptid = $this->params->requests->getParam('deptid', 0);
		$objid = $this->params->requests->getParam('objid', 0);
		$ioid = $this->params->requests->getParam('ioid', 0);
		$form = new Qss_Model_Form();
		$form->initData($ifid, $deptid);
		if($this->b_fCheckRightsOnForm($form,15))
		{
			$object = $form->o_fGetObjectById($objid);
			$object->initData($form->i_IFID, $form->i_DepartmentID,$ioid);
			$this->html->events = $object->getEvents();
			$user = new Qss_Model_Admin_User();
			$user->init($object->i_UserID);
			$this->html->user = $user;
			$this->html->object = $object;
		}
		$this->setLayoutRender(false);
	}
	public function popupEditAction ()
	{
		$params = $this->params->requests->getParams();
		$service = $this->services->Object->Refresh($params);
		$object = $service->getData();
		foreach ($object->loadFields() as $f)
		{
			if(isset($params[$f->ObjectCode.'_'.$f->FieldCode]))
			{
				$f->bReadOnly = true;
			}
		}
		$this->html->objectedit = $this->views->Instance->Object->Edit($object, $this->_user,true);
		$this->html->object = $object;
		$this->html->mobile = $this->_mobile;
		$this->setLayoutRender(false);

		//
		//		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
		//		$ifid = (int) $this->params->requests->getParam('ifid', 0);
		//		$deptid = (int) $this->params->requests->getParam('deptid', 0);
		//		$ioid = (int) $this->params->requests->getParam('ioid', 0);
		//		$objid = (int) $this->params->requests->getParam('objid', 0);
		//		$form = new Qss_Model_Form();
		//		//$form->initData($ifid, $deptid);
		//		if ( $form->initData($ifid, $deptid) )
		//		{
		//			$this->_title = $form->sz_Name;
		//			if($this->b_fCheckRightsOnForm($form,15))
		//			{
		//				$object = $form->o_fGetObjectById($objid);
		//				$this->_title .= ' - ' . $object->sz_Name;
		//				$object->initData($ifid, $deptid, $ioid);
		//				$object->a_fLoadFields($object->i_IOID);
		//
		//				$rights = $form->i_fGetRights($this->_user->user_group_list);
		//				$this->html->form = $form;
		//				$this->html->object = $object;
		//				$this->html->objectedit = $this->views->Instance->Object->Edit($object, Qss_Register::get('userinfo'));
		//			}
		//		}
	}
	public function updownAction ()
	{
		$ifid = $this->params->requests->getParam('ifid', 0);
		$deptid = $this->params->requests->getParam('deptid', 0);
		$objid = $this->params->requests->getParam('objid', 0);
		$ioid = $this->params->requests->getParam('ioid', 0);
		$type = $this->params->requests->getParam('type', false);
		$service = $this->services->Object->Updown($ifid, $deptid,$objid,$ioid,$type);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function documentAction()
	{
		if ( $this->params->requests->isAjax() )
		{
			$ifid = $this->params->requests->getParam('ifid', 0);
			$deptid = $this->params->requests->getParam('deptid', 0);
			$ioid = $this->params->requests->getParam('ioid', 0);
			$form = new Qss_Model_Form();
			if ( $ifid && $deptid )
			{
				$form->initData($ifid, $deptid);
				$this->html->documents = $form->getDocuments($ioid);
				$docmodel = new Qss_Model_Admin_Document();
				$this->html->documenttypes = $docmodel->getAllByFormCode($form->FormCode,$form->i_Status);
				$this->html->requires = $form->getRequiredDocuments();
				$this->html->form = $form;
				$this->html->rights = $this->getFormRights($form);
				$this->html->ioid = (int) $ioid;
			}
		}
		$this->setLayoutRender(false);
	}
	public function bashAction ()
	{
		$id = $this->params->requests->getParam('id', 0);
		$ifid = $this->params->requests->getParam('ifid', 0);
		$objid = $this->params->requests->getParam('objid');
		$ioid = $this->params->requests->getParam('ioid');
		$form = new Qss_Model_Form();
		if($form->initData($ifid, $this->_user->user_dept_id) && $this->b_fCheckRightsOnForm($form,4))
		{
			$form->o_fGetMainObject()->initData($ifid, $this->_user->user_dept_id, $form->o_fGetMainObject()->i_IOID);
			if($ioid)
			{
				$form->o_fGetObjectByCode($objid)->initData($ifid, $this->_user->user_dept_id, $ioid);
			}
			$service = $this->services->Bash->Execute($form,$id);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>