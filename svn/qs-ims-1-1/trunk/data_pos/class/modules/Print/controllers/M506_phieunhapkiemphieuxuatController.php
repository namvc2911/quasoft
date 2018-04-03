<?php
class Print_M506_phieunhapkiemphieuxuatController extends Qss_Lib_PrintController
{
    public function init()
    {
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    public function indexAction() {
        $this->html->OXuatKho         = $this->_params;
        $this->html->ODanhSachXuatKho = $this->_params->ODanhSachXuatKho;
    }

    public function excelAction() {




        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Phiếu nhập kho kiêm phiếu xuất kho.xlsx\"");
        $file = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M506', 'PhieuNhapKhoKiemPhieuXuatKho.xlsx'));
        $data = new stdClass();
        $stt  = 0;
        $row  = 19;
        $tongTienHang = 0;

        $data->h5  = $this->_params->SoChungTu; // Số phiếu
        $data->e10 = $this->_params->NguoiGiao; // Họ tên người giao hàng
        $data->e11 = ''; // Đơn vị
        $data->e12 = ''; // Địa chỉ
        $data->e13 = ''; // Số hóa đơn
        $data->e14 = ''; // Tài khoản có
        $data->e15 = $this->_params->MoTa; // Nội dung
        $data->e16 = $this->_params->Kho; // Kho

        $file->init(array('m'=>$data));

        foreach ($this->_params->ODanhSachXuatKho as $item)
        {
            $s    = new stdClass();
            $s->a = ++$stt; // STT
            $s->b = $item->MaSP; // MÃ VT/ TTB
            $s->c = $item->TenSP; // TÊN VẬT TƯ/TBB
            $s->d = $item->DonViTinh; // ĐVT
            $s->e = ''; // TK
            $s->f = ''; // TK CP
            $s->g = ''; // VỤ VIỆC
            $s->h = Qss_Lib_Util::formatNumber($item->SoLuong); // SL
            $s->i = Qss_Lib_Util::formatMoney($item->DonGia); // GIÁ
            $s->j = Qss_Lib_Util::formatMoney($item->ThanhTien); // TIỀN

            $tongTienHang += $item->ThanhTien;

            $file->newGridRow(array('s'=>$s), $row, 18);
            $row++;
        }

        $data = new stdClass();
        $data->j20 = Qss_Lib_Util::formatMoney($tongTienHang); // TỔNG CỘNG TIỀN HÀNG
        $data->j21 = Qss_Lib_Util::formatMoney(0); // CHI PHÍ
        $tongTienHang += 0;
        $data->j22 = Qss_Lib_Util::formatMoney(0); // TỔNG CỘNG TIỀN THUẾ
        $tongTienHang += 0;
        $data->j23 = Qss_Lib_Util::formatMoney($tongTienHang); // TỔNG CỘNG
        $data->d24 = Qss_Lib_Util::VndText($tongTienHang);
        $file->init(array('m'=>$data));

        $file->removeRow(18);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}