<?php
class Print_M401Controller extends Qss_Lib_PrintController
{
    public function init()
    {
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    /**
     * SDM: Đơn mua hàng
     */
    public function template1Action()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Đơn Mua Hàng {$this->_params->SoDonHang}.xlsx\"");
        $mPartner = new Qss_Model_Master_Partner();
        $file = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M401', 'SDM_DonMuaHang.xlsx'));
        $data = new stdClass();
        $stt  = 0;
        $row  = 20;
        $total = 0;
        $contact = $mPartner->getContactByIOID(@(int)$this->_params->Ref_NguoiNhan);

        $data->m1 = $this->_params->SoDonHang;
        $data->m2 = date('Y');
        $data->m3 = $this->_params->TenNCC;
        $data->m4 = $this->_params->NguoiNhan;
        $data->m5 = @$contact->DienThoaiDiDong;
        $data->m6 = @$contact->Email;

        $file->init(array('m'=>$data));

//        echo '<pre>'; print_r($this->_params->ODanhSachNhapKho); die;

        foreach ($this->_params->ODSDonMuaHang as $item)
        {
            $s          = new stdClass();
            $total     += ($item->ThanhTien != 0)?$item->ThanhTien:0;

            $s->c  = ++$stt;
            $s->c1 = $item->TenSanPham;
            $s->c2 = '';
            $s->c3 = $item->DonViTinh;
            $s->c4 = Qss_Lib_Util::formatNumber($item->SoLuong);
            $s->c5 = $item->DonGia;
            $s->c6 = $item->ThanhTien;

            $file->newGridRow(array('s'=>$s), $row, 19);
            $row++;
        }

        $data = new stdClass();
        $data->m4 = $total;
        $data->m5 = $total*0.1;
        $data->m6 = $total + $total*0.1;
        $data->m7 = Qss_Lib_Util::moneyToString($data->m6);

        $file->init(array('m'=>$data));

        $file->removeRow(19);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}