<?php
class Qss_View_Extra_EquipStatus extends Qss_View_Abstract
{
    public function __doExecute ($form, $selected)
    {
        $this->html->equipStatus = Qss_Lib_System::getFieldRegx('ODanhSachThietBi', 'TrangThai');
    }
}
?>