<?php
/**
 * Class Button_M408Controller
 * Các button module Nhận hàng - M408
 */
class Button_M408Controller extends Qss_Lib_Controller
{  
    
    public function init()
    {
        $this->i_SecurityLevel = 15;
        parent::init();         
    }
    
    public function getbypoIndexAction()
    {
        $mReceive = new Qss_Model_Purchase_Receive();
        $ifid     = $this->params->requests->getParam('ifid', 0);
        $receive  = $mReceive->getReceiveByPO($ifid);
        
        $this->html->receive = $receive;
        $this->html->ifid    = $ifid;
    }
    
    public function getbypoSaveAction()
    {
        $params = $this->params->requests->getParams();
        
        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Purchase->Receive->CreateOrder($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);    
    }    
    
    private function _getOrder($receiveIFID)
    {
        
    }
}