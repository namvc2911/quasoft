<?php

class Qss_Model_Sale_Order extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getDocNo($prefix = '', $length = 3)
    {
        $prefix = $prefix?$prefix:'DBH.{Y}{m}{d}.';
        $object = new Qss_Model_Object();
        $object->v_fInit('ODonBanHang', 'M505');
        $document = new Qss_Model_Extra_Document($object);
        $document->setLenth($length);
        $document->setDocField('SoDonHang');
        $document->setPrefix($prefix);
        return $document->getDocumentNo();
    }
}