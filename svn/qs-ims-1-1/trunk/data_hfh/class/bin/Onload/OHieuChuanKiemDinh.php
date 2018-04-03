<?php

class Qss_Bin_Onload_OHieuChuanKiemDinh extends Qss_Lib_Onload
{
    public function __doExecute()
    {
        parent::__doExecute(); // TODO: Change the autogenerated stub

        $maThietBi = $this->_object->getFieldByCode('MaThietBi')->getRefIOID();

        $sql = sprintf('
            SELECT ODoiTac.*
            FROM ODanhSachThietBi
            INNER JOIN ODoiTac ON IFNULL(ODanhSachThietBi.Ref_HangBaoHanh, 0) = ODoiTac.IOID
            WHERE ODanhSachThietBi.IOID = %1$d
        ', $maThietBi);
        $dat  = $this->_db->fetchOne($sql);

        if($dat && !$this->_object->getFieldByCode('DonVi')->getValue())
        {
            $this->_object->getFieldByCode('DonVi')->setRefIOID($dat->IOID);
            $this->_object->getFieldByCode('DonVi')->setValue($dat->TenDoiTac);
        }
    }

    public function MaThietBi()
    {
        if(Qss_Lib_System::formActive('M843'))
        {
            $this->_object->getFieldByCode('MaThietBi')->arrFilters[] =
                sprintf('
                v.IOID in (
                    SELECT IOID
                    FROM ODanhSachThietBi
                    WHERE IFNULL(KiemDinh, 0) = 1
                )
            ');
        }


    }
}
