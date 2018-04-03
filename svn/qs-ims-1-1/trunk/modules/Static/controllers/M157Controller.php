<?php
/**
 * Class Static_M157Controller
 * Báo cáo chi phí điện năng hàng tháng
 */
class Static_M157Controller extends Qss_Lib_Controller
{  

    protected $_MuaBanTheoDonViMuaKhac = array();
    protected $_MuaBanTheoDonViNoiBo   = array();
    protected $_TongDienMuaQuangNinh   = 0;
    protected $_TongTienMuaQuangNinh   = 0;
    protected $_DienBanChoKhachHang    = 0;
    protected $_DienConLaiSuDung    = 0;
    protected $_TienConLaiSuDung    = 0;
    protected $_TitleDonViMuaKhac   = 0;

    
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

        $this->_getReportData2(@(int)$start, @(int)$end, @(int)$year);

        $this->html->start = @(int)$start;
        $this->html->end   = @(int)$end;
        $this->html->year  = @(int)$year;
        $this->html->DienMuaDienLucQuangNinh = $this->_TongDienMuaQuangNinh;
        $this->html->TienMuaDienLucQuangNinh = $this->_TongTienMuaQuangNinh;
        $this->html->DienBanChoKhachHang     = $this->_DienBanChoKhachHang;
        $this->html->TienBanChoKhachHang     = $this->_TienBanChoKhachHang;
        $this->html->DienConLaiSuDung        = $this->_DienConLaiSuDung;
        $this->html->GiaTriConLaiSuDung      = $this->_TienConLaiSuDung;
        $this->html->MuaBanDonViNoiBo        = $this->_MuaBanTheoDonViNoiBo;
        $this->html->MuaBanDonViKhacQuanNinh = $this->_MuaBanTheoDonViMuaKhac;
        $this->html->TitleDonViMuaKhac       = $this->_TitleDonViMuaKhac;
    }
    
  
    protected function _getReportData2($start, $end, $year)
    {
        $temp  = array();
        $temp2 = array();

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
            $electricLib->setTitleDonViMuaNgoai();
            $electricLib->calculateDienMuaTuDonViKhac();

            $sanLuongNghiemThu = $electricLib->getSanLuongNghiemThuDonVi();
            $thanhTienDonVi    = $electricLib->getThanhTienDonVi();
            $donVi             = $electricLib->getDonVi();
            $titles            = $electricLib->getTitleDonVi();
            $titles2           = $electricLib->getTitleDonViMuaNgoai();

            foreach($donVi as $item)
            {
                $deptID = $item['ID'];

                $sanLuongNT = isset($sanLuongNghiemThu[$deptID])?$sanLuongNghiemThu[$deptID]:0;
                $thanhTien  = isset($thanhTienDonVi[$deptID])?$thanhTienDonVi[$deptID]:0;
                $title      = isset($titles[$deptID])?$titles[$deptID]:'';


                if(!isset($temp[$deptID]))
                {
                    $temp[$deptID]['Code']   = $item['Code'];
                    $temp[$deptID]['Name']   = $item['Name'];
                    $temp[$deptID]['Title']  = "* Tháng {$iMonth} \n {$title}";
                    $temp[$deptID]['Qty']    = $sanLuongNT;
                    $temp[$deptID]['Amount'] = $thanhTien;
                }
                else
                {
                    $temp[$deptID]['Title']  .= "* Tháng {$iMonth} \n {$title}";
                    $temp[$deptID]['Qty']    += $sanLuongNT;
                    $temp[$deptID]['Amount'] += $thanhTien;
                }

            }

            foreach($electricLib->getDienMuaTuDonViKhac() as $refDonViCap1=>$donViCap1)
            {
                if(!isset($temp2[$refDonViCap1]))
                {
                    $temp2[$refDonViCap1]['Name']   = $donViCap1['Name'];
                    $temp2[$refDonViCap1]['Qty']    = $donViCap1['Qty'];
                    $temp2[$refDonViCap1]['Amount'] = $donViCap1['Amount'];
                    $temp2[$refDonViCap1]['Detail'] = array();
                }
                else
                {
                    $temp2[$refDonViCap1]['Qty']    += $donViCap1['Qty'];
                    $temp2[$refDonViCap1]['Amount'] += $donViCap1['Amount'];
                }

                foreach(@$donViCap1['Detail'] as $refDonViMua1=>$donViMua1)
                {
                    if(!isset($temp2[$refDonViCap1]['Detail'][$refDonViMua1]))
                    {
                        $temp2[$refDonViCap1]['Detail'][$refDonViMua1]['Name']   = $donViMua1['Name'];
                        $temp2[$refDonViCap1]['Detail'][$refDonViMua1]['Qty']    = $donViMua1['Qty'];
                        $temp2[$refDonViCap1]['Detail'][$refDonViMua1]['Amount'] = $donViMua1['Amount'];
                    }
                    else
                    {
                        $temp2[$refDonViCap1]['Detail'][$refDonViMua1]['Qty']    += $donViMua1['Qty'];
                        $temp2[$refDonViCap1]['Detail'][$refDonViMua1]['Amount'] += $donViMua1['Amount'];
                    }
                }
            }

            $this->_TongDienMuaQuangNinh   += $electricLib->getTongDienQuangNinhConLaiChoSuDung();
            $this->_TongTienMuaQuangNinh   += $electricLib->getTongTienDienQuangNinhConLaiChoSuDung();
            $this->_DienBanChoKhachHang    += $electricLib->getTongDienBanRa();
            $this->_TienBanChoKhachHang    += $electricLib->getTongTienBanTruChenhLech();
            $this->_DienConLaiSuDung       += $this->_TongDienMuaQuangNinh - $this->_DienBanChoKhachHang;
            $this->_TienConLaiSuDung       += $this->_TongTienMuaQuangNinh - $this->_TienBanChoKhachHang;
            $this->_TitleDonViMuaKhac      .= "* Tháng {$iMonth} \n ".$electricLib->getTitleDonViMuaNgoai();
        }

        // echo '<pre>'; print_r($temp); die;
        $this->_MuaBanTheoDonViNoiBo   = $temp;
        $this->_MuaBanTheoDonViMuaKhac = $temp2;
    }
}