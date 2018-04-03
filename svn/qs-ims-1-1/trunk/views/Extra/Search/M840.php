<?php
class Qss_View_Extra_Search_M840 extends Qss_View_Abstract
{
//    public static $firstStatus = true;

    public function __doExecute ($form)
    {
        $filter = Qss_Cookie::get('form_' . $form->FormCode . '_filter');

        $filter       = unserialize($filter);
        $shiftIOID    = (int) @$filter['nameShift'];
        $locationIOID = (int) @$filter['makhuvuc'];
        $date         = @$filter['nameDate']?$filter['nameDate']:'';

//        if(!isset($_COOKIE["M840_Filter"]))
//        {
//            Qss_Cookie::set("M840_Filter", 1);
//        }
//
//        $M840_Filter = Qss_Cookie::get("M840_Filter");
//
//        if($M840_Filter == 1)
//        {
//            $user        = Qss_Register::get('userinfo');
//            $modelShift  = new Qss_Model_Master_Shift();
//
//            if(!$shiftIOID && $user->user_mobile) {
//                $objShift  = $modelShift->getShiftByTime(date('H:i'));
//                $shiftIOID = $objShift?$objShift->IOID:0;
//            }
//
//            if(!$date && $user->user_mobile)
//            {
//                $date = date('d-m-Y');
//            }
//
//            $filter2 = array();
//            $filter2['nameShift'] = $shiftIOID;
//            $filter2['nameDate']  = $date;
//
//            Qss_Cookie::set('form_' . $form->FormCode . '_filter', serialize($filter2));
//            Qss_Cookie::set("M840_Filter", 0);
//        }

        // echo '<pre>'; print_r(unserialize(Qss_Cookie::get('form_' . $form->FormCode . '_filter'))); die;
        $this->html->htmlDate     = $date;
        $this->html->htmlShift    = $shiftIOID;
        $this->html->htmlLocation = $locationIOID;
    }
}
?>