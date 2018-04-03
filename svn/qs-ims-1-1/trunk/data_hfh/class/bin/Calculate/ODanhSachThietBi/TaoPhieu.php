<?php
class Qss_Bin_Calculate_ODanhSachThietBi_TaoPhieu extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        return "<a href='/user/form/edit?fid=M759&OPhieuBaoTri_TenThietBi={$this->_object->i_IOID}&OPhieuBaoTri_MaThietBi={$this->_object->i_IOID}'> ".$this->_translate(1)." </a>";
    }
}
?>

