<?php
class Qss_View_Object_OChiTietNghiNhanVien extends Qss_View_Abstract
{
    public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder ,$i_GroupBy) {
        $retval  = array();
        $model   = new Qss_Model_M043_Main();
        $mCommon = new Qss_Model_Extra_Extra();
        $ifid    = $form->i_IFID;
        $oDangKy = $mCommon->getTableFetchOne('ODangKyNghi', array('IFID_M077'=>$ifid));

        $ngayDangKy  = ($oDangKy && $oDangKy->NgayBatDau)?$oDangKy->NgayBatDau:'';
        $nguoiDangKy = ($oDangKy && $oDangKy->Ref_MaNhanVien)?$oDangKy->Ref_MaNhanVien:0;

        if($ngayDangKy && $nguoiDangKy) {
            $retval = $model->getNghiCuaNhanVien2($nguoiDangKy, date('Y', strtotime($ngayDangKy)));
        }

        $this->html->data = $retval;
    }
}
?>