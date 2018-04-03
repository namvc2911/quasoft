<?php
class Print_M747Controller extends Qss_Lib_PrintController
{
    public function init()
    {
        parent::init();
        // $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    /**
     * Biên bản sự cố
     */
    public function statementAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Biên bản sự cố.xlsx\"");
        $file     = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M747', 'SDM_BienBanSuCo.xlsx'));
        $data     = new stdClass();
        $mCommon  = new Qss_Model_Extra_Extra();
        $equip    = $mCommon->getTableFetchOne('ODanhSachThietBi', array('IOID'=>(int)$this->_params->Ref_MaThietBi));
        $date     = @$this->_params->Ngay?$this->_params->Ngay:date('Y-m-d');
        $time     = @$this->_params->ThoiGian?$this->_params->ThoiGian:date('H:i:s');
        $miTime   = strtotime("{$date} {$time}");
        $DaiDienChuDauTu1 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienChuDauTu1));
        $DaiDienChuDauTu2 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienChuDauTu2));

        $DaiDienBenLienQuan1 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienBenLienQuan1));
        $DaiDienBenLienQuan2 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienBenLienQuan2));

        $DaiDienKyThuat1 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienKyThuat1));
        $DaiDienKyThuat2 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienKyThuat2));

        $data->n                           = @$this->_params->SoPhieu;
        $data->n2                          = '/BBSC_TSN/ SDM';
        $data->h                           = date('H', $miTime);
        $data->i                           = date('i', $miTime);
        $data->d                           = date('d', $miTime);
        $data->m                           = date('m', $miTime);
        $data->Y                           = date('Y', $miTime);
        $data->NoiXayRaSuCo                = 'Nhà để xe ga quốc nội – Cảng HKQT Tân Sơn Nhất';//$this->_params->DiaDiem;
        $data->MaThietBi                   = @$this->_params->MaThietBi;
        $data->TenThietBi                  = @$this->_params->TenThietBi;
        $data->LoaiThietBi                 = $equip?$equip->LoaiThietBi:'';
        $data->HeThong                     = $equip?$equip->NhomThietBi:'';
        $data->KhuVucLapDat                = $equip?$equip->MaKhuVuc:'';
        $data->ChuDauTu                    = @$this->_params->ChuDauTu;
        $data->DaiDienChuDauTu1            = @$this->_params->DaiDienChuDauTu1;
        $data->DaiDienChuDauTu2            = @$this->_params->DaiDienChuDauTu2;
        $data->ChucDanhDaiDienChuDauTu1    = @$DaiDienChuDauTu1?$DaiDienChuDauTu1->ChucDanh:'';
        $data->ChucDanhDaiDienChuDauTu2    = @$DaiDienChuDauTu2?$DaiDienChuDauTu2->ChucDanh:'';
        $data->BenKyThuat                  = @$this->_params->BenKyThuat;
        $data->DaiDienKyThuat1             = @$this->_params->DaiDienKyThuat1;
        $data->DaiDienKyThuat2             = @$this->_params->DaiDienKyThuat2;
        $data->ChucDanhDaiDienKyThuat1     = @$DaiDienKyThuat1?$DaiDienKyThuat1->ChucDanh:'';
        $data->ChucDanhDaiDienKyThuat2     = @$DaiDienKyThuat2?$DaiDienKyThuat2->ChucDanh:'';
        $data->BenLienQuan                 = @$this->_params->BenLienQuan;
        $data->DaiDienBenLienQuan1         = @$this->_params->DaiDienBenLienQuan1;
        $data->DaiDienBenLienQuan2         = @$this->_params->DaiDienBenLienQuan2;
        $data->ChucDanhDaiDienBenLienQuan1 = @$DaiDienBenLienQuan1?$DaiDienBenLienQuan1->ChucDanh:'';
        $data->ChucDanhDaiDienBenLienQuan2 = @$DaiDienBenLienQuan2?$DaiDienBenLienQuan2->ChucDanh:'';
        $data->TinhTrangSuCo               = @$this->_params->MoTa;
        $data->NguyenNhanSuCo              = @$this->_params->NguyenNhanSuCo;
        $data->CachKhacPhuc                = @$this->_params->XuLy;
        $data->YKienKhac                   = @$this->_params->YKienKhac;

        $file->init(array('m'=>$data));

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    /**
     * Báo cáo sự cố
     */
    public function reportAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Báo cáo sự cố.xlsx\"");
        $file     = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M747', 'SDM_BaoCaoSuCo.xlsx'));
        $data     = new stdClass();
        $mCommon  = new Qss_Model_Extra_Extra();
        $equip    = $mCommon->getTableFetchOne('ODanhSachThietBi', array('IOID'=>(int)$this->_params->Ref_MaThietBi));
        $date     = @$this->_params->NgayBaoCao?$this->_params->NgayBaoCao:date('Y-m-d');
        $time     = @$this->_params->GioBaoCao?$this->_params->GioBaoCao:date('H:i:s');
        $miTime   = strtotime("{$date} {$time}");
        $DaiDienChuDauTu1 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienChuDauTu1));
        $DaiDienChuDauTu2 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienChuDauTu2));

        $DaiDienKyThuat1 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienKyThuat1));
        $DaiDienKyThuat2 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienKyThuat2));

        $data->n                           = @$this->_params->SoBaoCao;;
        $data->n2                          = '/BBSC_TSN/ SDM';
        $data->h                           = date('H', $miTime);
        $data->i                           = date('i', $miTime);
        $data->d                           = date('d', $miTime);
        $data->m                           = date('m', $miTime);
        $data->Y                           = date('Y', $miTime);
        $data->NoiXayRaSuCo                = 'Nhà để xe ga quốc nội – Cảng HKQT Tân Sơn Nhất';//$this->_params->DiaDiem;
        $data->MaThietBi                   = @$this->_params->MaThietBi;
        $data->TenThietBi                  = @$this->_params->TenThietBi;
        $data->LoaiThietBi                 = $equip?$equip->LoaiThietBi:'';
        $data->HeThong                     = $equip?$equip->NhomThietBi:'';
        $data->KhuVucLapDat                = $equip?$equip->MaKhuVuc:'';
        $data->ChuDauTu                    = @$this->_params->ChuDauTu;
        $data->DaiDienChuDauTu1            = @$this->_params->DaiDienChuDauTu1;
        $data->DaiDienChuDauTu2            = @$this->_params->DaiDienChuDauTu2;
        $data->ChucDanhDaiDienChuDauTu1    = @$DaiDienChuDauTu1?$DaiDienChuDauTu1->ChucDanh:'';
        $data->ChucDanhDaiDienChuDauTu2    = @$DaiDienChuDauTu2?$DaiDienChuDauTu2->ChucDanh:'';
        $data->BenKyThuat                  = @$this->_params->BenKyThuat;
        $data->DaiDienKyThuat1             = @$this->_params->DaiDienKyThuat1;
        $data->DaiDienKyThuat2             = @$this->_params->DaiDienKyThuat2;
        $data->ChucDanhDaiDienKyThuat1     = @$DaiDienKyThuat1?$DaiDienKyThuat1->ChucDanh:'';
        $data->ChucDanhDaiDienKyThuat2     = @$DaiDienKyThuat2?$DaiDienKyThuat2->ChucDanh:'';
        $data->TinhTrangSuCo               = @$this->_params->MoTa;
        $data->NguyenNhanSuCo              = @$this->_params->NguyenNhanSuCo;
        $data->CachKhacPhuc                = @$this->_params->XuLy;
        $data->YKienKhac                   = @$this->_params->YKienKhac;

        $file->init(array('m'=>$data));

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}