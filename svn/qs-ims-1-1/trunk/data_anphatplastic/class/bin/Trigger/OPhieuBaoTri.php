<?php

/**
 * @author: ThinhTuan
 */
class Qss_Bin_Trigger_OPhieuBaoTri extends Qss_Lib_Trigger
{
    public function checkDateRange($object)
    {
        $this->_checkDateRange($object->getFieldByCode('NgayBatDau')->getValue(), $object->getFieldByCode('Ngay')->getValue(), $this->_translate(1));
    }

    public function onInserted($object)
    {
        //send email if breakdown
        parent::init();
        if(!$this->isError())
        {
            $this->checkOrderExists($object);
        }

        if(!$this->isError())
        {
            $this->createTask($object);
        }

        // CUSTOMIZE -----------------------------------------------

        // Thêm ngày sự cố nếu là loại sự cố
//        $refLoaiBT   = $object->getFieldByCode('LoaiBaoTri')->getRefIOID();
//        $oLoaiBaoTri = $this->_db->fetchOne(sprintf('select * from OPhanLoaiBaoTri where IOID = %1$d', $refLoaiBT));
//        $loaiBaoTri  = ($oLoaiBaoTri)?$oLoaiBaoTri->LoaiBaoTri:'';
//
//        if($loaiBaoTri == 'B')
//        {
//            if(!$object->getFieldByCode('NgayDungMay')->getValue())
//            {
//                $this->_db->execute(sprintf('UPDATE OPhieuBaoTri SET NgayDungMay = NgayBatDau WHERE IFID_M759 = %1$d', $this->_form->i_IFID));
//            }
//
//            if(!$object->getFieldByCode('ThoiGianBatDauDungMay')->getValue())
//            {
//                $this->_db->execute(sprintf('UPDATE OPhieuBaoTri SET ThoiGianBatDauDungMay = GioBatDau WHERE IFID_M759 = %1$d', $this->_form->i_IFID));
//            }
//
//            if(!$object->getFieldByCode('NgayKetThucDungMay')->getValue())
//            {
//                $this->_db->execute(sprintf('UPDATE OPhieuBaoTri SET NgayKetThucDungMay = Ngay WHERE IFID_M759 = %1$d', $this->_form->i_IFID));
//            }
//
//            if(!$object->getFieldByCode('ThoiGianKetThucDungMay')->getValue())
//            {
//                $this->_db->execute(sprintf('UPDATE OPhieuBaoTri SET ThoiGianKetThucDungMay = GioKetThuc WHERE IFID_M759 = %1$d', $this->_form->i_IFID));
//            }
//        }
    }

    public function onUpdate($object)
    {
        parent::init();
        $this->checkDateRange($object);
        $this->checkWorkOrderExists($object, 1);
        //$this->checkDateInTimeOfWorkOrder($object);
    }
    public function onUpdated($object)
    {
        parent::init();
        $this->checkDateInTimeOfWorkOrder($object);

        if(!$this->isError())
        {
            $this->checkOrderExists($object);
        }
    }
    public function onInsert($object)
    {
        parent::init();
        $this->checkDateRange($object);
        $this->checkWorkOrderExists($object);
        //$this->checkDateInTimeOfWorkOrder($object);

        // Chen gia tri ngay cho ngay yeu cau
        $ngayYeuCau = $object->getFieldByCode('NgayYeuCau')->getValue();
        $object->getFieldByCode('NgayYeuCau')->setValue($ngayYeuCau);


    }

    private function checkWorkOrderExists($object, $update = 0)
    {
        $model = new Qss_Model_Maintenance_Workorder();
        $MaintTypeID = $object->getFieldByCode('LoaiBaoTri')->getRefIOID();
        $chukyID = $object->getFieldByCode('ChuKy')->getRefIOID();
        $EquipID = $object->getFieldByCode('MaThietBi')->getRefIOID();
        $componentID = $object->getFieldByCode('BoPhan')->getRefIOID();
        $EquipCode = $object->getFieldByCode('MaThietBi')->getValue();
        $MaintType = $object->getFieldByCode('LoaiBaoTri')->getValue();
        $Date = ($object->getFieldByCode('NgayYeuCau')->getValue());
        $DateSql = Qss_Lib_Date::displaytomysql($Date);
        $IOID = $object->i_IOID;

        $where = sprintf('Ref_LoaiBaoTri = %1$d and ifnull(Ref_MaThietBi,0) = %2$d 
					and ifnull(Ref_BoPhan,0) = %4$d 
					and ifnull(Ref_ChuKy,0) = %5$d
					and NgayYeuCau = \'%3$s\''
            , $MaintTypeID
            , $EquipID
            , $DateSql
            , $componentID
            , $chukyID);
        $where .= ($update) ? sprintf(' and IOID != %1$d', $IOID) : '';

//		$filter = array(
//			'module' => 'OPhieuBaoTri',
//			'where' => $where,
//			'count' => true,
//			'return' => 1
//		);

//        if($DateSql)
//        {
//            $check = $model->checkWorkOrderExists($object->i_IOID
//                , $MaintTypeID
//                , $EquipID
//                , $DateSql
//                , $componentID
//                , $chukyID);
//        }
//        else
//        {
//            $check = false;
//        }
//
//
//        if ($check)
//        {
//            $msg = "Đã tồn tại phiếu bảo trì số {$check->SoPhieu} vào ngày {$check->NgayYeuCau}: Chu kỳ: {$check->ChuKy}!";
//            $this->setMessage($msg);
//            $this->setError();
//        }

    }


