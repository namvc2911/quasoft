<?php
class Button_M412Controller extends Qss_Lib_Controller
{
    public function init()
    {

        $this->i_SecurityLevel = 15;
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/common.js');
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/popup.php';
    }

    /**
     * Button: Nút lấy phụ tùng vật tư
     */
    public function materialIndexAction()
    {
        // Lay ifid de truyen sang cac action khac
        $mStock  = new Qss_Model_Warehouse_Main();
        $ifid    = $this->params->requests->getParam('ifid', 0);
        $filter  = $this->params->requests->getParam('m412_material_filter', 0);

        $this->html->stocks = ($filter != 1)?$mStock->getAll($this->_user):array();
        $this->html->ifid   = $ifid;
    }

    /**
     * Hien thi dialbox chon mat hang
     */
    public function materialSelectAction()
    {
        $mRequest    = new Qss_Model_Purchase_Request();
        $mItem       = new Qss_Model_Master_Item();
        $mStock      = new Qss_Model_Warehouse_Main();
        $filter      = $this->params->requests->getParam('m412_material_filter', 0);

        $this->html->stocks       = ($filter != 1)?$mStock->getAll($this->_user):array();
        $this->html->filter       = $filter;
        $this->html->underminimum = ($filter == 1)?$mRequest->getUnderMinimumItems($this->_user):array();
        $this->html->items        = ($filter != 1)?$mItem->getInventoryOfItems():array();
    }

    /**
     * Hien thi danh sach mat hang da chon de tao danh sach nhap kho
     */
    public function materialShowAction()
    {
        $mRequest           = new Qss_Model_Purchase_Request();
        $ifid               = $this->params->requests->getParam('ifid', 0);
        $this->html->order  = $mRequest->getRequestLineByIFID($ifid);
    }

    public function materialDetailAction()
    {
        $mCommon  = new Qss_Model_Inventory_Inventory();
        $ioid     = $this->params->requests->getParam('item', 0);
        $this->html->detail = $mCommon->getInventoryOfItem($ioid, Qss_Lib_Extra_Const::WAREHOUSE_TYPE_MATERIAL, true);
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
            $service = $this->services->Purchase->Request->SaveLines($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function materialInventoryAction()
    {
        $mInv  = new Qss_Model_Warehouse_Inventory();
        $item  = $this->params->requests->getParam('item', 0);
        $stock = $this->params->requests->getParam('stock', 0);
        $data  = array();

        if ( $this->params->requests->isAjax())
        {
            if($item)
            {
                $inventory = $mInv->getSimpleInventory($item, 0, $stock);

                if(isset($inventory[0]))
                {
                    $data['min'] = $inventory[0]->Min;
                    $data['inv'] = $inventory[0]->Qty;
                }
            }
        }

        echo Qss_Json::encode(array('error'=>0,'message'=>'', 'data'=>$data));

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);

    }
}