<?php
class Print_M506Controller extends Qss_Lib_PrintController
{
    public function init()
    {
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    /**
     * SDM: Phiếu nhập kho
     */
    public function template1Action()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Phiếu Xuất Kho {$this->_params->SoChungTu}.xlsx\"");
        $file = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M506', 'SDM_PhieuXuatKho.xlsx'));
        $data = new stdClass();
        $stt  = 0;
        $row  = 23;
        $total = 0;

        $data->m1 = date('Y');
        $data->m2 = $this->_params->SoChungTu;
        $data->m3 = $this->_params->Kho;
        $data->m4 = $this->_params->NguoiNhan;
        $data->m5 = $this->_params->Ref_LoaiXuatKho;

        $file->init(array('m'=>$data));

//        echo '<pre>'; print_r($this->_params->ODanhSachNhapKho); die;

        foreach ($this->_params->ODanhSachXuatKho as $item)
        {
            $s          = new stdClass();
            $total     += ($item->ThanhTien != 0)?$item->ThanhTien:0;

            $s->c  = ++$stt;
            $s->c1 = $item->TenSP;
            $s->c2 = $item->MaSP;
            $s->c3 = $item->DonViTinh;
            $s->c4 = Qss_Lib_Util::formatNumber($item->SoLuongYeuCau);
            $s->c5 = Qss_Lib_Util::formatNumber($item->SoLuong);
            $s->c6 = $item->DonGia;
            $s->c7 = $item->ThanhTien;

            $file->newGridRow(array('s'=>$s), $row, 22);
            $row++;
        }

        $data = new stdClass();
        $data->m3 = $total;

        $file->init(array('m'=>$data));

        $file->removeRow(22);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}