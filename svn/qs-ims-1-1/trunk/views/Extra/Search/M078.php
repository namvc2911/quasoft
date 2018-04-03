<?php
class Qss_View_Extra_Search_M078 extends Qss_View_Abstract
{

    public function __doExecute ($form)
    {
        $mCommon   = new Qss_Model_Extra_Extra();
        $filter    = Qss_Cookie::get('form_' . $form->FormCode . '_filter', 'a:0:{}');
        $filter    = unserialize($filter);
        $iPhongBan = (int) @$filter['phongban'];
        $oPhongBan = $mCommon->getTableFetchOne('OPhongBan', array('IOID'=>$iPhongBan));


        $this->html->selectedTextPhongBan = $oPhongBan?"{$oPhongBan->MaPhongBan} - {$oPhongBan->TenPhongBan}":'';
        $this->html->selectedPhongBan     = (int) @$filter['phongban'];
    }
}
?>