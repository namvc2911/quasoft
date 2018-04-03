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
        $materials          = $this->params->requests->getParam('materials', array());
        $mRequest           = new Qss_Model_Purchase_Aprequest();
        $myStart            = Qss_Lib_Date::displaytomysql($start);
        $myEnd              = Qss_Lib_Date::displaytomysql($end);
        $track              = $mRequest->getTrackRequests($myStart, $myEnd, 0, 0, $materials);
        $val                = array();


        foreach($track as $index=>$item)
        {
            $key = $item->RequestIOID.'-'.$item->Ref_MaSP.'-'.$item->TenSP.'-'.$item->SoPhieuNhapKho;

            if(!isset($val[$key])) {
                $val[$key]['YeuCau']   = $item->SoLuong;
                $val[$key]['DaVe']     = 0;
                $val[$key]['Indexs'][] = $index;
            }

            $val[$key]['DaVe'] += $item->SoLuongNhapKho;
        }

        if($notenogh)
        {
            foreach ($val as $key=>$item)
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