<?php
class Qss_Lib_Mrp_Service extends Qss_Service_Abstract
{
    public $_common;
    
    public function __construct() 
    {
        parent::__construct();
        $this->_common = new Qss_Model_Extra_Extra;
    }
    
    /**
     * 
     * @param type $params
     * @des: update lai tham chieu vao doi tuong chinh
     */
    protected function _updateReferenceForRequirement($params)
    {
        $detailLines = $this->_common->getTable(array('*'), 'OChiTietYeuCau'
                            , array('IFID_M764'=>$params['ifid']), array(), 'NO_LIMIT');
        $ioidUpdate  = $this->_common->getTable(array('*'), 'OYeuCauSanXuat'
                            , array('IFID_M764'=>$params['ifid']), array(), 1, 1);
        $refArr      = array();
        $insert2     = array();

        foreach ($detailLines as $val) 
        {
            $refArr[] = $val->ThamChieu;
        }

        $refArr = array_unique($refArr);

        $insert2['OYeuCauSanXuat'][0]['ioid'] = $ioidUpdate->IOID;
        if(count($refArr) == 1)
        {
            $insert2['OYeuCauSanXuat'][0]['ThamChieu'] = $refArr[0];
        }
        else
        {
            $insert2['OYeuCauSanXuat'][0]['ThamChieu'] = implode(', ', $refArr);
        }

        $service = $this->services->Form->Manual('M764' , $params['ifid'], $insert2, false);
        if($service->isError())
        {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
        }
    }
}