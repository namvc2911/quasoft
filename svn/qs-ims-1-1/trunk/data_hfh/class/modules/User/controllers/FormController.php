<?php
/**
 *
 * @author HuyBD
 *
 */
class User_FormController extends Qss_Lib_Controller
{

    /**
     *
     * @return unknown_type
     */
    public function init ()
    {
        parent::init();
        //$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
    }

    /**
     *
     * @return void
     */
    public function indexAction ()
    {
        if($this->_mobile)
        {
            $this->html->setHtml(dirname(dirname(__FILE__)).'/html/form/index_m.phtml');
        }
        $this->headScript($this->params->requests->getBasePath() . '/js/form-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/popup-select.js');
        $fid = $this->params->requests->getParam('fid', 0);
        $user = $this->params->registers->get('userinfo', null);

        if ( $fid )
        {
            $o_Form = new Qss_Model_Form();
            $o_Form->init($fid, $user->user_dept_id, $user->user_id);
            $currentpage = (int) $this->params->cookies->get('form_' . $fid . '_currentpage', 1);
            $currentpage = $currentpage ? $currentpage : 1;
            $limit = $this->params->cookies->get('form_' . $fid . '_limit', 200);
            $uid = $this->params->cookies->get('form_' . $fid . '_uid', '');
            $filter = $this->a_fGetFilter();
            if(!count($filter))//check if filter by reference
            {
                $filter = $this->params->cookies->get('form_' . $fid . '_filter', 'a:0:{}');
                $filter = unserialize($filter);
            }
            else
            {
                $this->params->cookies->set('form_' . $fid . '_filter', serialize($filter));
            }
            $fieldorder = $this->params->cookies->get('form_' . $fid . '_fieldorder', 'a:0:{}');
            $fieldorder = unserialize($fieldorder);
            $groupbys = $this->params->cookies->get('form_' . $fid . '_groupby', 'a:0:{}');
            $groupbys = unserialize($groupbys);
            $status = $this->params->cookies->get('form_' . $fid . '_status', 'a:0:{}');
            $status = unserialize($status);

            //$this->_title = $o_Form->sz_Name;
            if($this->b_fCheckRightsOnForm($o_Form,15))
            {
                $this->html->form = $o_Form;
                $mainobject = $o_Form->o_fGetMainObject();
                $this->html->object = $mainobject;
                $sql = $o_Form->sz_fGetSQLByUser($this->_user, $currentpage, $limit, $fieldorder, $filter,$groupbys,$status,$uid);
                $this->html->searchform = $this->views->Instance->Form->Search($o_Form, $filter);
                if($this->_mobile)
                {
                    $this->html->listview = $this->views->Mobile->Form->GridEdit($sql, $o_Form, $currentpage, $limit, $fieldorder, $groupbys);
                    $this->html->pager = $this->views->Mobile->Form->Pager($sql, $o_Form, $currentpage, $limit,$groupbys,$status,$uid);
                }
                else
                {
                    $this->html->listview = $this->views->Instance->Form->GridEdit($sql, $o_Form, $currentpage, $limit, $fieldorder, $groupbys);
                    $this->html->pager = $this->views->Instance->Form->Pager($sql, $o_Form, $currentpage, $limit,$groupbys,$status,$uid);
                }
                $this->html->user = $user;
                $bash = new Qss_Model_Bash();
                $this->html->bashes = $bash->getManualByForm($o_Form->FormCode);
                $this->html->status = $status;
                /*$stepmodel = new Qss_Model_System_Step($o_Form->i_WorkFlowID);
                $this->html->steps = $stepmodel->getAll();*/

                for($i = 0; $i < 10; $i++)
                {
                    $classname = 'Qss_Bin_Tabs_'.$o_Form->FormCode.'_Tab'.$i;
                    if(class_exists($classname))
                    {

                    }
                }
            }
        }
        else
        {
            $this->setHtmlRender(false);
        }
    }
    /**
     *
     * @return void
     */
    public function deletedAction ()
    {
        $form = new Qss_Model_Form();
        $this->html->forms = $form->a_fGetDelFormsByUser(Qss_Register::get('userinfo'));
    }

