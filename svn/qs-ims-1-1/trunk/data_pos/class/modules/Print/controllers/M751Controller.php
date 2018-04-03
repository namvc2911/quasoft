<?php
class Print_M751Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    public function printAction() {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Phiếu YCCC Vật Tư Trang Thiết Bị.xlsx\"");

        $row    = 18;
        $stt    = 0;
        $main   = new stdClass();
        $common = new Qss_Model_Extra_Extra();
        $ifid   = $this->params->requests->getParam('ifid', 0);
        $file   = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M751', 'PhieuYeuCauTrangThietBiVatTu.xlsx'));

        $objYeuCau  = $common->getTableFetchOne('OYeuCauTrangThietBiVatTu', array('IFID_M751'=>$ifid));
        $objThietBi = $common->getTableFetchAll('OYeuCauTrangThietBi', array('IFID_M751'=>$ifid));
        $objVatTu   = $common->getTableFetchAll('OYeuCauVatTu', array('IFID_M751'=>$ifid));
        $ketThucDA  = @(int)$objYeuCau->KetThucDuAn;

        $main->SoYeuCau     = @$objYeuCau->SoPhieu;
        $main->DonViYeuCau  = @$objYeuCau->DonViYeuCau;
        $main->LyDoYeuCau   = @$objYeuCau->LyDo;
        $main->SoHopDong    = @$objYeuCau->SoHopDong;
        $main->ThuongKhan   = (@(int)$objYeuCau->MucDoUuTien == 0)?'v':'' ;
        $main->Khan         = (@(int)$objYeuCau->MucDoUuTien == 1)?'v':'';
        $main->BinhThuong   = (@(int)$objYeuCau->MucDoUuTien == 2)?'v':'';



        $file->init(array('m'=>$main));

        foreach ($objThietBi as $item)
        {
            $data     = new stdClass();
            $data->a  = ++$stt;
            $data->b  = '';
            $data->c  = $item->LoaiThietBi;
            $data->d  = $item->DonViTinh;
            $data->e  = Qss_Lib_Util::formatNumber($item->SoLuong);
            $data->f  = $item->YeuCauKyThuat;
            if($ketThucDA) {
                $data->g  = $item->NgayBatDau?'Dự kiến từ '. Qss_Lib_Date::mysqltodisplay($item->NgayBatDau):'';
                $data->g .= ' đến lúc kết thúc dự án ';
            }
            else {
                $data->g  = Qss_Lib_Date::mysqltodisplay($item->NgayBatDau);
                $data->g .= ' ' . Qss_Lib_Date::mysqltodisplay($item->NgayKetThuc);
            }

            $data->h  = Qss_Lib_Util::formatNumber(@$item->SoLuongXuatKho);
            $data->i  = Qss_Lib_Util::formatNumber($item->SoLuongDieuDong);
            $data->j  = Qss_Lib_Util::formatNumber($item->SoLuongMua);
            $data->k  = Qss_Lib_Util::formatNumber($item->SoLuongThue);
            $data->l  = $item->DonViKiemTra;
            $data->m  = $item->GhiChu;

            $file->newGridRow(array('s'=>$data), $row, 17);
            $row++;
        }

        foreach ($objVatTu as $item)
        {
            $data     = new stdClass();
            $data->a  = ++$stt;
            $data->b  = $item->MaVatTu;
            $data->c  = $item->TenVatTu;
            $data->d  = $item->DonViTinh;
            $data->e  = Qss_Lib_Util::formatNumber($item->SoLuongYeuCau);
            $data->f  = $item->YeuCauKyThuat;
            $data->g  = @$item->NgayBatDau?Qss_Lib_Date::mysqltodisplay($item->NgayBatDau):'';
            $data->g .= @$item->NgayKetThuc?' ' . Qss_Lib_Date::mysqltodisplay($item->NgayKetThuc):'';
            $data->h  = Qss_Lib_Util::formatNumber(@$item->SoLuongXuatKho);
            $data->i  = Qss_Lib_Util::formatNumber($item->SoLuongDieuDong);
            $data->j  = Qss_Lib_Util::formatNumber($item->SoLuongMua);
            $data->k  = Qss_Lib_Util::formatNumber($item->SoLuongThue);
            $data->l  = @$item->DonViKiemTra;
            $data->m  = $item->GhiChu;

            $file->newGridRow(array('s'=>$data), $row, 17);
            $row++;
        }

        $file->removeRow(17);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}