<?php

class Qss_Lib_Production_Common
{

        /**
         * Kiem tra xem day chuyen co du cong doan san xuat san pham theo thiet ke hay khong?
         * @param int $LineIOID  IOID cua day chuyen
         * @param int $BOMIOID  IOID cua BOM cua san pham
         * @return array Cac cong doan con thieu
         */
        public static function checkEnoughStages($LineIOID, $BOMIOID)
        {
//                $common = new Qss_Model_Extra_Extra(); // Cac ham model thuong xu dung
                $errMsg   = ''; // messeage bao loi
                $retval     = array(); // mang tra ve
                
                // Lay cong doan theo day chuyen
//                $filter['select']             = 'cd.*, cm.MaDayChuyen';
//                $filter['module']           = 'ODayChuyen';
//                $filter['join'][0]['table']  = 'OCongDoanDayChuyen';
//                $filter['join'][0]['alias']  = 'cd';
//                $filter['join'][0]['type']  = 1;
//                $filter['join'][0]['condition'][0]['col1']  = 'IFID_M702';
//                $filter['join'][0]['condition'][0]['alias1']  = 'cm';
//                $filter['join'][0]['condition'][0]['col2']  = 'IFID_M702';
//                $filter['join'][0]['condition'][0]['alias2']  = 'cd';
//                $filter['where'] = array('cm.IOID'=>$LineIOID);
                $mDayChuyen = Qss_Model_Db::Table('ODayChuyen');
                $mDayChuyen->select('OCongDoanDayChuyen.*, ODayChuyen.MaDayChuyen');
                $mDayChuyen->join('LEFT JOIN OCongDoanDayChuyen ON ODayChuyen.IFID_M702 = OCongDoanDayChuyen.IFID_M702');
                $mDayChuyen->where(sprintf('ODayChuyen.IOID = %1$d', $LineIOID));

//                $StagesByLine =  $common->getDataset($filter);
                $StagesByLine =  $mDayChuyen->fetchAll();
                $StagesByLineArr = array();
                $LineName = count((array)$StagesByLine)?$StagesByLine[0]->MaDayChuyen:'';
                
                foreach ($StagesByLine as $sl)
                {
                    if($sl->Ref_CongDoan)
                    $StagesByLineArr[$sl->Ref_CongDoan] = $sl->CongDoan;
                }
                //-----------------------------------------------------------------------
                
                // Lay cong doan theo BOM cua san pham
//                $filter['select']             = 'cd.*, cm.TenCauThanhSanPham';
//                $filter['module']           = 'OCauThanhSanPham';
//                $filter['join'][0]['table']  = 'OCongDoanBOM';
//                $filter['join'][0]['alias']  = 'cd';
//                $filter['join'][0]['type']  = 1;
//                $filter['join'][0]['condition'][0]['col1']  = 'IFID_M114';
//                $filter['join'][0]['condition'][0]['alias1']  = 'cm';
//                $filter['join'][0]['condition'][0]['col2']  = 'IFID_M114';
//                $filter['join'][0]['condition'][0]['alias2']  = 'cd';
//                $filter['where'] = array('cm.IOID'=>$BOMIOID);
                $mCauThanh = Qss_Model_Db::Table('OCauThanhSanPham');
            $mCauThanh->select('OCongDoanBOM.*, OCauThanhSanPham.TenCauThanhSanPham');
            $mCauThanh->join('LEFT JOIN OCongDoanBOM ON OCauThanhSanPham.IFID_M114 = OCongDoanBOM.IFID_M114');
            $mCauThanh->where(sprintf('ODayChuyen.IOID = %1$d', $BOMIOID));

//                $StagesByBOM =  $common->getDataset($filter);
                $StagesByBOM =  $mCauThanh->fetchAll();
                $StagesByBOMArr = array();
                $BOMName = count((array)$StagesByBOM)?$StagesByBOM[0]->TenCauThanhSanPham:'';
                
                foreach ($StagesByBOM as $sb)
                {
                        if($sb->Ref_Ten)
                        $StagesByBOMArr[$sb->Ref_Ten] = $sb->Ten;
                }
                //-----------------------------------------------------------------------
                
                // Kiem tra xem cong doan theo BOM cua san pham co xuat hien du trong cong doan day chuyen hay khong
                $i = 0;
                foreach ($StagesByBOMArr as $key => $value) 
                {
                        if(!isset($StagesByLineArr[$key]))
                        {
                                $errMsg .= ' Dây chuyền '. $LineName .' thiếu công đoạn '. $value . ' để sản xuất theo công đoạn của thiết kế \"' . $BOMName. '\"\n';
                                $retval['stage'][$i]['name']     = $value;
                                $retval['stage'][$i]['id'] = $key;
                                $i++;
                        }
                }
                
                if(count($retval))
                {
                        $retval['error'] = $errMsg;
                        $retval['line']   = $LineName;
                        $retval['BOM'] = $BOMName;
                }
                return $retval;
        }
}