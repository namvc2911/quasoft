<?php

class Filter_OdanhsachnhanvienController extends Qss_Lib_Controller {

    public function init() {
        parent::init();
    }

    public function indexAction() {
        $mCommon  = new Qss_Model_Extra_Extra();
        $tag      = $this->params->requests->getParam('tag', '');
        $request  = $mCommon->getDataLikeString('ODanhSachNhanVien', array('MaNhanVien', 'TenNhanVien'), $tag, array('MaNhanVien'));
        $retval   = array();
		if(count($request))
		{
        	$retval[] = array('id'=>-1, 'value'=>'Chưa giao việc');
		}

        foreach($request as $item)
        {
            $display  = "{$item->MaNhanVien} - {$item->TenNhanVien}";
            $retval[] = array('id'=>$item->IOID, 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}