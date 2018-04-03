<?php
class Qss_Bin_Trigger_ODonViTinhSP extends Qss_Lib_Trigger
{
        /**
         * onInsert:
         * 
         */
	public function onInsert($object)
        {
            parent::init();
        }
        
        /**
         * onUpdate:
         * - Can not change conversion rate
         * 
         */
	public function onUpdate($object)
        {
            parent::init();
            $defaultFieldVal    = 0;
            $newConRateFieldVal = $object->getFieldByCode('HeSoQuyDoi')->getValue();
            $newQtyAttrFieldVal = $object->getFieldByCode('SuDungTTSL')->getValue()?1:0;
            $newUomAttrFieldVal = $object->getFieldByCode('DonViTinh')->getValue();
            
            $oldConRateFieldVal = 0;
            $oldQtyAttrFieldVal = 0;
            $oldUomAttrFieldVal = 0;
            
            foreach($this->_params->ODonViTinhSP as $uom)
            {
                if($uom->IOID == $object->i_IOID)
                {
                    $defaultFieldVal    = (int)$uom->MacDinh;
                    $oldConRateFieldVal = $uom->HeSoQuyDoi;
                    $oldQtyAttrFieldVal = (int)$uom->SuDungTTSL;
                    $oldUomAttrFieldVal = $uom->DonViTinh;
                    
                }
            }
            
            if($defaultFieldVal == 1 && (
                        ($newConRateFieldVal != $oldConRateFieldVal) 
                        ||($newQtyAttrFieldVal != $oldQtyAttrFieldVal)
                        ||($newUomAttrFieldVal != $oldUomAttrFieldVal)
                    
                ) )
            {
                $this->setMessage($this->_translate(2));
                $this->setError();
            }
        }
        
        /**
         * onDelete:
         * - Can not remove base uom
         */
	public function onDelete($object)
        {
            
            parent::init();
            $defaultFieldVal = 0;
            foreach($this->_params->ODonViTinhSP as $uom)
            {
                if($uom->IOID == $object->i_IOID)
                {
                    $defaultFieldVal = (int)$uom->MacDinh;
                }
            }
            
            if($defaultFieldVal == 1)
            {
                $this->setMessage($this->_translate(1));
                $this->setError();
            }
        }
	
        /**
         * onInserted:
         * 
         */
        public function onInserted($object)
        {
            parent::init();
        }
        
        /**
         * onUpdated:
         * 
         */
	public function onUpdated($object)
        {
            parent::init();
        }
        
        /**
         * onDeleted:
         * 
         */
	public function onDeleted($object)
        {
            parent::init();
        }
	
}
