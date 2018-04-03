<?php
class Static_M618Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
        $this->html->curl = $this->params->requests->getRequestUri();
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $warehouse = new Qss_Model_Extra_Warehouse();
        $mItem     = new Qss_Model_Master_Item();

        $ngaybd    = $this->params->requests->getParam('start', '');
        $ngaykt    = $this->params->requests->getParam('end', '');
        $manvl     = $this->params->requests->getParam('item', 0);//IOID
        $kho       = $this->params->requests->getParam('warehouse', 0);//IOID

        $start     = Qss_Lib_Date::displaytomysql($ngaybd);
        $end       = Qss_Lib_Date::displaytomysql($ngaykt);
        
        // Tinh ton kho dau ky
        $begin = $warehouse->getInventoryByDate($start, $kho, $manvl);

        $this->html->begin = $begin;


        // Lay thong tin ve san pham
        $this->html->item  = $mItem->getItemByIOID($manvl);

        // Lay giao dich trong ky theo ngay
        $inout = array();
        $input = $warehouse->getInputForInventoryCardReport($start, $end, $kho, $manvl);
        $output = $warehouse->getOutputForInventoryCardReport($start, $end, $kho, $manvl);

        $i = 0;
        foreach($input as $in)
        {
            $inout[$in->NgayChuyenHang][$i]['SoChungTu']      = $in->SoChungTu;
            $inout[$in->NgayChuyenHang][$i]['NgayChuyenHang'] = $in->NgayChuyenHang;
            $inout[$in->NgayChuyenHang][$i]['NgayChungTu'] = $in->NgayChungTu;
            $inout[$in->NgayChuyenHang][$i]['SoLuong']        = $in->SoLuong;
            $inout[$in->NgayChuyenHang][$i]['Type']           = 1;
            $i++;
        }


        foreach($output as $out)
        {
            $inout[$out->NgayChuyenHang][$i]['SoChungTu']      = $out->SoChungTu;
            $inout[$out->NgayChuyenHang][$i]['NgayChuyenHang'] = $out->NgayChuyenHang;
            $inout[$out->NgayChuyenHang][$i]['NgayChungTu'] = $in->NgayChungTu;
            $inout[$out->NgayChuyenHang][$i]['SoLuong']        = $out->SoLuong;
            $inout[$out->NgayChuyenHang][$i]['Type']            = 0;
            $i++;
        }
        $this->html->inout = $inout;
    }
}