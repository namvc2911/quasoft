<?php

/**
 * Hiển thị combobox theo giá trị json của trường {"0":"dispaly1", "1":"dispaly2"}
 * Class Qss_View_Report_RegxCombobox
 */
class Qss_View_Report_RegxComboBox extends Qss_View_Abstract
{
    public function __doExecute ($id, $objectCode, $fieldCode, $htmlAttribute = 'style="width:180px;"', $name = '', $default = '')
    {
        $this->html->data = Qss_Lib_System::getFieldRegx($objectCode, $fieldCode);
    }
}