    private function checkDateInTimeOfWorkOrder(Qss_Model_Object $object)
    {
        /*$workOrderModel  = new Qss_Model_Maintenance_Workorder();
        $tasksNotInRange = $workOrderModel->getTasksOfWokrOrderNotInOrderRangeTime(
            $this->_form->i_IFID
            , Qss_Lib_Date::displaytomysql($object->getFieldByCode('NgayBatDau')->getValue())
            , Qss_Lib_Date::displaytomysql($object->getFieldByCode('Ngay')->getValue()));

        if(count($tasksNotInRange))
        {
            //$this->setError();
            $this->setMessage('Ít nhất một công việc bảo trì có ngày thực hiện không hợp lệ (Không nằm trong khoảng thời gian bắt đầu kết thúc của phiếu bảo trì).');
        }*/
        $model = new Qss_Model_Maintenance_Calendar();
        $model->adjustWorkOrder($this->_form->i_IFID);
    }

    /**
     * Chỉ áp dụng với loại có chỉ số giờ
     * @param Qss_Model_Object $object
     */
    public function checkOrderExists(Qss_Model_Object $object)
    {
        parent::init();

        // Lay cai dat xem cai dat trong ke hoach nhu the nao
        // Lay bao tri lan cuoi
        // Lay xem tu ngay bao tri cuoi thiet bi da hoat dong duoc bao lau
        // Lay thoi gian hoat dong cua thiet bi so voi ke hoach xem da den

        $sql   = sprintf('
            SELECT ChuKy.*
            FROM OChuKyBaoTri AS ChuKy
            INNER JOIN OChiSoMayMoc AS ChiSo ON IFNULL(ChuKy.Ref_ChiSo, 0) = ChiSo.IOID
            WHERE ChuKy.IOID = %1$d AND IFNULL(ChiSo.Gio,0) != 0'
            , $object->getFieldByCode('ChuKy')->intRefIOID);
        // echo '<pre>'; print_r($sql); die;
        $chuky = $this->_db->fetchOne($sql);
        $chiso = $chuky?$chuky->GiaTri:0;

        // echo '<pre>'; print_r($chiso); die;

        $boPhan    = $object->getFieldByCode('BoPhan')->intRefIOID;
        $locBoPhan = sprintf(' AND IFNULL(PhieuBaoTri.Ref_BoPhan, 0) = %1$d ', $boPhan);

//        echo '<pre>'; print_r($boPhan); die;

        $lastSql = sprintf('
            SELECT *
                , IF( IFNULL(Ngay, \'\') != \'\', Ngay, NgayBatDau )AS NgaySoSanh
            FROM
            (
                SELECT *
                FROM OPhieuBaoTri AS PhieuBaoTri
                WHERE
                    PhieuBaoTri.Ref_MaThietBi = %1$d
                    %2$s
                    AND PhieuBaoTri.Ref_LoaiBaoTri = %3$d
                ORDER BY PhieuBaoTri.Ngay DESC, Ref_MaThietBi, IFNULL(Ref_BoPhan, 0), Ref_LoaiBaoTri
            ) AS Phieu
            GROUP BY Ref_MaThietBi, IFNULL(Ref_BoPhan, 0), Ref_LoaiBaoTri
            LIMIT 1
        ', $object->getFieldByCode('MaThietBi')->intRefIOID
            , $locBoPhan
            , $object->getFieldByCode('LoaiBaoTri')->intRefIOID);
        // echo '<pre>'; print_r($lastSql); die;

        $last  = $this->_db->fetchOne($lastSql);
        $ngayCuoiCung = $last?$last->NgaySoSanh:'';

        // echo '<pre>'; print_r($ngayCuoiCung); die;

        if($ngayCuoiCung)
        {
            $boPhan    = $object->getFieldByCode('BoPhan')->intRefIOID;
            $locBoPhan = sprintf(' AND IFNULL(NhatTrinh.Ref_BoPhan, 0) = %1$d ', $boPhan);

            $activeSql = sprintf('
                SELECT
                    NhatTrinh.*
                    , SUM( IFNULL(NhatTrinh.SoHoatDong, 0) ) AS ThoiGianHoatDong
                FROM ONhatTrinhThietBi AS NhatTrinh
                INNER JOIN OChiSoMayMoc AS ChiSo ON IFNULL(NhatTrinh.Ref_ChiSo, 0) = ChiSo.IOID
                WHERE NhatTrinh.Ngay >= %1$s
                    AND NhatTrinh.Ref_MaTB = %2$d
                    %3$s
                    AND IFNULL(ChiSo.Gio,0) != 0
                GROUP BY NhatTrinh.Ref_MaTB, IFNULL(NhatTrinh.Ref_ChiSo, 0)
            ', $this->_db->quote($ngayCuoiCung)
                , $object->getFieldByCode('MaThietBi')->intRefIOID
                , $locBoPhan);

            // echo '<pre>'; print_r($activeSql); die;

            $active  = $this->_db->fetchOne($activeSql);

            // echo '<pre>'; print_r($active); die;

            $gioHoatDong = $active?$active->ThoiGianHoatDong:0;

            $lap = $chiso?$gioHoatDong%$chiso:0;

            if($lap != 0)
            {
                $this->setError();
                $this->setMessage('Đã tồn tại phiếu bảo trì '.$last->SoPhieu.' cho thiết bị, bộ phận với loại bảo trì này!'.
                    ' Nếu phiếu bảo trì trùng lặp, bạn hãy xóa nó đi!');
            }
        }

    }

    private function createTask(Qss_Model_Object $object)
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $user     = Qss_Register::get('userinfo');
        $type     = $mCommon->getTableFetchOne('OCongViecBaoTri');

        $insert['OCongViecBTPBT'][0]['MoTa']                  = $object->getFieldByCode('MoTa')->getValue();
        $insert['OCongViecBTPBT'][0]['ViTri']                 = (int)$object->getFieldByCode('BoPhan')->getRefIOID();;
        $insert['OCongViecBTPBT'][0]['BoPhan']                = (int)$object->getFieldByCode('BoPhan')->getRefIOID();;
        $insert['OCongViecBTPBT'][0]['NhanCong']              = 1;
        $insert['OCongViecBTPBT'][0]['Ten']                   = (int)$type->IOID;
        $insert['OCongViecBTPBT'][0]['NgayDuKien']            = $object->getFieldByCode('NgayBatDauDuKien')->getValue();
        $insert['OCongViecBTPBT'][0]['ThoiGianBatDauDuKien']  = $object->getFieldByCode('ThoiGianBatDauDuKien')->getValue();
        $insert['OCongViecBTPBT'][0]['ThoiGianKetThucDuKien'] = $object->getFieldByCode('ThoiGianBatDauDuKien')->getValue();
        $insert['OCongViecBTPBT'][0]['Ngay']                  = $object->getFieldByCode('NgayBatDauDuKien')->getValue();
        $insert['OCongViecBTPBT'][0]['GioBatDau']             = $object->getFieldByCode('ThoiGianBatDauDuKien')->getValue();
        $insert['OCongViecBTPBT'][0]['GioKetThuc']            = $object->getFieldByCode('ThoiGianBatDauDuKien')->getValue();
        $insert['OCongViecBTPBT'][0]['NguoiThucHien']         = (int)$object->getFieldByCode('NguoiThucHien')->getRefIOID();
        $insert['OCongViecBTPBT'][0]['ifid']                  = (int)$object->i_IFID;

        $service = $this->services->Form->Manual('M759',  $object->i_IFID,  $insert, false);

        if ($service->isError())
        {
            $this->setError();
            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
        }

//        if(!$this->isError())
//        {
//            $ifid = $service->getData();
//            $form       = new Qss_Model_Form();
//            $form->initData($object->i_IFID, $user->user_dept_id);
//            $service2 = $this->services->Form->Request($form, 2, $user , '');
//
//
//            if ($service2->isError())
//            {
//                $this->setError();
//                $this->setMessage($service2->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//            }
//        }
    }


}
