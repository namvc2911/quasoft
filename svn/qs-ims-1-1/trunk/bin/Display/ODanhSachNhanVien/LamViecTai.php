<?php
class Qss_Bin_Display_ODanhSachNhanVien_LamViecTai extends Qss_Lib_Style
{
    public function __doExecute() 
    {
        $html        = '';
        $refPhongBan = $this->_data->Ref_MaPhongBan;

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