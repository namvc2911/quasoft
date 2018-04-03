<?php
class Qss_View_Extra_Search_M026 extends Qss_View_Abstract
{

    public function __doExecute ($form)
    {
        $mCommon   = new Qss_Model_Extra_Extra();
        $filter    = Qss_Cookie::get('form_' . $form->FormCode . '_filter', 'a:0:{}');
        $filter    = unserialize($filter);
        $iPhongBan = (int) @$filter['phongban'];
        $oPhongBan = $mCommon->getTableFetchOne('OPhongBan', array('IOID'=>$iPhongBan));

        $iKyCong = (int) @$filter['kycong'];
        $oKyCong = $mCommon->getTableFetchOne('OKyCong', array('IOID'=>$iKyCong));



        $this->html->selectedTextPhongBan = $oPhongBan?"{$oPhongBan->MaPhongBan} - {$oPhongBan->TenPhongBan}":'';
        $this->html->selectedPhongBan     = (int) @$filter['phongban'];

        $this->html->selectedTextKyCong = $oKyCong?"{$oKyCong->MaKyCong} - {$oKyCong->TenKyCong}":'';
        $this->html->selectedKyCong     = (int) @$filter['kycong'];
    }
}
?>