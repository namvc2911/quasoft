<?php
class Static_M418Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $start              = $this->params->requests->getParam('start', date('Y-m-01'));
        $end                = $this->params->requests->getParam('end', date('Y-m-t'));
        $item               = $this->params->requests->getParam('item', 0);
        $serial             = $this->params->requests->getParam('serial', 0);
        $mRequest           = new Qss_Model_Maintenance_Workorder_Material();
        $myStart            = Qss_Lib_Date::displaytomysql($start);
        $myEnd              = Qss_Lib_Date::displaytomysql($end);
        $dat                = $mRequest->getSerialHistory($myStart, $myEnd, $item, $serial);
        $track              = array();

        foreach($dat as $item) {
            $key = @(int)$item->Ref_ThietBi.'_'.@(int)$item->Ref_ViTri;

            if($item->Ref_Serial) // Lap
            {
                // cap nhat ngay lap cua serial moi
                $track[$item->Ref_Serial][$key]           = $item;
                $track[$item->Ref_Serial][$key]->NgayLap  = $item->NgayCaiDatThaoDo;
                $track[$item->Ref_Serial][$key]->NgayThao = '';
                $track[$item->Ref_Serial][$key]->SoSerial = $item->Serial;
            }

            if($item->Ref_SerialKhac) // Thao
            {
                // ngay thao ra cua serial cu
                // $track[$item->Ref_ToSerial][$key]           = $item;
                // $track[$item->Ref_ToSerial][$key]->SoSerial = $item->ToSerial;
                $track[$item->Ref_SerialKhac][$key]->NgayThao = $item->NgayCaiDatThaoDo;
            }
        }

        //echo '<pre>'; print_r($dat); die;

        $this->html->track  = $track;
    }

    public function itemsAction()
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $tag      = $this->params->requests->getParam('tag', '');
        $request  = $mCommon->getDataLikeString('OSanPham', array('MaSanPham', 'TenSanPham'), $tag, array('MaSanPham'));
        $retval   = array();

        foreach($request as $item)
        {
            $display  = "{$item->MaSanPham} - {$item->TenSanPham}";
            $retval[] = array('id'=>$item->IOID, 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function serialsAction()
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $tag      = $this->params->requests->getParam('tag', '');
        $request  = $mCommon->getDataLikeString('OThuocTinhChiTiet', array('SoSerial'), $tag, array('SoSerial'));
        $retval   = array();

        foreach($request as $item)
        {
            if($item->IFID_M602 && $item->SoSerial)
            {
                $display  = "{$item->SoSerial} - {$item->MaSanPham}";
                $retval[] = array('id'=>$item->IOID, 'value'=>$display);
            }
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}

//$dat = array();
//
//$dat[0] = new stdClass();
//
//$dat[0]->Ref_ThietBi             = 1;
//$dat[0]->MaThietBi  = 'abc';
//$dat[0]->TenThietBi = 'abc';
//$dat[0]->NgayCaiDatThaoDo = '20-10-2016';
//$dat[0]->Ref_ViTri = 1;
//$dat[0]->ViTri = 'a';
//$dat[0]->BoPhan = 'a';
//$dat[0]->Serial = '';
//$dat[0]->Ref_Serial = 0;
//$dat[0]->ToSerial = 'ABC';
//$dat[0]->Ref_ToSerial = 1;
//$dat[0]->MaVatTu = 'ABC';
//$dat[0]->TenVatTu = 1;
//
//
//$dat[1]->Ref_ThietBi  = 1;
//$dat[1]->MaThietBi  = 'abc';
//$dat[1]->TenThietBi = 'abc';
//$dat[1]->NgayCaiDatThaoDo = '20-10-2016';
//$dat[1]->Ref_ViTri = 1;
//$dat[1]->ViTri = 'a';
//$dat[1]->BoPhan = 'a';
//$dat[1]->Serial = 'ABC';
//$dat[1]->Ref_Serial = 1;
//$dat[1]->ToSerial = 'DEF';
//$dat[1]->Ref_ToSerial = 2;
//$dat[1]->MaVatTu = 'd';
//$dat[1]->TenVatTu = 2;


//        echo '<pre>'; print_r($track); die;
//        $track[1]['1_1']->MaThietBi  = 'ThietBi1';
//        $track[1]['1_1']->TenThietBi = 'ThietBi1';
//        $track[1]['1_1']->ViTri      = 'ViTri';
//        $track[1]['1_1']->BoPhan     = 'ThietBi1';
//        $track[1]['1_1']->NgayLap    = '20-10-2016';
//        $track[1]['1_1']->NgayThao   = '22-10-2016';