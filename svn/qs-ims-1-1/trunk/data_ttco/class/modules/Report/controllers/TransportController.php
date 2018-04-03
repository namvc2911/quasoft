<?php

class Report_TransportController extends Qss_Lib_Controller
{

    public $_user;

    public function init()
    {
    	$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->_user = Qss_Register::get('userinfo');
    }

    // Bao cao thiet bi van tai theo thang
    public function monthAction()
    {

    }

    public function month1Action()
    {
        $month    = $this->params->requests->getParam('month', 0);
        $year     = $this->params->requests->getParam('year', 0);
        $location = $this->params->requests->getParam('location', 0);
        $eqtype   = $this->params->requests->getParam('eqtype', 0);


        $this->html->report = $this->getTransportByMonth($month, $year, $location, $eqtype);
        $this->html->month  = $month;
        $this->html->year   = $year;
    }

    public function getTransportByMonth($month, $year, $locationIOID = 0, $eqtypeIOID = 0)
    {
        $retval    = array();
        $transport = new Qss_Model_Extra_Transport();


        $equips    = $transport->getTransportEquips($locationIOID, $eqtypeIOID);
        $lastSCl   = $transport->getLastMaintainByType($month, $year, Qss_Lib_Ttco::SUA_CHUA_LON, $locationIOID, $eqtypeIOID);
        $lastB3    = $transport->getLastMaintainByType($month, $year, Qss_Lib_Ttco::BAO_DUONG_III, $locationIOID, $eqtypeIOID);
        $active    = $transport->getActiveByMonth($month, $year, $locationIOID, $eqtypeIOID);
        $countMT   = $transport->countMaintTypeOfEquip($month, $year, $locationIOID, $eqtypeIOID);
        $luykeHD   = $transport->getLuyKeHoatDongDenThang($month, $year, $locationIOID, $eqtypeIOID);
        $hdSauSCL  = $transport->getHoatDongSauLoaiBaoTri($month, $year, Qss_Lib_Ttco::SUA_CHUA_LON, $locationIOID, $eqtypeIOID);
        $hdSauB3   = $transport->getHoatDongSauLoaiBaoTri($month, $year, Qss_Lib_Ttco::BAO_DUONG_III, $locationIOID, $eqtypeIOID);
        $hdDongCo  = $transport->getHoatDongDongCoCuoiCung($month, $year, $locationIOID, $eqtypeIOID);
        $luykeDongCo = $transport->getLuyKeHoatDongDongCoCuoiCung($month, $year, $locationIOID, $eqtypeIOID);
        $luykeSanLuong = $transport->getLuyKeSanLuong($month, $year, $locationIOID, $eqtypeIOID);
        $nhienLieu   = $transport->getTieuHaoNhienLieu($month, $year, $locationIOID, $eqtypeIOID);
        $quyetToan   = $transport->getQuyetToan($month, $year, $locationIOID, $eqtypeIOID);
        $luyKeQuyetToan   = $transport->getLuyKeQuyetToan($month, $year, $locationIOID, $eqtypeIOID);


        foreach($equips as $item)
        {
            if(!$item->IOID) continue;

            $retval[$item->IOID]->MaThietBi    = $item->MaThietBi;
            $retval[$item->IOID]->TenThietBi   = $item->TenThietBi;
            $retval[$item->IOID]->TongHoatDong = 0;
            $retval[$item->IOID]->TongGio      = 0;
            $retval[$item->IOID]->TongKm       = 0;
            $retval[$item->IOID]->TongTan      = 0;
            $retval[$item->IOID]->TongTKm      = 0;
            $retval[$item->IOID]->TongXang     = 0;
            $retval[$item->IOID]->TongGadoan   = 0;
            $retval[$item->IOID]->TongMo       = 0;
            $retval[$item->IOID]->NangSuat     = 0;
            $retval[$item->IOID]->LuyKeHoatDong = 0;
            $retval[$item->IOID]->SLBD->{Qss_Lib_Ttco::SUA_CHUA_LON}  = 0;
            $retval[$item->IOID]->SLBD->{Qss_Lib_Ttco::BAO_DUONG_III} = 0;
            $retval[$item->IOID]->SLBD->{Qss_Lib_Ttco::BAO_DUONG_II}  = 0;
            $retval[$item->IOID]->SLBD->{Qss_Lib_Ttco::BAO_DUONG_I}   = 0;
            $retval[$item->IOID]->SLBD->{Qss_Lib_Ttco::KIEM_TRA}      = 0;
            $retval[$item->IOID]->NgayVaoSCL   = '';
            $retval[$item->IOID]->NgayRaSCL    = '';
            $retval[$item->IOID]->NgayVaoB3    = '';
            $retval[$item->IOID]->NgayRaB3     = '';
            $retval[$item->IOID]->HDSauSCL  = 0;
            $retval[$item->IOID]->HDSauB3   = 0;
            $retval[$item->IOID]->HoatDongDongCo = 0;
            $retval[$item->IOID]->SoDongCo = '';
            $retval[$item->IOID]->LuyKeDongCo  = 0;
            $retval[$item->IOID]->HDDongCoSauSCL  = 0;
            $retval[$item->IOID]->LuyKeTan = 0;
            $retval[$item->IOID]->NhienLieu->{Qss_Lib_Ttco::DONG_CO} = 0;
            $retval[$item->IOID]->NhienLieu->{Qss_Lib_Ttco::GIAM_TOC} = 0;
            $retval[$item->IOID]->NhienLieu->{Qss_Lib_Ttco::THUY_LUC} = 0;
            $retval[$item->IOID]->QuyetToan->{Qss_Lib_Ttco::SUA_CHUA_LON}  = 0;
            $retval[$item->IOID]->QuyetToan->{Qss_Lib_Ttco::SUA_CHUA_TX}  = 0;
            $retval[$item->IOID]->LuyKeQuyetToan = 0;
            $retval[$item->IOID]->LuyKeChiPhi = 0;
            $retval[$item->IOID]->ChiPhiTrongKy = 0;
            $retval[$item->IOID]->NLSLG      = 0;
            $retval[$item->IOID]->DNNL      = 0;
            $retval[$item->IOID]->MMNL      = 0;



        }

        // lay hoat dong cua thiet bi
        foreach($active as $item)
        {
            if(!$item->EQIOID) continue;

            $retval[$item->EQIOID]->TongHoatDong = $item->TongHoatDong;
            $retval[$item->EQIOID]->TongGio    = $item->TongGio;
            $retval[$item->EQIOID]->TongKm     = $item->TongKm;
            $retval[$item->EQIOID]->TongTan    = $item->TongTan;
            $retval[$item->EQIOID]->TongTKm    = $item->TongTKm;
            $retval[$item->EQIOID]->TongXang   = $item->TongXang;
            $retval[$item->EQIOID]->TongGadoan = $item->TongGadoan;
            $retval[$item->EQIOID]->TongMo = $item->TongMo;
            $retval[$item->EQIOID]->NangSuat   = $item->TongHoatDong?$item->TongTan/$item->TongHoatDong:0;

        }

        foreach($luykeHD as $item)
        {
            if(!$item->EQIOID) continue;
            $retval[$item->EQIOID]->LuyKeHoatDong = $item->LuyKeHoatDong;
        }

        foreach($countMT as $item)
        {
            if(!$item->EQIOID) continue;
            $retval[$item->EQIOID]->SLBD->{$item->MainType} = $item->Num;
         }

        foreach($lastSCl as $item)
        {
            if(!$item->EQIOID) continue;
            $retval[$item->EQIOID]->NgayVaoSCL = Qss_Lib_Date::mysqltodisplay($item->StartDate);
            $retval[$item->EQIOID]->NgayRaSCL  = Qss_Lib_Date::mysqltodisplay($item->EndDate);
        }

        foreach($lastB3 as $item)
        {
            if(!$item->EQIOID) continue;
            $retval[$item->EQIOID]->NgayVaoB3  = Qss_Lib_Date::mysqltodisplay($item->StartDate);
            $retval[$item->EQIOID]->NgayRaB3   = Qss_Lib_Date::mysqltodisplay($item->EndDate);
        }

        foreach($hdSauSCL as $item)
        {
            if(!$item->EQIOID) continue;
            $retval[$item->EQIOID]->HDSauSCL  = $item->TongHoatDong;
        }

        foreach($hdSauB3 as $item)
        {
            if(!$item->EQIOID) continue;
            $retval[$item->EQIOID]->HDSauB3  = $item->TongHoatDong;
        }

        foreach($hdDongCo as $item)
        {
            if(!$item->EQIOID) continue;
            $retval[$item->EQIOID]->HoatDongDongCo  = $item->TongHoatDong;
            $retval[$item->EQIOID]->SoDongCo = $item->SoDongCo;
        }

        foreach($luykeDongCo as $item)
        {
            if(!$item->EQIOID) continue;
            $retval[$item->EQIOID]->LuyKeDongCo  = $item->TongHoatDong;
            $retval[$item->EQIOID]->HDDongCoSauSCL  = $item->SauSCL;
        }

        foreach($luykeSanLuong as $item)
        {
            if(!$item->EQIOID) continue;
            $retval[$item->EQIOID]->LuyKeTan  = $item->LuyKeTongTan;
        }

        foreach($nhienLieu as $item)
        {
            if(!$item->EQIOID) continue;
            $retval[$item->EQIOID]->NhienLieu->{$item->ViTri} = $item->TieuHao;
        }

        foreach($quyetToan as $item)
        {
            if(!$item->EQIOID) continue;
            $retval[$item->EQIOID]->QuyetToan->{$item->LoaiBaoTri} = $item->GiaTri;
        }

        foreach($luyKeQuyetToan as $item)
        {
            if(!$item->EQIOID) continue;
            $retval[$item->EQIOID]->LuyKeQuyetToan = $item->GiaTri;
        }

        foreach($retval as $key=>$item)
        {
            // Lũy kế chi phí
            $retval[$key]->LuyKeChiPhi   = $item->LuyKeHoatDong?$item->LuyKeQuyetToan/$item->LuyKeHoatDong:0;

            // Chi phi trong ky
            $retval[$key]->ChiPhiTrongKy = $item->TongHoatDong?$item->QuyetToan->{Qss_Lib_Ttco::SUA_CHUA_TX}/$item->TongHoatDong:0;

            // NL/SLg
            $retval[$key]->NLSLG = $item->TongHoatDong?$item->TongGadoan/$item->TongHoatDong:0;

            // %DN/NL
            $retval[$key]->DNNL = $item->TongGadoan?($item->NhienLieu->{Qss_Lib_Ttco::THUY_LUC} + $item->NhienLieu->{Qss_Lib_Ttco::DONG_CO} + $item->NhienLieu->{Qss_Lib_Ttco::GIAM_TOC})/$item->TongGadoan:0;

            // %MM/NL
            $retval[$key]->MMNL = $item->TongGadoan?$item->TongMo/$item->TongHoatDong:0;
        }

        //echo '<pre>'; print_r($retval); die;
        return $retval;
    }
}

?>