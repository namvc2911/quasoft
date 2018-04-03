<?php

/**
 * Class Qss_View_Report_SimpleComboBox
 *
 * Hướng dẫn sử dụng
 *
 * B1: Lấy dữ liệu cần hiển thị
    public function getInputTypes()
    {
        $mStock = new Qss_Model_Inventory_Inventory();
        $stocks = $mStock->getInputTypes();
        $ret    = array();

        foreach($stocks as $item)
        {
            $ret[$item->IOID] = "{$item->Ten}";
        }
        return $ret;
    }
 *
 * B2: Truyền dữ liệu sang nơi cần sử dụng
 * $this->html->outputTypes = $this->getOutputTypes();
 *
 * B3: Gọi view
 * <?php echo $this->views->Report->SimpleComboBox('output_type', $this->outputTypes);?>
 */
class Qss_View_Report_SimpleComboBox extends Qss_View_Abstract 
{
    public function __doExecute ($id = '', $data = array(), $htmlAttribute = 'style="width:200px;"', $name = '', $default = '')
    {
              
    }
}
