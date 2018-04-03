<?php
class Print_M706_bienbangiaonhanController extends Qss_Lib_PrintController
{
    public function init()
    {
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    public function indexAction() {
        $this->html->OLichThietBi             = $this->_params;
        $this->html->ODanhSachDieuDongThietBi = $this->_params->ODanhSachDieuDongThietBi;
    }

    public function excelAction() {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Biên bản giao nhận.xlsx\"");
        $file = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M706', 'BienBanGiaoNhan.xlsx'));
        $data = new stdClass();
        $stt  = 0;
        $row  = 15;

        $data->a8  = date('Y'); // Delivery No/ Số:
        $data->a9  = ''; // Attn/ Người nhận:
        $data->a10 = ''; // Position/ Chức danh
        $data->a11 = ''; // Supplier/ Nhà CC:
        $data->f8  = $this->_params->PhieuYeuCau; // Request No./ Reason/ Số phiếu YC/ Lý do:
        $data->f9  = ''; // Request No./ Reason/ Số phiếu YC/ Lý do: dòng 2
        $data->f10 = ''; // Date/ Ngày giao:

        $file->init(array('m'=>$data));

        foreach ($this->_params->ODanhSachDieuDongThietBi as $item)
        {
            $s    = new stdClass();
            $s->a = ++$stt; // Stt
            $s->b = $item->TenThietBi; // Descriptions (Mô tả vật tư/ trang thiết bị/ dụng cụ)
            $s->c = $item->DonViTinh; // Unit (Đv tính)
            $s->d = 1; // Quantity (Số lượng)
            $s->e = ''; // Cert of Original No (chứng chỉ xuất xứ)
            $s->f = $item->Serial; // erial No./ Code (Số xêri/ Mã số)
            $s->g = $item->ThamChieu; // Attached Doc. (tài liệu kèm theo)
            $s->h = $item->TinhTrang; // Status (Tình trạng)
            $s->i = $item->GhiChu; // Remarks (Ghi chú)

            $file->newGridRow(array('s'=>$s), $row, 14);
            $row++;
        }

        $data = new stdClass();
        $data->a21 = '';
        $data->a22 = '';
        $data->a23 = '';
        $data->e21 = '';
        $data->e22 = '';
        $data->e23 = '';
        $file->init(array('m'=>$data));


        $file->removeRow(14);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}