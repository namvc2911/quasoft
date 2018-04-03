<?php
class Qss_Bin_Import_M705 extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $sql = "
            UPDATE ODanhSachThietBi
            SET TrangThai = 0
            WHERE TrangThai IS NULL 
        ";
        $this->_db->execute($sql);
    }

}