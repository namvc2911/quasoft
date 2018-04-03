<?php
class Static_M619Controller extends Qss_Lib_Controller
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
        $mPartner = new Qss_Model_Master_Partner();
        $aVendors = array();
        $oVendors = $mPartner->getPartners();
        $index    = 0;

        foreach($oVendors as $ven)
        {
            $aVendors[0]['Dat'][$index]['Display'] =  $ven->MaDoiTac .' - '. $ven->TenDoiTac;
            $aVendors[0]['Dat'][$index]['ID']      =  $ven->IOID;
            $index++;
        }

        $this->html->vendorsDialBoxData = $aVendors;
    }

    public function showAction()
    {
        $wInoutModel   = new Qss_Model_Warehouse_Inout();
        $startDate     = $this->params->requests->getParam('start', '');
        $endDate       = $this->params->requests->getParam('end', '');
        $stockIOIDArr  = $this->params->requests->getParam('warehouse', array());
        $vendorIOIDArr = $this->params->requests->getParam('vendor', array());
        $data          = $wInoutModel->getInputByTime(
            Qss_Lib_Date::displaytomysql($startDate), Qss_Lib_Date::displaytomysql($endDate), $vendorIOIDArr, $stockIOIDArr
        );
        $inputByVendor = array();
        $oldVendorIOID = '';
        $i             = 0;

        foreach($data as $d)
        {
            if($oldVendorIOID != $d->VendorIOID)
            {
                $temp            = array();
                $temp['Code']    = $d->VendorCode;
                $temp['Name']    = $d->VendorName;
                $temp['Total']   = 0; // init
                $temp['SoLuong'] = 0; // init
                $temp['Dat']     = array();
                $i               = 0;
                $inputByVendor[$d->VendorIOID] = $temp;
            }

            $oldVendorIOID = $d->VendorIOID;

            $temp2 = array();
            $temp2['DocDate']   = $d->DocDate;
            $temp2['DocNo']     = $d->DocNo;
            $temp2['ItemCode']  = $d->ItemCode;
            $temp2['ItemName']  = $d->ItemName;
            $temp2['UOM']       = $d->UOM;
            $temp2['Qty']       = $d->QTY;
            $temp2['UnitPrice'] = $d->UnitPrice;
            $temp2['Total']     = $d->Total;
            $temp2['SoLuong']   = $d->QTY;

            $inputByVendor[$d->VendorIOID]['Total']    += $d->Total;
            $inputByVendor[$d->VendorIOID]['SoLuong']  += $d->QTY;
            $inputByVendor[$d->VendorIOID]['Dat'][$i]   = $temp2;
            $i++;
        }

        $this->html->start = $startDate;
        $this->html->end   = $endDate;
        $this->html->data  = $inputByVendor;
    }
}
?>