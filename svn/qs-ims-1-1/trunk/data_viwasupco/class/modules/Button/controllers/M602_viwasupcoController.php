<?php
/**
 * @author: Huy.Bui
 * @component: Cac mau in su co thiet bi
 */
class Button_M602_viwasupcoController extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function indexAction() {

    }

    public function importAction() {
        $params = $this->params->requests->getParams();
        $service = $this->services->Button->M602Viwasupco->Inventory->Import($params);
        echo $service->getMessage(Qss_Service_Abstract::TYPE_HTML);
        echo $service->getData();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}