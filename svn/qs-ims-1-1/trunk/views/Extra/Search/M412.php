<?php
class Qss_View_Extra_Search_M412 extends Qss_View_Abstract
{
    /**
     * @param $form
     * Hiện view lọc đơn vị thực hiện trên M412 - Yêu cầu mua sắm
     */
    public function __doExecute ($form)
    {
        $filter = Qss_Cookie::get('form_' . $form->FormCode . '_filter', 'a:0:{}');
        $filter = unserialize($filter);
        $this->html->selected = (int)@$filter['workcenter'];
    }
}
?>