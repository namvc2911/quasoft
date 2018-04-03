<?php
class Qss_Service_Button_M759_Checklist_Save extends Qss_Service_Abstract
{
    private function _saveTasks($insert, $ifid)
    {
        $error = 0;

        if(count($insert)) {
            $model = new Qss_Model_Import_Form('M759',false, false);
            $model->setData($insert);
            $model->generateSQL();
            $error = $model->countFormError() + $model->countObjectError();
        }
        else {
            $error = 1;
        }

        if($error > 0) {

            $this->setError();
            $this->setMessage($this->_translate(2));
        }
    }

    public function __doExecute($params)
    {

        $ioidPhieuBaoTri = (isset($params['ioidPhieuBaoTri']) && count($params['ioidPhieuBaoTri']))?$params['ioidPhieuBaoTri']:array();
        $ioidKeHoach     = (isset($params['ioidKeHoach']) && count($params['ioidKeHoach']))?$params['ioidKeHoach']:array();
        $ifid            = (isset($params['ifid']) && $params['ifid'])?$params['ifid']:0;
        $insert          = array();
        $i               = 0;
        $j               = 0;
        $arrIOIDCongViec = array();

        if(count($ioidKeHoach)) { // Chen tu ke hoach
            $mCongViecKeHoach = Qss_Model_Db::Table('OCongViecBT');
            $mCongViecKeHoach->where(sprintf('IOID IN (%1$s)', implode(',', $ioidKeHoach)));
            $oCongViecKeHoach = $mCongViecKeHoach->fetchAll();

            foreach ($oCongViecKeHoach as $item) {
                $arrIOIDCongViec[] = $item->IOID;
                $insert['OCongViecBTPBT'][$i]['MoTa']   = $item->MoTa;
                $insert['OCongViecBTPBT'][$i]['Ten']    = (int)$item->Ref_Ten;
                $insert['OCongViecBTPBT'][$i]['ViTri']  = (int)$item->Ref_ViTri;
                $insert['OCongViecBTPBT'][$i]['BoPhan'] = (int)$item->Ref_ViTri;
                $insert['OCongViecBTPBT'][$i]['ifid']   = $ifid;
                $i++;
            }

            $this->_saveTasks($insert, $ifid);

            if(count($arrIOIDCongViec)) {
                $insert        = array(); //reset
                $mVatTuKeHoach = Qss_Model_Db::Table('OVatTu');
                $mVatTuKeHoach->where(sprintf('IFNULL(Ref_CongViec, 0) IN (%1$s)', implode(',', $arrIOIDCongViec)));
                $oVatTuKeHoach = $mVatTuKeHoach->fetchAll();

                foreach ($oVatTuKeHoach as $item) {
                    $insert['OVatTuPBT'][$j]['HinhThuc']  = $item->HinhThuc;
                    $insert['OVatTuPBT'][$j]['CongViec']  = $item->CongViec;
                    $insert['OVatTuPBT'][$j]['ViTri']     = (int)$item->Ref_ViTri;
                    $insert['OVatTuPBT'][$j]['BoPhan']    = (int)$item->Ref_ViTri;
                    $insert['OVatTuPBT'][$j]['MaVatTu']   = (int)$item->Ref_MaVatTu;
                    $insert['OVatTuPBT'][$j]['TenVatTu']  = (int)$item->Ref_MaVatTu;
                    $insert['OVatTuPBT'][$j]['DonViTinh'] = (int)$item->Ref_DonViTinh;
                    $insert['OVatTuPBT'][$j]['SoLuong']   = $item->SoLuong;
                    $insert['OVatTuPBT'][$j]['ifid']      = $ifid;
                    $j++;
                }

                $this->_saveTasks($insert, $ifid);
            }
        }
        elseif(count($ioidPhieuBaoTri)) { // Chen tu phieu bao tri
            $mCongViecBaoTri = Qss_Model_Db::Table('OCongViecBTPBT');
            $mCongViecBaoTri->where(sprintf('IOID IN (%1$s)', implode(',', $ioidPhieuBaoTri)));
            $oCongViecBaoTri = $mCongViecBaoTri->fetchAll();

            $insert['OPhieuBaoTri'][0]['ifid'] = $ifid;

            foreach ($oCongViecBaoTri as $item)
            {
                $arrIOIDCongViec[] = $item->IOID;
                $insert['OCongViecBTPBT'][$i]['MoTa']   = $item->MoTa;
                $insert['OCongViecBTPBT'][$i]['Ten']    = (int)$item->Ref_Ten;
                $insert['OCongViecBTPBT'][$i]['ViTri']  = (int)$item->Ref_ViTri;
                $insert['OCongViecBTPBT'][$i]['BoPhan'] = (int)$item->Ref_ViTri;
                $insert['OCongViecBTPBT'][$i]['ifid']   = $ifid;
                $i++;
            }

            $this->_saveTasks($insert, $ifid);

            if(count($arrIOIDCongViec)) {
                $insert        = array(); //reset
                $mVatTuKeHoach = Qss_Model_Db::Table('OVatTuPBT');
                $mVatTuKeHoach->where(sprintf('IFNULL(Ref_CongViec, 0) IN (%1$s)', implode(',', $arrIOIDCongViec)));
                $oVatTuKeHoach = $mVatTuKeHoach->fetchAll();

                foreach ($oVatTuKeHoach as $item) {
                    $insert['OVatTuPBT'][$j]['HinhThuc']  = $item->HinhThuc;
                    $insert['OVatTuPBT'][$j]['CongViec']  = $item->CongViec;
                    $insert['OVatTuPBT'][$j]['ViTri']     = (int)$item->Ref_ViTri;
                    $insert['OVatTuPBT'][$j]['BoPhan']    = (int)$item->Ref_ViTri;
                    $insert['OVatTuPBT'][$j]['MaVatTu']   = (int)$item->Ref_MaVatTu;
                    $insert['OVatTuPBT'][$j]['TenVatTu']  = (int)$item->Ref_MaVatTu;
                    $insert['OVatTuPBT'][$j]['DonViTinh'] = (int)$item->Ref_DonViTinh;
                    $insert['OVatTuPBT'][$j]['SoLuong']   = $item->SoLuong;
                    $insert['OVatTuPBT'][$j]['ifid']      = $ifid;
                    $j++;
                }

                $this->_saveTasks($insert, $ifid);
            }
        }
        else { // khong co gi de chen, bao loi
            $this->setError();
            $this->setMessage($this->_translate(1));
            return; // Nếu có lỗi xảy ra ngừng lại
        }
    }
}