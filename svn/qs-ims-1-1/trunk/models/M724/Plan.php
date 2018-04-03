<?php
class Qss_Model_M724_Plan extends Qss_Model_Abstract
{
    /**
     * Lấy ra tất cả kế hoạch của một thiết bị, bao gồm cả thông tin về chu kỳ bảo trì
     */
    public function getPlansWithCycleByEquip($equipIOID) {
        $sql = sprintf('
            SELECT OBaoTriDinhKy.*, GROUP_CONCAT(OChuKyBaoTri.ChuKy SEPARATOR ", ") AS ChuKy
              , OCauTrucThietBi.ViTri, OCauTrucThietBi.BoPhan  
            FROM OBaoTriDinhKy
            INNER JOIN OChuKyBaoTri ON OBaoTriDinhKy.IFID_M724 = OChuKyBaoTri.IFID_M724
            LEFT JOIN OCauTrucThietBi ON IFNULL(OBaoTriDinhKy.Ref_BoPhan, 0) = OCauTrucThietBi.IOID
            WHERE IFNULL(OBaoTriDinhKy.Ref_MaThietBi, 0) = %1$d
            GROUP BY OBaoTriDinhKy.IFID_M724
            ORDER BY OBaoTriDinhKy.IFID_M724
        ', $equipIOID);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getSecureM125Sql($field = 'btdk.Ref_DVBT') {
        $sql = '';
        if(Qss_Lib_System::formSecure('M125') && Qss_Lib_System::fieldActive('OBaoTriDinhKy', 'DVBT')) {
            $sql = sprintf('
                (
                    IFNULL(%2$s, 0) in (
                        SELECT IOID FROM ODonViSanXuat
                        inner join qsrecordrights on ODonViSanXuat.IFID_M125 = qsrecordrights.IFID
                        WHERE UID = %1$d
                    )
                    OR IFNULL(%2$s, 0) = 0
                )
            ', $this->_user->user_id, $field);
        }
        return $sql;
    }

    public function getSecureM720Sql($field = 'btdk.Ref_MaThietBi') {
        $sql = '';
        if(Qss_Lib_System::formSecure('M720')) {
            $sql = sprintf(' 
			    (
                    IFNULL(%2$s, 0) IN (
                        SELECT ODanhSachThietBi.IOID
                        FROM ODanhSachThietBi 
                        INNER JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ODanhSachThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
                        INNER JOIN OKhuVuc AS KhuVucCha ON KhuVucThietBi.lft >=  KhuVucCha.lft AND KhuVucThietBi.rgt <= KhuVucCha.rgt			 
                        INNER JOIN qsrecordrights on KhuVucCha.IFID_M720 = qsrecordrights.IFID 
                        WHERE UID = %1$d
                    )
                    OR IFNULL(%2$s, 0) = 0
                )
			',$this->_user->user_id, $field);
        }
        return $sql;
    }

    /**
     * Điều kiện lọc kế hoạch theo ngày
     * Lưu ý đã bỏ điều kiện không tồn tại Phiếu bảo trì với trường hợp thông thường phải tự thêm tay vào.
     * Lưu ý do có xét đến bảng điểm đo nên phải join với bảng này
     * Lưu ý Nếu kết hợp điều kiện khác cần thêm AND vào kết hợp với câu sql trả về
     *
     * Giải thích:
     * * Với căn cứ là định kỳ ( ifnull(%1$s.CanCu,0) =0 )
     *      - Với kỳ là hàng ngày, lấy số ngày từ ngày hiện tại trừ cho trường ngày bắt đầu/lần cuối để ra số ngày từ
     * lần bảo trì lần cuối đến hiện tại. Rồi chia số ngày cho kỳ lặp. Nếu ngày này chia hết cho hiện tại thì lấy dữ
     * liệu. ( %1$s.KyBaoDuong = 'D' )
     *      - Với kỳ là hàng tuần, ta cũng tính tương tự nhưng thay vì tính số ngày ta tính số tuần chia cho lặp lại
     * ( %1$s.KyBaoDuong = 'W' ). Ta so sánh thứ với ngày truyền vào.
     *      - Với kỳ là hàng tháng, ta cũng tính tương tự, ta cũng tính số tháng tương ứng. Tuy nhiên ở đây ngày bắt đầu
     * ta sẽ tính từ đầu tháng của ngày bắt đầu (date_add(%3$s.NgayBatDau, INTERVAL -day(%3$s.NgayBatDau) DAY)) bằng cách
     * lấy ngày bắt đầu trừ đi số ngày bắt đầu. Ta cũng so sánh ngày (01, 02) với ngày truyền vào
     *      - Với kỳ là hàng năm, ta cũng tính tương tự, ta cũng tính số năm tương ứng. Tuy nhiên ở đây ngày bắt đầu
     * ta sẽ tính từ đầu tháng của ngày bắt đầu (date_add(%3$s.NgayBatDau, INTERVAL -day(%3$s.NgayBatDau) DAY)) bằng cách
     * lấy ngày bắt đầu trừ đi số ngày bắt đầu. Ta cũng so sánh ngày (01, 02) với ngày truyền vào
     */
    public function getfilterPlansByDateSql(
        $ngayBatDau // %4$s (Từ %1$s)
        , $chuKyAlias = 'chuky' // %1$s
        , $phieuBaoTriAlias = 'pbt' // %2$s
        , $baoTriDinhKy = 'btdk' // %3$s
        , $diemdo = 'diemdo' // %10$s
    ) {
        $date = date_create($ngayBatDau);

        $sql = sprintf('             
            (
                (
                    ifnull(%1$s.CanCu,0) =0                        
                    AND 
                    (
                        (
                            %1$s.KyBaoDuong = \'D\' 
                                AND IFNULL(TIMESTAMPDIFF(DAY, %3$s.NgayBatDau ,%4$s) %% %1$s.LapLai,0) = 0
                        )
                        OR 
                        (
                            %1$s.KyBaoDuong = \'W\'  
                                AND %1$s.Thu =%9$d AND IFNULL(TIMESTAMPDIFF(WEEK, %3$s.NgayBatDau ,%4$s) %% %1$s.LapLai,0) = 0)
                        OR 
                        (
                            %1$s.KyBaoDuong = \'M\' 
                            AND (%1$s.Ngay =%5$d or (LAST_DAY(%1$s) = %1$s and %1$s.Ngay > %5$d)) 
                            AND IFNULL
                                (
                                    TIMESTAMPDIFF(MONTH, date_add(%3$s.NgayBatDau, INTERVAL -day(%3$s.NgayBatDau) DAY) ,%4$s) 
                                    %% %1$s.LapLai, 0
                                ) = 0
                        )
                        OR 
                        (
                            %1$s.KyBaoDuong = \'Y\' 
                            AND (%1$s.Ngay =%5$d or (LAST_DAY(%1$s) = %4$s and %1$s.Ngay > %5$d)) 
                            AND %1$s.Thang =%7$d 
                            AND IFNULL
                                (
                                    TIMESTAMPDIFF(YEAR, date_add(%3$s.NgayBatDau,INTERVAL -day(%3$s.NgayBatDau) DAY) ,%4$s) 
                                    %% %1$s.LapLai,0
                                ) = 0
                        )
                    )
                ) 
                OR 
                (
                    %1$s.CanCu in(1,2)  
                    AND
                    ifnull
                    (
                        (
                            select sum(SoHoatDong) 
                            from ONhatTrinhThietBi 
                            where Ref_MaTB = %3$s.Ref_MaThietBi 
                                and ifnull(Ref_DiemDo,0) = ifnull(%1$s.Ref_ChiSo,0)
                                and Ngay >= ifnull
                                    (
                                        (
                                            select NgayYeuCau 
                                            from OPhieuBaoTri as lastin 
                                            where lastin.Ref_MoTa = %3$s.IOID AND lastin.Ref_ChuKy = %1$s.IOID
                                            order by NgayYeuCau DESC LIMIT 1
                                        )
                                        , %3$s.NgayBatDau
                                    )
                        )
                        ,0
                    )
                    + (DATEDIFF(%4$s,now()) * ifnull(%10$s.SoHoatDongNgay,0)) >= %1$s.GiaTri
                    AND
                    ifnull
                    (   
                        (
                            select sum(SoHoatDong)
                            from ONhatTrinhThietBi
                            where Ref_MaTB = %3$s.Ref_MaThietBi
                                and ifnull(Ref_DiemDo,0) = ifnull(%1$s.Ref_ChiSo,0)
                                and Ngay >= ifnull
                                    (
                                        (
                                            select NgayYeuCau
                                            from OPhieuBaoTri as lastin
                                            where lastin.Ref_MoTa = %3$s.IOID
                                            AND lastin.Ref_ChuKy = %1$s.IOID
                                            order by NgayYeuCau DESC LIMIT 1
                                        )
                                        ,%3$s.NgayBatDau
                                    )
                        )
                        ,0
                    )
                    + (DATEDIFF(%4$s,now()) * ifnull(%10$s.SoHoatDongNgay,0)) %% %1$s.GiaTri < %10$s.SoHoatDongNgay
                )
            )
            AND (%4$s >= %3$s.NgayBatDau OR IFNULL(%3$s.NgayBatDau,"") = "")
            AND (%4$s <= %3$s.NgayKetThuc OR IFNULL(%3$s.NgayKetThuc,"") = "")
            '
            , $chuKyAlias
            , $phieuBaoTriAlias
            , $baoTriDinhKy
            , $date->format('Y-m-d')
            , $date->format('d')
            , $date->format('W')
            , $date->format('m')
            , $date->format('Y')
            , $date->format('w')
            , $diemdo);

        return $sql;
    }

//    /**
//     *  Thiếu biên created đã tạo phiếu bảo trì
//     */
//    public function getPlansByDate($filterdate,$locIOID = 0,$dvbt = 0,$maintainType = 0, $eqGroupIOID=0, $eqTypeIOID=0, $eqIOID=0)
//    {
//        $locName = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):'';
//        $eqTypes = $eqTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', $eqTypeIOID)):'';
//    	$date    = date_create($filterdate);
//        $where   = '';
//    	$where  .= $locName?sprintf('AND IFNULL(dstb.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)', $locName->lft, $locName->rgt):'';
//        $where  .= $eqTypes?sprintf('and dstb.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d) ',$eqTypes->lft, $eqTypes->rgt):'';
//        $where  .= $dvbt?sprintf(' and btdk.Ref_DVBT = %1$d ', $dvbt):'';
//        $where  .= $eqGroupIOID?sprintf(' and dstb.Ref_NhomThietBi = %1$d ', $eqGroupIOID):'';
//        $where  .= $eqIOID?sprintf(' and dstb.IOID = %1$d ', $eqIOID):'';
//        $M125    = $this->getSecureM125Sql('btdk.Ref_DVBT');
//        $M720    = $this->getSecureM720Sql('btdk.Ref_MaThietBi');
//        $byDate  = $this->getfilterPlansByDateSql($filterdate);
//        $where  .= $M125?' AND '.$M125:'';
//        $where  .= $M720?' AND '.$M720:'';
//        $where  .= $byDate;
//
//        $sql = sprintf('
//            SELECT
//	             dstb.*, chuky.*, btdk.*
//	             , btdk.IOID AS PlanIOID
//	            , ifnull(btdk.SoPhut, 0) AS XuLy
//	            , kv1.IOID AS Ref_KhuVuc, dstb.MaKhuVuc AS MaKhuVucTheoDS, kv1.Ten AS TenKhuVucTheoDS
//	            , chuky.IOID as ChuKyIOID
//	            , pbt.SoPhieu
//	            , pbt.NgayYeuCau as NgayYCBT
//	            , TongThe.IOID AS GeneralPlanIOID -- Ke hoach tong the
//	            , TongThe.Ma AS GeneralPlanNo
//	            , ChiTietTongThe.IOID AS GeneralPlanDetailIOID -- Ket noi voi chi tiet mot ke hoach
//            FROM OBaoTriDinhKy AS btdk
//            INNER JOIN OChuKyBaoTri AS chuky ON btdk.IFID_M724 = chuky.IFID_M724
//            LEFT JOIN ODanhSachThietBi AS dstb on dstb.`IOID` = btdk.`Ref_MaThietBi`
//            LEFT JOIN ODanhSachDiemDo as diemdo on diemdo.IFID_M705 = dstb.IFID_M705
//                  AND IFNULL(chuky.CanCu, 0) IN (1,2)  -- Phai la loai dinh ky chi so moi join
//                  AND IFNULL(chuky.Ref_ChiSo, 0) = IFNULL(diemdo.IOID, 0) -- Phai do cung 1 loai chi so  moi join
//                  AND IFNULL(btdk.Ref_BoPhan, 0) = IFNULL(diemdo.Ref_BoPhan, 0) -- Phai cung 1 bo phan moi join
//                  AND IFNULL(chuky.GiaTri, 0) > 0 -- Chỉ số phải có giá trị mới join
//            LEFT JOIN OKhuVuc as kv1 on kv1.IOID = dstb.Ref_MaKhuVuc
//            LEFT JOIN OPhieuBaoTri AS pbt ON
//                pbt.Ref_MoTa = btdk.IOID
//                AND pbt.Ref_ChuKy = chuky.IOID
//                AND ((chuky.KyBaoDuong = \'D\' AND %1$s = pbt.NgayYeuCau)
//                    or (chuky.KyBaoDuong = \'W\' AND %5$d = WEEK(pbt.NgayYeuCau, 3) AND  %7$d = YEAR(pbt.NgayYeuCau))
//                    or (chuky.KyBaoDuong = \'M\' AND %6$d = MONTH(pbt.NgayYeuCau) AND  %7$d = YEAR(pbt.NgayYeuCau))
//                    or (chuky.KyBaoDuong = \'Y\' AND  %7$d = YEAR(pbt.NgayYeuCau)))
//            LEFT JOIN OKeHoachBaoTri AS ChiTietTongThe ON
//                pbt.Ref_MoTa = btdk.IOID
//                AND ChiTietTongThe.Ref_ChuKy = chuky.IOID
//                AND ((chuky.KyBaoDuong = \'D\' AND %1$s = ChiTietTongThe.NgayBatDau)
//                    or (chuky.KyBaoDuong = \'W\' AND %5$d = WEEK(ChiTietTongThe.NgayBatDau, 3) AND  %7$d = YEAR(ChiTietTongThe.NgayBatDau))
//                    or (chuky.KyBaoDuong = \'M\' AND %6$d = MONTH(ChiTietTongThe.NgayBatDau) AND  %7$d = YEAR(ChiTietTongThe.NgayBatDau))
//                    or (chuky.KyBaoDuong = \'Y\' AND  %7$d = YEAR(ChiTietTongThe.NgayBatDau)))
//            LEFT JOIN OKeHoachTongThe AS TongThe ON IFNULL(ChiTietTongThe.Ref_KeHoachTongThe, 0) = TongThe.IOID
//            LEFT JOIN (SELECT kv3.IOID, lft, rgt FROM OKhuVuc AS kv3 WHERE kv3.NgungHoatDong = 1)
//                as khuvucngunghoatdong on kv1.lft >= khuvucngunghoatdong.lft and kv1.rgt <= khuvucngunghoatdong.rgt
//            WHERE
//                khuvucngunghoatdong.IOID is null
//                AND ifnull(dstb.TrangThai , 0) = 0 AND btdk.DeptID in (%3$s) AND ifnull(btdk.NgungHoatDong,0) = 0
//                AND pbt.IOID is null
//                %2$s
//	        ORDER BY case when ifnull(btdk.`Ref_DVBT`, 0) = 0 then 1000000 else btdk.`Ref_DVBT` end, btdk.MaThietBi
//            LIMIT 1000'
//            , $this->_o_DB->quote($filterdate)
//            , $where
//            , $this->_user->user_dept_id . ',' . $this->_user->user_dept_list
//            , $date->format('d')
//            , $date->format('W')
//            , $date->format('m')
//            , $date->format('Y')
//            , $date->format('w')
//            , $this->_user->user_id);
//            // echo '<pre>'; echo $sql;die;
//        return $this->_o_DB->fetchAll($sql);
//    }

    /**
     * Lấy vật tư từ kế hoạch M724 nhóm theo khu vực, thiết bị
     * @in: M768 Viwasupco
     * @param $mSDate
     * @param $mEDate
     * @param int $locIOID
     * @param int $eqGroupIOID
     * @param int $eqTypeIOID
     * @return array
     */
    public function getMaterialsPlansOrderByRootLocationAndEquipName(
        $mSDate
        , $mEDate
        , $locIOID = 0
        , $eqGroupIOID = 0
        , $eqTypeIOID = 0
        , $materialIOID = 0
    ) {
        $tmpStartDate            = date_create($mSDate);
        $tmpEndDate              = date_create($mEDate);
        $mPlan                   = new Qss_Model_Maintenance_Plan();
        $arrMatchEquipsWithPlans = array(); // Nối thiết bị với các chu kỳ của thiết bị qua IFID_M724 và IOID của thiết bị
        $arrIFID_M724            = array(); // Mảng chứa ifid của các kế hoạch
        $arrIFID_M724[]          = 0;       // Thêm một phần tử mặc định để tránh lỗi implode khi query
        $arrEqInfo               = array(); // Mảng chưa thông tin của thiết bị
        $locThietBiKhuVuc        = array(); // Công dồn mỗi vât tư theo thiết bị khu vưc
        $arrSortThietBi          = array(); // Key là IOID, value là trường cần sort theo.
        $retval                  = array(); // Mảng trả về
        $i                       = -1;      // $key của mảng trả về
        $arrMaterials            = array(); // Mảng chứa các vật tư theo kế hoạch $materials[ifid] = array();

        // Hiển thị khu vực theo hình cây, chỉ lấy cây có ref_tructhuoc = 0 (Node root)
        $objLocs = $this->_o_DB->fetchAll(sprintf('SELECT * FROM OKhuVuc WHERE IFNULL(Ref_TrucThuoc, 0) = 0 ORDER BY lft'));

        // Cần lấy kế hoạch theo từng ngày
        while ($tmpStartDate <= $tmpEndDate) {
            $plans = $mPlan->getAllPlansByDate($tmpStartDate->format('Y-m-d'), $locIOID, 0, $eqGroupIOID, $eqTypeIOID);
            $existsPlan = array();

            foreach ($plans as $plan) {
                if(in_array($plan->ChuKyIOID, $existsPlan)) {
                    continue;
                }
                $existsPlan[] = $plan->ChuKyIOID;

                // Ghép nối thiết bị với số lượng chu kỳ của nó qua IFID_M724
                if(!isset($arrMatchEquipsWithPlans[(int)$plan->Ref_MaThietBi])) {
                    $arrMatchEquipsWithPlans[(int)$plan->Ref_MaThietBi] = array(); //Khởi tạo mảng khi chưa tồn tại
                }
                $arrMatchEquipsWithPlans[(int)$plan->Ref_MaThietBi][] = (int)$plan->IFID_M724; // Có thể có nhiều IFID_M724 giống nhau(Nhiều chu kỳ)

                // Dùng để sắp xếp lại mảng theo tên thiết bị
                if(!isset($arrSortThietBi[(int)$plan->Ref_MaThietBi])) {
                    $arrSortThietBi[(int)$plan->Ref_MaThietBi] = $plan->TenThietBi;
                }

                // Lấy mảng IFID để lấy Vật tư đi kèm
                if(!in_array((int)$plan->IFID_M724, $arrIFID_M724)) {
                    $arrIFID_M724[] = (int)$plan->IFID_M724;
                }

                // Lấy thông tin thiết bị
                if(!isset($arrEqInfo[(int)$plan->Ref_MaThietBi])) {
                    $arrEqInfo[(int)$plan->Ref_MaThietBi] = $plan;
                }
            }
            $tmpStartDate = Qss_Lib_Date::add_date($tmpStartDate, 1); // Tăng ngày
        }

        // Lọc theo vật tư
        $whereMaterial = $materialIOID?sprintf(' AND OSanPham.IOID = %1$d ', $materialIOID):'';

        // Lấy ra danh sách vật tư theo IFID để cộng vào thèo IFID sau đó
        $sql = sprintf('
            SELECT OVatTu.*, OSanPham.*, (OVatTu.SoLuong * DonViTinh.HeSoQuyDoi) AS SoLuong
            FROM OVatTu
            INNER JOIN OSanPham ON OVatTu.Ref_MaVatTu = OSanPham.IOID
            INNER JOIN ODonViTinhSP AS DonViTinh ON OSanPham.IFID_M113 = DonViTinh.IFID_M113 AND OVatTu.Ref_DonViTinh = DonViTinh.IOID
            WHERE IFID_M724 IN (%1$s) %2$s
            ORDER BY OVatTu.TenVatTu
        ', implode(',', $arrIFID_M724), $whereMaterial);
        $objMaterials = $this->_o_DB->fetchAll($sql);


        foreach($objMaterials as $material) {
            if(!isset($arrMaterials[$material->IFID_M724])) {
                $arrMaterials[$material->IFID_M724] = array();
            }
            $arrMaterials[$material->IFID_M724][] = $material;
        }

        // Cần sắp xếp lại mảng thiết bị theo tên thiết bị
        asort($arrSortThietBi);

        // Lọc theo khu vực hình cây (Chỉ lấy node root, không trực thuộc node nào) - foreach ($objLocs
        // Sau đó lặp tiếp theo mảng thiết bị đã được sắp xếp theo tên - foreach ($arrSortThietBi
        // Kiểm tra xem thiết bị có nằm trong khu vực đang xét hay không? - if($arrEqInfo[$refEquip]->lft_KhuVuc
        // Nếu có tiền hành lặp tiếp qua các mảng nối giữa thiết bị và các kế hoạch - foreach($arrMatchEquipsWithPlans[$refEquip]
        // Sau đó lặp các vật tư của kế hoạch cộng dồn vật tư theo thiết bị - foreach($arrMaterials[$ifid_M724]
        foreach ($objLocs as $indexLoc=>$loc) { // Sắp xếp theo hình cây khu vực (Node cao nhất, không trực thuộc node nào)
            foreach ($arrSortThietBi as $refEquip=>$equipName) { // Sắp xếp theo tên thiết bị
                // Lấy các thiết bị có trong khu vực đang lặp theo lft rgt của khu vực thiết bị và khu vực đang xét
                if($arrEqInfo[$refEquip]->lft_KhuVuc >= $loc->lft && $arrEqInfo[$refEquip]->rgt_KhuVuc <= $loc->rgt) {
                    // Lấy xem thiết bị tương ứng với các kế hoạch M724 nào để tiếp tục lấy vật tư tương ứng của kế hoạch
                    foreach($arrMatchEquipsWithPlans[$refEquip] as $ifid_M724) {
                        // Sau khi có kế hoạch lấy danh sách vật tư theo kế hoạch, cộng dồn lại theo thiết bị
                        if(isset($arrMaterials[$ifid_M724])) {
                            foreach($arrMaterials[$ifid_M724] as $material) {

                                if(!isset($locThietBiKhuVuc[$refEquip][(int)$material->Ref_MaVatTu])) {
                                    $i++;
                                    $retval[$i] = (object)array_merge((array)$objLocs[$indexLoc], (array)$arrEqInfo[$refEquip], (array)$material);
                                    $retval[$i]->SoLuong = 0;

                                    $locThietBiKhuVuc[$refEquip][(int)$material->Ref_MaVatTu] = 1;
                                }

                                if(@(int)$retval[$i]->Ref_MaVatTu == @(int)$material->Ref_MaVatTu && @(int)$material->Ref_MaVatTu != 0) {
                                    $retval[$i]->SoLuong += $material->SoLuong;
                                }

                            }
                        }
                    }
                }
            }
        }

        // echo '<pre>'; print_r($retval); die;
        return $retval;
    }

    public function getYearReportWithMaterials($year, $locIOID = 0, $eqGroupIOID = 0, $eqTypeIOID = 0, $equipIOID = 0, $workcenter = 0)
    {
        $mSDate           = $year.'-01-01';
        $mEDate           = $year.'-12-31';
        $tmpStartDate     = date_create($mSDate); // Thời gian bắt đầu của kế hoạch
        $tmpEndDate       = date_create($mEDate); // Thời gian kết thúc của kế hoạch
        $mPlan            = new Qss_Model_Maintenance_Plan();
        $arrIFID_M724     = array(); // Mảng chứa ifid của các kế hoạch, dùng để lấy vật tư, công việc và lần bảo trì gần nhất.
        $arrIFID_M724[]   = 0;       // Thêm một phần tử mặc định để tránh lỗi implode khi query
        $retvalBeforeSort = array(); // Mảng trả về trước khi sắp xếp theo các trường yêu cầu
        $retval           = array(); // Mảng trả về
        $i                = 0;       // $key của mảng trả về chưa sắp xếp
        $j                = 0;       // Key của mảng trả về chưa xắp xếp loop lần 2 để sắp xếp
        $arrPlans         = array();
        $arrRowspan       = array();
        $arrTasks         = array(); // Mảng lưu công việc theo IFID M724
        $arrMaterials     = array(); // Mảng lưu vật tư theo IFID M724 và công việc (Lưu ý công việc không bắt buộc)
        $arrLastUpdate    = array(); // Mảng chứa lần bảo trì cuối cùng theo chu kỳ
        $arrRootLocs      = array(); // Mảng chứa khu vực root (không trực thuộc)

        // Lấy ra toàn bộ kế hoạch theo ngày. Xác định kế hoạch trong khoảng thời gian xảy ra trong những tháng nào và trong thời gian đó
        // có bao nhiêu lần xảy ra
        // @todo: Lên đổi theo hướng lấy hết kế hoạch ra rồi cộng kỳ lặp lấy ra ngày đầu tiên rồi tiếp tục lặp ra các lần tiếp theo.
        while ($tmpStartDate <= $tmpEndDate) {
            $plans = $mPlan->getAllPlansByDate($tmpStartDate->format('Y-m-d'), $locIOID, $workcenter, $eqGroupIOID, $eqTypeIOID, $equipIOID);
            foreach ($plans as $plan) {
                if (!isset($arrPlans[$plan->ChuKyIOID])) {
                    $arrPlans[$plan->ChuKyIOID] = $plan;
                    $arrPlans[$plan->ChuKyIOID]->SoLanXayRa = 0;
                }

                // Xác định tháng xảy, số lần xảy ra
                $ThangXayRa = (int)$tmpStartDate->format('m');
                $arrPlans[$plan->ChuKyIOID]->{"Thang" . $ThangXayRa} = 'v';
                $arrPlans[$plan->ChuKyIOID]->SoLanXayRa += 1;

                // Lây danh ifid kế hoạch
                if (!in_array($plan->IFID_M724, $arrIFID_M724)) {
                    $arrIFID_M724[] = $plan->IFID_M724;
                }
            }
            $tmpStartDate = Qss_Lib_Date::add_date($tmpStartDate, 1); // Tăng ngày
        }

        // @todo: Cach nay thieu truong hop chi so
//        $plans = $mPlan->getActivePlans($locIOID, $eqTypeIOID, $eqGroupIOID, 0, $equipIOID, $workcenter);
//
//        foreach ($plans as $plan) {
//
//            if($plan->NgayBatDau) {
//                // Lấy ra ngày xảy ra đầu tiên trong năm đang xét
//                $firstOfYear  = $plan->NgayBatDau;
//                while (true) {
//                    if(date('Y', strtotime($firstOfYear)) >= $year) {
//                        break;
//                    }
//
//                    if($plan->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_DAILY) {
//                        $firstOfYear = date("Y-m-d", strtotime("+".(int)$plan->LapLai." weeks", strtotime($firstOfYear)));
//                    }
//                    elseif($plan->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY) {
//                        $firstOfYear = date("Y-m-d", strtotime("+".(int)$plan->LapLai." weeks", strtotime($firstOfYear)));
//                    }
//                    elseif($plan->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY) {
//                        $firstOfYear = date("Y-m-d", strtotime("+".(int)$plan->LapLai." months", strtotime($firstOfYear)));
//                    }
//                    elseif($plan->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY) {
//                        $firstOfYear = date("Y-m-d", strtotime("+".(int)$plan->LapLai." years", strtotime($firstOfYear)));
//                    }
//                }
//            }
//
//            // Chỉ lấy các chu kỳ xảy ra trong năm nay
//            if(date('Y', strtotime($firstOfYear)) == $year) {
//                if (!isset($arrPlans[$plan->ChuKyIOID])) {
//                    $arrPlans[$plan->ChuKyIOID] = $plan;
//                    $arrPlans[$plan->ChuKyIOID]->SoLanXayRa = 0;
//                }
//
//                while (true) {
//                    if($plan->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_DAILY) {
//                        $firstOfYear = date("Y-m-d", strtotime("+".(int)$plan->LapLai." weeks", strtotime($firstOfYear)));
//                    }
//                    elseif($plan->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY) {
//                        $firstOfYear = date("Y-m-d", strtotime("+".(int)$plan->LapLai." weeks", strtotime($firstOfYear)));
//                    }
//                    elseif($plan->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY) {
//                        $firstOfYear = date("Y-m-d", strtotime("+".(int)$plan->LapLai." months", strtotime($firstOfYear)));
//                    }
//                    elseif($plan->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY) {
//                        $firstOfYear = date("Y-m-d", strtotime("+".(int)$plan->LapLai." years", strtotime($firstOfYear)));
//                    }
//
//                    $ThangXayRa = (int)date('m', strtotime($firstOfYear));
//                    $arrPlans[$plan->ChuKyIOID]->{"Thang" . $ThangXayRa} = 'v';
//                    $arrPlans[$plan->ChuKyIOID]->SoLanXayRa += 1;
//
//                    // Lứu ý đoạn break này phải ớ dưới vì luôn lấy ngày đầu tiên
//                    if(date('Y', strtotime($firstOfYear)) >= $year) {
//                        break;
//                    }
//                }
//
//                // Xác định tháng xảy, số lần xảy ra
//                $ThangXayRa = (int)$tmpStartDate->format('m');
//                $arrPlans[$plan->ChuKyIOID]->{"Thang" . $ThangXayRa} = 'v';
//                $arrPlans[$plan->ChuKyIOID]->SoLanXayRa += 1;
//
//                // Lây danh ifid kế hoạch
//                if (!in_array($plan->IFID_M724, $arrIFID_M724)) {
//                    $arrIFID_M724[] = $plan->IFID_M724;
//                }
//            }
//        }

        $strIFIDs = implode(',', $arrIFID_M724); // Chuỗi ifid m724 để truyền vào sql

        // Lấy  công việc và vật tư theo kế hoạch M724
        $objTasks = $this->_o_DB->fetchAll(sprintf('SELECT *, IOID AS TaskIOID FROM OCongViecBT WHERE IFID_M724 IN(%1$s) ORDER BY MoTa', $strIFIDs));
        $objMaterials = $this->_o_DB->fetchAll(sprintf('SELECT *, Ref_CongViec AS TaskIOID FROM OVatTu WHERE IFID_M724 IN(%1$s) ORDER BY TenVatTu', $strIFIDs));

        // Lấy các phiếu bảo trì đã đóng hoặc hoàn thành cuối cùng tương tứng với chu kỳ.
        // @todo: Nên tách ra một hàm riêng để viết chung cho nhiều trường hợp
        $lastUpdate   = $this->_o_DB->fetchAll(sprintf('
            SELECT *
            FROM
            (
                SELECT  OChuKyBaoTri.IOID, OPhieuBaoTri.NgayBatDau
                FROM OChuKyBaoTri 
                INNER JOIN OPhieuBaoTri ON OChuKyBaoTri.IOID = OPhieuBaoTri.Ref_ChuKy
                INNER JOIN qsiforms ON OPhieuBaoTri.IFID_M759 = qsiforms.IFID            
                WHERE OChuKyBaoTri.IFID_M724 IN(%1$s) AND qsiforms.Status IN (3, 4)
                ORDER BY OChuKyBaoTri.IOID, OPhieuBaoTri.NgayBatDau DESC
                LIMIT 18446744073709551615
            ) AS PhieuBaoTri
            GROUP BY IOID
        ', $strIFIDs));

        // Lấy danh sách khu vưc theo dạng hình cây chỉ có node root (Không trực thuộc)
        // @todo: Lên viết ra thành hàm riêng để viết chung cho nhiều trường hợp
        $objRootLocs = $this->_o_DB->fetchAll('SELECT * FROM OKhuVuc WHERE IFNULL(Ref_TrucThuoc, 0) = 0 ORDER BY lft');

        foreach ($objTasks as $task) { // Công việc
            $arrTasks[$task->IFID_M724][] = $task;
        }

        foreach ($objMaterials as $material) { // Vật tư
            $arrMaterials[$material->IFID_M724][(int)$material->Ref_CongViec][(int)$material->IOID] = $material;
        }

        foreach($lastUpdate as $last) { // Lần bảo trì cuối cùng
            $arrLastUpdate[$last->IOID] = $last->NgayBatDau;
        }

        // Gắn kế hoạch với công việc và vật tư tương ứng
        foreach ($arrPlans as $plan) {
            $intSoDongVatTu = 0; // reset, để cập nhật số dòng ở rowspan đầu tiên (Thiết bị)

            if (!isset($arrRowspan['Equip'][@(int)$plan->Ref_MaThietBi])) {
                $arrRowspan['Equip'][@(int)$plan->Ref_MaThietBi] = 0;
            }

            // Trường hợp vật tư có công việc
            foreach (@$arrTasks[$plan->IFID_M724] as $task) {
                $intSoDongVatTuTungCongViec = 0; // reset
                $first = true; // Dùng để cập nhật lại dòng đầu tiên nếu có vật tư đi kèm công việc

                // Đây là dòng đầu tiên nếu công việc không có vật tư nào kèm theo
                // Dòng này sẽ được cập nhật lại thông tin vật tư nếu có vật tư đi kèm
                $copyplan               = clone($plan);
                $copyplan->TaskIOID     = (int)$task->IOID;
                $copyplan->CongViec     = $task->MoTa;
                $copyplan->MaterialIOID = 0;
                $copyplan->MaVatTu      = '';
                $copyplan->TenVatTu     = '';
                $copyplan->DonViTinh    = '';
                $copyplan->SoLuong      = '';
                $retvalBeforeSort[$i]   = $copyplan;
                $intSoDongVatTu++;
                $intSoDongVatTuTungCongViec++;
                $i++;

                // Nếu tồn tại vật tư theo công việc thì sẽ loop để ghép công việc với vật tư
                // CV1 VT1, CV2 VT2
                // Sẽ cập nhật lại dòng đầu tiên đã ghi ở trên với trường hợp không có vật tư theo công việc
                // Lưu ý: ở đây sẽ bỏ qua những vật tư không có công việc sang phần dưới
                if (isset($arrMaterials[$plan->IFID_M724][(int)$task->IOID])) {
                    foreach ($arrMaterials[$plan->IFID_M724][(int)$task->IOID] as $material) {
                        if ($first == true) {
                            $first = false;
                            $i     = $i - 1; // Do trước đó tăng lên 1 cho trường hợp không có vật tư, nếu có vật tư cần phải quay lại số trước
                            $copyplan->MaterialIOID = $material->IOID;
                            $copyplan->MaVatTu      = $material->MaVatTu;
                            $copyplan->TenVatTu     = $material->TenVatTu;
                            $copyplan->DonViTinh    = $material->DonViTinh;
                            $copyplan->SoLuong      = $material->SoLuong;
                            $i++; // Ở đây không cần đếm rowspan $intSoDongVatTu và $intSoDongVatTuTungCongViec vì dòng đầu tiên đã đếm rồi
                        } else { // Ghép dữ liệu và tiếp tục đếm rowspan
                            $copyplan               = clone($plan);
                            $copyplan->TaskIOID     = (int)$material->Ref_CongViec;
                            $copyplan->CongViec     = $material->CongViec;
                            $copyplan->MaterialIOID = $material->IOID;
                            $copyplan->MaVatTu      = $material->MaVatTu;
                            $copyplan->TenVatTu     = $material->TenVatTu;
                            $copyplan->DonViTinh    = $material->DonViTinh;
                            $copyplan->SoLuong      = $material->SoLuong;
                            $retvalBeforeSort[$i]   = $copyplan;
                            $intSoDongVatTu++;
                            $intSoDongVatTuTungCongViec++;
                            $i++;
                        }
                    }
                }

                // Khởi tạo đếm số lượng vật tư của công việc, nếu không có vật tư thì sẽ là 1 dòng
                if (!isset($arrRowspan['Task'][@(int)$task->IOID])) {
                    $arrRowspan['Task'][@(int)$task->IOID] = 0;
                }

                // Đếm rowspan đếm số vật tư theo một công việc, không có vật tư thì sẽ là 1 dòng
                $arrRowspan['Task'][@(int)$task->IOID] += ($intSoDongVatTuTungCongViec ? $intSoDongVatTuTungCongViec : 1);
            }

            // Trường hợp các vật tư không có công việc
            $intSoDongVatTuTungCongViec = 0; // reset lại để đếm tiếp theo công việc nhưng vì không có công việc nên ID = 0
            if (isset($arrMaterials[$plan->IFID_M724][0])) {
                // Ở trường hợp này công việc không tồn tại lên id và nội dung bằng rỗng
                foreach ($arrMaterials[$plan->IFID_M724][0] as $material) {
                    $copyplan               = clone($plan);
                    $copyplan->TaskIOID     = 0;
                    $copyplan->CongViec     = '';
                    $copyplan->MaterialIOID = $material->IOID;
                    $copyplan->MaVatTu      = $material->MaVatTu;
                    $copyplan->TenVatTu     = $material->TenVatTu;
                    $copyplan->DonViTinh    = $material->DonViTinh;
                    $copyplan->SoLuong      = $material->SoLuong;
                    $retvalBeforeSort[$i]   = $copyplan;
                    $intSoDongVatTu++;
                    $intSoDongVatTuTungCongViec++;
                    $i++;
                }

                // Đếm số lượng rowspan của ô khi ko có công việc chỉ có vật tư
                if (!isset($arrRowspan['Task'][0])) {
                    $arrRowspan['Task'][0] = 0;
                }

                $arrRowspan['Task'][0] += ($intSoDongVatTuTungCongViec ? $intSoDongVatTuTungCongViec : 1);
            }

            // Không có vật tư lẫn công việc
            if (!isset($arrMaterials[$plan->IFID_M724]) && !isset($arrTasks[$plan->IFID_M724])) {
                $copyplan               = clone($plan);
                $copyplan->TaskIOID     = 0;
                $copyplan->CongViec     = $plan->MoTa;
                $copyplan->MaterialIOID = 0;
                $copyplan->MaVatTu      = '';
                $copyplan->TenVatTu     = '';
                $copyplan->DonViTinh    = '';
                $copyplan->SoLuong      = '';
                $retvalBeforeSort[$i]   = $copyplan;
                // $intSoDongVatTu++; // Đã cộng ở trên
                // $intSoDongVatTuTungCongViec++; // Không có vật tư cần cộng
                $i++;
            }

            // Đêm rowspan theo thiết bị, lưu ý lúc này thiết bị chưa được sắp xếp cần cộng dồn theo kế hoạch
            $arrRowspan['Equip'][@(int)$plan->Ref_MaThietBi] += ($intSoDongVatTu ? $intSoDongVatTu : 1);
        }

        foreach($retvalBeforeSort as $index=>$plan) {
            $sortKey  = (string)((int)$plan->lft_KhuVuc.'-'.$plan->TenThietBi.'-'.(int)$plan->ChuKyIOID.'-'.$plan->CongViec.'-'. $plan->TenVatTu.'-'.$plan->IFID_M724);
            $copyplan = clone($plan);
            $copyplan->RowspanByEquip    = @(int)$arrRowspan['Equip'][@(int)$plan->Ref_MaThietBi];
            $copyplan->RowspanByTask     = @(int)$arrRowspan['Task'][@(int)$plan->TaskIOID]?@(int)$arrRowspan['Task'][@(int)$plan->TaskIOID]:1;
            $copyplan->NgayBaoTriLanCuoi = @$arrLastUpdate[$copyplan->ChuKyIOID];

            foreach ($objRootLocs as $loc) {
                if($copyplan->lft_KhuVuc >= $loc->lft && $copyplan->lft_KhuVuc <= $loc->rgt) {
                    $copyplan->MaKhuVuc  = $loc->MaKhuVuc;
                    $copyplan->TenKhuVuc = $loc->Ten;
                }
            }

            $retval[$sortKey]            = $copyplan; // Cho vào mảng sắp xếp, sử dung ksort để sắp xếp lại mảng theo key truyền vào
        }

        ksort($retval);

        // echo '<pre>'; print_r($retval); die;

        return $retval;
    }
}