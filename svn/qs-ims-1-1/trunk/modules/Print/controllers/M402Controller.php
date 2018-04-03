<?php
class Print_M402Controller extends Qss_Lib_PrintController
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
        header("Content-disposition: attachment; filename=\"Phiếu Nhập Kho {$this->_params->SoChungTu}.xlsx\"");

        $mInout = new Qss_Model_Warehouse_Inout();
        $file   = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M402', 'SDM_PhieuNhapKho.xlsx'));
        $data   = new stdClass();
        $stt    = 0;
        $row    = 19;
        $total  = 0;
        $input  = $mInout->getInputWithPOInfo($this->_params->IOID);
        $lines  = $mInout->getInputLineWithPOInfo($this->_params->IOID);
        $soDonHang   = @$input->SoDonHang?$input->SoDonHang:'        ';
        $NgayDatHang = @$input->NgayDatHang?Qss_Lib_Date::mysqltodisplay($input->NgayDatHang):'        ';
        $TenNCC      = @$input->TenNCC?$input->TenNCC:'        ';

        $data->m1 = date('Y');
        $data->m2 = @$input->SoChungTu;
        $data->m3 = @$input->NguoiGiao;
        $data->m4 = @$input->Kho;
        $data->m5 = "Theo {$soDonHang} ngày {$NgayDatHang} của {$TenNCC} ";
        $data->m7 = @$input->DiaDiemGiaoHang;

        $file->init(array('m'=>$data));

//        echo '<pre>'; print_r($this->_params->ODanhSachNhapKho); die;

        foreach ($lines as $item)
        {
            $s          = new stdClass();
            $total     += ($item->ThanhTien != 0)?$item->ThanhTien:0;

            $s->c  = ++$stt;
            $s->c1 = $item->TenSanPham;
            $s->c2 = $item->MaSanPham;
            $s->c3 = $item->DonViTinh;
            $s->c4 = Qss_Lib_Util::formatNumber($item->SoLuongDatMua);
            $s->c5 = Qss_Lib_Util::formatNumber($item->SoLuong);
            $s->c6 = $item->DonGia;
            $s->c7 = $item->ThanhTien;

            $file->newGridRow(array('s'=>$s), $row, 18);
            $row++;
        }

        $data = new stdClass();
        $data->m3 = $total;

        $file->init(array('m'=>$data));

        $file->removeRow(18);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}