<?php
/**
 * Created by PhpStorm.
 * User: Thinh
 * Date: 2/15/2016
 * Time: 11:03 AM
 */
class Qss_Model_Maintenance_Equip_Monitor extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getDailyRecordByIFID($ifid)
    {
        $sql = sprintf('
            SELECT qsws.*, pbt.*, DiemDo.*, iform.Status, ThietBi.MaThietBi, ThietBi.TenThietBi
			FROM ONhatTrinhThietBi AS pbt
			INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(pbt.Ref_MaTB, 0) = ThietBi.IOID			
			INNER JOIN qsiforms AS iform ON pbt.IFID_M765 = iform.IFID
			INNER JOIN qsworkflows AS qsw ON qsw.FormCode = iform.FormCode
			INNER JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND iform.Status = qsws.StepNo
			LEFT JOIN ODanhSachDiemDo AS DiemDo ON IFNULL(pbt.Ref_DiemDo, 0) = DiemDo.IOID
			WHERE pbt.IFID_M765 = %1$d
		', $ifid);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }

    public function rejectMonitor($params)
    {
        $model  = new Qss_Model_Import_Form('M765',true);
        $insert = array();

        if(!isset($params['ifid']) || !$params['ifid'])
        {
            return;
        }

        $insert['ONhatTrinhThietBi'][0]['ifid'] = (int)$params['ifid'];
        $insert['ONhatTrinhThietBi'][0]['TinhTrang'] = (int)3;

        $model->setData($insert);

        $model->generateSQL();

        if(isset($service) && $service->isError())
        {
            $this->setError();
            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
        }
    }

    /**
     * Update lại nhập chỉ số, chỉ dùng cho M816
     * @param $params
     */
    public function updateMonitor($params)
    {
        // echo '<pre>'; print_r($params); die;
        if(!is_array($params['diemdo']) || !count($params['diemdo']))
        {
            return false;
        }

        $mCommon        = new Qss_Model_Extra_Extra();
        $sqlIFID        = ''; // values insert to qsiform
        $sqlDailyNew    = ''; // values insert to nhat trinh thiet bi
        $sqlDailyUpdate = ''; // values insert to nhat trinh thiet bi
        $mIndex         = 0;  // Index chính
        $arrReplace     = array(); // Giup thay doi ifid cho ban ghi moi ghi vao view
        $arrIFID        = array(); // Mang ifid tam cho bang view truoc khi update
        $first          = time();

        // Lay danh sach cac ca lam viec
        $shifts = array();
        if(Qss_Lib_System::fieldActive('ONhatTrinhThietBi', 'Ca'))
        {
            $mShifts  = $mCommon->getTable(array('*'), 'OCa', array(), array('MaCa'));

            foreach($mShifts as $item)
            {
                $shifts[$item->IOID] = "{$item->MaCa}";
            }
        }


        foreach($params['diemdo'] as $diemDo)
        {
            $tenNhanVien = '';

            if($params['m765_author_tag'])
            {
                $tenNhanVienArr = explode('-', $params['m765_author_tag']);
                $tenNhanVien    = isset($tenNhanVienArr[1])?trim($tenNhanVienArr[1]):'';
            }

            $params['val'][$mIndex] = isset($params['val'][$mIndex]) && is_numeric($params['val'][$mIndex])?$params['val'][$mIndex]:0;

            // Doi voi truong hop ghi moi thi phai insert vao iform va view
            if(!$params['nhattrinhioid'][$mIndex])
            {
                $ifid      = 'IFID_'. ($first++);
                $arrIFID[] = $ifid;

                // Thiet lap values insert vao bang qsiform truoc
                $sqlIFID .= ($sqlIFID != '')?',':'';

                // 1.SDate, 2.UID, 3.Status, 4.FormCode, 5.DepartmentID, 6.deleted
                $sqlIFID .= sprintf('(%1$d , %2$d, 0, "M765", %3$d, 0)' //
                    , time()
                    , $this->_user->user_id
                    , $params['thietbideptid'][$mIndex]);


                // Thiet lap values insert vao bang view, du lieu duoc insert sau khi da insert vao bang qsiforms
                $sqlDailyNew .= ($sqlDailyNew != '')?',':'';

                // 1.DeptID	2.DiemDo 3.Ref_DiemDo 4.MaTB 5.Ref_MaTB	6.BoPhan 7.Ref_BoPhan 8.ChiSo 9.Ref_ChiSo 10.DonViTinh
                // x.Ref_DonViTinh	11.NguoiKiemTra	12.Ref_NguoiKiemTra	13.Ngay	14.ThoiGian	15.Ca 16.Ref_Ca	17.SoHoatDong 18.Dat
                // x.Ref_Dat x.NguoiVanHanh	x.Ref_NguoiVanHanh x.DuAn x.Ref_DuAn 19.TinhTrang x.Ref_TinhTrang x.GhiChu
                // 20. IFID_M765

                if(Qss_Lib_System::fieldActive('ONhatTrinhThietBi', 'NguyenNhan'))
                {
                    $sqlDailyNew .= sprintf(
                        '(%20$s, %1$d , %2$s, %3$d, %4$s, %5$d, %6$s, %7$d, %8$s, %9$d, %10$s, %11$s, %12$d, %13$s, %14$s, %15$s, %16$d, %17$s , %18$d, %19$d, %21$s, %22$s, %23$s  )'
                        , $params['thietbideptid'][$mIndex] //1
                        , $this->_o_DB->quote($diemDo) //2
                        , $params['refdiemdo'][$mIndex] //3
                        , $this->_o_DB->quote($params['mathietbi'][$mIndex]) //4
                        , $params['refmathietbi'][$mIndex] //5
                        , $this->_o_DB->quote($params['bophan'][$mIndex]) //6
                        , $params['refbophan'][$mIndex] //7
                        , $this->_o_DB->quote($params['param']) //8
                        , $params['filter_param'] //9
                        , $this->_o_DB->quote($params['param_uom']) // 10
                        , $this->_o_DB->quote($tenNhanVien)  // 11
                        , $params['m765_author'] //12
                        , $this->_o_DB->quote(Qss_Lib_Date::displaytomysql($params['input_date'])) //13
                        , $this->_o_DB->quote($params['input_time']) //14
                        , $this->_o_DB->quote(@$shifts[$params['m765_shift']]) //15
                        , $params['m765_shift'] //16
                        , $this->_o_DB->quote($params['val'][$mIndex]) // 17
                        , ($params['dinhluong'][$mIndex] == 0)?$params['val'][$mIndex]:0 //18
                        , $params['status'][$mIndex]//19
                        , $this->_o_DB->quote($ifid) // 20
                        , $this->_o_DB->quote($params['reason'][$mIndex])
                        , $this->_o_DB->quote($params['remedy'][$mIndex])
                        , $this->_o_DB->quote($params['note'][$mIndex])
                    );
                }
                else
                {
                    $sqlDailyNew .= sprintf(
                        '(%20$s, %1$d , %2$s, %3$d, %4$s, %5$d, %6$s, %7$d, %8$s, %9$d, %10$s, %11$s, %12$d, %13$s, %14$s, %15$s, %16$d, %17$s , %18$d, %19$d  )'
                        , $params['thietbideptid'][$mIndex] //1
                        , $this->_o_DB->quote($diemDo) //2
                        , $params['refdiemdo'][$mIndex] //3
                        , $this->_o_DB->quote($params['mathietbi'][$mIndex]) //4
                        , $params['refmathietbi'][$mIndex] //5
                        , $this->_o_DB->quote($params['bophan'][$mIndex]) //6
                        , $params['refbophan'][$mIndex] //7
                        , $this->_o_DB->quote($params['param']) //8
                        , $params['filter_param'] //9
                        , $this->_o_DB->quote($params['param_uom']) // 10
                        , $this->_o_DB->quote($tenNhanVien)  // 11
                        , $params['m765_author'] //12
                        , $this->_o_DB->quote(Qss_Lib_Date::displaytomysql($params['input_date'])) //13
                        , $this->_o_DB->quote($params['input_time']) //14
                        , $this->_o_DB->quote(@$shifts[$params['m765_shift']]) //15
                        , $params['m765_shift'] //16
                        , $this->_o_DB->quote($params['val'][$mIndex]) // 17
                        , ($params['dinhluong'][$mIndex] == 0)?$params['val'][$mIndex]:0 //18
                        , $params['status'][$mIndex]//19
                        , $this->_o_DB->quote($ifid) // 20
                    );
                }


                // echo '<pre>'; print_r($sqlDailyNew);die;
            }
            else // Doi voi truong hop ban ghi da duoc save truoc do thi chi update lai
            {
                // Thiet lap values insert vao bang view, du lieu duoc insert sau khi da insert vao bang qsiforms
                $sqlDailyUpdate .= ($sqlDailyUpdate != '')?',':'';

                // 1.DeptID	2.DiemDo 3.Ref_DiemDo 4.MaTB 5.Ref_MaTB	6.BoPhan 7.Ref_BoPhan 8.ChiSo 9.Ref_ChiSo 10.DonViTinh
                // x.Ref_DonViTinh	11.NguoiKiemTra	12.Ref_NguoiKiemTra	13.Ngay	14.ThoiGian	15.Ca 16.Ref_Ca	17.SoHoatDong 18.Dat
                // x.Ref_Dat x.NguoiVanHanh	x.Ref_NguoiVanHanh x.DuAn x.Ref_DuAn 19.TinhTrang x.Ref_TinhTrang x.GhiChu
                // 20. IOID


                if(Qss_Lib_System::fieldActive('ONhatTrinhThietBi', 'NguyenNhan'))
                {
                    $sqlDailyUpdate .= sprintf(
                        '(%21$d, %20$d, %1$d , %2$s, %3$d, %4$s, %5$d, %6$s, %7$d, %8$s, %9$d, %10$s, %11$s, %12$d, %13$s, %14$s, %15$s, %16$d, %17$s , %18$d, %19$d, %22$s, %23$s, %24$s  )'
                        , $params['thietbideptid'][$mIndex] //1
                        , $this->_o_DB->quote($diemDo) //2
                        , $params['refdiemdo'][$mIndex] //3
                        , $this->_o_DB->quote($params['mathietbi'][$mIndex]) //4
                        , $params['refmathietbi'][$mIndex] //5
                        , $this->_o_DB->quote($params['bophan'][$mIndex]) //6
                        , $params['refbophan'][$mIndex] //7
                        , $this->_o_DB->quote($params['param']) //8
                        , $params['filter_param'] //9
                        , $this->_o_DB->quote($params['param_uom']) // 10
                        , $this->_o_DB->quote($tenNhanVien) // 11
                        , $params['m765_author'] //12
                        , $this->_o_DB->quote(Qss_Lib_Date::displaytomysql($params['input_date'])) //13
                        , $this->_o_DB->quote($params['input_time']) //14
                        , $this->_o_DB->quote(@$shifts[$params['m765_shift']]) //15
                        , $params['m765_shift'] //16
                        , $this->_o_DB->quote($params['val'][$mIndex]) // 17
                        , ($params['dinhluong'][$mIndex]  == 0)?$params['val'][$mIndex]:0 //18
                        , $params['status'][$mIndex]//19
                        , $params['nhattrinhioid'][$mIndex]//20
                        , $params['nhattrinhifid'][$mIndex]//21
                        , $this->_o_DB->quote($params['reason'][$mIndex])
                        , $this->_o_DB->quote($params['remedy'][$mIndex])
                        , $this->_o_DB->quote($params['note'][$mIndex])
                    );
                }
                else
                {
                    $sqlDailyUpdate .= sprintf(
                        '(%21$d, %20$d, %1$d , %2$s, %3$d, %4$s, %5$d, %6$s, %7$d, %8$s, %9$d, %10$s, %11$s, %12$d, %13$s, %14$s, %15$s, %16$d, %17$s , %18$d, %19$d  )'
                        , @(int)$params['thietbideptid'][$mIndex] //1
                        , $this->_o_DB->quote($diemDo) //2
                        , $params['refdiemdo'][$mIndex] //3
                        , $this->_o_DB->quote($params['mathietbi'][$mIndex]) //4
                        , $params['refmathietbi'][$mIndex] //5
                        , $this->_o_DB->quote($params['bophan'][$mIndex]) //6
                        , @(int)$params['refbophan'][$mIndex] //7
                        , $this->_o_DB->quote($params['param']) //8
                        , $params['filter_param'] //9
                        , $this->_o_DB->quote($params['param_uom']) // 10
                        , $this->_o_DB->quote($tenNhanVien) // 11
                        , $params['m765_author'] //12
                        , $this->_o_DB->quote(Qss_Lib_Date::displaytomysql($params['input_date'])) //13
                        , $this->_o_DB->quote($params['input_time']) //14
                        , $this->_o_DB->quote(@$shifts[$params['m765_shift']]) //15
                        , $params['m765_shift'] //16
                        , $this->_o_DB->quote($params['val'][$mIndex]) // 17
                        , ($params['dinhluong'][$mIndex]  == 0)?$params['val'][$mIndex]:0 //18
                        , @(int)$params['status'][$mIndex]//19
                        , $params['nhattrinhioid'][$mIndex]//20
                        , $params['nhattrinhifid'][$mIndex]//21
                    );
                }

            }

            $mIndex++;
        }

        // Insert into qsiforms va view voi truong hop insert ban ghi moi
        if($sqlIFID && $sqlDailyNew)
        {
            $sql = sprintf(
                'insert into qsiforms (SDate,UID,Status,FormCode,DepartmentID,deleted) values %1$s '
            ,$sqlIFID);

            $lastifid = $this->_o_DB->execute($sql);

            // echo '<pre>'; print_r($lastifid);die;

            // Trao ifid tu qsiform
            for($i=$lastifid;$i < ($lastifid + count($arrIFID));$i++)
            {
                $arrReplace[] = $i;
            }

//            echo '<pre>'; print_r($arrIFID);
//            echo '<pre>'; print_r($arrReplace);die;

            if($sqlDailyNew)
            {
                $sqlDailyNew = str_replace($arrIFID, $arrReplace, $sqlDailyNew);

                // echo '<pre>'; print_r($sqlDailyNew);die;

                // 1.DeptID	2.DiemDo 3.Ref_DiemDo 4.MaTB 5.Ref_MaTB	6.BoPhan 7.Ref_BoPhan 8.ChiSo 9.Ref_ChiSo 10.DonViTinh
                // x.Ref_DonViTinh	11.NguoiKiemTra	12.Ref_NguoiKiemTra	13.Ngay	14.ThoiGian	15.Ca 16.Ref_Ca	17.SoHoatDong 18.Dat
                // x.Ref_Dat x.NguoiVanHanh	x.Ref_NguoiVanHanh x.DuAn x.Ref_DuAn 19.TinhTrang x.Ref_TinhTrang x.GhiChu
                // 20. IFID_M765 <vi tri dau tien>

                if(Qss_Lib_System::fieldActive('ONhatTrinhThietBi', 'NguyenNhan'))
                {
                    $sql = sprintf('
                    insert into ONhatTrinhThietBi (IFID_M765,DeptID,DiemDo,Ref_DiemDo,MaTB,Ref_MaTB,BoPhan,Ref_BoPhan,ChiSo,Ref_ChiSo,DonViTinh, NguoiKiemTra, Ref_NguoiKiemTra,Ngay,ThoiGian,Ca,Ref_Ca,SoHoatDong,Dat,TinhTrang, NguyenNhan, BienPhapKhacPhuc, GhiChu)values %1$s'
                        ,$sqlDailyNew);
                    //echo '<pre>'; print_r($sql);die;
                    $this->_o_DB->execute($sql);
                }
                else
                {
                    $sql = sprintf('
                    insert into ONhatTrinhThietBi (IFID_M765,DeptID,DiemDo,Ref_DiemDo,MaTB,Ref_MaTB,BoPhan,Ref_BoPhan,ChiSo,Ref_ChiSo,DonViTinh, NguoiKiemTra, Ref_NguoiKiemTra,Ngay,ThoiGian,Ca,Ref_Ca,SoHoatDong,Dat,TinhTrang)values %1$s'
                        ,$sqlDailyNew);
                    //echo '<pre>'; print_r($sql);die;
                    $this->_o_DB->execute($sql);
                }

            }
        }

        // voi truong hop update lai
        if($sqlDailyUpdate)
        {
            // 1.DeptID	2.DiemDo 3.Ref_DiemDo 4.MaTB 5.Ref_MaTB	6.BoPhan 7.Ref_BoPhan 8.ChiSo 9.Ref_ChiSo 10.DonViTinh
            // x.Ref_DonViTinh	11.NguoiKiemTra	12.Ref_NguoiKiemTra	13.Ngay	14.ThoiGian	15.Ca 16.Ref_Ca	17.SoHoatDong 18.Dat
            // x.Ref_Dat x.NguoiVanHanh	x.Ref_NguoiVanHanh x.DuAn x.Ref_DuAn 19.TinhTrang x.Ref_TinhTrang x.GhiChu
            // 20. IOID <vi tri dau tien>

            if(Qss_Lib_System::fieldActive('ONhatTrinhThietBi', 'NguyenNhan'))
            {
                $sql = sprintf('
                replace into ONhatTrinhThietBi (IFID_M765, IOID,DeptID,DiemDo,Ref_DiemDo,MaTB,Ref_MaTB,BoPhan,Ref_BoPhan,ChiSo,Ref_ChiSo,DonViTinh, NguoiKiemTra, Ref_NguoiKiemTra,Ngay,ThoiGian,Ca,Ref_Ca,SoHoatDong,Dat,TinhTrang, NguyenNhan, BienPhapKhacPhuc, GhiChu) values %1$s'
                    ,$sqlDailyUpdate);
                //echo '<pre>'; print_r($sql);die;
                $this->_o_DB->execute($sql);
            }
            else
            {
                $sql = sprintf('
                replace into ONhatTrinhThietBi (IFID_M765, IOID,DeptID,DiemDo,Ref_DiemDo,MaTB,Ref_MaTB,BoPhan,Ref_BoPhan,ChiSo,Ref_ChiSo,DonViTinh, NguoiKiemTra, Ref_NguoiKiemTra,Ngay,ThoiGian,Ca,Ref_Ca,SoHoatDong,Dat,TinhTrang) values %1$s'
                    ,$sqlDailyUpdate);
                //echo '<pre>'; print_r($sql);die;
                $this->_o_DB->execute($sql);
            }

        }
    }

    public function countMonitorsByDate(
        $date
        , $locationIOID = 0
        , $eqTypeIOID = 0
        , $paramIOID = 0
        , $equipIOID = 0
        , $shift = 0
        , $lineIOID = 0
        , $coNhatTrinh = false)
    {
        $weekday = DATE('w', strtotime($date));
        $day     = DATE('d', strtotime($date));
        $month   = DATE('m', strtotime($date));
        $filter  = '';
        $loc     = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $filter .= $loc?sprintf(' AND ifnull(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d) ', $loc->lft, $loc->rgt):'';
        $eqType  = $eqTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', @(int)$eqTypeIOID)):false;
        $filter .= $eqType?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',  $eqType->lft, $eqType->rgt):'';
        $filter .= $paramIOID?sprintf(' AND ChiSo.IOID = %1$d ', $paramIOID):'';
        $filter .= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ', $equipIOID):'';
        $filter .= $lineIOID?sprintf(' AND ThietBi.Ref_DayChuyen = %1$d ', $lineIOID):'';
        $filter .= $coNhatTrinh?sprintf(' AND IFNULL(nt.IOID, 0) != 0 '):'';
        $joinNT  = ($shift !== false)?sprintf(' AND ifnull(nt.Ref_Ca,0) = %1$d ', $shift):''; // Neu shift === false coi nhu ko loc theo ca


        $sql = sprintf('
            SELECT
                count(1) AS Total
            FROM ODanhSachDiemDo AS DanhSach
            INNER JOIN ODanhSachThietBi AS ThietBi ON DanhSach.IFID_M705 = ThietBi.IFID_M705
            LEFT JOIN OChiSoMayMoc AS ChiSo ON DanhSach.Ref_ChiSo = ChiSo.IOID
            LEFT JOIN OKhuVuc AS khuvuc ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = khuvuc.IOID
            LEFT JOIN ONhatTrinhThietBi as nt on DanhSach.IOID = nt.Ref_DiemDo and nt.Ngay = %5$s %6$s
            LEFT JOIN OCa AS Ca ON IFNULL(nt.Ref_Ca, 0) = Ca.IOID
            WHERE
                IFNULL(DanhSach.ThuCong, 0) = 1
                AND
                (
                    (DanhSach.Ky = \'D\')
                    OR
                    (DanhSach.Ky = \'W\' AND %1$d = DanhSach.Thu)
                    OR
                    (DanhSach.Ky = \'M\' AND %2$d = DanhSach.Ngay)
                    OR
                    (DanhSach.Ky = \'Y\' AND  %2$d = DanhSach.Ngay AND  %3$d = DanhSach.Thang)
                )
            AND (
                IFNULL(ThietBi.Ref_MaKhuVuc, 0) in (
                    SELECT IOID FROM OKhuVuc
                    inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID
                    WHERE UID = %7$d
                )
            )
            AND ThietBi.DeptID in (%8$s)
            %4$s
        ', $weekday, $day, $month, $filter, $this->_o_DB->quote($date), $joinNT, $this->_user->user_id, $this->_user->user_dept_list);
        // echo '<pre>'; print_r($sql); die;
        $dataSql = $this->_o_DB->fetchOne($sql);

        return $dataSql?$dataSql->Total:0;
    }

    public function getMonitorsByDate(
        $date
        , $locationIOID = 0
        , $eqTypeIOID = 0
        , $paramIOID = 0
        , $equipIOID = 0
        , $shift = 0
        , $lineIOID = 0
        , $coNhatTrinh = false
        , $page = 0
        , $perpage = 0
        , $equipGroup = 0)
    {
        $weekday = DATE('w', strtotime($date));
        $day     = DATE('d', strtotime($date));
        $month   = DATE('m', strtotime($date));
        $filter  = '';
        $loc     = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $filter .= $loc?sprintf(' AND ifnull(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d) ', $loc->lft, $loc->rgt):'';
        $eqType  = $eqTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', @(int)$eqTypeIOID)):false;
        $filter .= $eqType?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',  $eqType->lft, $eqType->rgt):'';
        $filter .= $paramIOID?sprintf(' AND ChiSo.IOID = %1$d ', $paramIOID):'';
        $filter .= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ', $equipIOID):'';
        $filter .= $lineIOID?sprintf(' AND ThietBi.Ref_DayChuyen = %1$d ', $lineIOID):'';
        $filter .= $coNhatTrinh?sprintf(' AND IFNULL(nt.IOID, 0) != 0 '):'';
        $filter .= $equipGroup?sprintf(' AND IFNULL(ThietBi.Ref_NhomThietBi, 0) = %1$d ', $equipGroup):'';
        $joinNT  = ($shift !== false)?sprintf(' AND ifnull(nt.Ref_Ca,0) = %1$d ', $shift):''; // Neu shift === false coi nhu ko loc theo ca
        $sPage   = ($page && $perpage)?($page - 1)*$perpage:0;
        $limit   = ($page && $perpage)?sprintf(' LIMIT %1$d, %2$d', $sPage, $perpage):'';
        $lang    = $this->_user->user_lang == 'vn'?'':'_'.$this->_user->user_lang;
        $addFields = '';

        $sql = sprintf('
            SELECT
                ThietBi.*
                , nt.*
                , DanhSach.*
                , ThietBi.IOID AS EQIOID
                , ThietBi.MaThietBi
                , ThietBi.TenThietBi
                , DanhSach.IOID AS MonitorIOID
                , ifnull(khuvuc.MaKhuVuc, \'\') AS MaKhuVuc
                , ifnull(khuvuc.Ten, \'\') AS TenKhuVuc
                , ifnull(khuvuc.IOID, 0) AS LOCIOID
                , ifnull(khuvuc.lft, 0) AS lft
                , ifnull(khuvuc.rgt, 0) AS rgt
                , ifnull(nt.IOID, 0) AS NhatTrinhIOID
                , ifnull(nt.IFID_M765, 0) AS NhatTrinhIFID
                , ifnull(nt.SoHoatDong, 0) AS SoGio
                , ifnull(nt.SoHoatDong, 0) AS SoHoatDong
                , ifnull(nt.Dat, 0) AS Dat
                , ifnull(nt.TinhTrang,0) AS TinhTrang
                , nt.Ref_TinhTrang
                , nt.ThoiGian AS GioNhap
                , nt.Ngay AS NgayNhap   
                , NhatTrinhIForm.Status  AS Status 
                , qsws.Name%10$s  AS StatusName
                , qsws.Color                             
                , Ca.TenCa
                , Ca.MaCa
                , ChiSo.DonViTinh
                , ChiSo.DongHo
                , DanhSach.Thu as GiaTri
                , DanhSach.Ngay
                , DanhSach.Thang
                , DanhSach.Ma AS NoiDungKiemTra
                , DanhSach.BoPhan AS BoPhanCaiDat
                , IF(DongHo = \'COUNTER\' OR DongHo = \'METER\', 1, 0) AS DinhLuong
                , ThietBi.DeptID AS ThietBiDeptID
                , case when DanhSach.Ky = \'D\' THEN \'Hàng ngày\'
                when DanhSach.Ky = \'W\' THEN CONCAT(DanhSach.Thu,  \' hàng tuần\')
                when DanhSach.Ky = \'M\' THEN CONCAT(DanhSach.Ngay,  \' hàng tháng\')
                when DanhSach.Ky = \'Y\' THEN CONCAT(\'Ngày \', DanhSach.Ngay, \' tháng \', DanhSach.Thang,  \' hàng năm\')
                END AS GiaTriChuKy
            FROM ONhatTrinhThietBi as nt 
            INNER JOIN ODanhSachThietBi AS ThietBi ON nt.Ref_MaTB = ThietBi.IOID
            INNER JOIN OKhuVuc AS khuvuc ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = khuvuc.IOID
            INNER JOIN ODanhSachDiemDo AS DanhSach ON nt.Ref_DiemDo = DanhSach.IOID
            INNER JOIN OChiSoMayMoc AS ChiSo ON DanhSach.Ref_ChiSo = ChiSo.IOID     
            INNER JOIN qsiforms as NhatTrinhIForm ON nt.IFID_M765 = NhatTrinhIForm.IFID
            INNER JOIN qsworkflows AS qsw ON qsw.FormCode = NhatTrinhIForm.FormCode
			INNER JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND NhatTrinhIForm.Status = qsws.StepNo               
            LEFT JOIN OCa AS Ca ON IFNULL(nt.Ref_Ca, 0) = Ca.IOID    
            WHERE
                IFNULL(DanhSach.ThuCong, 0) = 1
                AND nt.Ngay = %5$s %6$s
                AND (
                    DanhSach.Ky = \'D\'
                    OR (DanhSach.Ky = \'W\' AND %1$d = DanhSach.Thu)
                    OR (DanhSach.Ky = \'M\' AND %2$d = DanhSach.Ngay)
                    OR (DanhSach.Ky = \'Y\' AND  %2$d = DanhSach.Ngay AND  %3$d = DanhSach.Thang)
                )
                AND (
                    IFNULL(ThietBi.Ref_MaKhuVuc, 0) in (
                        SELECT IOID FROM OKhuVuc
                        inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID
                        WHERE UID = %7$d)
                )
                AND ThietBi.DeptID in (%8$s)
                %4$s
            ORDER BY IFNULL(nt.IOID, 0) DESC
            %9$s
        '   , $weekday
            , $day
            , $month
            , $filter
            , $this->_o_DB->quote($date)
            , $joinNT
            , $this->_user->user_id
            , $this->_user->user_dept_list
            , $limit
            , $lang);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * @param $start
     * @param $end
     * @param int $locationIOID
     * @param int $eqTypeIOID
     * @param int $paramIOID
     * @param int $equipIOID
     * @param mix $shift (false/int) Nếu shift === false coi như không lọc theo ca, còn lại là IOID của ca
     * @param int $lineIOID
     * @return array
     */
    public function getFailureMonitors($start, $end, $locationIOID = 0, $eqTypeIOID = 0, $paramIOID = 0, $equipIOID = 0, $shift=false, $lineIOID = 0, $equipGroup = 0)
    {
        $retval   = array();
        $j        = 0;
        $nStart   = date_create($start);
        $nEnd     = date_create($end);

        while($nStart <= $nEnd)
        {
            $date  = $nStart->format('Y-m-d');
            $equip = $this->getMonitorsByDate($date, $locationIOID, $eqTypeIOID, $paramIOID, $equipIOID, $shift, $lineIOID, true, 0, 0, $equipGroup);

            foreach($equip as $item)
            {
                $item->LOCIOID     = (int)$item->LOCIOID;
                $item->GioiHanDuoi = (double)$item->GioiHanDuoi;
                $item->GioiHanTren = (double)$item->GioiHanTren;

                if(
                    $item->NhatTrinhIOID
                    &&
                    (
                        ( $item->DinhLuong
                            && !($item->GioiHanTren == 0 && $item->GioiHanDuoi == 0)
                            && ($item->SoGio >= $item->GioiHanTren || $item->SoGio <= $item->GioiHanDuoi)
                        )
                        ||
                        (
                            !$item->DinhLuong && $item->Dat == 2
                        )
                    )
                    && $item->TinhTrang != 2
                    && $item->TinhTrang != 3
                )
                {
                    $retval[$j]       = new stdClass();
                    $retval[$j]       = $item;
                    $retval[$j]->Ngay = $date;
                    $j++;
                }
            }
            $nStart = Qss_Lib_Date::add_date($nStart, 1);
        }

        // echo '<Pre>'; print_r($retval); die;

        return $retval;
    }

    public function getMonitorByWorkorder($workOrderIFID)
    {
        $sql = sprintf('
            SELECT
                DiemDo.*,
                DiemDo.Ma AS DiemDo,
                IF(ChiSo.DongHo = \'ONOFF\', 1, 0) AS DinhTinh,
                IFNULL(GiamSat.IOID, 0) AS Ref_GiamSat,
                IFNULL(DiemDo.IOID, 0) AS Ref_DiemDo,
                GiamSat.GiaTri,
                GiamSat.IFID_M759 AS MainIFID,
                IFNULL(GiamSat.Dat, 0) AS Dat
            FROM OPhieuBaoTri AS PhieuBaoTri
            INNER JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
            INNER JOIN ODanhSachDiemDo AS DiemDo ON ThietBi.IFID_M705 = DiemDo.IFID_M705
            INNER JOIN OChiSoMayMoc AS ChiSo ON DiemDo.Ref_ChiSo = ChiSo.IOID
            LEFT JOIN OGiamSatBaoTri AS GiamSat ON PhieuBaoTri.IFID_M759 = GiamSat.IFID_M759
                AND IFNULL(DiemDo.IOID, 0) = IFNULL(GiamSat.Ref_DiemDo, 0)

            WHERE PhieuBaoTri.IFID_M759 = %1$d
        ', $workOrderIFID);

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

    public function getMonitorByCalibration($calibrationIFID)
    {
        $sql = sprintf('
            SELECT
                DiemDo.*,
                DiemDo.Ma AS DiemDo,
                IF(ChiSo.DongHo = \'ONOFF\', 1, 0) AS DinhTinh,
                IFNULL(GiamSat.IOID, 0) AS Ref_GiamSat,
                IFNULL(DiemDo.IOID, 0) AS Ref_DiemDo,
                GiamSat.GiaTri,
                GiamSat.IFID_M753 AS MainIFID,
                IFNULL(GiamSat.Dat, 0) AS Dat
            FROM OHieuChuanKiemDinh AS PhieuBaoTri
            INNER JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
            INNER JOIN ODanhSachDiemDo AS DiemDo ON ThietBi.IFID_M705 = DiemDo.IFID_M705
            INNER JOIN OChiSoMayMoc AS ChiSo ON DiemDo.Ref_ChiSo = ChiSo.IOID
            LEFT JOIN OGiamSatHieuChuan AS GiamSat ON PhieuBaoTri.IFID_M753 = GiamSat.IFID_M753
                AND IFNULL(DiemDo.IOID, 0) = IFNULL(GiamSat.Ref_DiemDo, 0)
            WHERE PhieuBaoTri.IFID_M753 = %1$d
        ', $calibrationIFID);

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

    public function getMonitorByDetailPlan($detailPlanIFID)
    {
        $sql = sprintf('
            SELECT
                DiemDo.*,
                DiemDo.Ma AS DiemDo,
                IF(ChiSo.DongHo = \'ONOFF\', 1, 0) AS DinhTinh,
                IFNULL(GiamSat.IOID, 0) AS Ref_GiamSat,
                IFNULL(DiemDo.IOID, 0) AS Ref_DiemDo,
                GiamSat.GiaTri,
                GiamSat.IFID_M837 AS MainIFID,
                IFNULL(GiamSat.Dat, 0) AS Dat
            FROM OKeHoachBaoTri AS PhieuBaoTri
            INNER JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
            INNER JOIN ODanhSachDiemDo AS DiemDo ON ThietBi.IFID_M705 = DiemDo.IFID_M705
            INNER JOIN OChiSoMayMoc AS ChiSo ON DiemDo.Ref_ChiSo = ChiSo.IOID
            LEFT JOIN OGiamSatChiTiet AS GiamSat ON PhieuBaoTri.IFID_M837 = GiamSat.IFID_M837
                AND IFNULL(DiemDo.IOID, 0) = IFNULL(GiamSat.Ref_DiemDo, 0)
            WHERE PhieuBaoTri.IFID_M837 = %1$d
        ', $detailPlanIFID);

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }
}