<?php
class Static_M417Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $start              = $this->params->requests->getParam('start', date('Y-m-01'));
        $end                = $this->params->requests->getParam('end', date('Y-m-t'));
        $notenogh           = $this->params->requests->getParam('notenough', 0);
        $mRequest           = new Qss_Model_Purchase_Request();
        $myStart            = Qss_Lib_Date::displaytomysql($start);
        $myEnd              = Qss_Lib_Date::displaytomysql($end);
        $track              = $mRequest->getTrackRequests($myStart, $myEnd);
        $val                = array();


        foreach($track as $index=>$item)
        {
            if(!isset($val[$item->RequestIOID][$item->Ref_MaSP])) {
                $val[$item->RequestIOID][$item->Ref_MaSP]['YeuCau']   = $item->SoLuong;
                $val[$item->RequestIOID][$item->Ref_MaSP]['DaVe']     = 0;
                $val[$item->RequestIOID][$item->Ref_MaSP]['Indexs'][] = $index;
            }

            $val[$item->RequestIOID][$item->Ref_MaSP]['DaVe'] += $item->SoLuongNhapKho;
        }

        foreach ($val as $requestIOID=>$items)
        {
            foreach ($items as $itemIOID=>$item)
            {
                if( ($item['YeuCau'] - $item['DaVe']) <= 0)
                {
                    foreach ($item['Indexs'] as $index)
                    {
                        unset($track[$index]);
                    }
                }
            }
        }

        $this->html->track  = $track;
    }
}