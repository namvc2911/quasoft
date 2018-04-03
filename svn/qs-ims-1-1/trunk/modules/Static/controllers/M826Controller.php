<?php
class Static_M826Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    	parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js'); //lay js
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
        $this->html->curl = $this->params->requests->getRequestUri(); //lay crurl (giong data) de su dung trong html
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $mMaintain      = new Qss_Model_Extra_Maintain();
        $start          = $this->params->requests->getParam('start', '');
        $end            = $this->params->requests->getParam('end', '');
        $location       = $this->params->requests->getParam('location', '');
        $group          = $this->params->requests->getParam('group', '');
        $type           = $this->params->requests->getParam('type', '');
        $equip          = $this->params->requests->getParam('equip', '');

        $tongSo         = new stdClass();
        $report         = $mMaintain->getThucHienKeHoachVanHanhSuaChua(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $location
            , $group
            , $type
            , $equip
        );

        $tongSo->SoLanBaoTriB1  = 0;
        $tongSo->SoLanBaoTriB2  = 0;
        $tongSo->SoLanBaoTriTT  = 0;
        $tongSo->SoCong         = 0;
        $tongSo->ChiPhiNhanCong = 0;
        $tongSo->ChiPhiVatTu    = 0;

        foreach($report as $item)
        {
            $tongSo->SoLanBaoTriB1  += $item->SoLanBaoTriB1;
            $tongSo->SoLanBaoTriB2  += $item->SoLanBaoTriB2;
            $tongSo->SoLanBaoTriTT  += $item->SoLanBaoTriTT;
            $tongSo->SoCong         += $item->SoCong;
            $tongSo->ChiPhiNhanCong += $item->ChiPhiNhanCong;
            $tongSo->ChiPhiVatTu    += $item->ChiPhiVatTu;

            $QuyDinhBaoDuongB1 = explode(',', $item->QuyDinhBaoDuongB1);
            $QuyDinhBaoDuongB2 = explode(',', $item->QuyDinhBaoDuongB2);
            $QuyDinhBaoDuongTT = explode(',', $item->QuyDinhBaoDuongTT);

            if(count($QuyDinhBaoDuongB1) > 1)
            {
                $lastB1 = count($QuyDinhBaoDuongB1) - 1;
                $item->QuyDinhBaoDuongB1 = $QuyDinhBaoDuongB1[0].'-:-'.$QuyDinhBaoDuongB1[$lastB1];
            }

            if(count($QuyDinhBaoDuongB2) > 1)
            {
                $lastB2 = count($QuyDinhBaoDuongB2) - 1;
                $item->QuyDinhBaoDuongB1 = $QuyDinhBaoDuongB2[0].'-:-'.$QuyDinhBaoDuongB2[$lastB2];
            }

            if(count($QuyDinhBaoDuongTT) > 1)
            {
                $lastTT = count($QuyDinhBaoDuongTT) - 1;
                $item->QuyDinhBaoDuongB1 = $QuyDinhBaoDuongTT[0].'-:-'.$QuyDinhBaoDuongTT[$lastTT];
            }
        }

        $this->html->month  = date('m', strtotime($start));
        $this->html->year   = date('Y', strtotime($start));
        $this->html->report = $report;
        $this->html->total  = $tongSo;

    }
}
