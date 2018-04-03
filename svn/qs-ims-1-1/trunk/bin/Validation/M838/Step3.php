<?php

class Qss_Bin_Validation_M838_Step3 extends Qss_Lib_Warehouse_WValidation
{
    public function onNext()
    {
//        parent::init();
//        $mGeneral = new Qss_Model_Maintenance_GeneralPlans();
//        $detail   = $mGeneral->getDetailPlanByGeneralIFID($this->_params->IFID_M838);
//        $model    = new Qss_Model_Import_Form('M837',true);
//        $insert   = array();
//        $ex       = array();
//
//        // print_r($detail); die;
//
//        foreach($detail as $item)
//        {
//            if(!$this->isError())
//            {
//                if(!isset($ex[(int)$item->Ref_MaThietBi][(int)$item->Ref_BoPhan][(int)$item->Ref_LoaiBaoTri][(int)$item->Ref_ChuKy]))
//                {
//                    $ex[(int)$item->Ref_MaThietBi][(int)$item->Ref_BoPhan][(int)$item->Ref_LoaiBaoTri][(int)$item->Ref_ChuKy] = 1;
//                }
//                else
//                {
//                    $ex[(int)$item->Ref_MaThietBi][(int)$item->Ref_BoPhan][(int)$item->Ref_LoaiBaoTri][(int)$item->Ref_ChuKy] += 1;
//                }
//
//                $insert['OKeHoachBaoTri'][0]['LanBaoTri'] = $ex[(int)$item->Ref_MaThietBi][(int)$item->Ref_BoPhan][(int)$item->Ref_LoaiBaoTri][(int)$item->Ref_ChuKy];
//                $insert['OKeHoachBaoTri'][0]['ioid']      = $item->IOID;
//                $insert['OKeHoachBaoTri'][0]['ifid']      = $item->IFID_M837;
//
//                $service = $this->services->Form->Manual('M837',  $item->IFID_M837,  $insert, false);
//                if ($service->isError())
//                {
//                    $this->setError();
//                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//                }
//                // $model->setData($insert);
//            }
//        }
//        // $model->generateSQL();
    }
}