<?php
class Qss_Model_M770_Equiptype extends Qss_Model_Abstract
{
    /**
     * Lấy danh sách loại thiết bị theo hình cây
     */
    public function getEquipTypes()
    {
        return $this->_o_DB->fetchAll('SELECT * FROM OLoaiThietBi ORDER BY lft');
    }
}