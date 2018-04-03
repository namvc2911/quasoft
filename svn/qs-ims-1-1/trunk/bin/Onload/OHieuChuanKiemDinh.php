<?php

class Qss_Bin_Onload_OHieuChuanKiemDinh extends Qss_Lib_Onload
{
    public function __doExecute()
    {
        parent::__doExecute(); // TODO: Change the autogenerated stub
        $mCalibration = new Qss_Model_Maintenance_Equip_Calibration();
        $nextDate     = $mCalibration->getNextDate($this->_object);

        // Khi có ngày kiểm định tiếp theo
        // Nếu bản ghi chưa được lưu lại thì tải giá tri ngày kiểm định tiếp theo tùy ý
        // Nếu bản ghi đã lưu chỉ tải giá trị khi ngày kiểm định tiếp theo chưa có giá trị
        if($nextDate
            && (!$this->_object->i_IFID || !$this->_object->getFieldByCode('NgayKiemDinhTiepTheo')->getValue()))
        {
            $this->_object->getFieldByCode('NgayKiemDinhTiepTheo')->setValue($nextDate);
        }
    }

    public function MaThietBi()
    {
        if(Qss_Lib_System::formActive('M843'))
        {
            $this->_object->getFieldByCode('MaThietBi')->arrFilters[] =
                sprintf('
                v.IOID in (
                    SELECT Ref_MaThietBi
                    FROM OQuyDinhHieuChuanKiemDinh
                )
            ');
        }


    }

    public function TenHieuChuan()
    {
        if(Qss_Lib_System::formActive('M843'))
        {
            $maThietBi = (int)$this->_object->getFieldByCode('MaThietBi')->getRefIOID();

            $this->_object->getFieldByCode('TenHieuChuan')->arrFilters[] =
                sprintf(' v.Ref_MaThietBi = %1$d'
                	, $maThietBi);
        }

    }
}