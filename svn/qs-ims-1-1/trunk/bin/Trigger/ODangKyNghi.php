<?php
class Qss_Bin_Trigger_ODangKyNghi extends Qss_Lib_Trigger {
    /**
     * Kiểm tra xem có vượt quá giới hạn cho phép không
     * @param $object
     */
    public function onInsert($object) {
        parent::init();
        $mM043       = new Qss_Model_M043_Main();
        $refNhanVien = $object->getFieldByCode('MaNhanVien')->getRefIOID();
        $refLoaiNghi = $object->getFieldByCode('LoaiNgayNghi')->getRefIOID();
        $SoGioNghi   = $object->getFieldByCode('SoGioNghi')->getValue();

        if(!$refNhanVien) {
            $empl        = $mM043->getEmployeeByUser($this->_user->user_id);
            $refNhanVien = @(int)$empl->IOID;
        }

        $quyNghi     = $mM043->getNghiCuaNhanVien2($refNhanVien, date('Y'), $refLoaiNghi);

        // echo '<pre>';print_r($refLoaiNghi); die;
        // Check ko cho dăng ký
        if(
            !isset($quyNghi[$refLoaiNghi]['ConLaiTheoGio']) ||
            (
                $quyNghi[$refLoaiNghi]['ConLaiTheoGio'] <= 0
                || $quyNghi[$refLoaiNghi]['ConLaiTheoGio'] < $SoGioNghi
            )
        ) {
            $this->setError();
            $this->setMessage('Vượt quá giới hạn cho phép!');
        }
    }
}
?>