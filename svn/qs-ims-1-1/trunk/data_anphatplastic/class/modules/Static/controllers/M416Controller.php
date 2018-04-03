<?php
class Static_M416Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->i_SecurityLevel = 15;
        parent::init();
        $this->layout          = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $start              = $this->params->requests->getParam('start', date('Y-m-01'));
        $end                = $this->params->requests->getParam('end', date('Y-m-t'));
        $employee           = $this->params->requests->getParam('emp', 0);
        $doc                = $this->params->requests->getParam('docno', 0);
        $notenogh           = $this->params->requests->getParam('notenough', 0);
        $materials          = $this->params->requests->getParam('materials', array());
        $mRequest           = new Qss_Model_Purchase_Aprequest();
        $myStart            = Qss_Lib_Date::displaytomysql($start);
        $myEnd              = Qss_Lib_Date::displaytomysql($end);
        $track              = $mRequest->getTrackRequests($myStart, $myEnd, $doc, $employee, $materials);
        $oldRequest         = '';
        $oldItem            = '';
        $oldItemName        = '';
        $oldNhapKho         = '';


        $countItemByRequest = array(); //
        // $countMaxNumReceipt = 2; // Đếm số lần nhập kho của từng mặt hàng trong từng yêu cầu, lấy ra số lần lớn nhất

        $retval             = array();
        $i                  = -1;
        $lanThu             = 0;
        $maxLanThu          = 2;

        foreach($track as $item)
        {
            if($oldRequest != $item->RequestIOID
                || $oldItem != $item->Ref_MaSP
                || $oldItemName != $item->TenSP
                // || $oldNhapKho != $item->SoPhieuNhapKho
            )
            {
                // 1
                if(!isset($countItemByRequest[$item->RequestIOID]))
                {
                    $countItemByRequest[$item->RequestIOID] = 0;
                }
                ++$countItemByRequest[$item->RequestIOID];

                // 3
                If($lanThu > 0 && ((int)$maxLanThu < (int)$lanThu))
                {
                    $maxLanThu = $lanThu;
                }

                // 2
                ++$i;
                $retval[$i]                  = new stdClass();
                $retval[$i]                  = $item;
                $retval[$i]->TongSoDaNhapKho = 0;
                $lanThu                      = 0; // reset
            }

            $lanThu++;


            $retval[$i]->TongSoDaNhapKho     += $item->SoLuongNhapKho;
            $retval[$i]->{"NgayVe_".$lanThu}  = $item->NgayChuyenHangNhapKho;
            $retval[$i]->{"SoLuong_".$lanThu} = $item->SoLuongNhapKho;

            $oldRequest  = $item->RequestIOID;
            $oldItem     = $item->Ref_MaSP;
            $oldItemName = $item->TenSP;
            $oldNhapKho  = $item->SoPhieuNhapKho;
        }

        if($notenogh)
        {
            foreach ($retval as $index=>$item)
            {
                if(($item->SoLuong - $item->TongSoDaNhapKho) <= 0)
                {
                    unset($retval[$index]);
                    if(isset($countItemByRequest[$item->RequestIOID]) && $countItemByRequest[$item->RequestIOID] > 1)
                    {
                        $countItemByRequest[$item->RequestIOID] = $countItemByRequest[$item->RequestIOID] - 1;
                    }

                }
            }
        }

        If($lanThu > 0 && ((int)$maxLanThu < (int)$lanThu))
        {
            $maxLanThu = $lanThu;
        }

        // echo '<pre>'; print_r($retval); die;

        $this->html->track              = $retval;
        $this->html->countItemByRequest = $countItemByRequest;
        $this->html->countMaxNumReceipt = $maxLanThu;
    }

    public function employeeAction()
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $tag      = $this->params->requests->getParam('tag', '');
        $request  = $mCommon->getDataLikeString('ODanhSachNhanVien', array('MaNhanVien', 'TenNhanVien'), $tag, array('MaNhanVien'));
        $retval   = array();

        foreach($request as $item)
        {
            $display  = "{$item->MaNhanVien} - {$item->TenNhanVien}";
            $retval[] = array('id'=>$item->IOID, 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function requestAction()
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $tag      = $this->params->requests->getParam('tag', '');
        $request  = $mCommon->getDataLikeString('OYeuCauMuaSam', array('SoPhieu'), $tag, array('Ngay DESC'));
        $retval   = array();

        foreach($request as $item)
        {
            $display  = "{$item->SoPhieu} {$item->NguoiDeNghi} (".Qss_Lib_Date::mysqltodisplay($item->Ngay).")";
            $retval[] = array('id'=>$item->IOID, 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}