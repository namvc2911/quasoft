<?php
class Static_M627Controller extends Qss_Lib_Controller
{    
    private $_M627Dat;
    
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
    }

    public function indexAction()
    {

    }
    
    public function showAction()
    {
        $start = $this->params->requests->getParam('start', 0);
        $end   = $this->params->requests->getParam('end', 0);
        $year  = $this->params->requests->getParam('year', 0);

        if($end == 0)
        {
            $end = $start;
        }

        $this->html->start = @(int)$start;
        $this->html->end   = @(int)$end;
        $this->html->year  = @(int)$year;
        $this->html->report = $this->_getReportData(@(int)$start, @(int)$end, @(int)$year);

    }    
    
    /**
     * 
     * @param int $month
     * @param int $year
     * @return mix: Phẩn bổ điện năng hàng tháng theo kỳ/ Electric by month and period
     */
    private function _getReportData($start, $end, $year)
    {

        for($iMonth = $start; $iMonth <= $end; $iMonth++)
        {
            $electricLib = Qss_Lib_Maintenance_Electric_Calculate::createInstance($iMonth, $year);
            $electricLib->setGiaMua();
            $electricLib->setGiaBan();
            $electricLib->calculateDienMuaTuQuangNinh();
            $electricLib->calculateDienBanChoQuangNinh();
            $electricLib->calculateDienNangMuaVao();
            $electricLib->calculateDienNangBanRa();
            $electricLib->calculateChechLechMuaBan();
            $electricLib->calculateDienNangMuaVaoTheoKy();
            $electricLib->calculateTongDienMuaVaoTheoDonVi();
            $electricLib->calculateTongDienBanRaTheoDonVi();
            $electricLib->calculateKhoanDienNangChoDonVi();
            $electricLib->calculateTongSanLuongDonVi();
            $electricLib->calculateSanLuongDonVi();
            $electricLib->calculateHeSoTonHao();
            $electricLib->calculateSanLuongNghiemThuDonVi();
            $electricLib->setTitleDonVi();

            $tongDienBanRa     =  $electricLib->getTongDienBanRa();
            $tongTienBanRa     =  $electricLib->getTongTienBanTruChenhLech();
            $sanLuongNghiemThu = $electricLib->getSanLuongNghiemThuDonVi();
            $thanhTienDonVi    = $electricLib->getThanhTienDonVi();
            $donVi             = $electricLib->getDonVi();
            $titles            = $electricLib->getTitleDonVi();

            $temp              = array();
            $TongKy1SanLuong   = 0;
            $TongKy1ThanhTien  = 0;
            $TongKy2SanLuong   = 0;
            $TongKy2ThanhTien  = 0;
            $TongKy3SanLuong   = 0;
            $TongKy3ThanhTien  = 0;
            $TongTongSanLuong  = 0;
            $TongTongThanhTien = 0;


            $temp['DienMuaKy1QuangNinh']  =  $electricLib->getDienNangMuaVaoTheoKy(1);
            $temp['TienMuaKy1QuangNinh']  =  $electricLib->getTienMuaVaoTheoKy(1);
            $temp['DienMuaKy2QuangNinh']  =  $electricLib->getDienNangMuaVaoTheoKy(2);
            $temp['TienMuaKy2QuangNinh']  =  $electricLib->getTienMuaVaoTheoKy(2);
            $temp['DienMuaKy3QuangNinh']  =  $electricLib->getDienNangMuaVaoTheoKy(3);
            $temp['TienMuaKy3QuangNinh']  =  $electricLib->getTienMuaVaoTheoKy(3);
            $temp['TongDienMuaQuangNinh'] =  $electricLib->getTongDienQuangNinhConLaiChoSuDung();
            $temp['TongTienMuaQuangNinh'] =  $electricLib->getTongTienDienQuangNinhConLaiChoSuDung();

            $temp['DienBanNgoaiKy1']  =  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_1 * $tongDienBanRa)/100;
            $temp['TienBanNgoaiKy1']  =  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_1 * $tongTienBanRa)/100;
            $temp['DienBanNgoaiKy2']  =  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_2 * $tongDienBanRa)/100;
            $temp['TienBanNgoaiKy2']  =  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_2 * $tongTienBanRa)/100;
            $temp['DienBanNgoaiKy3']  =  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_3 * $tongDienBanRa)/100;
            $temp['TienBanNgoaiKy3']  =  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_3 * $tongTienBanRa)/100;
            $temp['TongDienBanNgoai'] =  $tongDienBanRa;
            $temp['TongTienBanNgoai'] =  $tongTienBanRa;

            $temp['DienSuDungKy1']    =  $temp['DienMuaKy1QuangNinh'] - $temp['DienBanNgoaiKy1'];
            $temp['TienSuDungKy1']    =  $temp['TienMuaKy1QuangNinh'] - $temp['TienBanNgoaiKy1'];
            $temp['DienSuDungKy2']    =  $temp['DienMuaKy2QuangNinh'] - $temp['DienBanNgoaiKy2'];
            $temp['TienSuDungKy2']    =  $temp['TienMuaKy2QuangNinh'] - $temp['TienBanNgoaiKy2'];
            $temp['DienSuDungKy3']    =  $temp['DienMuaKy3QuangNinh'] - $temp['DienBanNgoaiKy3'];
            $temp['TienSuDungKy3']    =  $temp['TienMuaKy3QuangNinh'] - $temp['TienBanNgoaiKy3'];
            $temp['TongDienSuDung']   =  $temp['TongDienMuaQuangNinh'] - $temp['TongDienBanNgoai'];
            $temp['TongTienSuDung']   =  $temp['TongTienMuaQuangNinh'] - $temp['TongTienBanNgoai'];            
            

            if(!isset($this->_M627Dat['DienMuaKy1QuangNinh']))
            {
                $this->_M627Dat['DienMuaKy1QuangNinh']  =  $electricLib->getDienNangMuaVaoTheoKy(1);
                $this->_M627Dat['TienMuaKy1QuangNinh']  =  $electricLib->getTienMuaVaoTheoKy(1);
                $this->_M627Dat['DienMuaKy2QuangNinh']  =  $electricLib->getDienNangMuaVaoTheoKy(2);
                $this->_M627Dat['TienMuaKy2QuangNinh']  =  $electricLib->getTienMuaVaoTheoKy(2);
                $this->_M627Dat['DienMuaKy3QuangNinh']  =  $electricLib->getDienNangMuaVaoTheoKy(3);
                $this->_M627Dat['TienMuaKy3QuangNinh']  =  $electricLib->getTienMuaVaoTheoKy(3);
                $this->_M627Dat['TongDienMuaQuangNinh'] =  $electricLib->getTongDienQuangNinhConLaiChoSuDung();
                $this->_M627Dat['TongTienMuaQuangNinh'] =  $electricLib->getTongTienDienQuangNinhConLaiChoSuDung();

                $this->_M627Dat['DienBanNgoaiKy1']  =  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_1 * $tongDienBanRa)/100;
                $this->_M627Dat['TienBanNgoaiKy1']  =  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_1 * $tongTienBanRa)/100;
                $this->_M627Dat['DienBanNgoaiKy2']  =  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_2 * $tongDienBanRa)/100;
                $this->_M627Dat['TienBanNgoaiKy2']  =  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_2 * $tongTienBanRa)/100;
                $this->_M627Dat['DienBanNgoaiKy3']  =  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_3 * $tongDienBanRa)/100;
                $this->_M627Dat['TienBanNgoaiKy3']  =  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_3 * $tongTienBanRa)/100;
                $this->_M627Dat['TongDienBanNgoai'] =  $tongDienBanRa;
                $this->_M627Dat['TongTienBanNgoai'] =  $tongTienBanRa;

                $this->_M627Dat['DienSuDungKy1']    =  $this->_M627Dat['DienMuaKy1QuangNinh'] - $this->_M627Dat['DienBanNgoaiKy1'];
                $this->_M627Dat['TienSuDungKy1']    =  $this->_M627Dat['TienMuaKy1QuangNinh'] - $this->_M627Dat['TienBanNgoaiKy1'];
                $this->_M627Dat['DienSuDungKy2']    =  $this->_M627Dat['DienMuaKy2QuangNinh'] - $this->_M627Dat['DienBanNgoaiKy2'];
                $this->_M627Dat['TienSuDungKy2']    =  $this->_M627Dat['TienMuaKy2QuangNinh'] - $this->_M627Dat['TienBanNgoaiKy2'];
                $this->_M627Dat['DienSuDungKy3']    =  $this->_M627Dat['DienMuaKy3QuangNinh'] - $this->_M627Dat['DienBanNgoaiKy3'];
                $this->_M627Dat['TienSuDungKy3']    =  $this->_M627Dat['TienMuaKy3QuangNinh'] - $this->_M627Dat['TienBanNgoaiKy3'];
                $this->_M627Dat['TongDienSuDung']   =  $this->_M627Dat['TongDienMuaQuangNinh'] - $this->_M627Dat['TongDienBanNgoai'];
                $this->_M627Dat['TongTienSuDung']   =  $this->_M627Dat['TongTienMuaQuangNinh'] - $this->_M627Dat['TongTienBanNgoai'];
            }
            else
            {
                $this->_M627Dat['DienMuaKy1QuangNinh']  +=  $electricLib->getDienNangMuaVaoTheoKy(1);
                $this->_M627Dat['TienMuaKy1QuangNinh']  +=  $electricLib->getTienMuaVaoTheoKy(1);
                $this->_M627Dat['DienMuaKy2QuangNinh']  +=  $electricLib->getDienNangMuaVaoTheoKy(2);
                $this->_M627Dat['TienMuaKy2QuangNinh']  +=  $electricLib->getTienMuaVaoTheoKy(2);
                $this->_M627Dat['DienMuaKy3QuangNinh']  +=  $electricLib->getDienNangMuaVaoTheoKy(3);
                $this->_M627Dat['TienMuaKy3QuangNinh']  +=  $electricLib->getTienMuaVaoTheoKy(3);
                $this->_M627Dat['TongDienMuaQuangNinh'] +=  $electricLib->getTongDienQuangNinhConLaiChoSuDung();
                $this->_M627Dat['TongTienMuaQuangNinh'] +=  $electricLib->getTongTienDienQuangNinhConLaiChoSuDung();

                $this->_M627Dat['DienBanNgoaiKy1']  +=  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_1 * $tongDienBanRa)/100;
                $this->_M627Dat['TienBanNgoaiKy1']  +=  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_1 * $tongTienBanRa)/100;
                $this->_M627Dat['DienBanNgoaiKy2']  +=  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_2 * $tongDienBanRa)/100;
                $this->_M627Dat['TienBanNgoaiKy2']  +=  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_2 * $tongTienBanRa)/100;
                $this->_M627Dat['DienBanNgoaiKy3']  +=  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_3 * $tongDienBanRa)/100;
                $this->_M627Dat['TienBanNgoaiKy3']  +=  (Qss_Lib_Maintenance_Electric_Calculate::TI_LE_KI_3 * $tongTienBanRa)/100;
                $this->_M627Dat['TongDienBanNgoai'] +=  $tongDienBanRa;
                $this->_M627Dat['TongTienBanNgoai'] +=  $tongTienBanRa;

                $this->_M627Dat['DienSuDungKy1']    +=  $this->_M627Dat['DienMuaKy1QuangNinh'] - $this->_M627Dat['DienBanNgoaiKy1'];
                $this->_M627Dat['TienSuDungKy1']    +=  $this->_M627Dat['TienMuaKy1QuangNinh'] - $this->_M627Dat['TienBanNgoaiKy1'];
                $this->_M627Dat['DienSuDungKy2']    +=  $this->_M627Dat['DienMuaKy2QuangNinh'] - $this->_M627Dat['DienBanNgoaiKy2'];
                $this->_M627Dat['TienSuDungKy2']    +=  $this->_M627Dat['TienMuaKy2QuangNinh'] - $this->_M627Dat['TienBanNgoaiKy2'];
                $this->_M627Dat['DienSuDungKy3']    +=  $this->_M627Dat['DienMuaKy3QuangNinh'] - $this->_M627Dat['DienBanNgoaiKy3'];
                $this->_M627Dat['TienSuDungKy3']    +=  $this->_M627Dat['TienMuaKy3QuangNinh'] - $this->_M627Dat['TienBanNgoaiKy3'];
                $this->_M627Dat['TongDienSuDung']   +=  $this->_M627Dat['TongDienMuaQuangNinh'] - $this->_M627Dat['TongDienBanNgoai'];
                $this->_M627Dat['TongTienSuDung']   +=  $this->_M627Dat['TongTienMuaQuangNinh'] - $this->_M627Dat['TongTienBanNgoai'];                
            }

            foreach ($donVi as $dept)
            {
                $deptID = $dept['ID'];

                $tiLeKy1 = ($temp['TongDienSuDung'])?$temp['DienSuDungKy1']/$temp['TongDienSuDung']:0;
                $tiLeKy2 = ($temp['TongDienSuDung'])?$temp['DienSuDungKy2']/$temp['TongDienSuDung']:0;
                $tiLeKy3 = ($temp['TongDienSuDung'])?$temp['DienSuDungKy3']/$temp['TongDienSuDung']:0;
                $thanhTien = isset($thanhTienDonVi[$deptID])?$thanhTienDonVi[$deptID]:0;
                $donViMuaBan = isset($donVi[$deptID])?$thanhTienDonVi[$deptID]:0;
                $val    = isset($sanLuongNghiemThu[$deptID])?$sanLuongNghiemThu[$deptID]:0;
                $title      = isset($titles[$deptID])?$titles[$deptID]:'';

                if(!isset($this->_M627Dat[4][$deptID]))
                {
                    $this->_M627Dat[4][$deptID][1] = $tiLeKy1 * $val;
                    $this->_M627Dat[4][$deptID][2] = $tiLeKy1 * $thanhTien;

                    $this->_M627Dat[4][$deptID][3] = $tiLeKy2 * $val;
                    $this->_M627Dat[4][$deptID][4] = $tiLeKy2 * $thanhTien;

                    $this->_M627Dat[4][$deptID][5] = $tiLeKy3 * $val;
                    $this->_M627Dat[4][$deptID][6] = $tiLeKy3 * $thanhTien;

                    $this->_M627Dat[4][$deptID][7] = $val;
                    $this->_M627Dat[4][$deptID][8] = $thanhTien;

                    $this->_M627Dat[4][$deptID][9]  = $dept['Name'];
                    $this->_M627Dat[4][$deptID][10] = $dept['Name'];
                    $this->_M627Dat[4][$deptID][11] = "* Tháng {$iMonth} \n {$title}";
                }
                else
                {
                    $this->_M627Dat[4][$deptID][1] += $tiLeKy1 * $val;
                    $this->_M627Dat[4][$deptID][2] += $tiLeKy1 * $thanhTien;

                    $this->_M627Dat[4][$deptID][3] += $tiLeKy2 * $val;
                    $this->_M627Dat[4][$deptID][4] += $tiLeKy2 * $thanhTien;

                    $this->_M627Dat[4][$deptID][5] += $tiLeKy3 * $val;
                    $this->_M627Dat[4][$deptID][6] += $tiLeKy3 * $thanhTien;

                    $this->_M627Dat[4][$deptID][7] += $val;
                    $this->_M627Dat[4][$deptID][8] += $thanhTien;

                    $this->_M627Dat[4][$deptID][11] .= "* Tháng {$iMonth} \n {$title}";
                }


                $TongKy1SanLuong   += $tiLeKy1 * $val;
                $TongKy1ThanhTien  += $tiLeKy1 * $thanhTien;
                $TongKy2SanLuong   += $tiLeKy2 * $val;
                $TongKy2ThanhTien  += $tiLeKy2 * $thanhTien;
                $TongKy3SanLuong   += $tiLeKy3 * $val;
                $TongKy3ThanhTien  += $tiLeKy3 * $thanhTien;
                $TongTongSanLuong  += $val;
                $TongTongThanhTien += $thanhTien;
            }


            if(!isset($this->_M627Dat[5]))
            {
                $this->_M627Dat[5][1] = $TongKy1SanLuong;
                $this->_M627Dat[5][2] = $TongKy1ThanhTien;
                $this->_M627Dat[5][3] = $TongKy2SanLuong;
                $this->_M627Dat[5][4] = $TongKy2ThanhTien;
                $this->_M627Dat[5][5] = $TongKy3SanLuong;
                $this->_M627Dat[5][6] = $TongKy3ThanhTien;
                $this->_M627Dat[5][7] = $TongTongSanLuong;
                $this->_M627Dat[5][8] = $TongTongThanhTien;
            }
            else
            {
                $this->_M627Dat[5][1] += $TongKy1SanLuong;
                $this->_M627Dat[5][2] += $TongKy1ThanhTien;
                $this->_M627Dat[5][3] += $TongKy2SanLuong;
                $this->_M627Dat[5][4] += $TongKy2ThanhTien;
                $this->_M627Dat[5][5] += $TongKy3SanLuong;
                $this->_M627Dat[5][6] += $TongKy3ThanhTien;
                $this->_M627Dat[5][7] += $TongTongSanLuong;
                $this->_M627Dat[5][8] += $TongTongThanhTien;
            }
        
        }

        //echo '<pre>'; print_r($this->_M627Dat); die;
        return $this->_M627Dat;
    }
    


}
