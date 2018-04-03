<?php
class Qss_Bin_Onload_OPhieuBaoTri extends Qss_Bin_Onload_Base_OPhieuBaoTri
{
    public function __doExecute()
    {
        parent::__doExecute();
        $this->_KhoiTao();
        // Điền tên thiết bị theo mã tạm nếu sử dụng mã tạm
        $this->_MaTam();
        // Điền tên khu vực theo mã thiết bị
        $this->_DienMaKhuVuc();
        // Điền loại bảo trì mặc định khi tạo mới, mặc định là loại sự cố
        // $this->_LoaiBaoTriMacDinh();
        // Điền đơn vị bảo trì và người thực hiện mặc định, với người thực hiện theo user và phải làm trong đơn vị bảo trì
        $this->_DonViBaoTriVaNguoiThucHienMacDinh();
        // Lọc đơn vị bảo trì theo nhân viên (user đăng nhập của nhân viên)
        $this->_LocDonViBaoTriTheoPhanQuyen();
        // Không cho chọn bộ phận nếu trong các đối tượng vật tư hay công việc đã chọn nhiều bộ phận
        $this->_DisableBoPhan();
        // Ẩn hiện tab phân tích hư hỏng theo loại bảo trì và cấu hình trong sysAdmin
        $this->_AnTabPhanTichHuHong();
        // Hien listbox khi loai la dinh ky va textbox khi khong phai dinh ky
        $this->_ThayDoiNhapLieuChuKyVaMoTaTheoLoaiBaoTri();
        // Điền người giao việc theo user đăng nhập
        $this->_DienNguoiGiaoViecMacDinh();
        // Xóa chu kỳ nếu chu kỳ không thuộc kế hoạch M724 (Mô tả công việc)
        $this->_XoaChuKy();
        // Bat buoc ngay ke hoach khi chon ke hoach tong the
        $this->_BatBuocNgayKeHoachKhiChonKeHoachTongThe();
        // Load tên công việc theo kế hoạch tông thể
        $this->_loadTenCongViecTheoKeHoachTongThe();
    }
}