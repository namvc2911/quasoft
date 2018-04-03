<?php
/**
 * Class Static_M156Controller
 * Báo cáo tiêu thụ điện năng nội bộ hàng tháng
 */
class Static_M156Controller extends Qss_Lib_Controller
{  
    protected $_MuaBanTheoDonViNoiBo   = array();
    protected $_TongSanLuongDonViNoiBo = 0;
    
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
        $this->html->MuaBanDonViNoiBo        = $this->_MuaBanTheoDonViNoiBo;
        $this->html->TongSanLuongDonViNoiBo  = $this->_TongSanLuongDonViNoiBo;
    }
    
    protected function _getReportData2($start, $end, $year)
    {
        $temp         = array();
        $tongSanLuong = 0;


        for($iMonth = $start; $iMonth <= $end; $iMonth++)
        {
            $electricLib = Qss_Lib_Maintenance_Electric_Calculate::createInstance($iMonth, $year);
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

            $donVi        = $electricLib->getDonVi();
            $chiTietMua   = $electricLib->getChiTietDonViMuaVao();
            $capChoDonVi  = $electricLib->getDonViCapChoDonViKhac();
            $tiLeDonVi    = $electricLib->getTiLeTheoDonVi();
            $sanLuong     = $electricLib->getSanLuongDonVi();
            $nghiemThu    = $electricLib->getSanLuongNghiemThuDonVi();
            $tonHao       = $electricLib->getTonHaoTheoDonVi();
            $titles       = $electricLib->getTitleDonVi();
            $tongSanLuong += $electricLib->getTongSanLuongDonVi();

            //echo '<pre>';print_r($chiTietMua); die;
            foreach($donVi as $item)
            {
                $deptID = $item['ID'];

                $temp[$deptID]['Code']   = $item['Code'];
                $temp[$deptID]['Name']   = $item['Name'];
                $temp[$deptID]['Detail'] = array();

                $tempDetail = isset($chiTietMua[$deptID])?$chiTietMua[$deptID]:array();
                $donViCap   = isset($capChoDonVi[$deptID])?$capChoDonVi[$deptID]:0;
                $tiLe       = isset($tiLeDonVi[$deptID])?$tiLeDonVi[$deptID]:0;
                $sanLuongNT = isset($nghiemThu[$deptID])?$nghiemThu[$deptID]:0;
                $tonHaoDV   = isset($tonHao[$deptID])?$tonHao[$deptID]:0;
                $sanLuongDV = isset($sanLuong[$deptID])?$sanLuong[$deptID]:0;
                $title      = isset($titles[$deptID])?$titles[$deptID]:'';

                // echo '<pre>';print_r($tempDetail); die;



                foreach($tempDetail as $metters)
                {
                    foreach($metters as $tDetail)
                    {
                        $refCongTo   = $tDetail['IDCongTo'];
                        $refDonViMua = $tDetail['IDDonVi'];
                        //echo '<pre>'; print_r($tDetail); die;

                        if(!isset($temp[$refDonViMua]['Detail'][$refCongTo]))
                        {
                            $temp[$refDonViMua]['Detail'][$refCongTo] = array(
                                'Code'=> $tDetail['TenCongTo'],
                                'PurchaseStartNumber'=> $tDetail['ChiSoDau'],
                                'PurchaseEndNumber'=> $tDetail['ChiSoCuoi'],
                                'PurchaseRate'=>$tDetail['HeSo'],
                                'PurchaseQty'=>$tDetail['TongSo'],
                            );
                        }
                        else
                        {
                            $temp[$refDonViMua]['Detail'][$refCongTo]['PurchaseEndNumber'] = $tDetail['ChiSoCuoi'];
                            $temp[$refDonViMua]['Detail'][$refCongTo]['PurchaseRate']      = $tDetail['HeSo'];
                            $temp[$refDonViMua]['Detail'][$refCongTo]['PurchaseQty']      += $tDetail['TongSo'];
                        }
                    }
                }

                if(!isset($temp[$deptID]['SaleQty2']))
                {
                    $temp[$deptID]['SaleQty2'] = $donViCap;
                    $temp[$deptID]['Rate']     = $tiLe;
                    $temp[$deptID]['Qty']      = $sanLuongDV;
                    $temp[$deptID]['Loss']     = $tonHaoDV;
                    $temp[$deptID]['LQty']     = $sanLuongNT;
                    $temp[$deptID]['Title']    = "* Tháng {$iMonth} \n {$title}";
                }
                else
                {
                    $temp[$deptID]['SaleQty2'] += $donViCap;
                    $temp[$deptID]['Rate']     = $tiLe;
                    $temp[$deptID]['Qty']      += $sanLuongDV;
                    $temp[$deptID]['Loss']     += $tonHaoDV;
                    $temp[$deptID]['LQty']     += $sanLuongNT;
                    $temp[$deptID]['Title']    .= "* Tháng {$iMonth} \n {$title}";;
                }
            }

        }
        //echo '<pre>'; print_r($temp); die;

        $this->_MuaBanTheoDonViNoiBo   = $temp;
        $this->_TongSanLuongDonViNoiBo = $tongSanLuong;
        
    }
    
}