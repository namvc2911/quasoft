<?php

class Qss_Bin_Calculate_OKeHoachBaoTri_NgayKetThuc extends Qss_Lib_Calculate
{
    protected static $_namKeHoach = '';

    public function __doExecute()
    {
        $retval = $this->_object->getFieldByCode('NgayKetThuc')->getValue();

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
                    $retval = '31-03-'.self::$_namKeHoach;
                    break;

                case 2:
                    $retval = '30-06-'.self::$_namKeHoach;
                    break;

                case 3:
                    $retval = '31-10-'.self::$_namKeHoach;
                    break;

                case 4:
                    $retval = '31-12-'.self::$_namKeHoach;
                    break;

                case 5:
                    $retval = '30-06-'.self::$_namKeHoach;
                    break;

                case 6:
                    $retval = '31-12-'.self::$_namKeHoach;
                    break;

                case 7:
                    $retval = '31-12-'.self::$_namKeHoach;
                    break;
            }
        }

        return $retval;
    }
}