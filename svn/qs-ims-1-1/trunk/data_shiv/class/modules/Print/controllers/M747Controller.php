<?php
class Print_M747Controller extends Qss_Lib_PrintController
{
    public function init()
    {
        parent::init();
        // $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    /**
     * Mau in: PHIẾU BÁO CÁO SỰ CỐ VÀ BÀN GIAO THIẾT BỊ (SHIV)
     */
    public function template1Action()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"PHIẾU BÁO CÁO SỰ CỐ VÀ BÀN GIAO THIẾT BỊ.xlsx\"");
        $file     = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M747', 'PhieuBaoCaoSuCoVaBanGiao.xlsx'));
        $data     = new stdClass();
        $mCommon  = new Qss_Model_Extra_Extra();

        $thoiGian   = $this->_params->ThoiGian?date('H:i', strtotime($this->_params->ThoiGian)):'';
        $ngay       = $this->_params->Ngay?date('d/m/Y', strtotime($this->_params->Ngay)):'';
        $workOrder  = $mCommon->getTableFetchOne('OPhieuBaoTri', array('IFNULL(Ref_PhieuYeuCau, 0)'=>$this->_params->IOID));
        $reason     = ($workOrder && $workOrder->Ref_MaNguyenNhanSuCo)?$mCommon->getTableFetchOne('ONguyenNhanSuCo', array('IOID'=>$workOrder->Ref_MaNguyenNhanSuCo)):false;

        $thoiGianKT = ($workOrder && $workOrder->ThoiGianKetThucDungMay)?date('H:i', strtotime($workOrder->ThoiGianKetThucDungMay)):'';
        $ngayKT     = ($workOrder && $workOrder->NgayKetThucDungMay)?date('d/m/Y', strtotime($workOrder->NgayKetThucDungMay)):'';

        $data->a8   = 1;
        $data->b8r1 = $this->_params->TenThietBi; // Ten Thiet Bi (Lấy từ yêu cầu bảo trì)
        $data->b8r2 = $this->_params->MaThietBi; // Ma Thiet Bi (Lấy từ yêu cầu bảo trì)
        $data->c8   = "{$thoiGian} {$ngay}"; // Thời điểm xảy ra sự cố: 10h 15/02/2017 (Lấy từ yêu cầu bảo trì)
        $data->d8   = $this->_params->MoTa; // Noi dung su co (Lấy từ yêu cầu bảo trì)
        $data->e8   = $this->_params->XuLy; // Đối sách (Lấy từ yêu cầu bảo trì)
        $data->f8   = $workOrder?$workOrder->NguyenNhan:''; // Nguyên nhân (Lấy từ phiếu bảo trì)
        $data->g8   = $workOrder?$workOrder->BienPhapKhacPhuc:''; // Phương án sửa chữa (Lấy từ phiếu bảo trì)
        $data->h8   = $workOrder?$workOrder->DoiSach:''; // Đối sách
        $data->i8   = "{$thoiGianKT} {$ngayKT}"; // Thời điểm khắc phục xong
        $data->j8   = $workOrder?$workOrder->ThoiGianDungMay:'';; // Thời gian dừng
        $data->k8   = ''; // Ghi chú
        $data->l8   = ''; // Có cần quản lí điểm thay đôi không: Co
        $data->m8   = 'V'; // Có cần quản lí điểm thay đôi không: Khong
        $data->n8   = ''; // Mã số điểm thay đổi
        $data->o8   = ''; // Đánh giá

        $data->c14  = $this->_params->NguoiYeuCau; // Người thao tác
        $data->d14  = ''; // Leader
        $data->e14  = 'Mr.Huệ'; // Manager
        $data->j14  = $workOrder?$workOrder->NguoiThucHien:''; // Thực hiện
        $data->k14  = 'Mr.Luận'; // Kiểm tra
        $data->m14  = $workOrder?$workOrder->NguoiThucHien:''; // Kỹ thuật
        $data->n14  = $this->_params->NguoiYeuCau; // Sản xuất
        $data->o14  = ''; // QC

        $data->a16  = $this->_params->TenThietBi; // QC
        $data->a18  = $this->_params->MaKhuVuc; // Khu vực
        $data->a20  = $this->_params->MoTa; // Tình trạng truocs bao tri
        $data->a23  = $workOrder?$workOrder->TinhTrangSauBaoTri:''; // Tình trạng sau bao tri
        $data->a32  = "{$thoiGianKT} {$ngayKT}"; // Thoi gian ket thuc dung may


        $data->a30r1 = date('d'); // Ngày
        $data->a30r2 = date('m'); // Tháng
        $data->a30r3 = date('Y'); // Năm

        $data->c34  = $workOrder?$workOrder->NguoiThucHien:''; // Người bàn giao.: Tuấn
        $data->h34  = 'Mr.Luận'; // Xác nhận của cấp trên(KTSX) : Luận
        $data->m34  = $workOrder?$workOrder->NguoiThucHien:''; // Người nhận : Khắc

        $file->init(array('s'=>$data));

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}