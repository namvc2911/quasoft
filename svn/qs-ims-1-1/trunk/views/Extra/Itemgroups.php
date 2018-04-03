<?php
class Qss_View_Extra_Itemgroups extends Qss_View_Abstract
{
    public function __doExecute ($form, $selected)
    {
        $model            = new Qss_Model_Extra_Extra();
        $this->html->nhom = $model->getNestedSetTable('ONhomSanPham');
    }
}
?>