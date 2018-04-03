<?php
class Qss_Bin_Onload_OLichThietBi extends Qss_Lib_Onload {
    public function __doExecute() {
        parent::__doExecute();
    }

    public function PhieuYeuCau() {
        $iDuAn = $this->_object->getFieldByCode('DuAn')->getRefIOID();

        $this->_object->getFieldByCode('PhieuYeuCau')->arrFilters[] = sprintf('
            v.Ref_DuAn in (
                SELECT Ref_DuAn 
                FROM OYeuCauTrangThietBiVatTu
                WHERE IFNULL(Ref_DuAn, 0) = %1$d
            )
        ', $iDuAn);
    }
}