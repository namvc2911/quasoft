<?php
class Qss_Bin_Calculate_OQuaTrinhCongTac_NoiLamViecMoi extends Qss_Lib_Calculate {
    public function __doExecute() {
        $html        = '';
        $refPhongBan = (int)$this->_object->getFieldByCode('PhongBanMoi')->getRefIOID();

        // Lấy toàn bộ phòng ban cha của nhân viên
        $sql = sprintf('
            SELECT *
            FROM OPhongBan
            WHERE lft <= (SELECT lft FROM OPhongBan WHERE IOID = %1$d)
            AND rgt >= (SELECT rgt FROM OPhongBan WHERE IOID = %1$d)
            ORDER BY lft
        ', $refPhongBan);
        $data = $this->_db->fetchAll($sql);

        foreach ($data as $item) {
            $html .= "<p><span style=\"display:inline-block; width: 70px;\">{$item->Ref_Loai} :</span>  {$item->TenPhongBan} ({$item->MaPhongBan})</p>";
        }

        return $html;
    }
}
?>