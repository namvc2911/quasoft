<?php
class Qss_Bin_Calculate_OYeuCauDieuDong_NgayKetThuc extends Qss_Lib_Calculate
{
    protected static $ngayKetThuc  = '';

    public function __doExecute()
    {
        // Thêm ngày bắt đầu kết thúc mặc định khi tạo mới
        if(
            Qss_Lib_System::fieldActive('OYeuCauTrangThietBiVatTu', 'NgayKetThuc')
            && @(int)$this->_object->i_IOID == 0
        ) {

            if(self::$ngayKetThuc === '') {
                $sql = sprintf('SELECT * FROM OYeuCauTrangThietBiVatTu WHERE IFID_M751 = %1$d', $this->_object->i_IFID);
                $dat = $this->_db->fetchOne($sql);

                self::$ngayKetThuc  = $dat?$dat->NgayKetThuc:0;  // 0 với trường hợp đã lấy nhưng chưa có giá trị
            }

            return self::$ngayKetThuc;
        }
    }
}
?>