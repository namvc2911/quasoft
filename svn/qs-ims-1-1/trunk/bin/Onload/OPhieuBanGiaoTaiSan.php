<?php
class Qss_Bin_Onload_OPhieuBanGiaoTaiSan extends Qss_Lib_Onload
{
    /**
     * Dua vao can cu an hien chi so va ky bao duong
     */
    public function __doExecute()
    {
        parent::__doExecute();

//        if($this->_object->i_IOID)
//        {
//            $this->_object->getFieldByCode('SoPhieu')->bReadOnly = true;
//        }
    }

    /**
     * Chi lay cac phieu thu hoi da dong
     */
    public function PhieuThuHoi()
    {
        $this->_object->getFieldByCode('PhieuThuHoi')->arrFilters[] = sprintf('
            v.IFID_M183 in
            (
                SELECT Phieu.IFID_M183
                FROM OPhieuThuHoiTaiSan AS Phieu
                INNER JOIN qsiforms AS IForm ON Phieu.IFID_M183 = IForm.IFID
                WHERE IFNULL(IForm.Status, 0) = 2
            )');
    }
}