<?php

/**
 * Class Qss_View_Report_SimpleListBox
 *
 * Hướng đãn sử dụng
 *
 * B1: Tạo một action
 *
 *
    public function itemsAction()
    {
        $params = $this->params->requests->getParams();
        $mItem  = new Qss_Model_Master_Item();
        $items  = $mItem->getItemByCodeOrName($params['tag']); // Query lay du lieu
        $retval = array();

        foreach ($items as $item)
        {
            $display  = "{$item->MaSanPham} - {$item->TenSanPham}";
            $retval[] = array('id'=>$item->IOID, 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
 *
 * B2: Gọi view
 * <?php echo $this->views->Report->SimpleListBox('item', '/static/m607/items');?>
 *
 *
 */
class Qss_View_Report_SimpleListBox extends Qss_View_Abstract
{
    /**
     * @param string $id
     * @param string $url url
     * @param string $data json du lieu loc truyen vao
     * @param string $htmlAttribute
     */
    public function __doExecute ($id = '', $url = '', $htmlAttribute = 'style="width:200px;"', $name = '', $defaultID = 0, $defaultText = '', $placeHolder = '')
    {

    }
}
