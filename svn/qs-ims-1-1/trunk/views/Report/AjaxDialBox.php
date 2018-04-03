<?php
class Qss_View_Report_AjaxDialBox extends Qss_View_Abstract
{
    /**
     * @note Chỉ cho lấy dữ liệu từ một bảng không lấy từ nhiều bảng giống listbox
     * @param string $id
     * @param string $objectCode
     * @param array $displayField
     * @param array $compareField
     * @param string $keyField
     * @param array $extendCondition
     * @param string $name
     * @param string $htmlAttribute
     */
    public function __doExecute (
        $id = ''
        , $objectCode = ''
        , $keyField = '' // Trường lấy giá trị value của option
        , $displayFields = array() // Hiển thị
        , $compareFields = array() // So sánh lấy dữ liệu
        , $extendCondition = array() // Lọc theo trường của object khác hoặc điều kiện khác
        , $name = ''
        , $htmlAttribute = ''
        , $orderFields = array()
        , $join = '') {

    }
}