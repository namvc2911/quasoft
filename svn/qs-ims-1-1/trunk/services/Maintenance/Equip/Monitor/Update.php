<?php
class Qss_Service_Maintenance_Equip_Monitor_Update extends Qss_Service_Abstract
{
    public function __doExecute($params)
    {
        $insert    = array();
        $i         = 0;
        $mImpport  = new Qss_Model_Import_Form('M765',true);


        // Kiểm tra với điệu kiện điểm đo yêu cầu bắt buộc
        if(!isset($params['DiemDo']) || !count($params['DiemDo']))
        {
            $this->setMessage('Điểm đo yêu cầu bắt buộc!');
            $this->setError();
        }

        if(isset($params['DiemDo']) && count($params['DiemDo']))
        {
            foreach($params['DiemDo'] as $item)
            {
                if(!$item)
                {
                    $this->setMessage('Điểm đo yêu cầu bắt buộc!');
                    $this->setError();
                }
            }
        }

        if(!$this->isError())
        {
            foreach($params['MaTB'] as $item)
            {
                // IFID
                if(isset($params['ifid'][$i]) && $params['ifid'][$i])
                {
                    $insert['ONhatTrinhThietBi'][0]['ifid'] = (int)$params['ifid'][$i];
                }

                // IOID
                if(isset($params['ioid'][$i]) && $params['ioid'][$i])
                {
                    $insert['ONhatTrinhThietBi'][0]['ioid'] = (int)$params['ioid'][$i];
                }

                // DeptID
                if(isset($params['DeptID'][$i]) && $params['DeptID'][$i])
                {
                    $insert['ONhatTrinhThietBi'][0]['DeptID'] = (int)$params['DeptID'][$i];
                }

                // Đạt
                if(isset($params['Dat'][$i]) && is_numeric($params['Dat'][$i]))
                {
                    $insert['ONhatTrinhThietBi'][0]['Dat'] = (int)$params['Dat'][$i];
                }

                // Tình trạng
                if(isset($params['TinhTrang'][$i]) && is_numeric($params['TinhTrang'][$i]))
                {
                    $insert['ONhatTrinhThietBi'][0]['TinhTrang'] = (int)$params['TinhTrang'][$i];
                }

                $insert['ONhatTrinhThietBi'][0]['DiemDo']       = (int)((isset($params['DiemDo'][$i]) && $params['DiemDo'][$i])?$params['DiemDo'][$i]:0); // Ref
                $insert['ONhatTrinhThietBi'][0]['MaTB']         = (int)((isset($params['MaTB'][$i]) && $params['MaTB'][$i])?$params['MaTB'][$i]:0); // Ref
                $insert['ONhatTrinhThietBi'][0]['BoPhan']       = (int)((isset($params['BoPhan'][$i]) && $params['BoPhan'][$i])?$params['BoPhan'][$i]:0); // Ref
                $insert['ONhatTrinhThietBi'][0]['ChiSo']        = (int)((isset($params['ChiSo'][$i]) && $params['ChiSo'][$i])?$params['ChiSo'][$i]:0); // Ref
                $insert['ONhatTrinhThietBi'][0]['DonViTinh']    = (int)((isset($params['DonViTinh'][$i]) && $params['DonViTinh'][$i])?$params['DonViTinh'][$i]:0); // Ref
                $insert['ONhatTrinhThietBi'][0]['NguoiKiemTra'] = (int)((isset($params['NguoiKiemTra'][$i]) && $params['NguoiKiemTra'][$i])?$params['NguoiKiemTra'][$i]:0); // Ref
                $insert['ONhatTrinhThietBi'][0]['Ngay']         = (isset($params['Ngay'][$i]) && $params['Ngay'][$i])?Qss_Lib_Date::displaytomysql($params['Ngay'][$i]):'';
                $insert['ONhatTrinhThietBi'][0]['ThoiGian']     = (isset($params['ThoiGian'][$i]) && $params['ThoiGian'][$i])?$params['ThoiGian'][$i]:0;
                $insert['ONhatTrinhThietBi'][0]['Ca']           = (int)((isset($params['Ca'][$i]) && $params['Ca'][$i])?$params['Ca'][$i]:0); // Ref
                $insert['ONhatTrinhThietBi'][0]['SoHoatDong']   = (isset($params['SoHoatDong'][$i]) && $params['SoHoatDong'][$i])?$params['SoHoatDong'][$i]:0;
                $insert['ONhatTrinhThietBi'][0]['NguoiVanHanh'] = (int)((isset($params['NguoiVanHanh'][$i]) && $params['NguoiVanHanh'][$i])?$params['NguoiVanHanh'][$i]:0); // Ref
                $i++;

                $mImpport->setData($insert);
            }

            /** @todo: Cần báo lỗi khi chạy xong import */
            $mImpport->generateSQL();
        }
    }
}