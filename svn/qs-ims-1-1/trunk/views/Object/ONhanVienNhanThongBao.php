<?php
class Qss_View_Object_ONhanVienNhanThongBao extends Qss_View_Abstract
{
    public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder,$i_GroupBy)
    {
        $ifid   = $form->i_IFID;

        $mTable = Qss_Model_Db::Table('ODanhSachNhanVien');
        $mTable->join(sprintf('LEFT JOIN ONhanVienNhanThongBao ON ODanhSachNhanVien.IOID = ONhanVienNhanThongBao.Ref_MaNV 
        					AND ONhanVienNhanThongBao.IFID_M856 = %1$d', $ifid));
        $mTable->select(' ODanhSachNhanVien.* ');
        $mTable->select(' IFNULL(ONhanVienNhanThongBao.IOID, 0) AS SelectedID ');
        $mTable->where('ifnull(ThoiViec,0) = 0');
        $mTable->orderby('MaPhongBan');
        $mTable->orderby('MaNhanVien');

        $this->html->data = $mTable->fetchAll();
        $this->html->ifid = $ifid;
    }
}
?>