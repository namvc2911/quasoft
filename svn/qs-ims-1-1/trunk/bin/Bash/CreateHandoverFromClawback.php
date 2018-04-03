<?php
class Qss_Bin_Bash_CreateHandoverFromClawback extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $ifid        = $this->_params->IFID_M183;
        $mAsset      = new Qss_Model_Maintenance_Asset();
        $mCommon     = new Qss_Model_Extra_Extra();
        $detail      = $mAsset->getClawBackDetailByIFID($ifid);
        $insert      = array();
        $user        = Qss_Register::get('userinfo');
        $i           = 0;
        $mImport     = new Qss_Model_Import_Form('M182',true, false);
        $countByNew  = array();
        $handover    = $mCommon->getTableFetchOne('OPhieuBanGiaoTaiSan', array('IFNULL(Ref_PhieuThuHoi, 0)'=>$this->_params->IOID));
        $clawback    = $mCommon->getTableFetchOne('OPhieuThuHoiTaiSan', array('IFNULL(IFID_M183, 0)'=>$ifid));
        // Dieu kien loc o day giong luu cua nut tao phieu thu hoi
        if($handover)
        {
            $this->setError();
            $this->setMessage('Phiếu bàn giao lại tài sản đã được tạo trước đó <a href="/user/form/edit?ifid='.$handover->IFID_M182.'&deptid='.$user->user_dept_id .'"> (Xem phiếu bàn giao '.$handover->SoPhieu.') </a>');
            return;
        }

        foreach($detail as $item)
        {
            if($item->Ref_MaNhanVienMoi)
            {
                $key = $item->Ref_MaNhanVienMoi .'_'. $item->Ref_MaTaiSan;

                if(!isset($countByNew[$key]))
                {
                    $countByNew[$key]['qty'] = 0;
                }

                $countByNew[$key]['qty']++;
            }


            if(!$item->Ref_MaNhanVienMoi)
            {
                $this->setError();
                $this->setMessage('Phiếu thu hồi chưa điền đầy đủ nhân viên bàn giao mới.');
            }

            if($item->Ref_MaNhanVienMoi == $item->Ref_MaNhanVien
                && $item->NhaMay == $item->NhaMayBanGiao
            )
            {
                $this->setError();
                $this->setMessage('Nhân viên bị thu hồi và nhân viên bàn giao không được trùng nhau.');
            }
        }

        foreach ($countByNew as $item)
        {
            if($item['qty'] > 1)
            {
                $this->setError();
                $this->setMessage('Không hỗ trỡ bàn giao nhiều người cho một người cùng một tài sản');
            }
        }

        if(!$this->isError())
        {
            $object     = new Qss_Model_Object(); $object->v_fInit('OPhieuBanGiaoTaiSan', 'M182');
            $document   = new Qss_Model_Extra_Document($object);
            $document->setLenth(5);
            $document->setDocField('SoPhieu');

            if($clawback && $clawback->NhaMay)
            {
                $document->setPrefix($clawback->NhaMay.'BG.');
            }
            else
            {
                $document->setPrefix($clawback->NhaMay.'BG.');
            }

            $insert['OPhieuBanGiaoTaiSan'][0]['NhaMay']      = $clawback->NhaMay;
            $insert['OPhieuBanGiaoTaiSan'][0]['SoPhieu']     = $document->getDocumentNo();
            $insert['OPhieuBanGiaoTaiSan'][0]['Ngay']        = date('Y-m-d');
            $insert['OPhieuBanGiaoTaiSan'][0]['PhieuThuHoi'] = (int)$this->_params->IOID;

            foreach($detail as $item)
            {
                if((int)$item->Hong == 0)
                {
                    $insert['OChiTietBanGiaoTaiSan'][$i]['Ngay']            = date('Y-m-d');
                    $insert['OChiTietBanGiaoTaiSan'][$i]['MaNhanVien']      = (int)$item->Ref_MaNhanVienMoi;
                    $insert['OChiTietBanGiaoTaiSan'][$i]['TenNhanVien']     = (int)$item->Ref_MaNhanVienMoi;
                    $insert['OChiTietBanGiaoTaiSan'][$i]['NhaMay']          = $item->NhaMayBanGiao;
                    $insert['OChiTietBanGiaoTaiSan'][$i]['BoPhan']          = $item->BoPhanBanGiao;
                    $insert['OChiTietBanGiaoTaiSan'][$i]['MaTaiSan']        = (int)$item->Ref_MaTaiSan;
                    $insert['OChiTietBanGiaoTaiSan'][$i]['TenTaiSan']       = (int)$item->Ref_MaTaiSan;
                    $insert['OChiTietBanGiaoTaiSan'][$i]['DonViTinh']       = (int)$item->Ref_DonViTinh;
                    $insert['OChiTietBanGiaoTaiSan'][$i]['SoLuong']         = (int)$item->SoLuong;
                    $insert['OChiTietBanGiaoTaiSan'][$i]['DonGia']          =  $item->DonGia;
                    $insert['OChiTietBanGiaoTaiSan'][$i]['ThanhTien']       =  $item->ThanhTien;
                    $insert['OChiTietBanGiaoTaiSan'][$i]['PhanTramKhauHao'] = $item->PhanTramKhauHao;
                    $insert['OChiTietBanGiaoTaiSan'][$i]['ThoiGianDaSuDung']= $item->ThoiGianDaSuDung;
                    $i++;
                }
            }

            if(!isset($insert['OChiTietBanGiaoTaiSan']) || !count($insert['OChiTietBanGiaoTaiSan']))
            {
                $this->setError();
                $this->setMessage('Không có tài sản để bàn giao, kiểm tra lại tình trạng hỏng hóc của tài sản');
            }

            // echo '<pre>'; print_r($insert); die;

            if(!$this->isError())
            {
                $mImport->setData($insert);
                $mImport->generateSQL();
                $error = $mImport->countFormError() + $mImport->countObjectError();

                // echo '<pre>'; print_r($mImport->getImportRows()); die;

                if($error)
                {
                    $this->setError();
                    $this->setMessage('Có '.$error.' dòng lỗi!');
                }
            }

            if(!$this->isError())
            {
                $import = $mImport->getIFIDs();
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
                    Qss_Service_Abstract::$_redirect = '/user/form/edit?ifid='.$ifid.'&deptid='.$user->user_dept_id;
                }
            }
        }
    }
}