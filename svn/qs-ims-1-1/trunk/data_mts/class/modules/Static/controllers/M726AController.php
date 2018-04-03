<?php
class Static_M726AController extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }


    public function indexAction()
    {

    }

    public function showAction()
    {

    }

    /**
     * Thiết bị vận tải bộ #487
     */
    public function vantaiboAction()
    {
        $model = new Qss_Model_Mtsequips();
        $this->html->equips  = $model->getThietBiVanTaiBo();
        $this->html->dacTinh = $this->getDacTinhThietBiVanTaiBo();
    }

//    public function vantaiboexcelAction()
//    {
//        $model   = new Qss_Model_Mtsequips();
//        $equips  = $model->getThietBiVanTaiBo();
//        $dacTinh = $this->getDacTinhThietBiVanTaiBo();
//
//        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
//        header("Content-disposition: attachment; filename=\"Báo cáo tổng hợp thiết bị vận tải bộ.xlsx\"");
//
//        $m = new Qss_Model_Excel(QSS_DATA_DIR.'/template/M726/MTS_ThietBiVanTaiBo.xlsx');
//        $m->init();
//
//        $row            = 13;
//        $oldKhuVuc      = '';
//        $sttKhuVuc      = 0;
//        $oldLoaiThietBi = '';
//        $sttLoaiThietBi = 0;
//        $oldThongSo     = '';
//        $oldThietBi     = '';
//        $sttThietBi     = 0;
//
//
//        foreach ($equips as $item) {
//            if($oldKhuVuc != $item->KhuVucRootIOID) {
//                $data = new stdClass();
//                $data->a = Qss_Lib_Util::numberToRome(++$sttKhuVuc);
//                $data->b = $item->MaKhuVuc;
//                $m->newGridRow(array('m'=>$data), $row, 10);
//                $row++;
//
//                $sttLoaiThietBi = 0;
//            }
//            $oldKhuVuc = $item->KhuVucRootIOID;
//
//            if($oldLoaiThietBi != $item->LoaiThietBiIOID) {
//                $data = new stdClass();
//                $data->a = ++$sttLoaiThietBi;
//                $data->b = $item->TenLoai;
//                $m->newGridRow(array('n'=>$data), $row, 11);
//                $row++;
//
//                $sttThietBi = 0; //reset
//            }
//            $oldLoaiThietBi = $item->LoaiThietBiIOID;
//
//            $data = new stdClass();
//            $data->a = ++$sttThietBi;
//            $data->b = $item->MaThietBi;
//            $data->c = @$item->CongDung;
//            $data->d = @$dacTinh[$item->IFID_M705]['Kiểu'];
//            $data->e = @$dacTinh[$item->IFID_M705]['Công suất'];
//            $data->f = $item->XuatXu;
//            $data->g = $item->NgayDuaVaoSuDung;
//            $data->h = @$item->Ref_TrangThai;
//
//            $m->newGridRow(array('s'=>$data), $row, 12);
//            $row++;
//        }
//
//        $m->removeRow(12);
//        $m->removeRow(11);
//        $m->removeRow(10);
//
//        $m->save();
//        die();
//        $this->setHtmlRender(false);
//        $this->setLayoutRender(false);
//    }

    private function getDacTinhThietBiVanTaiBo() {
        $model  = new Qss_Model_Mtsequips();
        $equips = $model->getDacTinhThietBiVanTaiBo();
        $retval = array();

        foreach($equips as $item) {
            $retval[$item->IFID_M705][trim($item->Ten)] = $item->GiaTri;
        }

        return $retval;
    }

    /**
     * Thiết bị vận tải thủy #488
     */
    public function vantaithuyAction()
    {
        $model = new Qss_Model_Mtsequips();
        $this->html->equips = $model->getThietBiVanTaiThuy();
        $this->html->dacTinh = $this->getDacTinhThietBiVanTaiThuy();
    }

