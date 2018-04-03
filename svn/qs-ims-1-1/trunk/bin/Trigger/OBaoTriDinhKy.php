<?php
class Qss_Bin_Trigger_OBaoTriDinhKy extends Qss_Lib_Trigger
{
    /**
     * onInserted: Thêm bản ghi quy trình mặc định theo loại bảo trì
     * @param Qss_Model_Object $object
     */
    public function onInserted(Qss_Model_Object $object)
    {
        parent::init();
//        if(Qss_Lib_System::formActive('M724') && Qss_Lib_System::objectInForm('M724', 'OChuKyBaoTri'))
//        {
//            $common     = new Qss_Model_Extra_Extra();
//            $refMType   = $object->getFieldByCode('LoaiBaoTri')->intRefIOID;
//            $loaiBaoTri = $common->getTableFetchOne('OPhanLoaiBaoTri', array('IOID'=>$refMType));
//            $ky         = $common->getTableFetchOne('OKy', array('IOID'=>@(int)$loaiBaoTri->Ref_KyBaoDuong));
//            $startDate   = $this->_params->NgayBatDau;
//            $cSDate    = date_create($startDate);
//	        $day       = (int)$cSDate->format('d');
//	        $wday      = (int)$cSDate->format('w');
//	        $month     = (int)$cSDate->format('m');
//            $insert     = array();
//
//            if($loaiBaoTri && ($loaiBaoTri->ChiSo || $ky))
//            {
//                $insert['OChuKyBaoTri'][0]['CanCu']  = $loaiBaoTri->CanCu;
//
//                if($ky && $ky->MaKy)
//                {
//                    $insert['OChuKyBaoTri'][0]['KyBaoDuong'] = $loaiBaoTri->KyBaoDuong;
//                }
//
//                $insert['OChuKyBaoTri'][0]['LapLai'] = $loaiBaoTri->LapLai;
//
//                if($ky && $loaiBaoTri->CanCu == Qss_Lib_Extra_Const::CAUSE_PERIOD || $loaiBaoTri->CanCu == Qss_Lib_Extra_Const::CAUSE_BOTH )
//                {
//                    if($ky->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY)
//                    {
//                        $thu                              = $common->getTableFetchAll('OThu',array('GiaTri'=>1));
//                        $insert['OChuKyBaoTri'][0]['Thu'] = $loaiBaoTri->DieuChinhTheoPBT?((string)$wday):$thu[0]->Thu;//$loaiBaoTri->Thu;
//                    }
//
//                    if($ky->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY || $ky->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY)
//                    {
//                        $ngay                               = $common->getTableFetchAll('ONgay');
//                        $insert['OChuKyBaoTri'][0]['Ngay']  = $loaiBaoTri->DieuChinhTheoPBT?((string)$day):$ngay[30]->Ngay;//$loaiBaoTri->Ngay;
//                    }
//
//                    if($ky->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY)
//                    {
//                        //$thang                              = $common->getTableFetchAll('OThang');
//                        $insert['OChuKyBaoTri'][0]['Thang'] = $loaiBaoTri->DieuChinhTheoPBT?((string)$month):"1";//$loaiBaoTri->Thang;
//                    }
//                }
//
//                if($loaiBaoTri->CanCu ==  Qss_Lib_Extra_Const::CAUSE_PARAM || $loaiBaoTri->CanCu == Qss_Lib_Extra_Const::CAUSE_BOTH )
//                {
//                    $insert['OChuKyBaoTri'][0]['ChiSo']  = $loaiBaoTri->ChiSo;
//                    $insert['OChuKyBaoTri'][0]['GiaTri'] = $loaiBaoTri->GiaTri;
//                }
//
//                $insert['OChuKyBaoTri'][0]['DieuChinhTheoPBT'] = $loaiBaoTri->DieuChinhTheoPBT;
//
//                $services =  $this->services->Form->Manual('M724', $this->_params->IFID_M724, $insert, false);
//
//                if($services->isError())
//                {
//                    $this->setError();
//                    $this->setMessage($services->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//                }
//            }
//        }
    }
 	public function onUpdated(Qss_Model_Object $object)
    {
        parent::init();
//        $common     = new Qss_Model_Extra_Extra();
//        $data =  $common->getTableFetchAll('OChuKyBaoTri', array('IFID_M724'=>$this->_params->IFID_M724));
//        foreach($data as $item)
//        {
//	        $insert     = array();
//	        $insert['OChuKyBaoTri'][0]['ioid']  = $item->IOID;
//	        $insert['OChuKyBaoTri'][0]['ChuKy']  = '';
//	        $this->services->Form->Manual('M724', $this->_params->IFID_M724, $insert, false);
//        }
    }
}
?>