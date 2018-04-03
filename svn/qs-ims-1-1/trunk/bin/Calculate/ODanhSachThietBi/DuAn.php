<?php
class Qss_Bin_Calculate_ODanhSachThietBi_DuAn extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $duAnMua  = $this->_object->getFieldByCode('DuAnMua')->getValue();
        return $duAnMua;
    }
}
?>