<?php
class Qss_Bin_Onload_Base_OPhieuBaoTri extends Qss_Lib_Onload
{
    private $_LoaiBaoTri;
    // private $_RightPhieuBaoTri;
    private $_NhanVienTheoUser; // Chỉ lấy ra 1 nhân viên (Kể cả trường hợp gắn 1 user với nhiều nhân viên)



    protected function _KhoiTao() {
        // Lấy loại bảo trì
        $refLoaiBT         = $this->_object->getFieldByCode('LoaiBaoTri')->getRefIOID();
        $objLoaiBaoTri     = $this->_db->fetchOne(sprintf('select * from OPhanLoaiBaoTri where IOID = %1$d', $refLoaiBT));
        $this->_LoaiBaoTri = ($objLoaiBaoTri)?$objLoaiBaoTri->LoaiBaoTri:'';
        // Lấy user và quyên của user
        // $this->_RightPhieuBaoTri = Qss_Lib_System::getFormRights('M759', $this->_user->user_group_list);
        // Lấy nhân viên (M316) theo user đăng nhập
        $mEmp                    = new Qss_Model_Maintenance_Employee();
        $this->_NhanVienTheoUser = $mEmp->getEmployeeByUserID($this->_user->user_id); // Lấy nhân viên theo user đăng nhập
    }

    /**
     *
     */
    protected function _loadTenCongViecTheoKeHoachTongThe() {
        $SoKeHoach   = (int)$this->_object->getFieldByCode('SoKeHoach')->getRefIOID();
        $LoaiKeHoach = (int)$this->_object->getFieldByCode('NgayKeHoach')->getRefIOID(); // Code là ngày kế hoạch
        $TenCongViec = trim($this->_object->getFieldByCode('MoTa')->getValue()); // Code là mô tả

        $mTable     = Qss_Model_Db::Table('OKeHoachBaoTri');
        $mTable->where(sprintf('IOID = %1$d', $LoaiKeHoach));
        $objKeHoach = $mTable->fetchOne();

        if($TenCongViec == '' && $objKeHoach) {
            $this->_object->getFieldByCode('MoTa')->setValue($objKeHoach->MoTa);
        }
    }

    /**
     * Yêu cầu nhập tình trạng xử lý khi tick đạt
     */
    protected function _YeuCauNhapTinhTrangKhiTickDat() {
        $dat = $this->_object->getFieldByCode('Dat')->getValue();

        if($dat == 2) {
            $this->_object->getFieldByCode('TinhTrangSauBaoTri')->bRequired = true;
        }
    }

