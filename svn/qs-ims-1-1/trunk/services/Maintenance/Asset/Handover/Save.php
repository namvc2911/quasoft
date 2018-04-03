<?php
class Qss_Service_Maintenance_Asset_Handover_Save extends Qss_Service_Abstract
{
    public function __doExecute($params)
    {
        $view = (isset($params['change_view']) && $params['change_view'])?$params['change_view']:1;

        if($view == 2) // Ban giao theo người
        {
            $this->validateHandoverByPerson($params);
        }
        else // Bàn giao theo tài sản
        {
            $this->validateHandoverByAsset($params);
        }

        $this->updateHandover($params);
    }

    private function updateHandover($params)
    {
        if(!$this->isError())
        {
            $insert = array();
            $i      = 0;
            $model  = new Qss_Model_Import_Form('M182',false, false);

            $insert['OPhieuBanGiaoTaiSan'][0]['SoPhieu'] = $params['docno'];

            foreach($params['soLuong'] as $qty)
            {
                $insert['OChiTietBanGiaoTaiSan'][$i]['MaTaiSan']        = (int)$params['refTaiSan'][$i];
                $insert['OChiTietBanGiaoTaiSan'][$i]['TenTaiSan']       = (int)$params['refTaiSan'][$i];
                $insert['OChiTietBanGiaoTaiSan'][$i]['MaNhanVien']      = (int)$params['refNhanVien'][$i];
                $insert['OChiTietBanGiaoTaiSan'][$i]['TenNhanVien']     = (int)$params['refNhanVien'][$i];
                $insert['OChiTietBanGiaoTaiSan'][$i]['NhaMay']          = (int)$params['refNhaMay'][$i];
                $insert['OChiTietBanGiaoTaiSan'][$i]['BoPhan']          = (int)$params['refBoPhan'][$i];
                $insert['OChiTietBanGiaoTaiSan'][$i]['DonViTinh']       = (int)$params['refTaiSan'][$i];
                $insert['OChiTietBanGiaoTaiSan'][$i]['SoLuong']         = $params['soLuong'][$i];
                $insert['OChiTietBanGiaoTaiSan'][$i]['DonGia']          = $params['donGia'][$i] * 1000;
                $insert['OChiTietBanGiaoTaiSan'][$i]['ThanhTien']       = $params['soLuong'][$i] * ($params['donGia'][$i] * 1000);
                //$insert['OChiTietBanGiaoTaiSan'][$i]['PhanTramKhauHao'] = $params['phanTramKhauHao'][$i];
                $insert['OChiTietBanGiaoTaiSan'][$i]['ThoiGianDaSuDung']= $params['thoiGianSuDung'][$i];
                //$insert['OChiTietBanGiaoTaiSan'][$i]['ifid']            = $params['ifid'];
                $i++;
            }

            $model->setData($insert);
            $model->generateSQL();
            $error = $model->countFormError() + $model->countObjectError();

            if($error)
            {
                $this->setError();
                $this->setMessage('Có '.$error.' dòng lỗi!');
            }
        }
    }

    private function validateHandoverByPerson($params)
    {
        $employee  = 0;
        $assets    = array();
        $qty       = array();

        if((isset($params['m182HandOver_opmi_employee']) && $params['m182HandOver_opmi_employee'])) // Nhân viên
        {
            $employee = $params['m182HandOver_opmi_employee'];
        }

        if((isset($params['refTaiSan']) && count($params['refTaiSan']))) // Tai san
        {
            $assets = $params['refTaiSan'];
        }

        if((isset($params['soLuong']) && count($params['soLuong']))) // Số lượng
        {
            $qty = $params['soLuong'];
        }

        if(!count($assets))
        {
            $this->setError();
            $this->setMessage('Tài sản yêu cầu bắt buộc');
        }

        if(!$employee)
        {
            $this->setError();
            $this->setMessage('Nhân viên yêu cầu bắt buộc');
        }

        foreach($qty as $num)
        {
            if(!is_numeric($num) || $num == 0)
            {
                $this->setError();
                $this->setMessage('Số lượng bàn giao phải lớn hơn 0');
            }
        }
    }

    private function validateHandoverByAsset($params)
    {
        $asset     = 0;
        $employees = array();
        $qty       = array();

        if((isset($params['m182HandOver_oimp_asset']) && $params['m182HandOver_oimp_asset'])) // Tai san
        {
            $asset = $params['m182HandOver_oimp_asset'];
        }

        if((isset($params['refNhanVien']) && count($params['refNhanVien']))) // Nhan vien
        {
            $employees = $params['refNhanVien'];
        }

        if((isset($params['soLuong']) && count($params['soLuong']))) // Số lượng
        {
            $qty = $params['soLuong'];
        }

        if(!$asset)
        {
            $this->setError();
            $this->setMessage('Tài sản yêu cầu bắt buộc');
        }

        if(!count($employees))
        {
            $this->setError();
            $this->setMessage('Nhân viên yêu cầu bắt buộc');
        }

        foreach($qty as $num)
        {
            if(!is_numeric($num) || $num == 0)
            {
                $this->setError();
                $this->setMessage('Số lượng bàn giao phải lớn hơn 0');
            }
        }
    }
}