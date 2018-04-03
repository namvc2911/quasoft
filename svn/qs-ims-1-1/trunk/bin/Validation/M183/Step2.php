<?php
class Qss_Bin_Validation_M183_Step2 extends Qss_Lib_WValidation
{
    /**
     *  onNext():
     * Khi chuyển bước kiểm tra xem có đủ số lượng thu hồi hay không?
     */
    public function onNext()
    {
        parent::init();

        $error = 0;

        $sql = sprintf('
            SELECT 
                ThuHoiHienTai.PhieuBanGiao
                , ThuHoiHienTai.TenNhanVien
                , ThuHoiHienTai.TenTaiSan
                , ThuHoiHienTai.SoLuong
                , ThuHoiHienTai.DonViTinh
                , (IF(IformBanGiao.Status = 2, OChiTietBanGiaoTaiSan.SoLuong, 0)
                - IFNULL(ThuHoiKhac.SoLuongDaThuHoi, 0)) AS SoLuongConTon               
                , (IF(IformBanGiao.Status = 2, OChiTietBanGiaoTaiSan.SoLuong, 0)
                - IFNULL(ThuHoiHienTai.SoLuong, 0) - IFNULL(ThuHoiKhac.SoLuongDaThuHoi, 0)) AS SoLuongConLai
            FROM (
                SELECT OChiTietThuHoiTaiSan.*
                FROM OPhieuThuHoiTaiSan 
                INNER JOIN OChiTietThuHoiTaiSan ON OPhieuThuHoiTaiSan.IFID_M183 = OChiTietThuHoiTaiSan.IFID_M183
                WHERE OPhieuThuHoiTaiSan.IFID_M183 = %1$d
            ) AS ThuHoiHienTai
            INNER JOIN OPhieuBanGiaoTaiSan ON ThuHoiHienTai.Ref_PhieuBanGiao = OPhieuBanGiaoTaiSan.IOID
            INNER JOIN qsiforms AS IformBanGiao ON OPhieuBanGiaoTaiSan.IFID_M182 = IformBanGiao.IFID
            INNER JOIN OChiTietBanGiaoTaiSan ON OPhieuBanGiaoTaiSan.IFID_M182 = OChiTietBanGiaoTaiSan.IFID_M182
                AND ThuHoiHienTai.Ref_MaTaiSan = OChiTietBanGiaoTaiSan.Ref_MaTaiSan
                AND ThuHoiHienTai.Ref_MaNhanVien = OChiTietBanGiaoTaiSan.Ref_MaNhanVien     
            LEFT JOIN (
                SELECT ChiTietKhac.*, Sum(IFNULL(ChiTietKhac.SoLuong, 0)) AS SoLuongDaThuHoi
                FROM OPhieuThuHoiTaiSan 
                INNER JOIN OChiTietThuHoiTaiSan ON OPhieuThuHoiTaiSan.IFID_M183 = OChiTietThuHoiTaiSan.IFID_M183
                INNER JOIN OChiTietThuHoiTaiSan AS ChiTietKhac ON 
                    ChiTietKhac.IFID_M183 != %1$d
                    AND OChiTietThuHoiTaiSan.Ref_PhieuBanGiao = ChiTietKhac.Ref_PhieuBanGiao
                    AND OChiTietThuHoiTaiSan.Ref_MaTaiSan = ChiTietKhac.Ref_MaTaiSan
                    AND OChiTietThuHoiTaiSan.Ref_MaNhanVien = ChiTietKhac.Ref_MaNhanVien  
                INNER JOIN OPhieuThuHoiTaiSan AS PhieuThuHoiKhac ON  ChiTietKhac.IFID_M183 = PhieuThuHoiKhac.IFID_M183
                INNER JOIN qsiforms ON PhieuThuHoiKhac.IFID_M183 = qsiforms.IFID
                WHERE OPhieuThuHoiTaiSan.IFID_M183 = %1$d AND qsiforms.Status = 2
                GROUP BY ChiTietKhac.Ref_PhieuBanGiao, ChiTietKhac.Ref_MaNhanVien, ChiTietKhac.Ref_MaTaiSan
            ) AS ThuHoiKhac ON ThuHoiHienTai.Ref_PhieuBanGiao = ThuHoiKhac.Ref_PhieuBanGiao
                    AND ThuHoiHienTai.Ref_MaTaiSan = ThuHoiKhac.Ref_MaTaiSan
                    AND ThuHoiHienTai.Ref_MaNhanVien = ThuHoiKhac.Ref_MaNhanVien  
        ', $this->_form->i_IFID);
        $dat   = $this->_db->fetchAll($sql);


        foreach($dat as $item)
        {
            if($item->SoLuongConLai < 0)
            {
                $error = 1;
                $this->setMessage("Số lượng {$item->TenTaiSan} thu hồi từ nhân viên {$item->TenNhanVien} thuộc phiếu bàn giao {$item->PhieuBanGiao} vượt quá số lượng tồn của nhân viên ({$item->SoLuongConTon} {$item->DonViTinh}).");
            }
        }

        if($error)
        {
            $this->setError();
        }
    }

    /**
     * next()
     * Khi chuyển bước tạo phiếu bàn giao rồi chuyển bước đóng ##&*#*(@!@
     */
    public function next()
    {
        parent::init();

        $handover = array();
        $hIdx     = 0;
        $user     = Qss_Register::get('userinfo');
        $model    = new Qss_Model_Import_Form('M182',false, false);
        $tempExists = array();
        $docNo      = '';

        $oldSql = sprintf('
            SELECT OPhieuBanGiaoTaiSan.*
            FROM OPhieuBanGiaoTaiSan
            INNER JOIN (
                SELECT *
                FROM  OPhieuThuHoiTaiSan
                WHERE IFID_M183 = %1$d
            ) AS PhieuThuHoi ON IFNULL(OPhieuBanGiaoTaiSan.Ref_PhieuThuHoi, 0) = PhieuThuHoi.IOID
            ORDER BY OPhieuBanGiaoTaiSan.Ngay Desc
            LIMIT 1
        ', $this->_form->i_IFID);

        $old = $this->_db->fetchOne($oldSql);

        $linesSql = sprintf('
                SELECT OChiTietThuHoiTaiSan.*
                FROM OPhieuThuHoiTaiSan            
                INNER JOIN OChiTietThuHoiTaiSan ON OPhieuThuHoiTaiSan.IFID_M183 = OChiTietThuHoiTaiSan.IFID_M183            
                WHERE OPhieuThuHoiTaiSan.IFID_M183 = %1$d
            ', $this->_form->i_IFID);

        $lines = $this->_db->fetchAll($linesSql);

        foreach ($lines as $line)
        {
            if(
                $line->Ref_MaNhanVienMoi != 0
                && (
                    $line->Ref_MaNhanVienMoi != $line->Ref_MaNhanVien
                    || $line->NhaMayBanGiao != $line->NhaMay
                )
            )
            {
                $key = @(int)$line->Ref_MaNhanVienMoi.'_'.@(int)$line->Ref_MaTaiSan;

                if(!isset($tempExists[$key]))
                {
                    $tempExists[$key] = $hIdx;

                    $handover['OPhieuBanGiaoTaiSan'][0]['DienGiai']              = "{$line->TenNhanVienMoi} nhận bàn giao của {$line->TenNhanVien}.";
                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['MaNhanVien']      = @(int)$line->Ref_MaNhanVienMoi;
                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['TenNhanVien']     = @(int)$line->Ref_TenNhanVienMoi;
                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['NhaMay']          = @(int)$line->Ref_NhaMayBanGiao;
                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['BoPhan']          = @(int)$line->Ref_BoPhanBanGiao;
                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['MaTaiSan']        = @(int)$line->Ref_MaTaiSan;
                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['TenTaiSan']       = @(int)$line->Ref_MaTaiSan;
                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['SoLuong']         = $line->SoLuong;
                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['MaNhanVienCu']    = @(int)$line->Ref_MaNhanVien;
                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['TenNhanVienCu']   = @(int)$line->Ref_TenNhanVien;

                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['DonViTinh']       = (int)$line->Ref_DonViTinh;
                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['Ngay']            = $this->_params->Ngay;
                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['DonGia']          = $line->DonGia;
                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['ThanhTien']       = $line->ThanhTien;
                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['PhanTramKhauHao'] = $line->PhanTramKhauHao;
                    $handover['OChiTietBanGiaoTaiSan'][$hIdx]['ThoiGianDaSuDung']= $line->ThoiGianDaSuDung;
                    $hIdx++;
                }
                else
                {
                    $handover['OChiTietBanGiaoTaiSan'][$tempExists[$key]]['SoLuong']  += $line->SoLuong;
                }


            }
        }


        if($old && $old->SoPhieu) // Da tao phieu ban giao truoc do, kiem tra lai
        {
            if(isset($handover['OChiTietBanGiaoTaiSan']) && count($handover['OChiTietBanGiaoTaiSan']))
            {
                $this->setMessage(" <a href=\"/user/form/edit?ifid={$this->_form->i_IFID}&deptid={$user->user_dept_id}\">Phiếu bàn giao lại tài sản đã được tạo trước đó.  Click để xem chi tiết phiếu {$old->SoPhieu} </a>");
            }
        }
        else // Chua tao phieu ban giao, tao phieu ban giao
        {
            // Neu co ban giao lai thi tao phieu ban giao
            if(isset($handover['OChiTietBanGiaoTaiSan']) && count($handover['OChiTietBanGiaoTaiSan']))
            {
                $asset    = new Qss_Model_Maintenance_Asset();

                if(count($handover['OChiTietBanGiaoTaiSan']) > 1)
                {
                    $handover['OPhieuBanGiaoTaiSan'][0]['DienGiai'] = $this->_params->DienGiai;
                }

                $docNo = $asset->getHandoverDocNo($this->_params->NhaMay);

                $handover['OPhieuBanGiaoTaiSan'][0]['NhaMay']      = @(int)$this->_params->Ref_NhaMay;
                $handover['OPhieuBanGiaoTaiSan'][0]['SoPhieu']     = $docNo;
                $handover['OPhieuBanGiaoTaiSan'][0]['Ngay']        = $this->_params->Ngay;
                $handover['OPhieuBanGiaoTaiSan'][0]['PhieuThuHoi'] = @(int)$this->_params->IOID;


                $model->setData($handover);
                $model->generateSQL();
                $error = $model->countFormError() + $model->countObjectError();



                if($error)
                {
                    // echo '<pre>'; print_r($model->getErrorRows()); die;
                    $this->setError();
                    $this->setMessage('Có '.$error.' dòng lỗi!');
                }

                if(!$this->isError())
                {
                    $import = $model->getIFIDs();
                    $ifid   = 0;

                    foreach ($import as $item)
                    {
                        $ifid = $item->oldIFID;
                    }

                    $form     = new Qss_Model_Form();
                    $form->initData($ifid, $user->user_dept_id);
                    $service2 = $this->services->Form->Request($form, 2, $user, '');

                    if ($service2->isError())
                    {
                        $this->setError();
                        $this->setMessage($service2->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }

                    if(!$this->isError())
                    {
                        $this->setMessage("<a href=\"/user/form/edit?ifid={$ifid}&deptid={$user->user_dept_id}\">Phiếu bàn giao tài sản {$docNo} đã được tạo thành công.  Click để xem chi tiết </a>");
                    }
                }
            }
        }


    }
}