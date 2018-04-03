<?php
class Static_M625Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
    }

    public function indexAction()
    {
        $mInventory = new Qss_Model_Inventory_Inventory();
        $aTypes     = array();
        $oTypes     = $mInventory->getOutputTypes();
        $index      = 0;

        foreach($oTypes as $ven)
        {
            $aTypes[0]['Dat'][$index]['Display'] = $ven->Ten;
            $aTypes[0]['Dat'][$index]['ID']      = $ven->IOID;
            $index++;
        }

        $this->html->typesDialBoxData = $aTypes;
    }

    public function showAction()
    {
        $wInoutModel   = new Qss_Model_Warehouse_Inout();
        $startDate     = $this->params->requests->getParam('start', '');
        $endDate       = $this->params->requests->getParam('end', '');
        $stockIOIDArr  = $this->params->requests->getParam('warehouse', array());
        $typeIOIDArr   = $this->params->requests->getParam('type', array());

        $data          = $wInoutModel->getOutputByType(
            Qss_Lib_Date::displaytomysql($startDate)
            , Qss_Lib_Date::displaytomysql($endDate)
            , $stockIOIDArr
            , $typeIOIDArr);

        $outputByType = array();
        $oldTypeIOID = '';
        $i             = 0;
        $totalByVenDor = 0;// init

        foreach($data as $d)
        {
            if($oldTypeIOID != $d->VendorIOID)
            {
                $temp          = array();
                $temp['Code']  = $d->VendorCode;
                $temp['Name']  = $d->VendorName;
                $temp['Total'] = 0; // init
                $temp['SoLuong'] = 0; // init
                $temp['Dat']   = array();
                $totalByVenDor = 0; // reset
                $i             = 0;
                $outputByType[$d->VendorIOID] = $temp;
            }
            $oldTypeIOID = $d->VendorIOID;

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

            $outputByType[$d->VendorIOID]['Total']  += $d->Total;
            $outputByType[$d->VendorIOID]['SoLuong']  += $d->QTY;
            $outputByType[$d->VendorIOID]['Dat'][$i] = $temp2;
            $i++;

        }

        $this->html->start = $startDate;
        $this->html->end   = $endDate;
        $this->html->data  = $outputByType;
    }
}
