<?php
class Print_M402_nhapkhotralaiController extends Qss_Lib_PrintController
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    public function indexAction() {
        $this->html->ONhapKho         = $this->_params;
        $this->html->ODanhSachNhapKho = $this->_params->ODanhSachNhapKho;

    }

    public function excelAction() {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Danh mục vật tư nhập lại kho.xlsx\"");
        $file = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M402', 'NhapKhoTraLai.xlsx'));
        $data = new stdClass();
        $stt  = 0;
        $row  = 16;

        $data->a7  = $this->_params->NguoiGiao; // Người giao dịch
        $data->a8  = ''; // Đơn vị
        $data->a9  = ''; // Địa chỉ
        $data->a10 = $this->_params->MoTa; // Diễn giải
        $data->a11 = $this->_params->Kho; // Nhập tại kho
        $data->a12 = $this->_params->LoaiNhapKho; // Dạng nhập

        $file->init(array('m'=>$data));


        foreach ($this->_params->ODanhSachNhapKho as $item)
        {
            $s    = new stdClass();
            $s->a = ++$stt; // Stt
            $s->b = $item->TenSanPham; // Tên Vật tư
            $s->c = $item->MaSanPham; // Mã Vật tư
            $s->d = $item->DonViTinh; // Đvt
            $s->e = Qss_Lib_Util::formatNumber($item->SoLuong); // Số lượng
            $s->f = ''; // Tình trạng sử dụng
            $s->g = $this->_params->SoYeuCau; // Số phiếu yêu cầu
            $s->h = $item->MoTa; // Ghi chú

            $file->newGridRow(array('s'=>$s), $row, 15);
            $row++;
        }

        $file->removeRow(15);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}