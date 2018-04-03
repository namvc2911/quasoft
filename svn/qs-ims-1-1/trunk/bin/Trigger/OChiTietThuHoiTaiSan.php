<?php
class Qss_Bin_Trigger_OChiTietThuHoiTaiSan extends Qss_Lib_Trigger
{
    /**
     * onInsert
     * Kiểm tra số lượng thu hồi có đúng không
     */
    public function onInsert(Qss_Model_Object $object)
    {
        parent::init();
        $this->checkQty($object);
    }

    /**
     * onUpdate
     * Kiểm tra số lượng thu hồi có đúng không
     */
    public function onUpdate($object)
    {
        parent::init();
        $this->checkQty($object);
    }

    private function checkQty($object)
    {
        $phieuBanGiao = $object->getFieldByCode('PhieuBanGiao')->getRefIOID();
        $maNhanVien   = $object->getFieldByCode('MaNhanVien')->getRefIOID();
        $maTaiSan     = $object->getFieldByCode('MaTaiSan')->getRefIOID();
        $soLuong      = $object->getFieldByCode('SoLuong')->getValue();

        $sql = sprintf('
            SELECT 
                IF( IFNULL(iFormThuHoi.Status, 0) = 2
                    , (IFNULL(BanGiao.SoLuong, 0) - SUM(IFNULL(OChiTietThuHoiTaiSan.SoLuong, 0)))
                    ,  IFNULL(BanGiao.SoLuong, 0)) AS SoLuongConLai
            FROM 
            (
                SELECT OChiTietBanGiaoTaiSan.*, OPhieuBanGiaoTaiSan.IOID AS RefBanGiao
                FROM OPhieuBanGiaoTaiSan
                INNER JOIN qsiforms ON OPhieuBanGiaoTaiSan.IFID_M182 = qsiforms.IFID
                INNER JOIN OChiTietBanGiaoTaiSan ON OPhieuBanGiaoTaiSan.IFID_M182 = OChiTietBanGiaoTaiSan.IFID_M182              
                WHERE OPhieuBanGiaoTaiSan.IOID = %1$d
                    AND OChiTietBanGiaoTaiSan.Ref_MaNhanVien = %2$d
                    AND OChiTietBanGiaoTaiSan.Ref_MaTaiSan = %3$d
                    AND qsiforms.Status = 2
            ) AS BanGiao
            LEFT JOIN OChiTietThuHoiTaiSan ON BanGiao.RefBanGiao = OChiTietThuHoiTaiSan.Ref_PhieuBanGiao
                AND BanGiao.Ref_MaTaiSan = OChiTietThuHoiTaiSan.Ref_MaTaiSan
                AND BanGiao.Ref_MaNhanVien = OChiTietThuHoiTaiSan.Ref_MaNhanVien     
            LEFT JOIN OPhieuThuHoiTaiSan ON IFNULL(OChiTietThuHoiTaiSan.IFID_M183, 0) = OPhieuThuHoiTaiSan.IFID_M183 
            LEFT JOIN qsiforms AS iFormThuHoi ON OPhieuThuHoiTaiSan.IFID_M183 = iFormThuHoi.IFID          
            GROUP BY BanGiao.RefBanGiao
        ', $phieuBanGiao, $maNhanVien, $maTaiSan);
        $dat = $this->_db->fetchOne($sql);
        $rest = $dat?$dat->SoLuongConLai:0;

        if($soLuong > $rest)
        {
            $this->setError();
            $this->setMessage('Số lượng thu hồi lớn hơn số lượng nhân viên còn tồn.');
        }
    }
}