    /**
     * Onload lại tên mặt hàng có thể sửa khi sử dụng mã tạm
     */
    protected function _MaTam() {
        if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'MaTam')) {
            $item = @(int)$this->_object->getFieldByCode('MaThietBi')->getRefIOID();

            // Mặc định luôn để tên thiết bị readonly, thay đổi theo mã thiết bị có phải là mã tạm hay không?
            $this->_object->getFieldByCode('TenThietBi')->bReadOnly = true;

            $mItem = Qss_Model_Db::Table('ODanhSachThietBi');
            $mItem->where(sprintf('IOID = %1$d', $item));
            $oItem = $mItem->fetchOne();

            if($oItem) {
                if($oItem->MaTam) {
                    if(!$this->_object->getFieldByCode('MaThietBi')->bReadOnly) {
                        // Khi sử dụng mã tạm cho sửa tên thiết bị
                        $this->_object->getFieldByCode('TenThietBi')->bReadOnly = false;
                        // Khi sử dụng mã tạm tên thiết bị luôn bắt buộc
                        $this->_object->getFieldByCode('TenThietBi')->bRequired = true;
                    }

                }
                else {
                    // Khi không sử dụng mã tạm load tên thiết bị theo mã thiết bị
                    $this->_object->getFieldByCode('TenThietBi')->setValue($oItem->TenThietBi);
                }
            }

            // Set lại tên thiết bị readonly khi phiếu đóng (nghiệm thu) hoặc hủy
            if($this->_object && ($this->_object->intStatus == 4 || $this->_object->intStatus == 5)) {
                $this->_object->getFieldByCode('TenThietBi')->bReadOnly = true;
            }
        }
    }

    /**
     * Điền mã khu vực khi nhập mã thiết bị
     */
    protected function _DienMaKhuVuc() {
        if(Qss_Lib_System::fieldActive('OPhieuBaoTri', 'MaKhuVuc')) {
            $maKhuVuc     = trim($this->_object->getFieldByCode('MaKhuVuc')->getValue());
            $refMaThietBi = (int)$this->_object->getFieldByCode('MaThietBi')->getRefIOID();

            if(!$this->_object->i_IOID && $maKhuVuc == '') {
                $mThietBi = Qss_Model_Db::Table('ODanhSachThietBi');
                $mThietBi->where($mThietBi->ifnullNumber('IOID', $refMaThietBi));
                $oThietBi = $mThietBi->fetchOne();

                if($oThietBi && @$oThietBi->Ref_MaKhuVuc) {
                    $this->_object->getFieldByCode('MaKhuVuc')->setValue($oThietBi->MaKhuVuc);
                    $this->_object->getFieldByCode('MaKhuVuc')->setRefIOID($oThietBi->Ref_MaKhuVuc);
                }
            }
        }
    }

    /**
     * Mặc định loại bảo trì khi tạo phiếu mới là sự cố, mặc định là loại sự cố
     */
    protected function _LoaiBaoTriMacDinh() {
        $defaultType = $this->_db->fetchOne(sprintf(' 
            SELECT * FROM OPhanLoaiBaoTri WHERE LoaiBaoTri = "%1$s" ', Qss_Lib_Extra_Const::MAINT_TYPE_BREAKDOWN));

        if($defaultType && !$this->_object->getFieldByCode('LoaiBaoTri')->getRefIOID()) {
            $this->_object->getFieldByCode('LoaiBaoTri')->setValue($defaultType->LoaiBaoTri);
            $this->_object->getFieldByCode('LoaiBaoTri')->setRefIOID((int)$defaultType->IOID);
        }
    }

    /**
     * Mặc định chọn một mức độ ưu tiên
     */
    protected function _MucDoUuTienMacDinh() {
        if (!$this->_object->i_IOID && $this->_object->getFieldByCode('MucDoUuTien')->getValue() === '') {
            $this->_object->getFieldByCode('MucDoUuTien')->setValue(1);
        }
    }

    /**
     * Điền đơn vị bảo trì và người thực hiện mặc định, với người thực hiện theo user và phải thuộc trong đơn vị bảo trì
     */
    protected function _DonViBaoTriVaNguoiThucHienMacDinh() {
        $nguoiThucHien = (int)$this->_object->getFieldByCode('NguoiThucHien')->getRefIOID();

        if($this->_NhanVienTheoUser) {
            $maDVBT   = $this->_NhanVienTheoUser->MaDonViThucHien ? $this->_NhanVienTheoUser->MaDonViThucHien : '';
            $tenDVBT  = $this->_NhanVienTheoUser->TenDonViThucHien ? $this->_NhanVienTheoUser->TenDonViThucHien : '';
            $ioidDVBT = $this->_NhanVienTheoUser->RefDonViThucHien ? $this->_NhanVienTheoUser->RefDonViThucHien : 0;

            $mNhanVienDonVi = Qss_Model_Db::Table('ONhanVien');
            $mNhanVienDonVi->join('INNER JOIN ODonViSanXuat ON ONhanVien.IFID_M125 = ODonViSanXuat.IFID_M125');
            $mNhanVienDonVi->where(sprintf(' ODonViSanXuat.IOID = %1$d ', (int)$this->_object->getFieldByCode('MaDVBT')->getRefIOID()));
            $mNhanVienDonVi->where(sprintf(' ONhanVien.Ref_MaNV = %1$d ', $this->_NhanVienTheoUser->IOID));
            $nhanVienLamTrongDonVi = $mNhanVienDonVi->fetchOne();

            if ($ioidDVBT && $this->_NhanVienTheoUser->BaoTri && !$this->_object->getFieldByCode('MaDVBT')->getRefIOID()) {
                $this->_object->getFieldByCode('MaDVBT')->setValue($maDVBT);
                $this->_object->getFieldByCode('MaDVBT')->setRefIOID($ioidDVBT);
                $this->_object->getFieldByCode('TenDVBT')->setValue($tenDVBT);
                $this->_object->getFieldByCode('TenDVBT')->setRefIOID($ioidDVBT);
            }

            if (!$this->_object->i_IOID && $this->_NhanVienTheoUser->IOID && $nguoiThucHien == 0 && ($ioidDVBT && $nhanVienLamTrongDonVi)) {
                $this->_object->getFieldByCode('NguoiThucHien')->setValue($this->_NhanVienTheoUser->TenNhanVien);
                $this->_object->getFieldByCode('NguoiThucHien')->setRefIOID($this->_NhanVienTheoUser->IOID);
            }
        }
    }

    /**
     * Lọc bản ghi theo đơn vị bảo trì nếu có phân quyền theo đơn vị bảo trì
     */
    protected function _LocDonViBaoTriTheoPhanQuyen() {
        // if($this->_RightPhieuBaoTri && !($this->_RightPhieuBaoTri & 2))
//        {
//            $dvbt = $this->_object->getFieldByCode('MaDVBT');
//            $dvbt->arrFilters[] = sprintf('
//                v.IFID_M125 in (
//                    select IFID_M125 from ONhanVien
//                    inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = ONhanVien.Ref_MaNV
//                    where IFNULL(Ref_TenTruyCap, 0) = %1$d
//                )',
//                $this->_user->user_id);
//        }
    }

    /**
     * Không cho chọn bộ phận nếu trong các đối tượng vật tư hay công việc đã chọn nhiều bộ phận
     * Xét trên công việc, vật tư và vật tư dự kiến (nếu có)
     */
    protected function _DisableBoPhan() {
        $sql = sprintf('select count(1) as Total from OCongViecBTPBT where IFID_M759 = %1$d and ifnull(Ref_ViTri, 0) != 0 ',$this->_object->i_IFID);
        $dataSQL1 = $this->_db->fetchOne($sql);

        $sql = sprintf('select count(1) as Total from OVatTuPBT where IFID_M759 = %1$d and ifnull(Ref_ViTri, 0) != 0 ',$this->_object->i_IFID);
        $dataSQL2 = $this->_db->fetchOne($sql);

        $vatTuDuKien = 0;
        if(Qss_Lib_System::objectInForm('M759', 'OVatTuPBTDK')) {
            $sql = sprintf('select count(1) as Total from OVatTuPBTDK where IFID_M759 = %1$d and ifnull(Ref_ViTri, 0) != 0 ',$this->_object->i_IFID);
            $dataSQL3 = $this->_db->fetchOne($sql);
            $vatTuDuKien = $dataSQL3?$dataSQL3->Total:0;
        }

        $totalCom = @(int)$dataSQL1->Total + @(int)$dataSQL2->Total + $vatTuDuKien;

        if($totalCom) {
            $this->_object->getFieldByCode('BoPhan')->bReadOnly = true;
        }
    }

    /**
     * Ẩn tab phân tích hư hỏng
     * Miêu tả: Mặc định ẩn tab phân tích hư hỏng đi. Nếu là loại bảo trì sự cố sẽ hiện theo cấu hình trong sysAdmin
     */
    protected function _AnTabPhanTichHuHong() {
        if(@$this->_form) {
            $cauHinh = $this->_form->o_fGetObjectByCode('ODanhMucBoPhanHuHong')->bPublic; // lấy mặc định
            $this->_form->o_fGetObjectByCode('ODanhMucBoPhanHuHong')->bPublic = 3; // ẩn hết dù mặc định là gì

            if($this->_LoaiBaoTri == Qss_Lib_Extra_Const::MAINT_TYPE_BREAKDOWN) {
                $this->_form->o_fGetObjectByCode('ODanhMucBoPhanHuHong')->bPublic = $cauHinh;
            }
        }
    }

    /**
     * Khi loại bảo trì là định kỳ bắt buộc nhập chu kỳ, cho chọn mô tả dạng listbox
     * Khi là loại khác chỉ cần nhập mô tả dạng ô text
     */
    protected function _ThayDoiNhapLieuChuKyVaMoTaTheoLoaiBaoTri() {
        if($this->_object && (int)$this->_object->intStatus <= 1) { // Có thể status lúc tạo mới bằng rỗng nên phải set int và nhỏ hơn bằng 1
            if($this->_LoaiBaoTri == Qss_Lib_Extra_Const::MAINT_TYPE_PREVENTIVE) {
                $this->_object->getFieldByCode('MoTa')->intInputType = 4; // Listbox
                $this->_object->getFieldByCode('MoTa')->bEditStatus = true;
                $this->_object->getFieldByCode('ChuKy')->bReadOnly  = false;
                $this->_object->getFieldByCode('ChuKy')->bRequired  = true;
            }
            else {
                $this->_object->getFieldByCode('MoTa')->intInputType = 1; // Text
                $this->_object->getFieldByCode('MoTa')->bEditStatus = true;
                $this->_object->getFieldByCode('ChuKy')->bReadOnly = true;
                $this->_object->getFieldByCode('ChuKy')->bRequired = false;
            }
        }
    }

    /**
     * Xoa chu ky neu chu ky khong thuoc cong viec
     */
    protected function _XoaChuKy() {
        if($this->_LoaiBaoTri != Qss_Lib_Extra_Const::MAINT_TYPE_PREVENTIVE)
        {
            $this->_object->getFieldByCode('ChuKy')->setValue('');
            $this->_object->getFieldByCode('ChuKy')->setRefIOID(0);
        }
    }

    /**
     * Điền người giao việc theo user đăng nhập
     */
    protected function _DienNguoiGiaoViecMacDinh() {
        if(!$this->_object->getFieldByCode('NguoiGiaoViec')->getValue() && $this->_NhanVienTheoUser && !$this->_object->i_IFID)
        {
            $this->_object->getFieldByCode('NguoiGiaoViec')->setValue("{$this->_NhanVienTheoUser->TenNhanVien} ({$this->_NhanVienTheoUser->MaNhanVien})");
            $this->_object->getFieldByCode('NguoiGiaoViec')->setRefIOID($this->_NhanVienTheoUser->IOID);
        }
    }


    protected function _DienThoiGianDungMayMacDinh() {
        // Nhập thời gian dừng máy mặc định
        if($this->_LoaiBaoTri == 'B')
        {
            if(!$this->_object->getFieldByCode('NgayDungMay')->getValue())
            {
                $temp = $this->_object->getFieldByCode('NgayBatDau')->getValue();
                $this->_object->getFieldByCode('NgayDungMay')->setValue($temp);
            }

            if(!$this->_object->getFieldByCode('ThoiGianBatDauDungMay')->getValue())
            {
                $temp = $this->_object->getFieldByCode('GioBatDau')->getValue();
                $this->_object->getFieldByCode('ThoiGianBatDauDungMay')->setValue($temp);
            }

            if(!$this->_object->getFieldByCode('NgayKetThucDungMay')->getValue())
            {
                $temp = $this->_object->getFieldByCode('Ngay')->getValue();
                $this->_object->getFieldByCode('NgayKetThucDungMay')->setValue($temp);
            }

            if(!$this->_object->getFieldByCode('ThoiGianKetThucDungMay')->getValue())
            {
                $temp = $this->_object->getFieldByCode('GioKetThuc')->getValue();
                $this->_object->getFieldByCode('ThoiGianKetThucDungMay')->setValue($temp);
            }
        }
    }

    protected function _BatBuocNgayKeHoachKhiChonKeHoachTongThe() {
        if(Qss_Lib_System::fieldActive('OPhieuBaoTri', 'SoKeHoach')) {
            $soKeHoach = @(int)$this->_object->getFieldByCode('SoKeHoach')->getRefIOID();

            if(Qss_Lib_System::fieldActive('OPhieuBaoTri', 'NgayKeHoach')) {
                if($soKeHoach != 0) {
                    $this->_object->getFieldByCode('NgayKeHoach')->bRequired  = true;
                }
                else {
                    $this->_object->getFieldByCode('NgayKeHoach')->bRequired  = false;
                }
            }
        }
    }


    public function MoTa() {
        // Không lấy được theo poperty loại bảo trì ở đây vì không chạy qua hàm khởi tạo, phải lấy lại
        $refLoaiBT         = $this->_object->getFieldByCode('LoaiBaoTri')->getRefIOID();
        $objLoaiBaoTri     = $this->_db->fetchOne(sprintf('select * from OPhanLoaiBaoTri where IOID = %1$d', $refLoaiBT));
        $this->_LoaiBaoTri = ($objLoaiBaoTri)?$objLoaiBaoTri->LoaiBaoTri:'';

        if($this->_LoaiBaoTri == Qss_Lib_Extra_Const::MAINT_TYPE_PREVENTIVE)
        {
            $makv     = $this->_object->getFieldByCode('MaKhuVuc')->getRefIOID();
            $matb     = $this->_object->getFieldByCode('MaThietBi')->getRefIOID();
            $manophan = $this->_object->getFieldByCode('BoPhan')->getRefIOID();

            if($matb) // Cho chọn theo thiết bị
            {
                $this->_object->getFieldByCode('MoTa')->arrFilters[] = sprintf('
            v.IFID_M724 in (select IFID_M724 from OBaoTriDinhKy where IFNULL(Ref_MaThietBi, 0) = %1$d and ifnull(Ref_BoPhan,0) = %2$d)
            ', $matb, $manophan);
            }
            elseif($makv) // Cho chon theo khu vuc
            {
                $this->_object->getFieldByCode('MoTa')->arrFilters[] = sprintf('
            v.IFID_M724 in (select IFID_M724 from OBaoTriDinhKy where IFNULL(Ref_MaThietBi, 0) = 0 and ifnull(Ref_MaKhuVuc,0) = %1$d)
            ', $makv);
            }
            else // Chua co loc, khong cho chon
            {
                $this->_object->getFieldByCode('MoTa')->arrFilters[] = sprintf('v.IFID_M724 = 0');
            }
        }
    }

    public function ChuKy()
    {
    	if($this->_LoaiBaoTri != Qss_Lib_Extra_Const::MAINT_TYPE_PREVENTIVE)
        {
        	$this->_object->getFieldByCode('ChuKy')->arrFilters[] = sprintf(
            ' v.IFID_M724 in (select IFID_M724 from OBaoTriDinhKy where IOID = %1$d) '
            , @(int)$this->_object->getFieldByCode('MoTa')->getRefIOID());
        }
    }

    public function NgayKeHoach()
    {
        $soKeHoach = (int)$this->_object->getFieldByCode('SoKeHoach')->getRefIOID();
        $makv      = (int)$this->_object->getFieldByCode('MaKhuVuc')->getRefIOID();
        $matb      = (int)$this->_object->getFieldByCode('MaThietBi')->getRefIOID();
        $manophan  = (int)$this->_object->getFieldByCode('BoPhan')->getRefIOID();
        $loaitb    = (int)$this->_object->getFieldByCode('LoaiBaoTri')->getRefIOID();
        $chuKy     = (int)$this->_object->getFieldByCode('ChuKy')->getRefIOID();
        if($matb)
        {
            $this->_object->getFieldByCode('NgayKeHoach')->arrFilters[] = sprintf('
            v.IFID_M837 in
            (
                SELECT IFID_M837
                FROM OKeHoachBaoTri
                WHERE Ref_KeHoachTongThe = %1$d
                    and Ref_MaThietBi = %2$d
                    AND IFNULL(Ref_BoPhan, 0) = %3$d
                    AND Ref_LoaiBaoTri = %4$d
                    AND IFNULL(Ref_ChuKy, 0) = %5$d
            )'
                , $soKeHoach, $matb, $manophan, $loaitb, $chuKy
            );
        }
        elseif($makv)
        {
            $this->_object->getFieldByCode('NgayKeHoach')->arrFilters[] = sprintf('
            v.IFID_M837 in
            (
                SELECT IFID_M837
                FROM OKeHoachBaoTri
                WHERE Ref_KeHoachTongThe = %1$d
                    and IFNULL(Ref_MaKhuVuc, 0) = %2$d                    
                    AND Ref_LoaiBaoTri = %3$d
                    AND IFNULL(Ref_ChuKy, 0) = %4$d
            )'
                , $soKeHoach, $makv, $loaitb, $chuKy
            );
        }
        else
        {
        	$this->_object->getFieldByCode('NgayKeHoach')->arrFilters[] = sprintf(' 1=0 ');
        }
    }

    //    public function LanBaoTri()
//    {
//        $soKeHoach = $this->_object->getFieldByCode('SoKeHoach')->getRefIOID();
//        $matb      = $this->_object->getFieldByCode('MaThietBi')->getRefIOID();
//        $manophan  = $this->_object->getFieldByCode('BoPhan')->getRefIOID();
//        $loaitb    = $this->_object->getFieldByCode('LoaiBaoTri')->getRefIOID();
//        $chuKy     = $this->_object->getFieldByCode('ChuKy')->getRefIOID();
//
//        $this->_object->getFieldByCode('LanBaoTri')->arrFilters[] = sprintf('
//            v.IFID_M837 in
//            (
//                SELECT IFID_M837
//                FROM OKeHoachBaoTri
//                WHERE Ref_KeHoachTongThe = %1$d
//                    and Ref_MaThietBi = %2$d
//                    AND IFNULL(Ref_BoPhan, 0) = %3$d
//                    AND Ref_LoaiBaoTri = %4$d
//                    AND IFNULL(Ref_ChuKy, 0) = %5$d
//            )'
//            , $soKeHoach, $matb, $manophan, $loaitb, $chuKy
//        );
//    }

    public function MaDVBT()
    {
        $field = $this->_object->getFieldByCode('MaDVBT');
        $field->arrFilters[] = ' (v.BaoTri = 1)';

        if(Qss_Lib_System::formSecure('M125')) {
            $field->arrFilters[] = sprintf('
            	(
					v.IFID_M125 in (
						SELECT IFID_M125 FROM ODonViSanXuat
						inner join qsrecordrights on ODonViSanXuat.IFID_M125 = qsrecordrights.IFID
						WHERE UID = %1$d
					)
				)
            ', $this->_user->user_id);
        }

    }

    public function MaThietBi()
    {
        $this->_object->getFieldByCode('MaThietBi')->arrFilters[] = sprintf(' IFNULL(v.TrangThai, 0) = 0 ');

        if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'MaTam')) {
            $makhuvuc = $this->_object->getFieldByCode('MaKhuVuc')->getRefIOID();

            if($makhuvuc) {
                $this->_object->getFieldByCode('MaThietBi')->arrFilters[] = sprintf(' 
                (
                    v.Ref_MaKhuVuc in (
                        SELECT IOID 
                        FROM OKhuVuc
						WHERE OKhuVuc.lft >= (SELECT lft FROM OKhuVuc WHERE IOID=%1$d) 
						and OKhuVuc.rgt <= (SELECT rgt FROM OKhuVuc WHERE IOID = %1$d)
                    ) 
                    or IFNULL(v.MaTam, 0) = 1
                )',$makhuvuc);
            }

            if(Qss_Lib_System::formSecure('M720')) {
                $this->_object->getFieldByCode('MaThietBi')->arrFilters[] = sprintf(' 
                (
                    (
                        ifnull(v.Ref_MaKhuVuc,0)=0 
                        or v.Ref_MaKhuVuc in (
                            SELECT IOID 
                            FROM OKhuVuc
						    inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID 
						    WHERE UID = %1$d
                        )
                    ) 
                    or IFNULL(v.MaTam, 0) = 1 
                ) ',$this->_user->user_id);
            }
        }
        else {
            $makhuvuc = $this->_object->getFieldByCode('MaKhuVuc')->getRefIOID();
            if($makhuvuc)
            {
                $this->_object->getFieldByCode('MaThietBi')->arrFilters[] = sprintf(' v.Ref_MaKhuVuc in (SELECT IOID FROM OKhuVuc
						WHERE OKhuVuc.lft >= (SELECT lft FROM OKhuVuc WHERE IOID=%1$d) 
						and OKhuVuc.rgt <= (SELECT rgt FROM OKhuVuc WHERE IOID = %1$d))'
                    ,$makhuvuc);
            }
            if(Qss_Lib_System::formSecure('M720'))
            {
                $this->_object->getFieldByCode('MaThietBi')->arrFilters[] = sprintf(' (ifnull(v.Ref_MaKhuVuc,0)=0 or v.Ref_MaKhuVuc in (SELECT IOID FROM OKhuVuc
						inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID 
						WHERE UID = %1$d))'
                    ,$this->_user->user_id);
            }
        }
    }

    public function NguoiThucHien()
    {
        $madvbt = $this->_object->getFieldByCode('MaDVBT')->getRefIOID();
        $this->_object->getFieldByCode('NguoiThucHien')->arrFilters[] = sprintf(' 
        v.IOID in (
            SELECT Ref_MaNV 
            FROM ONhanVien
            WHERE IFID_M125 in (SELECT IFID_M125 FROM ODonViSanXuat WHERE IOID = %1$d))'
            ,$madvbt);
    }

    public function LoaiHuHong()
    {
        $boPhan    = (int)$this->_object->getFieldByCode('BoPhan')->getRefIOID();
        $maThietBi = (int)$this->_object->getFieldByCode('MaThietBi')->getRefIOID();
        $bCheck    = false;

        if(!$maThietBi && !$boPhan)
        {
            $sql = sprintf(' SELECT * FROM OPhieuBaoTri WHERE IFID_M759 = %1$d ', $this->_object->i_IFID);
            $dat = $this->_db->fetchOne($sql);

            if($dat)
            {
                $boPhan    = (int)$dat->Ref_BoPhan;
                $maThietBi = (int)$dat->Ref_MaThietBi;
            }
        }

        if($boPhan)
        {
            $szCheck = sprintf('
                SELECT  IFNULL(OCauTrucThietBi.Ref_ClassHuHong, 0) AS CoClassHuHong
                FROM ODanhSachThietBi
                INNER JOIN OCauTrucThietBi ON ODanhSachThietBi.IFID_M705 = OCauTrucThietBi.IFID_M705
                WHERE ODanhSachThietBi.IOID = %1$d AND OCauTrucThietBi.IOID = %2$d
            ', $maThietBi, $boPhan);
            $check = $this->_db->fetchOne($szCheck);

            if($check && $check->CoClassHuHong != 0)
            {
                $bCheck = true;
            }
        }


        if($bCheck)
        {
            $filter = sprintf('                    
                v.IOID IN (
                    SELECT OLoaiHuHong.IOID
                    FROM OLoaiHuHong
                    INNER JOIN OClassHuHong ON OLoaiHuHong.IFID_M172 = OClassHuHong.IFID_M172
                    INNER JOIN OCauTrucThietBi ON IFNULL(OCauTrucThietBi.Ref_ClassHuHong, 0) = OClassHuHong.IOID
                    INNER JOIN ODanhSachThietBi ON OCauTrucThietBi.IFID_M705 = ODanhSachThietBi.IFID_M705
                    WHERE ODanhSachThietBi.IOID = %1$d AND OCauTrucThietBi.IOID = %2$d
                )
            ', $maThietBi, $boPhan);
        }
        else
        {
            $filter = sprintf('                    
                v.IOID IN (
                    SELECT OLoaiHuHong.IOID
                    FROM OLoaiHuHong
                    INNER JOIN OClassHuHong ON OLoaiHuHong.IFID_M172 = OClassHuHong.IFID_M172
                    INNER JOIN ODanhSachThietBi ON IFNULL(ODanhSachThietBi.Ref_ClassHuHong, 0) = OClassHuHong.IOID
                    WHERE ODanhSachThietBi.IOID = %1$d
                )
            ', $this->_object->i_IFID);
        }

        $this->_object->getFieldByCode('LoaiHuHong')->arrFilters[] = $filter;
    }

    public function NguoiNghiemThu()
    {
        $this->_object->getFieldByCode('NguoiNghiemThu')->arrFilters[] = sprintf(' 
                v.IOID in (
                    SELECT ODanhSachNhanVien.IOID 
                    FROM  ODanhSachNhanVien
                    INNER JOIN ONhanVien ON ODanhSachNhanVien.IOID = ONhanVien.Ref_MaNV
                    WHERE IFNULL(ONhanVien.QuanLy, 0) = 1
                    
                )');
    }
}