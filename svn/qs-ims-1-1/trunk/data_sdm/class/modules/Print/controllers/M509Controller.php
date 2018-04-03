<?php
/**
 * User: huy.bv
 * Date: 4/5/2017
 * Time: 2:52 PM
 */
class Print_M509Controller extends Qss_Lib_PrintController
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }
    public function quoteAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Báo giá bán hàng.xlsx\"");

        $mCommon    = new Qss_Model_Extra_Extra();
        $mBreak     = new Qss_Model_Master_Partner();
        $startRow   = 13;
        $row        = $startRow + 1;
        $file       = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M509', 'SDM_BaoGiaBanHang.xlsx'));
        $main       = new stdClass();

        $InfoDoiTac         = $mBreak->getContactsOfPartners($this->_params->Ref_MaKH);
        $NhanVienBanHang    = $mCommon->getTableFetchOne('ODanhSachNhanVien', array('IOID'=>(int)$this->_params->Ref_NVBanHang));

        $main->TenKH        = $this->_params->TenKH;
        $main->GhiChu       = $this->_params->GhiChu;
        $main->NVBanHang    = $this->_params->NVBanHang;
        $main->NgayBaoGia_SoChungTu = Qss_Lib_Date::mysqltodisplay($this->_params->NgayBaoGia) . '_' . $this->_params->SoChungTu;
        if(count($InfoDoiTac) > 0) {
            $main->NguoiNhan    = $InfoDoiTac[0]->HoTen;
            $main->DTDoiTac     = $InfoDoiTac[0]->DienThoaiDiDongLienHe;
        }
        $main->Email        = $NhanVienBanHang->Email;
        $main->DienThoai    = $NhanVienBanHang->DienThoai;

        $file->init(array('m'=>$main));

        $stt = 0; $totalWithoutVAT = 0; $totalVAT = 0;
        foreach ($this->_params->ODSBGBanHang as $item)
        {
            $data     = new stdClass();
            $data->a  = ++$stt;
            $data->b  = $item->TenSP;
            $data->c  = $item->DonViTinh;
            $data->d  = $item->DonGia;
            $data->e  = $item->SoLuong;
            $data->f  = $item->ThanhTien;
            $totalWithoutVAT = $totalWithoutVAT + $data->f;

            $file->newGridRow(array('s'=>$data), $row, $startRow);
            $row++;
        }

        $VAT        = ($totalWithoutVAT * 10)/100;
        $totalVAT   = $totalWithoutVAT + $VAT;

        // insert value into Cell total
        $cellWithoutVAT = 'F' . $row;
        $file->setCellValue($cellWithoutVAT, $totalWithoutVAT);
        $row++;
        $cellVAT = 'F' . $row;
        $file->setCellValue($cellVAT, $VAT);
        $row++;
        $cellWithVAT = 'F' . $row;
        $file->setCellValue($cellWithVAT, $totalVAT);

        $file->removeRow($startRow);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}
?>