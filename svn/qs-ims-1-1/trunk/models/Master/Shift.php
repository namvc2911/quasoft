<?php
class Qss_Model_Master_Shift extends Qss_Model_Abstract
{
    public function __construct ()
    {
        parent::__construct();
    }

    public function getShifts()
    {
        $sql = sprintf('
            SELECT *
            FROM OCa
            ORDER BY TenCa
        ');

        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * @param $time
     * @return mixed
     */
    public function getShiftByTime($time)
    {
        if(is_null($time) || $time == '') {
            return null;
        }

        $sql    = sprintf('SELECT *  FROM OCa');
        $dat    = $this->_o_DB->fetchAll($sql);
        $cTime  = date('d-m-Y') . ' ' . $time;
        $retval = null;

//        echo '<pre>'; print_r($dat); die;

        foreach ($dat as $item) {
            if($item->GioBatDau && $item->GioKetThuc) {
                $mcStart  = strtotime($item->GioBatDau);
                $mcEnd    = strtotime($item->GioKetThuc);
                $mcMinus  = $mcEnd - $mcStart;
                $today    = date('d-m-Y');
                $tomorrow = date('d-m-Y');

                if($mcMinus <= 0) { // bat dau tu ngay hom truoc sang ngay hom sau
                    $today    = date('d-m-Y');               // ngay hien tai gia dinh
                    $tomorrow = date("d-m-Y", time()+86400); // ngay mai gia dinh
                }

                $todayTime    = $today .' '. $item->GioBatDau;
                $tomorrowTime = $tomorrow .' '. $item->GioKetThuc;

                if(Qss_Lib_Date::checkInRangeTime($cTime, $todayTime, $tomorrowTime)) {
                    $retval = $item;
                }
            }
        }

        return $retval;
    }
}