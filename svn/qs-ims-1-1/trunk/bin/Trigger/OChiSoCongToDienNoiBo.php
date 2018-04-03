<?php
class Qss_Bin_Trigger_OChiSoCongToDienNoiBo extends Qss_Lib_Trigger
{
    /**
     * onInserted
     */
    public function onInserted($object)
    {
        parent::init();
        $this->updateRate($object);
    }
    
	/**
	 * onUpdate
	 */
    public function onUpdated($object)
	{
		parent::init();		
        $this->updateRate($object);	
	}
	
	public function updateRate($object)
	{
	    $cmonth    = (int)date('m');
	    $cyear     = (int)date('Y');
	    $month     = @(int)$object->getFieldByCode('Thang')->getValue();
	    $year      = @(int)$object->getFieldByCode('Nam')->getValue();
	    	    	    
	    $common    = new Qss_Model_Extra_Extra();
	    $meterIOID = $object->getFieldByCode('MaCongTo')->getRefIOID();
	    $rate      = $object->getFieldByCode('HeSo')->getValue();
	    
	    
	    $meter     = $common->getTableFetchOne('OCongToDien', array('IOID'=>@(int)$meterIOID));
	    

	    
	    
	    if($object->getFieldByCode('ThayCongTo')->getValue() && $meter)
	    {
	        
	        
	        $insert['OCongToDien'][0]['HeSo'] = $rate;
	        $insert['OCongToDien'][0]['ifid'] = $meter->IFID_M556;
	        $insert['OCongToDien'][0]['ioid'] = $meter->IOID;
	        
	       	       
	    
	        $services =  $this->services->Form->Manual('M556', $meter->IFID_M556, $insert, false);
	    
	        if($services->isError())
	        {
	            $this->setError();
	            $this->setMessage($services->getMessage(Qss_Service_Abstract::TYPE_TEXT));
	        }
	    }
	}

}