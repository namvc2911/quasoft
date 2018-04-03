<?php
class Qss_Bin_Onload_OChiTietThuHoiTaiSan   extends Qss_Lib_Onload
{
    /**
     * 1. Khi Loai la ban giao lai cho nhan vien khac thi nhan vien moi bat buoc nhap, cac truong hop con lai thi an di
     */
    public function __doExecute()
    {
        parent::__doExecute();

        $loai = $this->_object->getFieldByCode('Loai')->getValue();

        if($this->_object->intStatus == 1)
        {
            switch ($loai)
            {
                case 1:
                    $this->_object->getFieldByCode('MaNhanVienMoi')->bReadOnly = false;
                    $this->_object->getFieldByCode('MaNhanVienMoi')->bRequired = true;
                    break;

                default:
                    $this->_object->getFieldByCode('MaNhanVienMoi')->bReadOnly = true;
                    $this->_object->getFieldByCode('MaNhanVienMoi')->bRequired = false;


                    $this->_object->getFieldByCode('MaNhanVienMoi')->setRefIOID(0);
                    $this->_object->getFieldByCode('TenNhanVienMoi')->setRefIOID(0);
                    $this->_object->getFieldByCode('MaNhanVienMoi')->setValue('');
                    $this->_object->getFieldByCode('TenNhanVienMoi')->setValue('');
                    break;
            }
        }



        $maTaiSan     = (int)$this->_object->getFieldByCode('MaTaiSan')->intRefIOID;
        $maNhanVien   = (int)$this->_object->getFieldByCode('MaNhanVien')->intRefIOID;

        if($maTaiSan && $maNhanVien)
        {
            $where        = '';
            $where       .= $maNhanVien?sprintf(' AND OChiTietBanGiaoTaiSan.Ref_MaNhanVien = %1$d ', $maNhanVien):' AND 1=0 ';
            $where       .= $maTaiSan?sprintf(' AND OChiTietBanGiaoTaiSan.Ref_MaTaiSan = %1$d ', $maTaiSan):'';
            $sql          = sprintf('
                SELECT 
                    OPhieuBanGiaoTaiSan.IOID
                    , OPhieuBanGiaoTaiSan.SoPhieu
                    , ((IFNULL(OChiTietBanGiaoTaiSan.SoLuong, 0)) 
                    - SUM(IFNULL(OChiTietThuHoiTaiSan.SoLuong, 0))) AS `Rest`
                FROM OPhieuBanGiaoTaiSan 
                INNER JOIN qsiforms AS IFormBanGiao ON OPhieuBanGiaoTaiSan.IFID_M182 = IFormBanGiao.IFID
                INNER JOIN OChiTietBanGiaoTaiSan ON OPhieuBanGiaoTaiSan.IFID_M182 = OChiTietBanGiaoTaiSan.IFID_M182
                
                LEFT JOIN OChiTietThuHoiTaiSan ON OPhieuBanGiaoTaiSan.IOID = IFNULL(OChiTietThuHoiTaiSan.Ref_PhieuBanGiao, 0)
                    AND IFNULL(OChiTietBanGiaoTaiSan.Ref_MaNhanVien, 0) = IFNULL(OChiTietThuHoiTaiSan.Ref_MaNhanVien, 0)
                    AND IFNULL(OChiTietBanGiaoTaiSan.Ref_MaTaiSan, 0) = IFNULL(OChiTietThuHoiTaiSan.Ref_MaTaiSan, 0)
                LEFT JOIN qsiforms as IFormThHoi ON IFNULL(OChiTietThuHoiTaiSan.IFID_M183, 0) = IFormThHoi.IFID
                
                WHERE IFNULL(IFormBanGiao.Status, 0) = 2 %1$s
                GROUP BY OPhieuBanGiaoTaiSan.IFID_M182
                HAVING `Rest` > 0
                ORDER BY OPhieuBanGiaoTaiSan.SoPhieu ASC
                LIMIT 1
                ', $where
            );
            // echo '<pre>'; print_r($sql); die;
            $first        = $this->_db->fetchOne($sql);

            if($first)
            {
                $this->_object->getFieldByCode('PhieuBanGiao')->setRefIOID($first->IOID);
                $this->_object->getFieldByCode('PhieuBanGiao')->setValue($first->SoPhieu);
            }
        }
    }

    /**
     * Loc cac phieu ban giao da dong theo nhan vien va tai san
     * Uu tien loc theo nhan vien, neu khong co nhan vien thi khong cho chon phieu nao. Tai san co the co loc cung co
     * the khong can
     */
    public function PhieuBanGiao()
    {
        $maTaiSan     = (int)$this->_object->getFieldByCode('MaTaiSan')->intRefIOID;
        $maNhanVien   = (int)$this->_object->getFieldByCode('MaNhanVien')->intRefIOID;
        $where        = '';
        $where       .= $maNhanVien?sprintf(' AND ChiTiet.Ref_MaNhanVien = %1$d ', $maNhanVien):' AND 1=0 ';
        $where       .= $maTaiSan?sprintf(' AND ChiTiet.Ref_MaTaiSan = %1$d ', $maTaiSan):'';

        $this->_object->getFieldByCode('PhieuBanGiao')->arrFilters[] = sprintf('
            v.IFID_M182 in
            (
                SELECT Phieu.IFID_M182
                FROM OPhieuBanGiaoTaiSan AS Phieu
                INNER JOIN qsiforms AS IForm ON Phieu.IFID_M182 = IForm.IFID
                INNER JOIN OChiTietBanGiaoTaiSan AS ChiTiet ON Phieu.IFID_M182 = ChiTiet.IFID_M182
                WHERE IFNULL(IForm.Status, 0) = 2 %1$s
                GROUP BY Phieu.IFID_M182
            )', $where
        );
    }

    /**
     * Loc cac tai san ban giao theo phieu ban giao va ma nhan vien
     * Neu khong co ma nhan vien va phieu ban giao thi khong cho chon tai san
     */
    public function MaTaiSan()
    {
        $maNhanVien  = (int)$this->_object->getFieldByCode('MaNhanVien')->intRefIOID;
        $where       = '';
        $where      .= $maNhanVien?sprintf(' AND ChiTiet.Ref_MaNhanVien = %1$d ', $maNhanVien):' AND 1=0 ';
        $main        = $this->_db->fetchOne(sprintf('SELECT * FROM OPhieuThuHoiTaiSan WHERE IFID_M183 = %1$d', $this->_object->i_IFID));
        $nhaMay      = '';
        $ngay        = '';

        if($main)
        {
            $nhaMay  = $main->NhaMay;
            $ngay    = $main->Ngay;
        }

        $where  .= $nhaMay?sprintf(' AND ChiTiet.NhaMay = %1$s ', $this->_db->quote($nhaMay)):' AND 1=0 ';
        $where  .= $ngay?sprintf(' AND Phieu.Ngay <= "%1$s" ', $ngay):' AND 1=0 ';

        $this->_object->getFieldByCode('MaTaiSan')->arrFilters[] = sprintf('
            v.IOID in
            (
                SELECT ChiTiet.Ref_MaTaiSan
                FROM OPhieuBanGiaoTaiSan AS Phieu
                INNER JOIN qsiforms AS IForm ON Phieu.IFID_M182 = IForm.IFID
                INNER JOIN OChiTietBanGiaoTaiSan AS ChiTiet ON Phieu.IFID_M182 = ChiTiet.IFID_M182
                WHERE IFNULL(IForm.Status, 0) = 2 %1$s
                GROUP BY ChiTiet.Ref_MaTaiSan
            )', $where
        );
    }

}