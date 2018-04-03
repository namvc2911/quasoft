<?php
class Qss_Service_Maintenance_Asset_Clawback_Save extends Qss_Service_Abstract
{
    public function __doExecute($params)
    {
        $this->validateParams($params);
        $this->updateHandover($params);
    }

    // Dieu kien loc o day giong nut tao ban giao
    private function validateParams($params)
    {
        $i                 = 0;
        $countByNew        = array();
        $manyPersonOneItem = false;

        if(!isset($params['taiSan']) || !count($params['taiSan']))
        {
            $this->setError();
            $this->setMessage('Cần chọn ít nhất một tài sản để tạo thu hồi.');
            return;
        }

        foreach($params['taiSan'] as $item)
        {
            if((int)$params['nhanVienMoi'][$i])
            {
                $key = (int)$params['nhanVienMoi'][$i] .'_'. (int)$params['taiSan'][$i] .'_'. (int)$params['phieuBanGiao'][$i];

                if(!isset($countByNew[$key]))
                {
                    $countByNew[$key]['qty'] = 0;
                }

                $countByNew[$key]['qty']++;
            }

            if(!is_numeric($params['soLuong'][$i]) || $params['soLuong'][$i] == 0)
            {
                $this->setError();
                $this->setMessage('Dòng thu hồi '.($i+1).' chưa nhập số lượng thu hồi.');
            }

            if((int)$params['loai'][$i] == 1 && !(int)$params['nhanVienMoi'][$i])
            {
                $this->setError();
                $this->setMessage('Dòng thu hồi '.($i+1).' chưa điền nhân viên bàn giao.');
            }

//            if((int)$params['nhanVienMoi'][$i] == (int)$params['nhanVien'][$i]
//             && $params['nhaMay'][$i] == $params['nhaMayHienTai'][$i])
//            {
//                $this->setError();
//                $this->setMessage('Dòng thu hồi '.($i+1).' có người thu hồi và người bàn giao trong cùng một nhà máy trùng nhau.');
//            }

            if($params['soLuong'] > $params['soLuongConLai'])
            {
                $this->setError();
                $this->setMessage('Dòng thu hồi '.($i+1).' có số lượng thu hồi lớn hơn số lượng tài sản nhân viên còn tồn.');
            }

            $i++;
        }

        foreach ($countByNew as $item)
        {
            if($item['qty'] > 1)
            {
                $manyPersonOneItem = true;
            }
        }

        if($manyPersonOneItem)
        {
            $this->setError();
            $this->setMessage('Không hỗ trỡ bàn giao nhiều người cho một người cùng một tài sản.');
        }
    }

