<?php

class Qss_Bin_Calculate_OKeHoachBaoTri_NgayBatDau extends Qss_Lib_Calculate
{
    protected static $_namKeHoach = '';

    public function __doExecute()
    {
        $retval = $this->_object->getFieldByCode('NgayBatDau')->getValue();

        if(Qss_Lib_System::fieldActive('OKeHoachBaoTri', 'LoaiKeHoach')) {
            $loaiKeHoach = $this->_object->getFieldByCode('LoaiKeHoach')->getValue();

            if(self::$_namKeHoach == '') {
                $keHoachTongTheIOID = $this->_object->getFieldByCode('KeHoachTongThe')->getRefIOID();

                if($keHoachTongTheIOID) {
                    $sql     = sprintf('SELECT * FROM OKeHoachTongThe WHERE IOID = %1$d ',$keHoachTongTheIOID);
                    $dataSQL = $this->_db->fetchOne($sql);

                    if($dataSQL && isset($dataSQL->Nam) && $dataSQL->Nam) {
                        self::$_namKeHoach = $dataSQL->Nam;
                    }
                }
            }

            switch ($loaiKeHoach) {
                case 1:
                    $retval = '01-01-'.self::$_namKeHoach;
                    break;

                case 2:
                    $retval = '01-04-'.self::$_namKeHoach;
                    break;

                case 3:
                    $retval = '01-07-'.self::$_namKeHoach;
                    break;

                case 4:
                    $retval = '01-10-'.self::$_namKeHoach;
                    break;

                case 5:
                    $retval = '01-01-'.self::$_namKeHoach;
                    break;

                case 6:
                    $retval = '01-07-'.self::$_namKeHoach;
                    break;

                case 7:
                    $retval = '01-01-'.self::$_namKeHoach;
                    break;
            }
        }

        return $retval;
    }
}