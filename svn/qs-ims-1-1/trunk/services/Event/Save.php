<?php

class Qss_Service_Event_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{       
                // Check name require
                // Check type is int
                // Check time require
                if(!isset($params['times']) || !count($params['times']))
                {
                    $this->setError();
                    $this->setMessage('Thời gian yêu cầu bắt buộc!');
                }
                
                if(!$this->isError())
                {
                    $event = new Qss_Model_Event();
                    $event->save($params);
                }
	}

}
?>