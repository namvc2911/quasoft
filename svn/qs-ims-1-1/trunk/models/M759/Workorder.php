<?php

/**
 * Class Qss_Model_M759_Workorder
 * 1. getClosedUnbrokenByEquip($eqID) Lấy các phiếu bảo trì không phải sự cố theo thiết bị.
 * 2. countWorkOrdersByStatus($start = '' , $end = '', $workcenter = 0, $employee = 0)
 * Đếm phiếu bảo trì theo tình trạng của phiếu bảo trì
 */
class Qss_Model_M759_Workorder extends Qss_Model_Abstract
{
    /**
     * Chọn tất cả phiếu đã đóng với loại bảo trì không phải là loại sự cố theo một thiết bị (Không phân biệt phòng ban)
     * @param $eqID
     * @return mixed
     */
    public function getClosedUnbrokenByEquip($eqID)
    {
        $sql = sprintf('
			SELECT
				pbt.*, CauTruc.ViTri, CauTruc.BoPhan  
			FROM OPhieuBaoTri AS pbt
			INNER JOIN qsiforms AS qsiforms ON qsiforms.IFID = pbt.IFID_M759
			LEFT JOIN OCauTrucThietBi AS CauTruc ON IFNULL(pbt.Ref_BoPhan, 0) = CauTruc.IOID
			LEFT JOIN OPhanLoaiBaoTri AS plbt ON plbt.IOID = pbt.Ref_LoaiBaoTri
			LEFT JOIN qsworkflows AS qsw ON qsw.FormCode = qsiforms.FormCode
			LEFT JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND qsiforms.Status = qsws.StepNo
			WHERE IFNULL(qsw.Actived, 0) = 1 
			    -- AND qsiforms.Status = 4  
			    and pbt.Ref_MaThietBi = %1$d 
			    and plbt.LoaiBaoTri != "%2$s"'
            /* ORDER BY */
            , $eqID, Qss_Lib_Extra_Const::MAINT_TYPE_BREAKDOWN);
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Hàm tương tự: \models\Maintenance\Workorder.php countWorkOrdersByStatus($start = '' , $end = '')
     * - Đếm số lượng bản ghi trong từng step (CountStep1)
     * - Đếm số lượng bản ghi ở step hoàn thành 3 hoặc đóng 4 có ngày kết thúc lớn hơn dự kiến (CountOver) và ngược lại (CountDue)
     * @param string $start
     * @param string $end
     * @return mixed
     */
    public function countWorkOrdersByStatus($start = '' , $end = '', $workcenter = 0, $employee = 0) {
        $where = '';
        // Lọc theo thời gian truyền vào
        if($start && $end) {
            $where .= sprintf(' AND (PhieuBaoTri.NgayBatDau BETWEEN %1$s AND %2$s)'
                , $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        }
        elseif($start) {
            $where .= sprintf(' AND PhieuBaoTri.NgayBatDau >= %1$s', $this->_o_DB->quote($start));
        }
        elseif($end) {
            $where .= sprintf(' AND PhieuBaoTri.NgayBatDau <= %1$s', $this->_o_DB->quote($end));
        }

        // Lọc theo đơn vị làm việc
        if($workcenter) {
            $where .= sprintf(' AND IFNULL(PhieuBaoTri.Ref_MaDVBT, 0) = %1$d ', $workcenter);
        }

        // Lọc theo nhân viên thực hiện
        if($employee) {
            $where .= sprintf(' AND IFNULL(PhieuBaoTri.Ref_NguoiThucHien, 0) = %1$d ', $employee);
        }

        $sql = sprintf('
            SELECT
                SUM(CASE WHEN ifnull(iform.Status, 0) = 1 THEN 1 ELSE 0 END) AS CountStep1
                , SUM(CASE WHEN ifnull(iform.Status, 0) = 2 THEN 1 ELSE 0 END) AS CountStep2
                , SUM(CASE WHEN ifnull(iform.Status, 0) = 3 THEN 1 ELSE 0 END) AS CountStep3
                , SUM(CASE WHEN ifnull(iform.Status, 0) = 4 THEN 1 ELSE 0 END) AS CountStep4
                , SUM(CASE WHEN ifnull(iform.Status, 0) = 5 THEN 1 ELSE 0 END) AS CountStep5
                , SUM(CASE WHEN ifnull(iform.Status, 0) = 6 THEN 1 ELSE 0 END) AS CountStep6
                , SUM(CASE WHEN ifnull(iform.Status, 0) = 7 THEN 1 ELSE 0 END) AS CountStep7
                , SUM(CASE WHEN ifnull(iform.Status, 0) = 8 THEN 1 ELSE 0 END) AS CountStep8
                , SUM(CASE WHEN ifnull(iform.Status, 0) = 9 THEN 1 ELSE 0 END) AS CountStep9
                , SUM(
                    CASE WHEN ifnull(iform.Status, 0) IN(3, 4) AND PhieuBaoTri.Ngay > PhieuBaoTri.NgayDuKienHoanThanh THEN 1 ELSE 0 END
                ) AS CountOver
                , SUM(
                    CASE WHEN ifnull(iform.Status, 0) IN(3, 4) AND PhieuBaoTri.Ngay <= PhieuBaoTri.NgayDuKienHoanThanh THEN 1 ELSE 0 END
                ) AS CountDue
            FROM OPhieuBaoTri AS PhieuBaoTri
            INNER JOIN qsiforms AS iform ON iform.IFID = PhieuBaoTri.IFID_M759
            WHERE 1=1 %1$s
            ', $where);
        return $this->_o_DB->fetchOne($sql);
    }


    public function getDetailWorkorderForM779(
        $start = ''
        , $end = ''
        , $locIOID = 0
        , $eqIOID = 0
        , $workcenter = 0
        , $orderByLocation = false) {
        $arrNoiCongViecVoiVatTuDK = array();
        $arrNoiCongViecVoiVatTuDK2= array();
        $arrNoiCongViecVoiVatTu   = array();
        $arrNoiCongViecVoiVatTu2  = array();
        $countByDate              = array();
        $countByDate2             = array(); // Nếu ở tình trạng 1 hoặc 2 thì không tính theo công việc mà chỉ cộng 1
        $arrIFIDs                 = array();
        $arrIFIDs[]               = 0; // Cho 1 phần tử để tránh lỗi trắng
        $oldNgayDuKien            = '';
        $oldKhuVuc                = ''; // Chỉ tính khi $orderByLocation = true
        $oldPhieuBaoTri           = '';
        $extSelect                = '';
        $extJoin                  = '';
        $extOrder                 = '';
        $where                    = '';

        $where .= ($start && $end)?sprintf(' AND (PhieuBaoTri.NgayBatDauDuKien >= %1$s and PhieuBaoTri.NgayBatDauDuKien <= %2$s)', $this->_o_DB->quote($start), $this->_o_DB->quote($end)):'';
        $loc    = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):'';
        $where .= $loc?sprintf(
            ' 
            AND (
                (IFNULL(ThietBi.Ref_MaKhuVuc, 0) IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))
                OR (IFNULL(PhieuBaoTri.Ref_MaKhuVuc, 0) IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))
            )'
            , $loc->lft, $loc->rgt):'';
        $where .= ($eqIOID)?sprintf(' AND ThietBi.IOID = %1$d  ', $eqIOID):'';
        $wc     = $workcenter?$this->_o_DB->fetchOne(sprintf('SELECT * FROM ODonViSanXuat WHERE IOID = %1$d', $workcenter)):'';
        $where .= $wc?sprintf(' AND (PhieuBaoTri.Ref_MaDVBT IN (select IOID from ODonViSanXuat where lft>= %1$d and  rgt <= %2$d))', $wc->lft, $wc->rgt):'';

        $extSelect.= Qss_Lib_System::fieldActive('OCongViecBTPBT', 'Ngay')?', CongViec.Ngay AS NgayCongViecThucTe':',null AS NgayCongViecThucTe';
        $extSelect.= Qss_Lib_System::fieldActive('OCongViecBTPBT', 'NguoiThucHien')?', CongViec.NguoiThucHien AS NguoiThucHienCongViec':',null AS AS NguoiThucHienCongViec';
        $extSelect.= Qss_Lib_System::fieldActive('OCongViecBTPBT', 'NguoiThucHien')?', CongViec.GhiChu AS GhiChuCongViec':',null AS AS GhiChuCongViec';

        if($orderByLocation) {
            $extSelect .= sprintf(' , IF(IFNULL(KhuVucThietBi.IOID, 0) = 0, KhuVucPhieu.MaKhuVuc, KhuVucThietBi.MaKhuVuc) AS MaKhuVucSapXep');
            $extSelect .= sprintf(' , IF(IFNULL(KhuVucThietBi.IOID, 0) = 0, KhuVucPhieu.Ten, KhuVucThietBi.Ten) AS TenKhuVucSapXep');
            $extSelect .= sprintf(' , IF(IFNULL(KhuVucThietBi.IOID, 0) = 0, KhuVucPhieu.lft, KhuVucThietBi.lft) AS LftKhuVucSapXep');



            $extJoin .= sprintf(' LEFT JOIN OKhuVuc AS KhuVucThietBi ON ThietBi.Ref_MaKhuVuc = KhuVucThietBi.IOID');
            $extJoin .= sprintf(' LEFT JOIN OKhuVuc AS KhuVucPhieu ON PhieuBaoTri.Ref_MaKhuVuc = KhuVucPhieu.IOID');

            $extOrder .= sprintf(' IF(IFNULL(KhuVucThietBi.IOID, 0) = 0, KhuVucPhieu.lft, KhuVucThietBi.lft) ,');
        }
        else {
            $extSelect .= sprintf(' , NULL AS MaKhuVucSapXep');
            $extSelect .= sprintf(' , NULL AS TenKhuVucSapXep');
        }

        $orderBySTTTHietBi = '';

        if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'STT')) {
            $orderBySTTTHietBi = ' , ThietBi.STT ';
        }

        // Lấy phiếu bảo trì kèm công việc xắp xếp theo ngày yêu cầu, số phiếu và nội dung công việc trong chi tiết
        $sqlCongViecPhieu = sprintf('
            SELECT 
                PhieuBaoTri.*
                , CongViec.ViTri
                , CongViec.BoPhan
                , CongViec.MoTa AS MoTaCongViec
                , IFNULL(CongViec.IOID, 0) AS CongViecIOID
                , IFNULL(CongViec.Ref_ViTri, 0) AS Ref_ViTri
                %2$s -- Bao gom ma ten va khu vuc
                , IForm.Status
                , IF(IFNULL(KhuVucThietBi.IOID, 0) = 0, KhuVucPhieu.MaKhuVuc, KhuVucThietBi.MaKhuVuc) AS MaKhuVuc
                , IF(IFNULL(KhuVucThietBi.IOID, 0) = 0, KhuVucPhieu.Ten, KhuVucThietBi.Ten) AS TenKhuVuc
            FROM OPhieuBaoTri AS PhieuBaoTri
            INNER JOIN qsiforms AS IForm ON PhieuBaoTri.IFID_M759 = IForm.IFID
            LEFT JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
            %3$s -- join voi khu vuc phieu va khu vuc thiet bi            
            LEFT JOIN OCongViecBTPBT AS CongViec ON PhieuBaoTri.IFID_M759 = CongViec.IFID_M759
            LEFT JOIN OCauTrucThietBi AS CauTruc ON CongViec.Ref_ViTri = CauTruc.IOID
            WHERE 1=1 %1$s -- AND PhieuBaoTri.NgayBatDauDuKien = "2017-11-17"
            ORDER BY 
                %4$s -- order theo lft cua khu vuc, muc dich de nhom ngay du kien roi nhom tiep khu vuc
                PhieuBaoTri.NgayBatDauDuKien
                %5$s
                , PhieuBaoTri.SoPhieu                
                , CauTruc.lft
                , CongViec.MoTa            
        ', $where, $extSelect, $extJoin, $extOrder, $orderBySTTTHietBi);
        $objCongViecPhieu = $this->_o_DB->fetchAll($sqlCongViecPhieu);

        foreach($objCongViecPhieu as $item) {
            if($orderByLocation) {
                $key = $item->LftKhuVucSapXep . '_' . $item->NgayBatDauDuKien;
            }
            else {
                $key = $item->NgayBatDauDuKien;
            }

            if($item->IFID_M759) {
                if(!isset($countByDate[$key])) {
                    $countByDate[$key] = 0;
                }

                if(!isset($countByDate2[$key])) {
                    $countByDate2[$key] = 0;
                }

                // Khi số phiếu thay đổi thì cộng thêm 1 với trường hợp hoàn thành
                // Ngược lại cộng thêm 2 tương ứng chỉ in ra 1 dòng cho trường hợp chưa hoàn thành
                if($oldPhieuBaoTri != $item->SoPhieu) {
                    $countByDate[$key]++; // Cộng 1 dòng cho phiếu bảo trì.

                    if(in_array($item->Status, array(1, 2))) {
                        $countByDate2[$key] += 2;
                    }
                    else {
                        $countByDate2[$key]++;
                    }
                }

                $countByDate[$key]++; // Cộng 1 dòng cho công việc (Hoặc 1 phiếu bảo trì nếu ko có công việc đi kèm)

                // Nếu không ở trong tình trạng ban hành hoặc soạn thảo thì cộng liên tục
                if(!in_array($item->Status, array(1, 2))) {
                    $countByDate2[$key]++;
                }

                $arrIFIDs[] = $item->IFID_M759;
                $oldPhieuBaoTri = $item->SoPhieu;
            }
         }

        // Lấy vật tư dự kiến theo phiếu bảo trì
        if(Qss_Lib_System::objectInForm('M759', 'OVatTuPBTDK')) {
            $sqlVatTuDuKien = sprintf('
                SELECT *, GROUP_CONCAT(CONCAT("- ", TenVatTu, " (", MaVatTu, "): ", SoLuong, " ", DonViTinh, "<br/>") SEPARATOR ""  ) AS VatTuGroup
                FROM OVatTuPBTDK AS VatTu
                WHERE IFID_M759 IN (%1$s)
                GROUP BY Ref_ViTri, Ref_CongViec
          ', implode(',', $arrIFIDs));
            $objVatTuDuKien = $this->_o_DB->fetchAll($sqlVatTuDuKien);

            foreach ($objVatTuDuKien as $item) {
                if(!isset($arrNoiCongViecVoiVatTuDK[(int)$item->Ref_ViTri])) {
                    $arrNoiCongViecVoiVatTuDK[(int)$item->Ref_ViTri] = array();
                }

                if(!isset($arrNoiCongViecVoiVatTu[(int)$item->Ref_ViTri][(int)$item->Ref_CongViec])) {
                    $arrNoiCongViecVoiVatTuDK[(int)$item->Ref_ViTri][(int)$item->Ref_CongViec] = array();
                }

                $arrNoiCongViecVoiVatTuDK[(int)$item->Ref_ViTri][(int)$item->Ref_CongViec] = $item->VatTuGroup;
                $arrNoiCongViecVoiVatTuDK2[(int)$item->IFID_M759][] = $item->VatTuGroup;
            }
        }

        // Lấy vật tư thực tế theo phiếu bảo trì
        if(Qss_Lib_System::objectInForm('M759', 'OVatTuPBT')) {
            $sqlVatTu = sprintf('
                SELECT *, GROUP_CONCAT("- ", TenVatTu, " (", MaVatTu, "): ", SoLuong, " ", DonViTinh, "" SEPARATOR "<br/>" ) AS VatTuGroup
                FROM OVatTuPBT AS VatTu
                WHERE IFID_M759 IN (%1$s)
                GROUP BY VatTu.Ref_ViTri, VatTu.Ref_CongViec
          ', implode(',', $arrIFIDs));

            $objVatTu = $this->_o_DB->fetchAll($sqlVatTu);

            foreach ($objVatTu as $item) {
                if(!isset($arrNoiCongViecVoiVatTu[(int)$item->Ref_ViTri])) {
                    $arrNoiCongViecVoiVatTu[(int)$item->Ref_ViTri] = array();
                }

                if(!isset($arrNoiCongViecVoiVatTu[(int)$item->Ref_ViTri][(int)$item->Ref_CongViec])) {
                    $arrNoiCongViecVoiVatTu[(int)$item->Ref_ViTri][(int)$item->Ref_CongViec] = array();
                }

                $arrNoiCongViecVoiVatTu[(int)$item->Ref_ViTri][(int)$item->Ref_CongViec] = $item->VatTuGroup;
                $arrNoiCongViecVoiVatTu2[(int)$item->IFID_M759][] = $item->VatTuGroup;
            }
        }

        // echo '<pre>'; print_r($arrNoiCongViecVoiVatTuDK2); die;

        // Gắn vật tư vào phiếu, công việc, vị trí tương ứng
        foreach($objCongViecPhieu as $key=>$item) {
            if($orderByLocation) {
                $key2 = $item->LftKhuVucSapXep . '_' . $item->NgayBatDauDuKien;
            }
            else {
                $key2 = $item->NgayBatDauDuKien;
            }


            $objCongViecPhieu[$key]->VatTuDuKien  = '';
            $objCongViecPhieu[$key]->VatTuThucTe  = '';
            $objCongViecPhieu[$key]->ToanBoVatTuDuKien  = '';
            $objCongViecPhieu[$key]->ToanBoVatTuThucTe  = '';
            $objCongViecPhieu[$key]->RowspanNgay  = isset($countByDate[$key2])?(int)$countByDate[$key2]:0;
            $objCongViecPhieu[$key]->RowspanNgay2 = isset($countByDate2[$key2])?(int)$countByDate2[$key2]:0;

            if(isset($arrNoiCongViecVoiVatTuDK[$item->Ref_ViTri][$item->CongViecIOID])) {
                $objCongViecPhieu[$key]->VatTuDuKien = $arrNoiCongViecVoiVatTuDK[$item->Ref_ViTri][$item->CongViecIOID];
            }

            if(isset($arrNoiCongViecVoiVatTu[$item->Ref_ViTri][$item->CongViecIOID])) {
                $objCongViecPhieu[$key]->VatTuThucTe = $arrNoiCongViecVoiVatTu[$item->Ref_ViTri][$item->CongViecIOID];
            }

            if(isset($arrNoiCongViecVoiVatTuDK2[$item->IFID_M759]) && count($arrNoiCongViecVoiVatTuDK2[$item->IFID_M759])) {
                $objCongViecPhieu[$key]->ToanBoVatTuDuKien = implode('<br/>', $arrNoiCongViecVoiVatTuDK2[$item->IFID_M759]);
            }

            if(isset($arrNoiCongViecVoiVatTu2[$item->IFID_M759]) && count($arrNoiCongViecVoiVatTu2[$item->IFID_M759])) {
                $objCongViecPhieu[$key]->ToanBoVatTuThucTe = implode('<br/>', $arrNoiCongViecVoiVatTu2[$item->IFID_M759]);
            }
        }

        // echo '<pre>'; print_r($objCongViecPhieu); die;
        return $objCongViecPhieu;
    }
}