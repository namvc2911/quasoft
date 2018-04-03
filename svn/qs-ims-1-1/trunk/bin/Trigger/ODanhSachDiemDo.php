<?php
class Qss_Bin_Trigger_ODanhSachDiemDo extends Qss_Lib_Trigger
{
    public function onInserted(Qss_Model_Object $object)
    {
        parent::init();
        $import   = new Qss_Model_Import_Form('M765',false, false);

        $date    = date('Y-m-d');
        $weekday = Qss_Lib_Const::$WEEKDAY_BITWISE[DATE('w', strtotime($date))];
        $day     = DATE('d', strtotime($date));
        $month   = DATE('m', strtotime($date));
        $shiftActive = (Qss_Lib_System::formActive('M701') && Qss_Lib_System::fieldActive('ONhatTrinhThietBi', 'Ca'));
        $selectShift = $shiftActive?', Ca.IOID AS Ref_Ca':', 0 AS Ref_Ca';
        $joinShift = $shiftActive?'LEFT JOIN OCa AS Ca ON  IFNULL(DanhSach.TheoCa, 0) = 1':'';
        $joinDiaryShift = $shiftActive?'AND IFNULL(Ca.IOID, 0) =  IFNULL(nt.Ref_Ca, 0) ':'';

        $sqlCongViec = sprintf('
            SELECT
                IFNULL(DanhSach.TheoCa, 0) AS TheoCa
                , DanhSach.IOID AS Ref_DiemDo
                , ThietBi.IOID AS Ref_ThietBi
                , DanhSach.Ref_BoPhan
                , ChiSo.IOID AS Ref_ChiSo                
                %6$s
            FROM ODanhSachDiemDo AS DanhSach
            INNER JOIN ODanhSachThietBi AS ThietBi ON DanhSach.IFID_M705 = ThietBi.IFID_M705
            INNER JOIN OChiSoMayMoc AS ChiSo ON DanhSach.Ref_ChiSo = ChiSo.IOID  
            %7$s            
            LEFT JOIN ONhatTrinhThietBi AS nt ON DanhSach.IOID = nt.Ref_DiemDo 
                AND nt.Ngay = %4$s 
                %8$s                                   
            WHERE
                IFNULL(DanhSach.ThuCong, 0) = 1
                AND (
                    DanhSach.Ky = \'D\'
                    OR (DanhSach.Ky = \'W\' AND %1$d&DanhSach.Thu)
                    OR (DanhSach.Ky = \'M\' AND %2$d = DanhSach.Ngay)
                    OR (DanhSach.Ky = \'Y\' AND  %2$d = DanhSach.Ngay AND  %3$d = DanhSach.Thang)
                )   
                
                AND (                 
                    (
                        DanhSach.Ky = \'D\' 
                        AND IFNULL(TIMESTAMPDIFF(DAY, DanhSach.NgayBatDau ,%4$s) %% DanhSach.LapLai,0) = 0
                    )
                    OR (
                        DanhSach.Ky = \'W\' 
                        AND  (%1$d & DanhSach.Thu)
                        AND IFNULL(TIMESTAMPDIFF(WEEK, DanhSach.NgayBatDau ,%4$s) %% DanhSach.LapLai,0) = 0
                    )
                    OR (
                        DanhSach.Ky = \'M\'                         
                        AND (DanhSach.Ngay = %2$d or (LAST_DAY(%4$s) = %4$s and DanhSach.Ngay > %2$d))
                        AND IFNULL(TIMESTAMPDIFF(MONTH, date_add(DanhSach.NgayBatDau,INTERVAL -day(DanhSach.NgayBatDau) DAY) ,%4$s) %% DanhSach.LapLai,0) = 0
                    )
                    OR (
                        DanhSach.Ky = \'Y\' 
                        AND  (DanhSach.Ngay =%2$d or (LAST_DAY(%4$s) = %4$s and DanhSach.Ngay > %2$d))
                        AND  %3$d = DanhSach.Thang
                        AND IFNULL(TIMESTAMPDIFF(YEAR, date_add(DanhSach.NgayBatDau,INTERVAL -day(DanhSach.NgayBatDau) DAY) ,%4$s) %% DanhSach.LapLai,0) = 0
                    )
                ) 
                AND IFNULL(nt.IOID, 0) = 0
                AND DanhSach.IOID = %5$d
            ORDER BY IFNULL(nt.IOID, 0) DESC             
        ', $weekday, $day, $month, $this->_db->quote($date), $object->i_IOID, $selectShift, $joinShift, $joinDiaryShift);

        //echo '<pre>'; print_r($sqlCongViec); die;

        $datCongViec = $this->_db->fetchAll($sqlCongViec);

        //echo '<pre>'; print_r($datCongViec); die;

        if(count($datCongViec))
        {
            foreach ($datCongViec as $item)
            {
                $insert                                      = array();
                $insert['ONhatTrinhThietBi'][0]['DiemDo']    = (int)$item->Ref_DiemDo;
                $insert['ONhatTrinhThietBi'][0]['MaTB']      = (int)$item->Ref_ThietBi;

                if($item->Ref_BoPhan)
                {
                    $insert['ONhatTrinhThietBi'][0]['BoPhan']= (int)$item->Ref_BoPhan;
                }

                $insert['ONhatTrinhThietBi'][0]['ChiSo']     = (int)$item->Ref_ChiSo;
                $insert['ONhatTrinhThietBi'][0]['DonViTinh'] = (int)$item->Ref_ChiSo;
                $insert['ONhatTrinhThietBi'][0]['Ngay']      = $date;

                if($item->Ref_Ca)
                {
                    $insert['ONhatTrinhThietBi'][0]['Ca']    = (int)$item->Ref_Ca;
                }

                $import->setData($insert);
            }

            $import->generateSQL();

            $formError   = $import->countFormError();
            $objectError = $import->countObjectError();
            $error       = $formError + $objectError;

            if($error)
            {
                $this->setError();
                $this->setMessage('Có '.$error.' dòng lỗi!');
            }
        }
    }
//
//    public function onDelete(Qss_Model_Object $object)
//    {
//        parent::init();
//
//        $ifid   = array();
//        $deptid = array();
//
//        $sqlCongViec = sprintf('
//            SELECT nt.IFID_M765, nt.DeptID
//            FROM ODanhSachDiemDo AS DanhSach
//            INNER JOIN ODanhSachThietBi AS ThietBi ON DanhSach.IFID_M705 = ThietBi.IFID_M705
//            INNER JOIN OChiSoMayMoc AS ChiSo ON DanhSach.Ref_ChiSo = ChiSo.IOID
//            INNER JOIN ONhatTrinhThietBi AS nt ON DanhSach.IOID = nt.Ref_DiemDo
//            INNER JOIN qsiforms ON nt.IFID_M765 = qsiforms.IFID
//            WHERE DanhSach.IOID = %5$d AND qsiforms.Status = 1
//        ', $object->i_IOID);
//
//        $datCongViec = $this->_db->fetchAll($sqlCongViec);
//
//        foreach ($datCongViec as $item)
//        {
//            $ifid[]   = $item->IFID_M765;
//            $deptid[] = $item->DeptID;
//        }
//
//        if(count($ifid))
//        {
//            $service = $this->services->Form->Delete($ifid, $deptid);
//
//            if($service->isError())
//            {
//                $this->setError();
//                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//            }
//        }
//    }
}