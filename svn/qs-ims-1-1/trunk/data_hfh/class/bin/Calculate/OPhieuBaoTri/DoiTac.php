<?php
class Qss_Bin_Calculate_OPhieuBaoTri_DoiTac extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $refThietBi = (int)$this->_object->getFieldByCode('MaThietBi')->getRefIOID(); // Thiết bị
        $doiTac     = $this->_object->getFieldByCode('DoiTac')->getValue(); // Đối tác

        if(!$refThietBi) // Đối với trên grid cần phải lấy trong csdl
        {
            $sql = $this->_db->fetchOne(sprintf('SELECT * FROM OKeHoachBaoTri WHERE IFID_M837 = %1$d ', $this->_object->i_IFID));

            if($sql)
            {
                $refThietBi = (int)$sql->Ref_MaThietBi;
                $doiTac     = $sql->DoiTac;
            }
        }

        if($refThietBi && !$doiTac)
        {
            $mTable  = Qss_Model_Db::Table('ODanhSachThietBi');
            $mTable->where(sprintf(' IOID = %1$d ', $refThietBi));
            $thietBi = $mTable->fetchOne(); // Lấy ra thiết bị theo IOID lấy được từ bản ghi

            if($thietBi && $thietBi->HangBaoHanh)
            {
                return $thietBi->HangBaoHanh; // Trả về tên đối tác
            }
        }
        // Chú ý: Luôn return khi có calculate
    }
}
?>