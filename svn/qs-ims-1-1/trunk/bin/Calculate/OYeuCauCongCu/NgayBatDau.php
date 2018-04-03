<?php
class Qss_Bin_Calculate_OYeuCauCongCu_NgayBatDau extends Qss_Lib_Calculate
{
    protected static $ngayBatDau  = '';

    public function __doExecute()
    {
        // Thêm ngày bắt đầu kết thúc mặc định khi tạo mới
        if(
            Qss_Lib_System::fieldActive('OYeuCauTrangThietBiVatTu', 'NgayBatDau')
            && @(int)$this->_object->i_IOID == 0
        ) {

            if(self::$ngayBatDau === '') {
                $sql = sprintf('SELECT * FROM OYeuCauTrangThietBiVatTu WHERE IFID_M751 = %1$d', $this->_object->i_IFID);
                $dat = $this->_db->fetchOne($sql);

                self::$ngayBatDau  = $dat?$dat->NgayBatDau:0;  // 0 với trường hợp đã lấy nhưng chưa có giá trị
            }

            return self::$ngayBatDau;
        }
    }
}
?>