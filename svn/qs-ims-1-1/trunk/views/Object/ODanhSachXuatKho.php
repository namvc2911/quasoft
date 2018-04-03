<?php
class Qss_View_Object_ODanhSachXuatKho extends Qss_View_Abstract
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
            $this->html->bashes = $bash->getManualByObjID( 'M506' , 'ODanhSachXuatKho');
        }
        else
        {
            $this->html->bashes = array();
        }

        $mInv               = new Qss_Model_Inventory_Inventory();
        $user               = Qss_Register::get('userinfo');
        $mForm              = new Qss_Model_Form();
        $model              = new Qss_Model_Inventory_Inventory();
        $out                = $model->getOutputByIFID($ifid);
        $form               = $mForm->initData($ifid, $user->user_dept_id);
        $object             = $mForm->o_fGetMainObject();
        $outObject          = $mForm->o_fGetObjectByCode('ODanhSachXuatKho');
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

        $this->html->oldBin  = $mInv->getBinConfigOfOutputLine($ifid);
        $this->html->output  = $out;
        $this->html->data    = $model->getOutputLineByOuputIFID($ifid);
        $this->html->deptid  = $user->user_dept_id;
        $this->html->fRight  = $form_rights;
        $this->html->right   = $rights;
        $this->html->status  = $mForm->i_Status;
        $this->html->ifid    = $ifid?$ifid:0;
    }
}