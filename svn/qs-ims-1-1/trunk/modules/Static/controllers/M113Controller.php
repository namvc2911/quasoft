<?php
/**
 *
 * @author HuyBD
 *
 */
class Static_M113Controller extends Qss_Lib_Controller
{

    /**
     *
     * @return unknown_type
     */
    public function init()
    {
        parent::init();
    }

    public function saveAction()
    {
        $params    = $this->params->requests->getParams();
        $modelItem = new Qss_Model_Master_Item();


        if ($this->params->requests->isAjax())
        {
            if(isset($params['ifid']) && isset($params['item']))
            {
                $import = new Qss_Model_Import_Form('M770',false, false);
                $insert = array();
                $i      = 0;

                foreach($params['ifid'] as $ifid)
                {
                    if($params['CheckBox'][$i] == 1)
                    {
                        $insert['OVatTuThayTheTheoLoai'][0]['MaSanPham']  = (int)$params['item'][$i];
                        $insert['OVatTuThayTheTheoLoai'][0]['TenSanPham'] = (int)$params['item'][$i];
                        $insert['OVatTuThayTheTheoLoai'][0]['ifid']       = $ifid;
                        // echo '<pre>'; print_r($insert);
                        $import->setData($insert);
                    }
                    else
                    {
                        $modelItem->deleteItemsForEquipType($ifid, (int)$params['ioid'][$i]);
                    }
                    $i++;
                }
                //die;

                if(count($insert))
                {
                    $import->generateSQL();
                    // echo '<pre>'; print_r($import->getImportRows());
                }
            }

            $json = array('message'=>'', 'error'=>false, 'status'=>null, 'redirect'=> null);
            echo Qss_Json::encode($json);
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}