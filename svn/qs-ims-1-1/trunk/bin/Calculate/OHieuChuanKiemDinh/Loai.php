<?php
class Qss_Bin_Calculate_OHieuChuanKiemDinh_Loai extends Qss_Lib_Calculate
{
    protected static $_arrHieuChuanKiemDinh = array();

    public function __doExecute()
    {
        if(Qss_Lib_System::fieldActive('OHieuChuanKiemDinh', 'TenHieuChuan'))
        {
            $common            = new Qss_Model_Extra_Extra();
            $hieuChuanKiemDinh = @(int)$this->_object->getFieldByCode('TenHieuChuan')->intRefIOID;

            if (!$hieuChuanKiemDinh)
            {
                $line              = $common->getTableFetchOne('OHieuChuanKiemDinh', array('IFID_M753' => $this->_object->i_IFID));
                $hieuChuanKiemDinh = @(int)$line->Ref_TenHieuChuan;
            }

            $sql = sprintf('
            SELECT *
            FROM OQuyDinhHieuChuanKiemDinh
            WHERE IOID = %1$d
        ', $hieuChuanKiemDinh);

            $dat  = $this->_db->fetchOne($sql);

            return $dat?$dat->Ref_Loai:'';
        }
    }
}