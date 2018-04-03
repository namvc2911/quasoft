<?php
class Qss_View_Object_MNotifyUser extends Qss_View_Abstract
{
    public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder,$i_GroupBy)
    {
        $ifid   = $form->i_IFID;

        $mTable = Qss_Model_Db::Table('ODanhSachNhanVien');
        $mTable->join(sprintf('LEFT JOIN MNotifyUser ON ODanhSachNhanVien.IOID = MNotifyUser.Ref_MaNV 
        					AND MNotifyUser.IFID_C005 = %1$d', $ifid));
        $mTable->select(' ODanhSachNhanVien.* ');
        $mTable->select(' IFNULL(MNotifyUser.IOID, 0) AS SelectedID ');
        $mTable->where('ifnull(ThoiViec,0) = 0');
        $mTable->orderby('MaPhongBan');
        $mTable->orderby('MaNhanVien');

        $this->html->data = $mTable->fetchAll();
        $this->html->ifid = $ifid;
    }
}
?>