<?php
class Qss_View_Object_OLoaiThietBiSuDungTheoVatTu extends Qss_View_Abstract
{
    public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder ,$i_GroupBy)
    {
        $modelItem   = new Qss_Model_Master_Item();
        $modelCommon = new Qss_Model_Extra_Extra();

        // Lay mat hang hien tai
        $item                 = $modelCommon->getTableFetchOne('OSanPham', array('IFID_M113'=>$form->i_IFID));
        $ioid                 = $item?$item->IOID:0;
        $this->html->htmlItem = $item;

        // Lay danh sach loai thiet bi
        $this->html->htmlData = $modelItem->getEquipTypeUseList($ioid);
    }
}