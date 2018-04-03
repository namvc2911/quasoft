<?php

class Qss_Model_Sale_Quote extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getDocNo($prefix = '', $length = 3)
    {
        $prefix = $prefix?$prefix:'BGB.{Y}{m}{d}.';
        $object = new Qss_Model_Object();
        $object->v_fInit('OBaoGiaBanHang', 'M509');
        $document = new Qss_Model_Extra_Document($object);
        $document->setLenth($length);
        $document->setDocField('SoBaoGia');
        $document->setPrefix($prefix);
        return $document->getDocumentNo();
    }
}