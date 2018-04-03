<?php
/**
 * Description of OBangThuocTinh
 *
 * @author Thinh
 */
class Qss_Bin_Trigger_OBangThuocTinh extends Qss_Lib_Trigger
{
    /**
     * 
     */
    public function onInsert($object)
    {
        //$this->checkDefaultWhenUpdate($object);
    }
    
    /**
     * 
     */
    public function onUpdate($object)
    {
        //$this->checkDefaultWhenUpdate($object);
    }
    
    
    private function checkDefaultWhenUpdate(Qss_Model_Object $object)
    {
        parent::init();
        $common             = new Qss_Model_Extra_Extra();
        $defaultFieldValNew = (int)$object->getFieldByCode('MacDinh')->bBoolean;
        $defaultFieldValOld = (int)$this->_params->MacDinh;
        $refItem            = $this->_params->Ref_MaSP;
		$where				= sprintf('Ref_MaSP = %1$d and  MacDinh = 1 and IOID != %2$d', $refItemm, $object->i_IOID);
        $getDefaultCurrent  = $common->getTable(array('*'), 'OBangThuocTinh'
                                                , $where
                                                , array(), 1, 1 );
       
        if($defaultFieldValNew == 1)
        {
            if($getDefaultCurrent)
            {
                $data['OBangThuocTinh'][0]['MacDinh'] = 0;  
                $data['OBangThuocTinh'][0]['ioid']    = $getDefaultCurrent->IOID; 
            
                $service = $this->services->Form->Manual('M119', $object->i_IFID, $data, false);

                if ($service->isError()) 
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }
        }
    }
    
}