    /**
     *
     * @return void
     */
    public function restoreAction ()
    {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $form = new Qss_Model_Form();
        if ( $ifid && $deptid )
        {
            $form->initData($ifid, $deptid);
            if($this->b_fCheckRightsOnForm($form,4))
            {
                $form->v_fRestore(Qss_Register::get('userinfo')->user_id);
                echo Qss_Json::encode(array('error' => 0));
            }
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    /**
     * Edit action
     *
     * @return void
     */
    public function editAction ()
    {
        if($this->_mobile)
        {
            $this->html->setHtml(dirname(dirname(__FILE__)).'/html/form/edit_m.phtml');
        }
        $this->headScript($this->params->requests->getBasePath() . '/js/object-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/popup-select.js');
        $params = $this->params->requests->getParams();
        $service = $this->services->Form->Refresh($params);
        $form = $service->getData();
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $fid = $this->params->requests->getParam('fid', 0);
        if($this->b_fCheckRightsOnForm($form,31))
        {
            $objects = $form->o_fGetMainObjects();
            foreach($objects as $object)
            {
                $object->initData($form->i_IFID, $form->i_DepartmentID, 0);
                $object->loadFields();
            }
            $fid = $form->FormCode;
            /*GetNexPrev*/
            $currentpage = (int) $this->params->cookies->get('form_' . $fid . '_currentpage', 1);
            $currentpage = $currentpage ? $currentpage : 1;
            $limit = $this->params->cookies->get('form_' . $fid . '_limit', 200);
            $fieldorder = $this->params->cookies->get('form_' . $fid . '_fieldorder', 'a:0:{}');
            $filter = $this->params->cookies->get('form_' . $fid . '_filter', 'a:0:{}');
            $i_GroupBy = $this->params->cookies->get('form_' . $fid . '_groupby', 'a:0:{}');
            $i_Status = $this->params->cookies->get('form_' . $fid . '_status', 'a:0:{}');
            $status = unserialize($i_Status);
            $groupby = unserialize($i_GroupBy);
            $fieldorder = unserialize($fieldorder);
            $uid = $this->params->cookies->get('form_' . $fid . '_uid', '');
            $filter = unserialize($filter);
            foreach($objects as $object)
            {
                //$sql = $object->sz_fGetSQLByFormAndUser($form, $this->_user, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC', $filter,$i_GroupBy);
                $sql = $form->sz_fGetSQLByUser($this->_user, $currentpage, $limit, $fieldorder, $filter,$groupby,$status,$uid,false);
                $navigator = $object->getNextPrevRecord($sql[0],$ifid,true);
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
            }
            //$form->read($this->_user->user_id);
            $classname = 'Qss_Bin_Validation_'.$form->FormCode;
            if(class_exists($classname))
            {
                $reader = new $classname($form);
                $reader->onRead();
            }
            $this->html->form = $form;
            $this->html->user_id = $this->_user->user_id;
            $this->html->formedit = $this->views->Instance->Form->Edit($form, $this->_user);
            //$this->html->formpopup = $this->views->Instance->Form->Popup($form, $this->_user);
            $this->html->documentcount = $form->countDocument();
            $this->html->eventcount = $form->countEvent();
            $rights = $this->getFormRights($form);
            //sub object
            $subobjects = $form->a_fGetSubObjects();
            $activesubobjects = $form->a_fGetSubObjects();
            foreach ($activesubobjects as $k=>$v)
            {
                if((($v->bPublic & 1) && !$this->_mobile) || (($v->bPublic & 2) && $this->_mobile))
                {
                    unset($activesubobjects[$k]);
                }
            }
            $this->html->subobjects = $subobjects;
            $this->html->activesubobjects = $activesubobjects;
            $this->html->searchform = $this->views->Instance->Form->Search($form, $filter);
            if(count($subobjects))
            {
                $subobjects = array_values($subobjects);
                $this->html->subobject = $subobjects[0];
            }
            if($rights & 4)
            {
                $bash = new Qss_Model_Bash();
                $this->html->bashes = $bash->getManualByForm($form->FormCode);
            }
            else
            {
                $this->html->bashes = array();
            }
            //Approve
            $arrApproved = array();
            $arrNotApproved = array();
            if($form->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE)
            {
                $form->i_Status = $form->i_Status?$form->i_Status:1;
                $traces = $form->a_fGetTrace();
                $step = new Qss_Model_System_Step($form->i_WorkFlowID);
                $step->v_fInitByStepNumber($form->i_Status);
                $approvers = $step->getApproverByUser($ifid,$this->_user->user_id);
                $this->html->traces = $traces;
                //$nextsteps = $step->a_fGetNextSteps();
                //$backsteps = $step->a_fGetBackSteps();
                //$this->html->nextsteps = $nextsteps;
                //$this->html->backsteps = $backsteps;
                foreach ($approvers as $item)
                {
                    if($item->AUID == $this->_user->user_id)
                    {
                        $arrApproved[] = $item;
                    }
                    elseif($item->UID == $this->_user->user_id)
                    {
                        $arrNotApproved[] = $item;
                    }
                }

                $this->html->step = $step;
                if($this->_mobile)
                {
                    $this->html->single = false;

                    $nextsteps = $step->a_fGetNextSteps();
                    $backsteps = $step->a_fGetBackSteps();
                    //$this->html->nextsteps = $nextsteps;
                    //$this->html->backsteps = $backsteps;
                    $allow = array();
                    foreach ($nextsteps as $item)
                    {
                        $allow[] = $item->StepNo;
                    }
                    foreach ($backsteps as $item)
                    {
                        $allow[] = $item->StepNo;
                    }
                    $this->html->allow = $allow;
                    $this->html->allsteps = $step->getAll();
                }
            }
            $this->html->arrApproved = $arrApproved;
            $this->html->arrNotApproved = $arrNotApproved;
            $this->html->rights = $rights;
        }
    }
    public function refreshAction ()
    {
        $params = $this->params->requests->getParams();
        $service = $this->services->Form->Refresh($params);
        $form = $service->getData();
        if($this->b_fCheckRightsOnForm($form,15))
        {
            if($service->isError())
            {
                echo $service->getMessage();
                $this->setHtmlRender(false);
            }
            else
            {
                $this->html->content = $this->views->Instance->Form->Edit($form, $this->_user);
                $this->html->form = $form;
                $this->html->mobile = $this->_mobile;
                //sub object
                $subobjects = $form->a_fGetSubObjects();
                $this->html->subobjects = $subobjects;
            }
        }
        $this->setLayoutRender(false);
    }
    public function rowAction ()
    {
        $params = $this->params->requests->getParams();
        $service = $this->services->Form->Refresh($params);
        $form = $service->getData();
        if($this->b_fCheckRightsOnForm($form,15))
        {
            echo $this->views->Instance->Form->Row($form, $this->_user);
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function reloadAction ()
    {
        $params = $this->params->requests->getParams();
        $service = $this->services->Form->Refresh($params);
        $form = $service->getData();
        if($this->b_fCheckRightsOnForm($form,15))
        {
            $object = $form->o_fGetMainObject();
            $fields = $object->loadFields();
            $data = array();
            foreach ($fields as $field)
            {
                $display = ($field->intFieldType != 7)?$field->getValue():'';
                if(in_array($field->intInputType,array(3,4,11,12)) && $field->intFieldType != 14 && $field->intFieldType != 15 && $field->intFieldType != 16)
                {
                    $value = $field->intRefIOID;
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
                $data[$field->ObjectCode .'_'. $field->FieldCode]['display'] = $display;
                //$data[$field->ObjectCode .'_'. $field->FieldCode]['filter'] =  $object->getFilter($field);
            }
            echo Qss_Json::encode($data);
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function detailAction ()
    {
        $this->headScript($this->params->requests->getBasePath() . '/js/popup-select.js');
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $form = new Qss_Model_Form();
        $form->initData($ifid, $deptid);
        $fid = $form->FormCode;
        //$this->_title = $form->sz_Name;
        if($this->b_fCheckRightsOnForm($form,15))
        {
            /*GetNexPrev*/
            $currentpage = (int) $this->params->cookies->get('form_' . $fid . '_currentpage', 1);
            $currentpage = $currentpage ? $currentpage : 1;
            $limit = $this->params->cookies->get('form_' . $fid . '_limit', 200);
            $fieldorder = $this->params->cookies->get('form_' . $fid . '_fieldorder', '0');
            $ordertype = $this->params->cookies->get('form_' . $fid . '_ordertype', '0');
            $filter = $this->params->cookies->get('form_' . $fid . '_filter', 'a:0:{}');
            $i_GroupBy = $this->params->cookies->get('form_' . $fid . '_groupby', '0');
            $filter = unserialize($filter);
            /*$objects = $form->o_fGetMainObjects();
             foreach($objects as $object)
             {
                $sql = $object->sz_fGetSQLByFormAndUser($form,$this->_user, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC', $filter,$i_GroupBy);
                $navigator = $object->getNextPrevRecord($sql,$ifid);
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
                }*/
            //$form->read($this->_user->user_id);
            $this->html->form = $form;
            $this->html->user_id = $this->_user->user_id;
            $this->html->formdetail = $this->views->Instance->Form->Detail($form, $this->_user);
            $this->html->documentcount = $form->countDocument();
            $this->html->eventcount = $form->countEvent();
        }
    }

    /**
     *
     * @return void
     */
    public function deleteAction ()
    {
        $ifid = $this->params->requests->getParam('ifid', array());
        $deptid = $this->params->requests->getParam('deptid', array());
        $service = $this->services->Form->Delete($ifid, $deptid);
        echo $service->getMessage();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function saveAction ()
    {
        $params = $this->params->requests->getParams();
        $service = $this->services->Form->Save($params);
        echo $service->getMessage();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);

    }
    public function traceAction()
    {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $form = new Qss_Model_Form();
        $form->initData($ifid, $deptid);
        if($this->b_fCheckRightsOnForm($form,15))
        {
            $user = new Qss_Model_Admin_User();
            $user->init($form->i_UserID);
            $dept = new Qss_Model_Admin_Department();
            $dept->init($form->i_DepartmentID);

            $this->html->user = $user;
            $this->html->cuser = $this->_user;
            $this->html->dept = $dept;
            $this->html->form = $form;
            /*$step = new Qss_Model_System_Step($form->i_WorkFlowID);
            if($form->i_Status)
            {
                $step->v_fInitByStepNumber($form->i_Status);
                $this->html->status = $step->szStepName;
            }
            else
            {
                $this->html->status = '';
            }*/
            $approvers = $form->getApprovers();
            $this->html->approvers = $approvers;
            $this->html->single = true;
            $ok = false;
            foreach ($approvers as $item)
            {
                if($item->intStepType == 1 || $item->intStepType == 2)//yêu cầu tất cả được duyệt, có StepNo là đã được duyệt
                {
                    $ok = true;
                    if(!$item->StepNo)
                    {
                        $ok = false;
                        break;
                    }
                }
                elseif($item->intStepType == 3)//Yêu cầu cái cuối cùng
                {
                    if(!$item->StepNo)
                    {
                        $ok = false;
                    }
                    else
                    {
                        $ok = true;
                    }
                }
                elseif($item->intStepType == 4)//Chỉ cần 1 nhóm duyệt
                {
                    if($item->StepNo)
                    {
                        $ok = true;
                        break;
                    }
                }
            }
            $this->html->ok = $ok;
            //$form->read(Qss_Register::get('userinfo')->user_id);
            $this->html->traces = $form->a_fGetTrace();
            $this->html->readers = $form->getReaders();
            $this->html->comments = $form->getComments();
            $mainobject = $form->o_fGetMainObject();
            $mainobject->initData($form->i_IFID, $form->i_DepartmentID,0);
            //$this->html->events = $mainobject->getEvents();
            $bash = new Qss_Model_Bash();
            $this->html->history = $bash->getHistoryByToIFID($ifid);
            $this->html->transfer = $bash->getHistoryByIFID($ifid);
            //$this->html->step = $step;
            //
            $arr = array();
            $depts = $dept->getAll();
            foreach ($depts as $item)
            {
                if($item->LegalEntity)
                {
                    $new = new stdClass();
                    $new->ID = $item->DepartmentID;
                    $new->Value = $item->Name;
                    $new->Parent = $item->ParentID;
                    $arr[] = $new;
                }
            }
            $this->html->departments = $arr;
        }
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
            $user = $this->params->registers->get('userinfo', null);
            $this->params->responses->clearBody();
            $i_FID = $this->params->requests->getParam('fid', 0);
            if ( $i_FID )
            {
                $o_Form = new Qss_Model_Form();
                $o_Form->init($i_FID, $user->user_dept_id, $user->user_id);
                if($this->b_fCheckRightsOnForm($o_Form,15))
                {
                    $tree = (int) $this->params->requests->getParam('tree', 0);
                    if($tree)
                    {
                        $treecookie = $this->params->cookies->get('form_' . $i_FID . '_tree', 'a:0:{}');
                        $treecookie = unserialize($treecookie);
                        if (($key = array_search($tree, $treecookie)) !== false)
                        {
                            unset($treecookie[$key]);
                        }
                        else
                        {
                            $treecookie[] = $tree;
                        }
                        $this->params->cookies->set('form_' . $i_FID . '_tree', serialize($treecookie));
                    }
                    $i_CurrentPage = $this->params->requests->getParam('pageno', 1);
                    $i_CurrentPage=$i_CurrentPage?$i_CurrentPage:1;
                    $i_PerPage = $this->params->requests->getParam('perpage', 200);
                    $i_FieldOrder = $this->params->requests->getParam('fieldorder', 0);
                    $i_Status = $this->params->requests->getParam('status', 0);
                    $uid = $this->params->requests->getParam('uid', '');
                    $i_GroupBy = $this->params->requests->getParam('groupby', 0);
                    $groupbys = $this->params->cookies->get('form_' . $i_FID . '_groupby', 'a:0:{}');
                    $groupbys = unserialize($groupbys);
                    $fieldorder = $this->params->cookies->get('form_' . $i_FID . '_fieldorder', 'a:0:{}');
                    $fieldorder = unserialize($fieldorder);
                    $status = $this->params->cookies->get('form_' . $i_FID . '_status', 'a:0:{}');
                    $status = unserialize($status);
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
                        $this->params->cookies->set('form_' . $i_FID . '_groupby', serialize($groupbys));
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
                                    $fieldorder[$i_FieldOrder] = 'ASC';
                                }
                            }
                            else
                            {
                                $fieldorder[$i_FieldOrder] = 'ASC';
                            }
                        }
                        $this->params->cookies->set('form_' . $i_FID . '_fieldorder', serialize($fieldorder));
                    }
                    if($i_Status)
                    {
                        if (($key = array_search($i_Status, $status)) !== false)
                        {
                            unset($status[$key]);
                        }
                        else
                        {
                            $status[] = $i_Status;
                        }
                        $this->params->cookies->set('form_' . $i_FID . '_status', serialize($status));
                    }
                    $a_Filter = $this->a_fGetFilter();
                    $this->params->cookies->set('form_' . $i_FID . '_currentpage', $i_CurrentPage);
                    $this->params->cookies->set('form_' . $i_FID . '_limit', $i_PerPage);
                    $this->params->cookies->set('form_' . $i_FID . '_filter', serialize($a_Filter));
                    //$this->params->cookies->set('form_' . $i_FID . '_status', $status);
                    $this->params->cookies->set('form_' . $i_FID . '_uid', $uid);
                    $sql = $o_Form->sz_fGetSQLByUser($this->_user, $i_CurrentPage, $i_PerPage, $fieldorder, $a_Filter,$groupbys,$status,$uid);
                    //change current page if has 
                    $gotoifid = $this->params->requests->getParam('gotoifid', 0);
                    if($gotoifid)
                    {
                        $rowno = $o_Form->o_fGetMainObject()->getRowNumber($sql[0],$gotoifid);
                        if($rowno)
                        {
                            $i_CurrentPage = ceil($rowno / $i_PerPage);
                        }
                    }
                    if($this->_mobile)
                    {
                        echo $this->views->Mobile->Form->Pager($sql, $o_Form, $i_CurrentPage, $i_PerPage,$groupbys,$status,$uid);
                        echo $this->views->Mobile->Form->GridEdit($sql, $o_Form, $i_CurrentPage, $i_PerPage, $fieldorder, $groupbys);
                    }
                    else
                    {
                        echo $this->views->Instance->Form->Pager($sql, $o_Form, $i_CurrentPage, $i_PerPage,$groupbys,$status,$uid);
                        echo $this->views->Instance->Form->GridEdit($sql, $o_Form, $i_CurrentPage, $i_PerPage, $fieldorder, $groupbys);
                    }
                }
            }
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    /**
     *
     * @return void
     */
    public function gridOnlyAction ()
    {
        if ( $this->params->requests->isAjax() )
        {
            $user= $this->params->registers->get('userinfo', null);
            $this->params->responses->clearBody();
            $ifid = $this->params->requests->getParam('ifid', 0);
            $deptid = $this->params->requests->getParam('deptid', 0);
            $objid = $this->params->requests->getParam('objid', 0);
            $fieldid = $this->params->requests->getParam('fieldid', 0);
            $vid = $this->params->requests->getParam('vid', 0);
            $ioid = $this->params->requests->getParam('ioid', 0);

            $i_CurrentPage = $this->params->requests->getParam('pageno', 1);
            $i_PerPage = $this->params->requests->getParam('perpage', 200);
            $i_FieldOrder = $this->params->requests->getParam('fieldorder', 0);
            $i_OrderType = $this->params->cookies->get('detail_' . $objid . '_ordertype', 0);
            $i_GroupBy = $this->params->requests->getParam('groupby', 0);
            $params = $this->params->requests->getParams();
            if ( $i_FieldOrder )
            {
                $i_OrderType = !$i_OrderType;
            }
            $form = new Qss_Model_Form();
            if ( $form->initData($ifid, $deptid) )
            {
                if($this->b_fCheckRightsOnForm($form,15))
                {
                    $object = new Qss_Model_Object();
                    $object->v_fInit($objid, $form->FormCode);
                    if($ioid)//inherit
                    {
                        $sql = $object->sz_fGetSQLOfInherit($objid,$ioid, $i_CurrentPage, $i_PerPage, $i_FieldOrder, $i_OrderType ? 'ASC' : 'DESC', array(),$i_GroupBy);
                    }
                    elseif ( $fieldid ) //all lookup
                    {
                        $dataobject = $form->o_fGetMainObject();
                        $dataobject->initData($form->i_IFID, $form->i_DepartmentID, 0);
                        $sql = $object->sz_fGetReferenceSQL($fieldid, $dataobject->i_fGetRecordIDByFieldID($vid), $i_CurrentPage, $i_PerPage, $i_FieldOrder, $i_OrderType ? 'ASC' : 'DESC', array(),$i_GroupBy);
                    }
                    else
                    {
                        $object->initData($form->i_IFID, $form->i_DepartmentID, 0);
                        $sql = $object->sz_fGetSQLByFormAndUser($form, Qss_Register::get('userinfo'), $i_CurrentPage, $i_PerPage, $i_FieldOrder, $i_OrderType ? 'ASC' : 'DESC', array(),$i_GroupBy);
                    }
                    $this->params->cookies->set('detail_' . $objid . '_ordertype', $i_OrderType);
                    echo $this->views->Instance->Object->DetailPager($sql, $object, $i_CurrentPage,$i_PerPage,$i_GroupBy,$params);
                    echo $this->views->Instance->Object->GridOnly($sql, $form, $object, $i_CurrentPage, $i_PerPage, $i_FieldOrder, $i_OrderType ? 'ASC' : 'DESC',$i_GroupBy,$params,$fieldid);
                }
            }
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    /**
     *
     * @return void
     */
    public function sharingAction ()
    {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $form = new Qss_Model_Form();
        $user = new Qss_Model_Admin_User();
        $form->initData($ifid, $deptid);
        if($this->b_fCheckRightsOnForm($form,18))
        {
            $this->html->form = $form;
            $this->html->user = $user;
            $groupmodel = new Qss_Model_Admin_Group();
            $this->html->groups = $groupmodel->getAll(Qss_Register::get('userinfo')->user_dept_id);
        }
    }

    /**
     *
     * @return void
     */
    public function dosharingAction ()
    {
        $params = $this->params->requests->getParams();
        $ifid = $params['ifid'];
        $deptid = $params['deptid'];
        $form = new Qss_Model_Form();
        $form->initData($ifid, $deptid);
        if($this->b_fCheckRightsOnForm($form,18))
        {
            $service = $this->services->Form->Sharing($form,$params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    /**
     *
     * @return void
     */
    public function deletesharingAction ()
    {
        $ifid = $this->params->requests->getParam('ifid',0);
        $deptid = $this->params->requests->getParam('deptid',0);
        $uid = $this->params->requests->getParam('uid',0);
        $form = new Qss_Model_Form();
        if($form->initData($ifid, $deptid))
        {
            if($this->b_fCheckRightsOnForm($form,18))
            {
                $form->v_fDeleteSharing($uid);
                echo Qss_Json::encode(array('error'=>false));
            }
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    /**
     *
     * @return void
     */
    public function updatesharingAction ()
    {
        $ifid = $this->params->requests->getParam('ifid',0);
        $deptid = $this->params->requests->getParam('deptid',0);
        $uid = $this->params->requests->getParam('uid',0);
        $status = $this->params->requests->getParam('status',0);
        $form = new Qss_Model_Form();
        if($form->initData($ifid, $deptid))
        {
            if($this->b_fCheckRightsOnForm($form,64))
            {
                $form->v_fUpdateSharing($uid,$status);
                echo Qss_Json::encode(array('error'=>false));
            }
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function validateAction ()
    {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $form = new Qss_Model_Form();
        $form->initData($ifid, $deptid);
        $service = $this->services->Form->Validate($form);
        echo $service->getMessage();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    /**
     *
     * @return void
     */
    /*public function getUserStepAction ()
     {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $stepno = $this->params->requests->getParam('step', 0);
        $retval = '<option value="">--- Chọn ---</option>';
        if($stepno)
        {
        $form = new Qss_Model_Form();
        $form->initData($ifid, $deptid);
        $step = new Qss_Model_System_Step($form->i_WorkFlowID);
        $step->v_fInitByStepNumber($stepno);
        //current step
        $cstep = new Qss_Model_System_Step($form->i_WorkFlowID);
        $cstep->v_fInitByStepNumber($form->i_Status);
        $users = $step->a_fGetUsers($this->_user->user_id);
        // check is back
        $uid = 0;
        if(in_array($stepno,explode(',',$cstep->szBackStep)))
        {
        $backuser = $form->getLastUser($form->i_Status,$stepno);
        $uid = $backuser->UID;
        }
        foreach ($users as $user)
        {
        $selected = '';
        if($user->UID == $uid)
        {
        $selected = 'selected';
        }
        $retval .= "<option {$selected} value='{$user->UID}'>{$user->UserName}</option>";
        }
        //foreach ($step->a_fGetUsers($this->_user->user_id) as $user)
        //{
        //$retval .= "<option value='{$user->UID}'>{$user->UserName}</option>";
        //}
        }
        echo $retval;
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
        }*/
    public function getUserGroupAction ()
    {
        $groupid = $this->params->requests->getParam('groupid', 0);
        $usermodel = new Qss_Model_Admin_User();
        $users = $usermodel->a_fGetAllByGroupID($groupid, $this->_user->user_id);
        $retval = '<option value="">--- Chọn ---</option>';
        foreach ($users as $user)
        {
            $retval .= "<option value='{$user->UID}'>{$user->UserName}</option>";
        }
        echo $retval;
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function printCheckAction ()
    {
        $userinfo = $this->params->registers->get('userinfo');
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $print = new Qss_Model_Admin_Print();
        $form = new Qss_Model_Form();
        $form->initData($ifid, $deptid);
        if($this->b_fCheckRightsOnForm($form,31))
        {
            $printers = $print->getByFIDAndStep($form->FormCode,$form->i_Status);
            if(count((array) $printers) == 1 && 0)
            {
                echo $printers[0]->Path .'?designid='.$printers[0]->FPID ;
                $this->setHtmlRender(false);
            }
            else
            {
                $this->html->printers = $printers;
            }
        }
        $this->setLayoutRender(false);
    }
    public function printAction ()
    {
        $userinfo = $this->params->registers->get('userinfo');
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $designid = $this->params->requests->getParam('design', 0);
        $form = new Qss_Model_Form();
        $form->initData($ifid, $deptid);
        if($this->b_fCheckRightsOnForm($form,31))
        {
            $print = new Qss_Model_Print($userinfo->user_dept_id);
            echo $print->sz_fPrintForm($form, $designid);
            //$form->read(Qss_Register::get('userinfo')->user_id);
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    /**
     *
     * Select để chọn từ module lookup
     */
    public function selectAction ()
    {
        if ( $this->params->requests->isAjax() )
        {
            $this->params->responses->clearBody();
            $org_fieldid = $this->params->requests->getParam('fieldid', 0);
            $objid = $this->params->requests->getParam('objid', 0);
            $org_field = new Qss_Model_System_Field();
            $org_field->b_fInit($objid,$org_fieldid);

            $i_FID = $org_field->RefFormCode;
            $i_ObjID = $org_field->RefObjectCode;
            if ( $i_FID )
            {
                $o_Form = new Qss_Model_Form();
                $o_Form->v_fInit($i_FID, $this->_user->user_dept_id, $this->_user->user_id);
                $i_CurrentPage = $this->params->requests->getParam('pageno', 1);
                $i_PerPage = $this->params->requests->getParam('perpage', 200);
                $i_FieldOrder = $this->params->requests->getParam('fieldorder', 0);
                $i_OrderType = $this->params->cookies->get('form_' . $i_FID . '_ordertype', 0);
                $i_GroupBy = $this->params->requests->getParam('groupby', 0);
                $fieldid = $this->params->requests->getParam('fieldid', 0);
                if ( $i_FieldOrder )
                {
                    $i_OrderType = !$i_OrderType;
                }
                else
                {
                    $i_FieldOrder = $i_FieldOrder ? $i_FieldOrder : $this->params->cookies->get('form_' . $i_FID . '_fieldorder', 0);
                }
                $a_Filter = $this->a_fGetFilter();
                $this->params->cookies->set('form_' . $i_FID . '_currentpage', $i_CurrentPage);
                $this->params->cookies->set('form_' . $i_FID . '_limit', $i_PerPage);
                $this->params->cookies->set('form_' . $i_FID . '_filter', serialize($a_Filter));
                $this->params->cookies->set('form_' . $i_FID . '_ordertype', $i_OrderType);
                $this->params->cookies->set('form_' . $i_FID . '_fieldorder', $i_FieldOrder);
                $this->params->cookies->set('form_' . $i_FID . '_groupby', $i_GroupBy);
                $object = $o_Form->o_fGetObjectById($i_ObjID);
                $sql = $object->getSelectSQL($o_Form, $this->_user, $i_CurrentPage, $i_PerPage, $i_FieldOrder, $i_OrderType ? 'ASC' : 'DESC', $a_Filter,$i_GroupBy,$org_field);
                echo $this->views->Instance->Form->SearchSelect($o_Form,$object, $a_Filter);
                echo $this->views->Instance->Form->PagerSelect($sql, $o_Form, $i_CurrentPage, $i_PerPage,$i_GroupBy,$object);
                echo $this->views->Instance->Form->GridSelect($sql, $o_Form, $i_CurrentPage, $i_PerPage, $i_FieldOrder, $i_OrderType ? 'ASC' : 'DESC',$i_GroupBy,$fieldid,$object);
            }
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    /**
     *
     * @return void
     */
    public function commentAction ()
    {
        $ifid = $this->params->requests->getParam('ifid',0);
        $deptid = $this->params->requests->getParam('deptid',0);
        //$uid = $this->params->requests->getParam('uid',0);
        $comment = $this->params->requests->getParam('comment','');
        $form = new Qss_Model_Form();
        if($comment && $form->initData($ifid, $deptid))
        {
            //$form->comment($uid,$comment);
            $service = $this->services->Form->Comment($form, $this->_user, $comment);
            echo $service->getMessage();
        }
        //echo Qss_Json::encode(array('error'=>false));
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }


    /**
     *
     * @return void
     */
    public function commentsAction ()
    {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $uid = $this->params->requests->getParam('uid', 0);
        $form = new Qss_Model_Form();
        $form->initData($ifid, $deptid);
        $this->params->cookies->set('form_' . $form->FormCode. '_object_selected', -3);
        $this->html->comments = $form->getComments();
        $this->html->ifid = $ifid;
        $this->html->deptid = $deptid;
        $this->html->uid = $uid;
        echo $this->params->cookies->get('form_' . $form->FormCode. '_object_selected');
    }
    /**
     *
     * @return void
     */
    public function reportAction ()
    {
        $params = $this->params->requests->getParams();
        $service = $this->services->Form->Report($params);
        echo $service->getMessage();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function excelAction ()
    {
        $i_FID = $this->params->requests->getParam('fid', 0);
        if ( $i_FID )
        {
            $user = $this->params->registers->get('userinfo', null);
            $o_Form = new Qss_Model_Form();
            $o_Form->init($i_FID, $user->user_dept_id, $user->user_id);
            if($this->b_fCheckRightsOnForm($o_Form,4))
            {
                $i_CurrentPage = $this->params->requests->getParam('pageno', 1);
                $i_PerPage = $this->params->requests->getParam('perpage', 200);
                $i_FieldOrder = $this->params->requests->getParam('fieldorder', 0);
                $i_OrderType = $this->params->cookies->get('form_' . $i_FID . '_ordertype', 0);
                $i_GroupBy = $this->params->requests->getParam('groupby', 0);
                if ( $i_FieldOrder )
                {
                    $i_OrderType = !$i_OrderType;
                }
                else
                {
                    $i_FieldOrder = $i_FieldOrder ? $i_FieldOrder : $this->params->cookies->get('form_' . $i_FID . '_fieldorder', 0);
                }
                $a_Filter = $this->a_fGetFilter();
                $this->params->cookies->set('form_' . $i_FID . '_currentpage', $i_CurrentPage);
                $this->params->cookies->set('form_' . $i_FID . '_limit', $i_PerPage);
                $this->params->cookies->set('form_' . $i_FID . '_filter', serialize($a_Filter));
                $this->params->cookies->set('form_' . $i_FID . '_ordertype', $i_OrderType);
                $this->params->cookies->set('form_' . $i_FID . '_fieldorder', $i_FieldOrder);
                $this->params->cookies->set('form_' . $i_FID . '_groupby', $i_GroupBy);
                $sql = $o_Form->sz_fGetSQLByUser($this->_user, $i_CurrentPage, $i_PerPage, $i_FieldOrder, $i_OrderType ? 'ASC' : 'DESC', $a_Filter,$i_GroupBy);
                $service = $this->services->Form->Excel($sql, $o_Form, $i_CurrentPage, $i_PerPage, $i_FieldOrder, $i_OrderType ? 'ASC' : 'DESC',$i_GroupBy);
                $fn = $service->getData();
                if ( file_exists($fn) )
                {
                    $file = $fn;
                    header("Content-type: application/force-download");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-length: " . filesize($file));
                    header("Content-disposition: attachment; filename=\"" . $o_Form->sz_Name . ".xls\"");
                    readfile("$file");
                    unlink($fn);
                    die();
                }
            }
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function countAction ()
    {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $form = new Qss_Model_Form();
        $form->initData($ifid, $deptid);
        $objects = $form->a_fGetSubObjects();
        $retval = array('error'=>false);
        $arrData = array();
        $arrData['form_detail_infor'] = '<nobr>';
        foreach($objects as $object)
        {
            if((!($object->bPublic & 1) && !$this->_mobile) || (!($object->bPublic & 2) && $this->_mobile))
            {
                $arrData['form_detail_infor'] .= $object->sz_Name . ' (' .$object->countAll().') ';
            }
        }
        $arrData['form_detail_infor'] .= '</nobr>';
        //$arrData['action_document'] = $form->countDocument();
        $event = $form->countEvent();
        $arrData['action_event'] = $event;
        //$arrData['action_activity'] = $event;
        //$arrData['action_process_log'] = 'Lịch sử chạy tiến trình (' . $form->countProcessLog().')';
        $retval['message'] = $arrData;
        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function otherAction()
    {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $objid = $this->params->requests->getParam('objid', 0);
        $ioid = $this->params->requests->getParam('ioid', 0);
        $form = new Qss_Model_Form();
        $form->initData($ifid, $this->_user->user_dept_id);
        if($this->b_fCheckRightsOnForm($form,4))
        {
            $bash = new Qss_Model_Bash();
            $this->html->bashes = $bash->getManualByFormAndStep($form->FormCode,$form->i_Status);
            $this->html->form = $form;
            $this->html->objid = $objid;
            $this->html->ioid = $ioid;
        }
        $this->setLayoutRender(false);
    }
    public function bashAction ()
    {
        $id = $this->params->requests->getParam('id', 0);
        $ifid = $this->params->requests->getParam('ifid', 0);
        $form = new Qss_Model_Form();
        if($form->initData($ifid, $this->_user->user_dept_id) && $this->b_fCheckRightsOnForm($form,4))
        {
            $form->o_fGetMainObject()->initData($ifid, $this->_user->user_dept_id, $form->o_fGetMainObject()->i_IOID);
            $service = $this->services->Bash->Execute($form,$id);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    /**
     *
     * @return void
     */
    public function requestAction ()
    {
        $ifids = $this->params->requests->getParam('ifids', '');
        $deptids = $this->params->requests->getParam('deptids', '');
        $stepno = $this->params->requests->getParam('stepno', 0);
        $comment = $this->params->requests->getParam('comment', '');
        $ifids = explode(',',$ifids);
        $deptids = explode(',',$deptids);
        foreach ($ifids as $key=>$ifid)
        {
            $deptid = $deptids[$key];
            $form = new Qss_Model_Form();
            $form->initData($ifid, $deptid);
            $check = $this->b_fCheckRightsOnForm($form,16,$stepno);
            if($check)
            {
                $service = $this->services->Form->Request($form, $stepno, $this->_user, $comment);
            }
        }
        if($check && count($ifids) > 1)
        {
            $service->setError(false);
        }
        if($check)
        {
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function workflowAction ()
    {
        if($this->_mobile)
        {
            $this->html->setHtml(dirname(dirname(__FILE__)).'/html/form/workflow_m.phtml');
        }
        $ifids = $this->params->requests->getParam('ifid', array());
        $deptids = $this->params->requests->getParam('deptid', array());
        $ifid = $ifids[0];
        $deptid = $deptids[0];
        $form = new Qss_Model_Form();
        $form->initData($ifid, $deptid);

        $step = new Qss_Model_System_Step($form->i_WorkFlowID);
        $step->v_fInitByStepNumber($form->i_Status);

        //echo '<pre>'; var_dump($this->b_fCheckRightsOnForm($form,4)); die;

        //if($this->b_fCheckRightsOnForm($form,4))
        {
            $user = new Qss_Model_Admin_User();
            $user->init($form->i_UserID);
            $this->html->cuser = $this->_user;
            $this->html->form = $form;


            $this->html->status = $step->szStepName;
            //$form->read($this->_user->user_id);
            $this->html->single = false;
            /*if(count($ifids) == 1)
            {
                $traces = $form->a_fGetTrace();
                $this->html->traces = $traces;
                $approvers = $step->getApproverByIFID($ifid);
                $this->html->approvers = $approvers;
                $this->html->single = true;
                $ok = false;
                foreach ($approvers as $item)
                {
                    if($step->intStepType == 1 || $step->intStepType == 2)//yêu cầu tất cả được duyệt, có StepNo là đã được duyệt
                    {
                        $ok = true;
                        if(!$item->StepNo)
                        {
                            $ok = false;
                            break;
                        }
                    }
                    elseif($step->intStepType == 3)//Yêu cầu cái cuối cùng
                    {
                        if(!$item->StepNo)
                        {
                            $ok = false;
                        }
                        else
                        {
                            $ok = true;
                        }
                    }
                    elseif($step->intStepType == 4)//Chỉ cần 1 nhóm duyệt
                    {
                        if($item->StepNo)
                        {
                            $ok = true;
                            break;
                        }
                    }
                }
                $this->html->ok = $ok;
            }*/
            $nextsteps = $step->a_fGetNextSteps();
            $backsteps = $step->a_fGetBackSteps();
            //$this->html->nextsteps = $nextsteps;
            //$this->html->backsteps = $backsteps;
            $allow = array();
            foreach ($nextsteps as $item)
            {
                $allow[] = $item->StepNo;
            }
            foreach ($backsteps as $item)
            {
                $allow[] = $item->StepNo;
            }
            $this->html->allow = $allow;
            $this->html->status = $step->szStepName;
            $this->html->allsteps = $step->getAll();
            $this->html->step = $step;
            $this->html->form = $form;
            $this->html->user = $this->_user;
            $this->html->ifids = implode(',',$ifids);
            $this->html->deptids = implode(',',$deptids);
        }
        $this->setLayoutRender(false);
    }
    public function verifyAction ()
    {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $traceid = $this->params->requests->getParam('traceid', 0);
        $accepted = $this->params->requests->getParam('accepted', 0);
        $comment = $this->params->requests->getParam('comment', '');
        $form = new Qss_Model_Form();
        $form->initData($ifid, $deptid);
        //if($this->b_fCheckRightsOnForm($form,512))
        {
            $service = $this->services->Form->Verified($form, $traceid, $accepted ,$comment);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function stopAction ()
    {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $traceid = $this->params->requests->getParam('traceid', 0);
        $form = new Qss_Model_Form();
        $form->initData($ifid, $deptid);
        if($this->b_fCheckRightsOnForm($form,48))
        {
            $service = $this->services->Form->Stop($form, $traceid);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function documentAction()
    {
        if ( $this->params->requests->isAjax() )
        {
            $ifid = $this->params->requests->getParam('ifid', 0);
            $deptid = $this->params->requests->getParam('deptid', 0);
            $popup = $this->params->requests->getParam('popup', 0);
            $form = new Qss_Model_Form();
            if ( $ifid && $deptid )
            {
                $form->initData($ifid, $deptid);
                $this->params->cookies->set('form_' . $form->FormCode. '_object_selected', -1);
                $this->html->documents = $form->getDocuments($ioid);
                $docmodel = new Qss_Model_Admin_Document();
                $this->html->documenttypes = $docmodel->getAllByFormCode($form->FormCode,$form->i_Status);
                $this->html->requires = $form->getRequiredDocuments();
                $this->html->form = $form;
                $this->html->rights = $this->getFormRights($form);
            }
            $this->html->popup = $popup;
        }
        $this->setLayoutRender(false);
    }
    public function secureAction()
    {
        if ( $this->params->requests->isAjax() )
        {
            $ifid = $this->params->requests->getParam('ifid', 0);
            $deptid = $this->params->requests->getParam('deptid', 0);
            $popup = $this->params->requests->getParam('popup', 0);
            $form = new Qss_Model_Form();
            if ( $ifid && $deptid )
            {
                $form->initData($ifid, $deptid);
                $this->params->cookies->set('form_' . $form->FormCode. '_object_selected', -2);
                $this->html->form = $form;
                $this->html->recordrights = $form->getRecordRights();
            }
            $this->html->popup = $popup;
        }
        $this->setLayoutRender(false);
    }
    public function activityAction()
    {
        if ( $this->params->requests->isAjax() )
        {
            $ifid = $this->params->requests->getParam('ifid', 0);
            $deptid = $this->params->requests->getParam('deptid', 0);
            $form = new Qss_Model_Form();
            if ( $ifid && $deptid )
            {
                $form->initData($ifid, $deptid);
                $this->html->activities     = $form->getActivities();
                $activity					= new Qss_Model_Event();
                $this->html->activititype   = $activity->getAllTypeByFID($form->FormCode,$form->i_Status);
                $this->html->requires       = $form->getRequiredActivities();
                $this->html->form 			= $form;
                $this->html->rights = $this->getFormRights($form);
            }
        }
        $this->setLayoutRender(false);
    }
    public function attachAction ()
    {
        $ifid = $this->params->requests->getParam('ifid',0);
        $params = $this->params->requests->getParams();
        $form = new Qss_Model_Form();
        $form->initData($ifid,$this->_user->user_dept_id);
        if($this->b_fCheckRightsOnForm($form,4))
        {
            $service = $this->services->Form->Attach($this->_user->user_id,$form,$params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function dettachAction ()
    {
        $ifid = $this->params->requests->getParam('ifid',0);
        $id = $this->params->requests->getParam('id');
        $form = new Qss_Model_Form();
        $form->initData($ifid,$this->_user->user_dept_id);
        if($this->b_fCheckRightsOnForm($form,4))
        {
            $form->dettach($id);
            echo Qss_Json::encode(array('error' => 0));
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function loadDocumentAction ()
    {
        $ifid = $this->params->requests->getParam('ifid',0);
        $id = $this->params->requests->getParam('id');
        $form = new Qss_Model_Form();
        $form->initData($ifid,$this->_user->user_dept_id);
        if($this->b_fCheckRightsOnForm($form,4))
        {
            $dataSQL = $form->getDocumentById($id);
            $data = array('fdid'=>$dataSQL->FDID,
                'dtid'=>$dataSQL->DTID,
                'docid'=>$dataSQL->DID,
                'docname'=>$dataSQL->docname,
                'reference'=>$dataSQL->Reference);
            echo Qss_Json::encode(array('error' => 0,'data'=>$data));
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function activitySaveAction()
    {
        $ifid = $this->params->requests->getParam('ifid',0);
        $params              = $this->params->requests->getParams();
        $form = new Qss_Model_Form();
        $form->initData($ifid,$this->_user->user_dept_id);
        if($this->b_fCheckRightsOnForm($form,4))
        {
            $params['uid']       = $this->_user->user_id;
            $params['ifid']      = $ifid;
            $params['stepno']    = $form->i_Status;
            $service = $this->services->Event->Log->Save($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function activityRemoveAction()
    {
        $ifid = $this->params->requests->getParam('ifid',0);
        $id   = $this->params->requests->getParam('id');
        $form = new Qss_Model_Form();
        $form->initData($ifid,$this->_user->user_dept_id);
        if($this->b_fCheckRightsOnForm($form,4))
        {
            $form->removeActivity($id);
            echo Qss_Json::encode(array('error' => 0));
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function activityLoadAction()
    {
        $ifid = $this->params->requests->getParam('ifid',0);
        $id = $this->params->requests->getParam('id');
        $form = new Qss_Model_Form();
        $form->initData($ifid,$this->_user->user_dept_id);
        if($this->b_fCheckRightsOnForm($form,2))
        {
            $dataSQL = $form->getActivityById($id);
            $data = array('elid'=>$dataSQL->ELID,
                'uid'=>$dataSQL->UID,
                'eventid'=>$dataSQL->EventID,
                'ifid'=>$dataSQL->IFID,
                'stepno'=>$dataSQL->StepNo,
                'etid'=>$dataSQL->ETID,
                'date'=>Qss_Lib_Date::mysqltodisplay($dataSQL->Date),
                'stime'=>$dataSQL->STime,
                'etime'=>$dataSQL->ETime,
                'status'=>$dataSQL->Status,
                'note'=>$dataSQL->Note,
                'typename'=>$dataSQL->TypeName,
                'name'=>$dataSQL->Name);
            echo Qss_Json::encode(array('error' => 0,'data'=>$data));
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function updownAction ()
    {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $type = $this->params->requests->getParam('type', false);
        $service = $this->services->Form->Updown($ifid, $deptid,$type);
        echo $service->getMessage();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function popupEditAction ()
    {
        $params = $this->params->requests->getParams();
        $service = $this->services->Form->Refresh($params);
        $form = $service->getData();
        if($this->b_fCheckRightsOnForm($form,31))
        {
            foreach ($form->o_fGetMainObjects() as $object)
            {
                foreach ($object->loadFields() as $f)
                {
                    if(isset($params[$f->ObjectCode.'_'.$f->FieldCode]))
                    {
                        //$f->bReadOnly = true;
                    }
                }
            }
            $this->html->formedit = $this->views->Instance->Form->Edit($form, $this->_user,true);
            $this->html->form = $form;
        }
        $this->setLayoutRender(false);
    }
    public function approveAction ()
    {
        $said = $this->params->requests->getParam('said', 0);
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $form = new Qss_Model_Form();
        $form->initData($ifid, $deptid);
        $step = new Qss_Model_System_Step($form->i_WorkFlowID);
        $step->v_fInitByStepNumber($form->i_Status);
        $approvers = $step->getApproverByUser($ifid, $this->_user->user_id);
        $last = true;
        $ret = Qss_Json::encode(array('error'=>false));
        foreach ($approvers as $item)
        {
            if($item->UID == $this->_user->user_id)
            {
                if($step->intStepType == 1)//yêu cầu tuần tự, có StepNo là đã được duyệt
                {
                    if(!$item->StepNo && $last)
                    {
                        //Save
                        $form->approve($said,$this->_user->user_id);
                        $form->updateApproveCount();
                        $mail = new Qss_Lib_Notify_Mail($form);
                        $mail->init($step);
                        $mail->approve($this->_user,$item);
                        //Thông báo cho các ông tiếp theo & liên quan
                    }
                }
                else//Bất cứ khi nào
                {
                    if(!$item->StepNo)
                    {
                        //save
                        $form->approve($said,$this->_user->user_id);
                        $form->updateApproveCount();
                        //Thông báo cho các ông tiếp theo & liên quan
                        $mail = new Qss_Lib_Notify_Mail($form);
                        $mail->init($step);
                        $mail->approve($this->_user,$item);
                    }
                }
            }
            $last = (bool)$item->StepNo;
        }
        $ok = false;
        $approvers = $step->getApproverByIFID($ifid);
        foreach ($approvers as $item)
        {
            if($step->intStepType == 1 || $step->intStepType == 2)//yêu cầu tất cả được duyệt, có StepNo là đã được duyệt
            {
                $ok = true;
                if(!$item->StepNo)
                {
                    $ok = false;
                    break;
                }
            }
            elseif($step->intStepType == 3)//Yêu cầu cái cuối cùng
            {
                if(!$item->StepNo)
                {
                    $ok = false;
                }
                else
                {
                    $ok = true;
                }
            }
            elseif($step->intStepType == 4)//Chỉ cần 1 nhóm duyệt
            {
                if($item->StepNo)
                {
                    $ok = true;
                    break;
                }
            }
        }
        //echo $ok.'----';die;
        if($ok)
        {
            $stepno = $step->a_fGetNextSteps();
            $stepno = $stepno[0]->StepNo;
            $service = $this->services->Form->Request($form, $stepno, $this->_user, '');
            $ret = $service->getMessage();
        }
        echo $ret;
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function rejectAction ()
    {
        $said = $this->params->requests->getParam('said', 0);
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $form = new Qss_Model_Form();
        $form->initData($ifid, $deptid);
        $step = new Qss_Model_System_Step($form->i_WorkFlowID);
        $step->v_fInitByStepNumber($form->i_Status);
        $approvers = $step->getApproverByUser($ifid,$this->_user->user_id);
        $last = true;
        $ret = Qss_Json::encode(array('error'=>false));
        foreach ($approvers as $item)
        {
            if($item->UID == $this->_user->user_id)
            {
                $form->reject($said,$this->_user->user_id);
                $form->updateApproveCount();
            }
        }
        echo $ret;
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    public function popupAction ()
    {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/popup.php';
        if($this->_mobile)
        {
            $this->html->setHtml(dirname(dirname(__FILE__)).'/html/form/popup_m.phtml');
            $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/mobile.php';
        }

        $this->headScript($this->params->requests->getBasePath() . '/js/object-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/popup-select.js');
        $params = $this->params->requests->getParams();
        $service = $this->services->Form->Refresh($params);
        $form = $service->getData();
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $fid = $this->params->requests->getParam('fid', 0);
        /*$form = new Qss_Model_Form();
        if ( $ifid && $deptid )
        {
            $form->initData($ifid, $deptid);
        }
        else
        {
            $form->init($fid, Qss_Register::get('userinfo')->user_dept_id, Qss_Register::get('userinfo')->user_id);
        }*/
        //$this->_title = $form->sz_Name;
        if($this->b_fCheckRightsOnForm($form,31))
        {
            $objects = $form->o_fGetMainObjects();
            foreach($objects as $object)
            {
                $object->initData($form->i_IFID, $form->i_DepartmentID, 0);
                $object->loadFields();
            }
            $fid = $form->FormCode;
            /*GetNexPrev*/
            $currentpage = (int) $this->params->cookies->get('form_' . $fid . '_currentpage', 1);
            $currentpage = $currentpage ? $currentpage : 1;
            $limit = $this->params->cookies->get('form_' . $fid . '_limit', 200);
            $fieldorder = $this->params->cookies->get('form_' . $fid . '_fieldorder', 'a:0:{}');
            $filter = $this->params->cookies->get('form_' . $fid . '_filter', 'a:0:{}');
            $i_GroupBy = $this->params->cookies->get('form_' . $fid . '_groupby', '0');
            $i_Status = $this->params->cookies->get('form_' . $fid . '_status', 'a:0:{}');
            $status = unserialize($i_Status);
            $uid = $this->params->cookies->get('form_' . $fid . '_uid', '');
            $filter = unserialize($filter);
            foreach($objects as $object)
            {
                //$sql = $object->sz_fGetSQLByFormAndUser($form, $this->_user, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC', $filter,$i_GroupBy);
                $sql = $form->sz_fGetSQLByUser($this->_user, $currentpage, $limit, $fieldorder, $filter,$i_GroupBy,$status,$uid,false);
                $navigator = $object->getNextPrevRecord($sql[0],$ifid,true);
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
            }
            //$form->read($this->_user->user_id);
            $this->html->form = $form;
            $this->html->user_id = $this->_user->user_id;
            $this->html->formedit = $this->views->Instance->Form->Edit($form, $this->_user);
            //$this->html->formpopup = $this->views->Instance->Form->Popup($form, $this->_user);
            $this->html->documentcount = $form->countDocument();
            $this->html->eventcount = $form->countEvent();
            $rights = $this->getFormRights($form);
            //sub object
            $subobjects = $form->a_fGetSubObjects();
            $activesubobjects = $form->a_fGetSubObjects();
            foreach ($activesubobjects as $k=>$v)
            {
                if((($v->bPublic & 1) && !$this->_mobile) || (($v->bPublic & 2) && $this->_mobile))
                {
                    unset($activesubobjects[$k]);
                }
            }
            $this->html->subobjects = $subobjects;
            $this->html->activesubobjects = $activesubobjects;
            $this->html->searchform = $this->views->Instance->Form->Search($form, $filter);
            if(count($subobjects))
            {
                $subobjects = array_values($subobjects);
                $this->html->subobject = $subobjects[0];
            }
            if($rights & 4)
            {
                $bash = new Qss_Model_Bash();
                $this->html->bashes = $bash->getManualByForm($form->FormCode);
            }
            else
            {
                $this->html->bashes = array();
            }
            //Approve
            if($form->i_Status && $ifid)
            {
                $step = new Qss_Model_System_Step($form->i_WorkFlowID);
                $step->v_fInitByStepNumber($form->i_Status);
                //$nextsteps = $step->a_fGetNextSteps();
                //$backsteps = $step->a_fGetBackSteps();
                //$this->html->nextsteps = $nextsteps;
                //$this->html->backsteps = $backsteps;
                $approvers = $step->getApproverByUser($ifid,$this->_user->user_id);
                $arrApproved = array();
                $arrNotApproved = array();
                foreach ($approvers as $item)
                {
                    if($item->AUID == $this->_user->user_id)
                    {
                        $arrApproved[] = $item;
                    }
                    elseif($item->UID == $this->_user->user_id)
                    {
                        $arrNotApproved[] = $item;
                    }
                }
            }
            $this->html->arrApproved = $arrApproved;
            $this->html->arrNotApproved = $arrNotApproved;
            $this->html->rights = $rights;
        }
    }
    public function changeDepartmentAction ()
    {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $deptid = $this->params->requests->getParam('deptid', 0);
        $form = new Qss_Model_Form();
        if($form->initData($ifid, $deptid))
        {
            $form->changeDepartment($deptid);
        }
        echo Qss_Json::encode(array('error'=>0));
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);

    }
    public function secureSaveAction()
    {
        $ifid = $this->params->requests->getParam('ifid',0);
        $users              = $this->params->requests->getParam('userRecordRights',array());
        $form = new Qss_Model_Form();
        $form->initData($ifid,$this->_user->user_dept_id);
        if($this->b_fCheckRightsOnForm($form,4))
        {
            $form->saveRecordRights($users);
            echo Qss_Json::encode(array('error'=>0));
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}
?>