<?php
class Qss_Bin_Trigger_OThaoTacVanHanh extends Qss_Lib_Trigger
{
    public function onInserted(Qss_Model_Object $object)
    {
        parent::init();
        $import  = new Qss_Model_Import_Form('M840',false, false);
        $date    = date('Y-m-d');
        // $weekday = DATE('w', strtotime($date));
        $weekday = Qss_Lib_Const::$WEEKDAY_BITWISE[DATE('w', strtotime($date))];
        $day     = DATE('d', strtotime($date));
        $month   = DATE('m', strtotime($date));

        $sqlCongViec = sprintf('
            SELECT
                ThietBi.MaThietBi
                , ThietBi.TenThietBi
                , ThietBi.IOID AS Ref_ThietBi
                , DanhSach.*
            FROM OThaoTacVanHanh AS DanhSach
            INNER JOIN ODanhSachThietBi AS ThietBi ON DanhSach.IFID_M705 = ThietBi.IFID_M705
            INNER JOIN OKhuVuc AS khuvuc ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = khuvuc.IOID
            LEFT JOIN OCongViecNhanVien as nt on ThietBi.IOID = IFNULL(nt.Ref_MaThietBi, 0)
                AND DanhSach.CongViec = nt.CongViec
                AND nt.Ngay = CURDATE()                            
            WHERE
            (                 
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
        ', $weekday, $day, $month, $this->_db->quote($date), $object->i_IOID);

        $datCongViec = $this->_db->fetchAll($sqlCongViec);

        if(count($datCongViec))
        {
            $activeFieldNguoiGiao = Qss_Lib_System::fieldActive('OThaoTacVanHanh', 'NguoiGiao');
            foreach ($datCongViec as $item)
            {
                $insert                                                  = array();
                $insert['OCongViecNhanVien'][0]['Ngay']                  = date('Y-m-d');
                $insert['OCongViecNhanVien'][0]['MaThietBi']             = $item->MaThietBi;
                $insert['OCongViecNhanVien'][0]['TenThietBi']            = $item->TenThietBi;
                $insert['OCongViecNhanVien'][0]['CongViec']              = $item->CongViec;
                $insert['OCongViecNhanVien'][0]['NgayGiao']              = date('Y-m-d');

                if($activeFieldNguoiGiao) {
                    $insert['OCongViecNhanVien'][0]['NguoiGiaoViec']     = @(int)$item->Ref_NguoiGiao;
                }

                // var_dump($activeFieldNguoiGiao); die;

                $insert['OCongViecNhanVien'][0]['ThoiGianGiaoViec']      = date('H:i');
                $insert['OCongViecNhanVien'][0]['ThoiGianBatDauDuKien']  = $item->ThoiGianBatDau;
                $insert['OCongViecNhanVien'][0]['ThoiGianKetThucDuKien'] = $item->ThoiGianKetThuc;

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
}