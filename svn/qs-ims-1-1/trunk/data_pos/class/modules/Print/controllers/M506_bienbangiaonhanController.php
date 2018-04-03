<?php
class Print_M506_bienbangiaonhanController extends Qss_Lib_PrintController
{
    public function init()
    {
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    public function indexAction() {
        $this->html->OLichThietBi             = $this->_params;
        $this->html->ODanhSachDieuDongThietBi = $this->_params->ODanhSachXuatKho;
    }

    public function excelAction() {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Biên bản giao nhận.xlsx\"");
        $file = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M506', 'BienBanGiaoNhan.xlsx'));
        $data = new stdClass();
        $stt  = 0;
        $row  = 15;

        $data->a8  = date('Y'); // Delivery No/ Số:
        $data->a9  = ''; // Attn/ Người nhận:
        $data->a10 = ''; // Position/ Chức danh
        $data->a11 = ''; // Supplier/ Nhà CC:
        $data->f8  = $this->_params->SoYeuCau; // Request No./ Reason/ Số phiếu YC/ Lý do:
        $data->f9  = ''; // Request No./ Reason/ Số phiếu YC/ Lý do: dòng 2
        $data->f10 = ''; // Date/ Ngày giao:

        $file->init(array('m'=>$data));

        foreach ($this->_params->ODanhSachXuatKho as $item)
        {
            $s    = new stdClass();
            $s->a = ++$stt; // Stt
            $s->b = $item->TenSP; // Descriptions (Mô tả vật tư/ trang thiết bị/ dụng cụ)
            $s->c = $item->DonViTinh; // Unit (Đv tính)
            $s->d = Qss_Lib_Util::formatNumber($item->SoLuong); // Quantity (Số lượng)
            $s->e = ''; // Cert of Original No (chứng chỉ xuất xứ)
            $s->f = ''; // erial No./ Code (Số xêri/ Mã số)
            $s->g = ''; // Attached Doc. (tài liệu kèm theo)
            $s->h = ''; // Status (Tình trạng)
            $s->i = $item->MoTa; // Remarks (Ghi chú)

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
    public function xuatKhoAction()
        {
            header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-disposition: attachment; filename=\"Biên bản xuất kho.xlsx\"");
            $file = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M506', 'BienBanXuatKho.xlsx'));
            $ifid   = $this->params->requests->getParam('ifid', 0);
            $mCommon = new Qss_Model_Extra_Extra();
            $oXuatKho = $mCommon->getTableFetchOne('OXuatKho',array('IFID_M506'=>$ifid));
            // $oXuatKho = $mCommon->getTableFetchAll('ODanhSachXuatKho',array('IFID_M506'=>$ifid));
            // echo "<pre>";
            // print_r($this->_params->ODanhSachXuatKho);die;
            $stt  = 0;
            $row =12;
            $data = new stdClass();
            $data->date = '';
            $data->SoChungTu = $oXuatKho->SoChungTu;
            $data->NguoiNhan = $oXuatKho->NguoiNhan;
            $data->PhongBan = '';
            $data->soYeuCau = $oXuatKho->SoYeuCau;
            $data->MieuTa = $oXuatKho->MoTa;
            $data->Kho = $oXuatKho->TenKhoChuyenDen;
            $file->init(array('m'=>$data));
            foreach ($this->_params->ODanhSachXuatKho as $item) {
                $s = new stdClass();
                $s->stt = ++$stt;
                $s->thietBi = $item->TenSP;
                $s->ma = $item->MaSP;
                $s->donVi = $item->DonViTinh;
                $s->soLuong = (int)$item->SoLuong;
                $s->moTa = $item->MoTa;
                $s->note = '';
               
                 $file->newGridRow(array('s'=>$s), $row, 11);
            
                $row++;
                }   

            $header = $file->wsMain->getHeaderFooter()->getOddHeader();
            $header = str_replace(
            array('{m:SoChungTu}', '{m:date}'),array($data->SoChungTu, $data->date), $header);
            $file->wsMain->getHeaderFooter()->setOddHeader($header);    

            $file->removeRow(11);
            $file->save();
            die();
            $this->setHtmlRender(false);
            $this->setLayoutRender(false);
        }

}