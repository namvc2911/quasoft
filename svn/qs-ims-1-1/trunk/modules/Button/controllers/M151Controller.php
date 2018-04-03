<?php
class Button_M151Controller extends Qss_Lib_Controller
{
    public function convertEquipIndexAction() {

    }

    public function convertEquipShowAction() {
        $mCommon         = new Qss_Model_Extra_Extra();
        $mAsset          = new Qss_Model_M151_Asset();



        $duplicateCode   = $this->params->requests->getParam('M151_Convert_Equip_duplicateAssetCode', 0);
        $notInAsset      = $this->params->requests->getParam('M151_Convert_Equip_NotInAssets', 0);
        $hasNotAssetCode = $this->params->requests->getParam('M151_Convert_Equip_NoAssetCode', 0);
        $page            = $this->params->requests->getParam('M151_Convert_Equip_PageNo', 1);
        $display         = $this->params->requests->getParam('M151_Convert_Equip_Display', 1);
        $total           = $mAsset->countEquips($hasNotAssetCode, $notInAsset, $duplicateCode);
        $totalPage       = ceil($total/$display);
        $page            = $page <= $totalPage?$page:1;

        $this->html->total    = $totalPage;
        $this->html->count    = $total;
        $this->html->display  = $display;
        $this->html->page     = $page;
        $this->html->next     = (($page + 1) > $totalPage)?$totalPage:($page + 1);
        $this->html->prev     = (($page - 1) < 1)?1:($page - 1);

        $this->html->equips     = $mAsset->getEquips($hasNotAssetCode, $notInAsset, $duplicateCode, $page, $display);
        $this->html->assetTypes = $mCommon->getTableFetchAll('OLoaiCongCuDungCu');
    }

    public function convertEquipSaveAction() {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Button->M151->Convert->Equip->Save($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}