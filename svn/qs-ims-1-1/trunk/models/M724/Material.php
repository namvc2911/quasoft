<?php

class Qss_Model_M724_Material extends Qss_Model_Abstract {

    /**
     * Hàm lấy vật tư của kê hoạch theo khoảng thời gian với số lượng cộng tổng lại theo từng vật tư.
     * @param $start
     * @param $end
     * @param $locIOID
     * @param $workcenterIOID
     * @param $equipGroupIOID
     * @param $equipTypeIOID
     * @param $eqIOID
     */
    public function getTotalMaterialsOfM724(
        $start
        , $end
        , $locIOID        = 0
        , $equipGroupIOID = 0
        , $equipTypeIOID  = 0
        , $eqIOID         = 0
    ) {
        $mPlan       = new Qss_Model_Maintenance_Plan();
        $retval      = array();
        $countByIFID = array();
        $planIFIDs   = array();
        $mStart      = date_create($start);
        $mEnd        = date_create($end);

        while ($mStart <= $mEnd) {
            $plans  = $mPlan->getAllPlansByDate($mStart->format('Y-m-d'), $locIOID, 0, $equipGroupIOID, $equipTypeIOID, $eqIOID);
            $existsPlan = array();

            foreach($plans as $item) {
                if(in_array($item->ChuKyIOID, $existsPlan)) {
                    continue;
                }
                $existsPlan[] = $item->ChuKyIOID;

                $planIFIDs[] = $item->IFID_M724; // Gắn mảng IFID

                if(!isset($countByIFID[$item->IFID_M724])) { // Số lần IFID xảy ra để nhân với số lượng vật tư
                    $countByIFID[$item->IFID_M724] = 0;
                }
                $countByIFID[$item->IFID_M724]++;
            }

            $mStart = Qss_Lib_Date::add_date($mStart, 1); // Tăng ngày
        }

        $materials = $this->layVatTuDaQuyDoiSoLuongTheoIFIDs($planIFIDs);


        foreach ($materials as $item) {
            if($item->MaTam) {
                $key = (int)$item->Ref_MaVatTu .'-' . $item->TenVatTu .'-' .(int)$item->Ref_DonViTinh;
            }
            else {
                $key = (int)$item->Ref_MaVatTu;
            }

            if(!isset($retval[$item->Ref_MaVatTu])) {
                $temp = clone($item);
                $retval[$key] = $temp;
                $retval[$key]->DonViTinh = $item->DonViTinhCoSo; // Đoạn này gán thế này vì hàm còn có thể dùng cho mục đích khác
                $retval[$key]->SoLuong   = 0;
            }

            $retval[$key]->SoLuong += $item->SoLuongQuyDoi * @(int)$countByIFID[$item->IFID_M724];
        }

        return $retval;
    }

    /**
     * @param $planIFIDArr
     * @return mixed
     */
    public function layVatTuDaQuyDoiSoLuongTheoIFIDs($planIFIDs)
    {
        $planIFIDs[] = 0;

        $sql = sprintf('
			SELECT 
			    VatTu.*
			    , MatHang.MaTam
			    , IFNULL(VatTu.SoLuong, 0) AS SoLuong
			    , IFNULL(VatTu.SoLuong, 0) * IFNULL(DonViTinh.HeSoQuyDoi, 0) AS SoLuongQuyDoi 	
                , MatHang.DonViTinh AS DonViTinhCoSo
	        FROM OVatTu AS VatTu         
	        INNER JOIN OSanPham AS MatHang ON VatTu.Ref_MaVatTu = MatHang.IOID
	        INNER JOIN ODonViTinhSP AS DonViTinh ON VatTu.Ref_DonViTinh = DonViTinh.IOID
			WHERE VatTu.IFID_M724 in (%1$s)
            ORDER BY VatTu.MaVatTu 
		', implode(', ', $planIFIDs));
        return $this->_o_DB->fetchAll($sql);
    }




}