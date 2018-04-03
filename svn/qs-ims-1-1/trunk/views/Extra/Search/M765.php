<?php
class Qss_View_Extra_Search_M765 extends Qss_View_Abstract
{
//    public static $firstStatus = true;

    public function __doExecute ($form)
    {
        $filter = Qss_Cookie::get('form_' . $form->FormCode . '_filter');

        $filter      = unserialize($filter);
        $shiftIOID   = (int) @$filter['nameShift'];
        $date        = @$filter['nameDate']?$filter['nameDate']:'';
        $this->html->htmlDate  = $date;
        $this->html->htmlShift = $shiftIOID;
        $this->html->khuvuc = (int) @$filter['makhuvuc'];
    }
}
?>