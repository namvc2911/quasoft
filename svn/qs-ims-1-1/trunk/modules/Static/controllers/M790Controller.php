<?php
class Static_M790Controller extends Qss_Lib_Controller
{
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
        $start   = $this->params->requests->getParam('start', '');
        $end     = $this->params->requests->getParam('end', '');
        $loc     = $this->params->requests->getParam('location', 0);
        $EqGroup = $this->params->requests->getParam('group', 0);
        $EqType  = $this->params->requests->getParam('type', 0);
        $EqID    = $this->params->requests->getParam('equip', 0);

        $mInventory = new Qss_Model_Inventory_Inventory();


        $this->html->report = $mInventory->getInOutOfWorkOrders(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $loc
            , $EqGroup
            , $EqType
            , $EqID
        );


        /*

        $model = new Qss_Model_Extra_Warehouse();
        $ret   = array();

        $output   = $model->getOutputForRecognizeReport($start, $end, $loc
            , $EqGroup, $EqType, $EqID);
        $input    = $model->getInputForRecognizeReport($start, $end, $loc
            , $EqGroup, $EqType, $EqID);
        $MaintOrder = $model->getMaintainOrderForRecognizeReport($start, $end, $loc
            , $EqGroup, $EqType, $EqID);


        // Xuat kho
        foreach($output as $out)
        {
            $code = $out->EID .'-'. $out->ComponentID;
            // Group
            if(!isset($ret[$code]))
            {
                $ret[$code]['ECode']     = $out->ECode;
                $ret[$code]['Component'] = $out->Component;
            }

            if(!isset($ret[$code]['Item'][$out->IID]))
            {
                $ret[$code]['Item'][$out->IID]['ID']   = $out->IID;
                $ret[$code]['Item'][$out->IID]['Code'] = $out->ICode;
                $ret[$code]['Item'][$out->IID]['Name'] = $out->IName;
                $ret[$code]['Item'][$out->IID]['UOM']  = $out->UOM;
            }

            $ret[$code]['Item'][$out->IID]['Out'] = $out->Qty;
        }


        // Nhap kho
        foreach($input as $in)
        {
            $code = $in->EID .'-'. $in->ComponentID;
            // Group
            if(!isset($ret[$code]))
            {
                $ret[$code]['ECode']     = $in->ECode;
                $ret[$code]['Component'] = $in->Component;
            }


            if(!isset($ret[$code]['Item'][$in->IID]))
            {
                $ret[$code]['Item'][$in->IID]['ID']   = $in->IID;
                $ret[$code]['Item'][$in->IID]['Code'] = $in->ICode;
                $ret[$code]['Item'][$in->IID]['Name'] = $in->IName;
                $ret[$code]['Item'][$in->IID]['UOM']  = $in->UOM;
            }


            $ret[$code]['Item'][$in->IID]['In']     = $in->Qty;

            if(isset($ret[$code]['Item'][$in->IID]['Out']) && $ret[$code]['Item'][$in->IID]['Out'])
            {
                $ret[$code]['Item'][$in->IID]['InLost'] = $ret[$code]['Item'][$in->IID]['Out'] - $in->Qty;
            }
            else
            {
                $ret[$code]['Item'][$in->IID]['InLost'] = 0;
            }
        }

        // Phieu bao tri
        foreach($MaintOrder as $order)
        {
            $code = $order->EID .'-'. $order->ComponentID;
            // Group
            if(!isset($ret[$code]))
            {
                $ret[$code]['ECode']     = $order->ECode;
                $ret[$code]['Component'] = $order->Component;
            }

            if(!isset($ret[$code]['Item'][$order->IID]))
            {
                $ret[$code]['Item'][$order->IID]['ID']   = $order->IID;
                $ret[$code]['Item'][$order->IID]['Code'] = $order->ICode;
                $ret[$code]['Item'][$order->IID]['Name'] = $order->IName;
                $ret[$code]['Item'][$order->IID]['UOM']  = $order->UOM;
            }

            $ret[$code]['Item'][$order->IID]['Use']    = $order->Use;
            $ret[$code]['Item'][$order->IID]['Return'] = $order->Return;
            $ret[$code]['Item'][$order->IID]['Lost']   = $order->Lost;
        }
        $this->html->report = $ret;
        */
    }
}