//    public function vantaithuyexcelAction()
//    {
//        $model   = new Qss_Model_Mtsequips();
//        $equips  = $model->getThietBiVanTaiBo();
//        $dacTinh = $this->getDacTinhThietBiVanTaiThuy();
//
//        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
//        header("Content-disposition: attachment; filename=\"Báo cáo tổng hợp thiết bị vận tải thủy.xlsx\"");
//
//        $m = new Qss_Model_Excel(QSS_DATA_DIR.'/template/M726/MTS_ThietBiVanTaiThuy.xlsx');
//        $m->init(array());
//
//        $row            = 14;
//        $oldKhuVuc      = '';
//        $sttKhuVuc      = 0;
//        $oldLoaiThietBi = '';
//        $sttLoaiThietBi = 0;
//        $oldThongSo     = '';
//        $oldThietBi     = '';
//        $sttThietBi     = 0;
//
//        foreach ($equips as $item) {
//            if($oldKhuVuc != $item->KhuVucRootIOID) {
//                $data = new stdClass();
//                $data->a = Qss_Lib_Util::numberToRome(++$sttKhuVuc);
//                $data->b = $item->MaKhuVuc;
//                $m->newGridRow(array('m'=>$data), $row, 11);
//                $row++;
//
//                $sttLoaiThietBi = 0;
//            }
//            $oldKhuVuc = $item->KhuVucRootIOID;
//
//            if($oldLoaiThietBi != $item->LoaiThietBiIOID) {
//                $data = new stdClass();
//                $data->a = ++$sttLoaiThietBi;
//                $data->b = $item->TenLoai;
//                $data->f = Qss_Lib_Util::formatNumber(@$dacTinh['CongSuatTheoLoai'][$item->LoaiThietBiIOID]);
//                $m->newGridRow(array('n'=>$data), $row, 12);
//                $row++;
//
//                $sttThietBi = 0; //reset
//            }
//            $oldLoaiThietBi = $item->LoaiThietBiIOID;
//
//            $data = new stdClass();
//            $data->a = ++$sttThietBi;
//            $data->b = $item->TenThietBi;
//            $data->c = @$item->CongDung;
//            $data->d = @$dacTinh[$item->IFID_M705]['Cấp tàu'];
//            $data->e = @$dacTinh[$item->IFID_M705]['Kiểu'];
//            $data->f = Qss_Lib_Util::formatNumber(@$dacTinh[$item->IFID_M705]['Công suất']);
//            $data->g = Qss_Lib_Util::formatNumber(@$dacTinh[$item->IFID_M705]['Trọng tải']);
//            $data->h = $item->MaKhuVuc;
//            $data->i = $item->XuatXu;
//            $data->j = $item->NamSanXuat;
//            $data->k = $item->NgayDuaVaoSuDung;
//            $data->m = $item->Ref_TrangThai;
//            $data->n = $item->GhiChu;
//
//            $m->newGridRow(array('s'=>$data), $row, 13);
//            $row++;
//        }
//
//        $m->removeRow(13);
//        $m->removeRow(12);
//        $m->removeRow(11);
//
//        $m->save();
//        die();
//        $this->setHtmlRender(false);
//        $this->setLayoutRender(false);
//    }


    private function getDacTinhThietBiVanTaiThuy() {
        $model  = new Qss_Model_Mtsequips();
        $equips = $model->getDacTinhThietBiVanTaiThuy();
        $retval = array();

        foreach($equips as $item) {
            $retval[$item->IFID_M705][trim($item->Ten)] = $item->GiaTri;

            if(!isset($retval[$item->IFID_M705]['CongSuatTheoLoai'][$item->LoaiThietBiIOID])) {
                $retval[$item->IFID_M705]['CongSuatTheoLoai'][$item->LoaiThietBiIOID] = 0;
            }

            if(trim($item->Ten) == 'Công suất') {
                $retval['CongSuatTheoLoai'][$item->LoaiThietBiIOID] += $item->GiaTri;
            }
        }

        return $retval;
    }


    /**
     * Gàu ngoạm #489
     */
    public function gaungoamAction()
    {
        $model = new Qss_Model_Mtsequips();
        $this->html->equips = $model->getGauNgoam();
        $this->html->dacTinh = $this->getDacTinhGauNgoam();
    }

    private function getDacTinhGauNgoam() {
        $model  = new Qss_Model_Mtsequips();
        $equips = $model->getDacTinhGauNgoam();
        $retval = array();

        foreach($equips as $item) {
            $retval[$item->IFID_M705][trim($item->Ten)] = $item->GiaTri;
        }

        return $retval;
    }

    /**
     * Stec nước #490
     */
    public function stecAction()
    {
        $model = new Qss_Model_Mtsequips();
        $this->html->DungTichStec          = $model->getDungTichStec();
        $this->html->stecXangDauDangSuDung = $model->getStecXangDau( 0);
        $this->html->stecXangDauChuaSuDung = $model->getStecXangDau( 4);
        $this->html->stecNuocDangSuDung    = $model->getStecNuoc( 0);
        $this->html->stecNuocChuaSuDung    = $model->getStecNuoc( 4);
    }

    /**
     * Thiết bị gps #491
     */
    public function gpsAction()
    {
        $model = new Qss_Model_Mtsequips();
        $this->html->equips = $model->getGps();
    }

    /**
     * Thiết bị plc #492
     */
    public function plcAction()
    {
        $model = new Qss_Model_Mtsequips();
        $this->html->equips = $model->getPlc();
    }

    /**
     * Thiết bị nâng và chịu áp lực #493
     */
    public function nangvaaplucAction()
    {
        $model = new Qss_Model_Mtsequips();
        $this->html->equips = $model->getThietBiNangVaApLuc();
    }
}

?>