<?php

class Qss_Bin_Trigger_ODanhSachThietBi extends Qss_Lib_Trigger {
    public function __construct($form) {
        parent::__construct($form);
    }

    /**
     * OnUpdated: Cập nhật lại tên mặt hàng (Với trường hợp sử dụng mã tạm)
     * - Cập nhật lại tên sản phẩm trong kế hoạch bảo trì M724
     * - Cập nhật lại tên sản phẩm trong kế hoạch tổng thể M838
     * - Cập nhật lại tên sản phẩm trong phiếu bảo trì M759
     * - Cập nhật lại tên sản phẩm trong yêu cầu bảo trì M747
     */
    public function onUpdate(Qss_Model_Object $object) {
        parent::init();
        // Chỉ cập nhật lại tên mặt hàng khi sủ dụng mã tạm
        if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'MaTam')) {
            $mCommon     = new Qss_Model_Extra_Extra();
            $ioidThietBi = $object->i_IOID; // #note: cái này có bằng IOID của sản phẩm

            // Cập nhật lại tên sản phẩm trong kế hoạch bảo trì M724
            if(Qss_Lib_System::objectInForm('M724', 'OBaoTriDinhKy')) {
                $fieldTenThietBiM724 = $mCommon->getTableFetchOne('qsfields', array('ObjectCode'=>'OBaoTriDinhKy', 'FieldCode'=>'TenThietBi'));

                // Chỉ cập nhật lại khi có field TenSanPham và field này dạng TextBox
                if($fieldTenThietBiM724 && $fieldTenThietBiM724->InputType = 1) {
                    $sql = sprintf('
                        UPDATE OBaoTriDinhKy
                        INNER JOIN ODanhSachThietBi ON OBaoTriDinhKy.Ref_MaThietBi = ODanhSachThietBi.IOID
                        SET OBaoTriDinhKy.TenThietBi = ODanhSachThietBi.TenThietBi
                        WHERE IFNULL(OBaoTriDinhKy.Ref_MaThietBi, 0) = %1$d AND IFNULL(ODanhSachThietBi.MaTam, 0) = 0 
                    ', $ioidThietBi);

                    $this->_db->execute($sql);
                }
            }

            // Cập nhật lại tên sản phẩm trong kế hoạch tổng thể M838
            if(Qss_Lib_System::objectInForm('M837', 'OKeHoachBaoTri')) {
                $fieldTenThietBiM838 = $mCommon->getTableFetchOne('qsfields', array('ObjectCode'=>'OKeHoachBaoTri', 'FieldCode'=>'TenThietBi'));

                // Chỉ cập nhật lại khi có field TenSanPham và field này dạng TextBox
                if($fieldTenThietBiM838 && $fieldTenThietBiM838->InputType = 1) {
                    $sql = sprintf('
                        UPDATE OKeHoachBaoTri
                        INNER JOIN ODanhSachThietBi ON OKeHoachBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
                        SET OKeHoachBaoTri.TenThietBi = ODanhSachThietBi.TenThietBi
                        WHERE IFNULL(OKeHoachBaoTri.Ref_MaThietBi, 0) = %1$d AND IFNULL(ODanhSachThietBi.MaTam, 0) = 0 
                    ', $ioidThietBi);

                    $this->_db->execute($sql);
                }
            }

            // Cập nhật lại tên sản phẩm trong phiếu bảo trì M759
            if(Qss_Lib_System::objectInForm('M759', 'OPhieuBaoTri')) {
                $fieldTenThietBiM759 = $mCommon->getTableFetchOne('qsfields', array('ObjectCode'=>'OPhieuBaoTri', 'FieldCode'=>'TenThietBi'));

                // Chỉ cập nhật lại khi có field TenSanPham và field này dạng TextBox
                if($fieldTenThietBiM759 && $fieldTenThietBiM759->InputType = 1) {
                    $sql = sprintf('
                        UPDATE OPhieuBaoTri
                        INNER JOIN ODanhSachThietBi ON OPhieuBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
                        SET OPhieuBaoTri.TenThietBi = ODanhSachThietBi.TenThietBi
                        WHERE IFNULL(OPhieuBaoTri.Ref_MaThietBi, 0) = %1$d AND IFNULL(ODanhSachThietBi.MaTam, 0) = 0 
                    ', $ioidThietBi);

                    $this->_db->execute($sql);
                }
            }

            // Cập nhật lại tên sản phẩm trong yêu cầu bảo trì M747
            if(Qss_Lib_System::objectInForm('M747', 'OYeuCauBaoTri')) {
                $fieldTenThietBiM747= $mCommon->getTableFetchOne('qsfields', array('ObjectCode'=>'OYeuCauBaoTri', 'FieldCode'=>'TenThietBi'));

                // Chỉ cập nhật lại khi có field TenSanPham và field này dạng TextBox
                if($fieldTenThietBiM747 && $fieldTenThietBiM747->InputType = 1) {
                    $sql = sprintf('
                        UPDATE OYeuCauBaoTri
                        INNER JOIN ODanhSachThietBi ON OYeuCauBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
                        SET OYeuCauBaoTri.TenThietBi = ODanhSachThietBi.TenThietBi
                        WHERE IFNULL(OYeuCauBaoTri.Ref_MaThietBi, 0) = %1$d AND IFNULL(ODanhSachThietBi.MaTam, 0) = 0 
                    ', $ioidThietBi);

                    $this->_db->execute($sql);
                }
            }
        }
    }
}