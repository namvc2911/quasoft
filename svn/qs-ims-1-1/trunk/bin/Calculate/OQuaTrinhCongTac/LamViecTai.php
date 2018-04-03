<?php
class Qss_Bin_Calculate_OQuaTrinhCongTac_LamViecTai extends Qss_Lib_Calculate {
    public function __doExecute() {
        $html        = '';
        $refNhanVien = (int)$this->_object->getFieldByCode('MaNhanVien')->getRefIOID();

        $nhanVien = $this->_db->fetchOne(sprintf('SELECT * FROM ODanhSachNhanVien WHERE IOID = %1$d', $refNhanVien));

        if($nhanVien) {
            // Lấy toàn bộ phòng ban cha của nhân viên
            $sql = sprintf('
                SELECT *
                FROM OPhongBan
                WHERE lft <= (SELECT lft FROM OPhongBan WHERE IOID = %1$d)
                AND rgt >= (SELECT rgt FROM OPhongBan WHERE IOID = %1$d)
                ORDER BY lft
            ', $nhanVien->Ref_MaPhongBan);
            $data = $this->_db->fetchAll($sql);

            foreach ($data as $item) {
                $html .= "<p><span style=\"display:inline-block; width: 70px;\">{$item->Ref_Loai} :</span>  {$item->TenPhongBan} ({$item->MaPhongBan})</p>";
            }
        }

        return $html;
    }
}
?>