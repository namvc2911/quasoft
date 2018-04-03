<?php
class Button_M401Controller extends Qss_Lib_Controller
{
    public function init()
    {

        $this->i_SecurityLevel = 15;
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/common.js');
    }

    /**
     * Button: Nút lấy phụ tùng vật tư
     */
    public function materialIndexAction()
    {
        // Lay ifid de truyen sang cac action khac
        $ifid = $this->params->requests->getParam('ifid', 0);
        $this->html->ifid = $ifid;
    }

    /**
     * Hien thi dialbox chon mat hang
     */
    public function materialSelectAction()
    {
        $mRequest    = new Qss_Model_Purchase_Request();
        $mItem       = new Qss_Model_Master_Item();
        $filter      = $this->params->requests->getParam('m401_material_filter', 0);

        $this->html->filter = $filter;
        $this->html->items  = $mItem->getItems();

        if($filter == 1)
        {
            $this->html->requests = $mRequest->getRequestDetails(); // lay cac yeu cau da duoc duyet
        }
        else
        {
            $this->html->items    = $mItem->getItems();
        }
    }

    /**
     * Hien thi danh sach mat hang da chon de tao danh sach nhap kho
     */
    public function materialShowAction()
    {
        $mOrder             = new Qss_Model_Purchase_Order();
        $ifid               = $this->params->requests->getParam('ifid', 0);
        $this->html->order  = $mOrder->getOrderLineByIFID($ifid);
    }


    /**
     * Luu lai mat hang da chon vao phieu nhap sau khi xoa danh sach da co
     */
    public function materialSaveAction()
    {
        $params = $this->params->requests->getParams();

        $params['deptid'] = $this->_user->user_dept_id;

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Purchase->Order->SaveLines($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}