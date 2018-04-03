<?php
class Qss_Bin_Calculate_OYeuCauDieuDong_SoLuongSanCo extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $loaiThietBi = @(int)$this->_object->getFieldByCode('LoaiThietBi')->getRefIOID();
        $user        = Qss_Register::get('userinfo');

        if(!$loaiThietBi)
        {
            $mTable = Qss_Model_Db::Table('OYeuCauTrangThietBi');
            $mTable->where(sprintf(' IOID = %1$d ', @(int)$this->_ioid));
            $data = $mTable->fetchOne();

            $loaiThietBi = @(int)$data->Ref_LoaiThietBi;
        }

        $sql = sprintf('
            SELECT COUNT(1) AS SoLuongSanSang
            FROM ODanhSachThietBi
            WHERE IFNULL(Ref_LoaiThietBi, 0) = %1$d
                AND IFNULL(DeptID, 0) = %2$d
                AND IFNULL(Ref_DuAn, 0) = 0
        ', $loaiThietBi, $user->user_dept_id);

        $dataSQL = $this->_db->fetchOne($sql);

        return $dataSQL?$dataSQL->SoLuongSanSang:0;
    }
}
?>