<?php
class Qss_Service_Maintenance_WorkOrder_Material_SaveByTask extends Qss_Lib_Service
{

    public function __doExecute ($params)
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $ifid     = ($params['ifid'])?$params['ifid']:0;
        $task     = $mCommon->getTableFetchOne('OCongViecBTPBT', array('IFID_M759'=>$params['ifid']));
        $material = $mCommon->getTableFetchOne('OSanPham', array('IFID_M113'=>$params['OVatTuPBT_MaVatTu']));
        $order    = $mCommon->getTableFetchOne('OPhieuBaoTri', array('IFID_M759'=>$params['ifid']));
        $insert   = array();
        $i        = 0;
        $mImport  = new Qss_Model_Import_Form('M759', false, false);


        $insert['OPhieuBaoTri'][0]['SoPhieu']      = (string)$order->SoPhieu;
        $insert['OVatTuPBT'][0]['HinhThuc']        = 0;
        $insert['OVatTuPBT'][0]['Ngay']            = date('Y-m-d');
        $insert['OVatTuPBT'][0]['ViTri']           = $task?(int)$task->Ref_ViTri:0;
        $insert['OVatTuPBT'][0]['BoPhan']          = $task?(int)$task->Ref_ViTri:0;
        $insert['OVatTuPBT'][0]['MaVatTu']         = $material?$material->MaSanPham:'';
        $insert['OVatTuPBT'][0]['TenVatTu']        = $material?$material->TenSanPham:'';
        $insert['OVatTuPBT'][0]['CongViec']        = $task?(int)$task->IOID:0;
        $insert['OVatTuPBT'][0]['DonViTinh']       = (int)$params['OVatTuPBT_DonViTinh'];
        $insert['OVatTuPBT'][0]['SoLuong']         = $params['OVatTuPBT_SoLuong'];
        $insert['OVatTuPBT'][0]['SoLuongDuKien']   = $params['OVatTuPBT_SoLuong'];


        $mImport->setData($insert);
        $mImport->generateSQL();

        $errorForm   = (int)$mImport->countFormError();
        $errorObject = (int)$mImport->countObjectError();
        $error       = $errorForm + $errorObject;

        if($error > 0)
        {
            $this->setError();
            $this->setMessage('Có '.$error.' dòng lỗi!');
        }


        if(!$this->isError() && (!isset($params['back']) || !$params['back']))
        {
            $importedRow = $mImport->getImportRows();
            $ioid        = 0;

            foreach ($importedRow as $objKey=>$itemArr)
            {
                if($objKey == 'OVatTuPBT' && count($itemArr))
                {
                    foreach ($itemArr as $item)
                    {
                        $ioid = $item->ExistsIOID;
                        break;
                    }
                }
            }

            // echo '<pre>'; print_r($ioid); die;

            Qss_Service_Abstract::$_redirect = '/mobile/m759/mytasks/addmaterials/index?fid=M759&ifid='.$ifid.'&deptid='.$params['deptid'].'&ioid='.$ioid;
        }
    }
}
?>