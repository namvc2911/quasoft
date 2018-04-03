<?php
class Qss_Bin_Calculate_OPhieuBaoTri_NgayBatDau extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $ngayBatDau = $this->_object->getFieldByCode('NgayBatDau')->getValue();

        if(!$ngayBatDau) // Đối với trên grid cần phải lấy trong csdl
        {
            $sql = $this->_db->fetchOne(sprintf('SELECT * FROM OPhieuBaoTri WHERE IFID_M759 = %1$d ', $this->_object->i_IFID));

            if($sql)
            {
                $ngayBatDau = $sql->NgayBatDau;
            }
        }

        if(!$ngayBatDau)
        {
            return date('d-m-Y');
        }
    }
}
?>