    private function updateHandover($params)
    {
        if(!$this->isError())
        {
            $insert    = array();
            $i         = 0;
            $newEmpArr = array();
            $newEmpArrTemp= array();
            $model     = new Qss_Model_Import_Form('M183',false, false);
            $mEmp      = new Qss_Model_Maintenance_Employee();
            $mTable    = Qss_Model_Db::Table('OChiTietThuHoiTaiSan');
            $mTable->where(sprintf(' IFID_M183 = %1$d ', $params['ifid']));
            $tempThuHoi = array();

            foreach ($mTable->fetchAll() as $item)
            {
                $key = (int)$item->Ref_PhieuBanGiao.'_'.(int)$item->Ref_MaTaiSan.'_'.(int)$item->Ref_MaNhanVien;
                $tempThuHoi[$key] = $item->SoLuong;
            }

            $insert['OPhieuThuHoiTaiSan'][0]['SoPhieu'] = $params['docno'];

            foreach($params['nhanVienMoi'] as $newEmp)
            {
                $newEmp             = (int)$newEmp;
                if($newEmp)
                {
                    $newEmpArrTemp[$newEmp] = $newEmp;
                }
            }

            $newEmpObjs = $mEmp->getEmployees($newEmpArrTemp);

            foreach ($newEmpObjs as $newEmp)
            {
                $newEmpArr[$newEmp->IOID] = $newEmp;
            }


            foreach($params['soLuong'] as $qty)
            {
                $key            = (int)$params['phieuBanGiao'][$i].'_'.(int)$params['taiSan'][$i].'_'.(int)$params['nhanVien'][$i];
                $tempSoLuong    = $params['soLuong'][$i];
                $tempOldSoLuong = isset($tempThuHoi[$key])?$tempThuHoi[$key]:0;
                $tempSoLuong    = $tempSoLuong + $tempOldSoLuong;


                $insert['OChiTietThuHoiTaiSan'][$i]['PhieuBanGiao']     = (int)$params['phieuBanGiao'][$i];
                $insert['OChiTietThuHoiTaiSan'][$i]['MaTaiSan']         = (int)$params['taiSan'][$i];
                $insert['OChiTietThuHoiTaiSan'][$i]['TenTaiSan']        = (int)$params['taiSan'][$i];
                $insert['OChiTietThuHoiTaiSan'][$i]['MaNhanVien']       = (int)$params['nhanVien'][$i];
                $insert['OChiTietThuHoiTaiSan'][$i]['TenNhanVien']      = (int)$params['nhanVien'][$i];
                $insert['OChiTietThuHoiTaiSan'][$i]['NhaMay']           = (int)$params['nhaMay'][$i];
                $insert['OChiTietThuHoiTaiSan'][$i]['BoPhan']           = (int)$params['boPhan'][$i];
                $insert['OChiTietThuHoiTaiSan'][$i]['DonViTinh']        = (int)$params['taiSan'][$i];
                $insert['OChiTietThuHoiTaiSan'][$i]['SoLuong']          = $tempSoLuong;
                $insert['OChiTietThuHoiTaiSan'][$i]['DonGia']           = $params['donGia'][$i] * 1000;
                $insert['OChiTietThuHoiTaiSan'][$i]['ThanhTien']        = $params['soLuong'][$i] * ($params['donGia'][$i]*1000);
                $insert['OChiTietThuHoiTaiSan'][$i]['MaNhanVienMoi']    = (int)$params['nhanVienMoi'][$i];
                $insert['OChiTietThuHoiTaiSan'][$i]['TenNhanVienMoi']   = (int)$params['nhanVienMoi'][$i];

                $insert['OChiTietThuHoiTaiSan'][$i]['NhaMayBanGiao']    = @(int)$newEmpArr[(int)$params['nhanVienMoi'][$i]]->Ref_MaPhongBan;
                $insert['OChiTietThuHoiTaiSan'][$i]['BoPhanBanGiao']    = @(int)$newEmpArr[(int)$params['nhanVienMoi'][$i]]->Ref_MaBoPhan;

                $insert['OChiTietThuHoiTaiSan'][$i]['PhanTramKhauHao']  = $params['phanTramKhauHao'][$i];
                $insert['OChiTietThuHoiTaiSan'][$i]['ThoiGianDaSuDung'] = $params['thoiGianDaSuDung'][$i];
                $insert['OChiTietThuHoiTaiSan'][$i]['Loai']             = (int)$params['loai'][$i];
                $insert['OChiTietThuHoiTaiSan'][$i]['Hong']             = (int)$params['hong'][$i];
                //$insert['OChiTietThuHoiTaiSan'][$i]['ifid']            = $params['ifid'];
                $i++;
            }


//            $service = $this->services->Form->Manual('M183',  $params['ifid'],  $insert, false);
//            if ($service->isError())
//            {
//                $this->setError();
//                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//            }


            $model->setData($insert);
            $model->generateSQL();
            $error = $model->countFormError() + $model->countObjectError();


            if($error)
            {
                // echo '<pre>'; print_r($model->getImportRows()); die;
                $this->setError();
                $this->setMessage('Có '.$error.' dòng lỗi!');
            }
        }
    }


}