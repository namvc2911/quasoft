<?php
class Qss_View_Object_ODanhSachNhapKho extends Qss_View_Abstract
{
   public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder ,$i_GroupBy)
    {
        $ifid = $form->i_IFID;
        $this->html->pager = $this->views->Instance->Object->Pager($sql, $object, $currentpage,$limit,$i_GroupBy);
        $this->html->grid = $this->views->Instance->Object->GridEdit($sql, $form, $object, $currentpage, $limit, $fieldorder, $i_GroupBy);


        $user   = Qss_Register::get('userinfo');
        $rights = $form->i_fGetRights($user->user_group_list);
        if($rights & 4)
        {
            $bash = new Qss_Model_Bash();
            $this->html->bashes = $bash->getManualByObjID( 'M402' , 'ODanhSachNhapKho');
        }
        else
        {
            $this->html->bashes = array();
        }

        $mInv               = new Qss_Model_Inventory_Inventory();
        $user               = Qss_Register::get('userinfo');
        $mForm              = new Qss_Model_Form();
        $model              = new Qss_Model_Inventory_Inventory();
        $in                 = $model->getInputByIFID($ifid);
        $form               = $mForm->initData($ifid, $user->user_dept_id);
        $object             = $mForm->o_fGetMainObject();
        $outObject          = $mForm->o_fGetObjectByCode('ODanhSachNhapKho');
        $form_rights        = $mForm->i_fGetRights($user->user_group_list);

        if(!$outObject->bInsert)
        {
            $form_rights = $form_rights & ~Qss_Lib_Const::FORM_RIGHTS_CREATE;
        }

        if(!$outObject->bEditing)
        {
            $form_rights = $form_rights & ~Qss_Lib_Const::FORM_RIGHTS_UPDATE;
        }

        if(!$outObject->bDeleting)
        {
            $form_rights = $form_rights & ~Qss_Lib_Const::FORM_RIGHTS_DELETE;
        }

        $rights = $form_rights;
        $rights = $rights & $outObject->intRights;

        $this->html->oldBin = $mInv->getBinConfigOfInputLine($ifid);
        $this->html->input  = $in;
        $this->html->data   = $model->getInputLineByInputIFID($ifid);
        $this->html->deptid = $user->user_dept_id;
        $this->html->fRight = $form_rights;
        $this->html->right  = $rights;
        $this->html->status = $mForm->i_Status;
        $this->html->ifid   = $ifid?$ifid:0;



//        $user                       = Qss_Register::get('userinfo');
//        $mForm                      = new Qss_Model_Form();
//        $form                       = $mForm->initData($ifid, $user->user_dept_id);
//        $mObject                    = new Qss_Model_Object();
//        $object                     = $mObject->v_fInit('ODanhSachNhapKho', 'M402');
//
//        // echo '<pre>'; print_r($mObject); die;
//
//        $this->html->user           = $user;
//        $this->html->objects        = $mObject->a_fGetIOIDBySQL($sql, $currentpage, $limit);
//        $this->html->gridFieldCount = $object->getGridFieldCount() + 1;
//        $this->html->fields         = $object->loadFields();
//        $this->html->o_Object       = $object;


        //---------------------------------------------------------------------------------

//        $object = $form->o_fGetMainObject();
//        $this->html->user = Qss_Register::get('userinfo');
//        $this->html->objects = $object->a_fGetIOIDBySQL($sql[0], $currentpage, $limit);
//        $fcount = 0;
//        foreach($form->o_fGetMainObjects() as $item)
//        {
//            $fcount += $item->getGridFieldCount();
//        }
//        $this->html->gridFieldCount = $fcount + 1;
//        $this->html->mainobjects = $form->o_fGetMainObjects();
//        $this->html->o_Object = $object;
//        $stepmodel = new Qss_Model_System_Step($form->i_WorkFlowID);
//        $steps = $stepmodel->getAll();
//        $arrname = array();
//        $arrcolor = array();
//        $arrformrights = array();
//        $lang = Qss_Translation::getInstance()->getLanguage();
//        $lang = ($lang=='vn')?'':'_'.$lang;
//        foreach ($steps as $step)
//        {
//            $arrname[$step->StepNo] = $step->{"Name".$lang};
//            $arrcolor[$step->StepNo] = $step->Color;
//            $arrformrights[$step->StepNo] = $step->FormRights;
//        }
//        $this->html->arrname = $arrname;
//        $this->html->arrcolor = $arrcolor;
//        $this->html->arrformrights = $arrformrights;
    }
}
?>