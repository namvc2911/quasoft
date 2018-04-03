<?php
class Static_M828Controller extends Qss_Lib_Controller
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

        $tongSo->SoLanBaoTriB1  = 0;
        $tongSo->SoLanBaoTriB2  = 0;
        $tongSo->SoLanBaoTriTT  = 0;
        $tongSo->SoLuotBaoTri   = 0;
        $tongSo->ChiPhiNhanCong = 0;
        $tongSo->ChiPhiVatTu    = 0;
        $tongSo->ChiPhi         = 0;

        $report         = $mMaintain->getThucHienKeHoachSuaChuaThuongXuyen(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $location
            , $group
            , $type
            , $equip
        );

        foreach($report as $item)
        {
            $tongSo->SoLanBaoTriB1  += $item->SoLanBaoTriB1;
            $tongSo->SoLanBaoTriB2  += $item->SoLanBaoTriB2;
            $tongSo->SoLanBaoTriTT  += $item->SoLanBaoTriTT;
            $tongSo->SoLuotBaoTri   += $item->SoLanBaoTriB1 + $item->SoLanBaoTriB2 + $item->SoLanBaoTriTT;
            $tongSo->ChiPhiNhanCong += $item->ChiPhiNhanCong;
            $tongSo->ChiPhiVatTu    += $item->ChiPhiVatTu;
            $tongSo->ChiPhi         += $item->ChiPhi;
        }



        $this->html->month  = date('m', strtotime($start));
        $this->html->year   = date('Y', strtotime($start));
        $this->html->report = $report;
        $this->html->total  = $tongSo;
    }
}
