<?php

class Qss_Service_Purchase_Quotation_SaveCompare extends Qss_Lib_Service
{

    public function __doExecute ($params)
    {
        $mCommon        = new Qss_Model_Extra_Extra();

        // Thay doi bao gia da chon ve trang
        $mQuote         = new Qss_Model_Purchase_Quotation();
        $selectedQuotes = $mQuote->getSelectedQuotesByPlan((int)$params['planioid']);
        $removeSelected = array();
        $indexRemove    = array();
        $indexRemove2   = 0;

        // Insert bao gia
        $insertBaoGia   = array();
        $indexBaoGia    = array();
        $indexBaoGia2   = 0;


        // Lay mang insert bao gia
        foreach($params['qifid2'] as $item)
        {
            if(!isset($indexBaoGia[$item]))
            {
                $indexBaoGia[$item] = 0;
            }

            $temp = $mCommon->getTableFetchOne('ODSBGMuaHang', array('Ref_MaSP'=>$params['itemioid2'][$indexBaoGia2], 'IFID_M406'=>$item));
            $insertBaoGia[$item]['ODSBGMuaHang'][$indexBaoGia[$item]]['GiaThuongLuong'] = $params['negotiate2'][$indexBaoGia2];
            $insertBaoGia[$item]['ODSBGMuaHang'][$indexBaoGia[$item]]['ChonBaoGia']     = 1;
            $insertBaoGia[$item]['ODSBGMuaHang'][$indexBaoGia[$item]]['ioid']           = $temp?$temp->IOID:0;
            $insertBaoGia[$item]['ODSBGMuaHang'][$indexBaoGia[$item]]['ifid']       = $item;

            $indexBaoGia[$item]++;
            $indexBaoGia2++;
        }

        //echo '<pre>'; print_r($insertBaoGia); die;

        // Xoa cac bao gia da chon truoc do
        foreach($selectedQuotes as $item)
        {
            if(!isset($indexRemove[$item->IFID_M406]))
            {
                $indexRemove[$item->IFID_M406] = 0;
            }

            $removeSelected[$item->IFID_M406]['ODSBGMuaHang'][$indexRemove[$item->IFID_M406]]['ifid']       = $item->IFID_M406;
            $removeSelected[$item->IFID_M406]['ODSBGMuaHang'][$indexRemove[$item->IFID_M406]]['ioid']       = $item->IOID;
            $removeSelected[$item->IFID_M406]['ODSBGMuaHang'][$indexRemove[$item->IFID_M406]]['ChonBaoGia'] = 0;

            $indexRemove[$item->IFID_M406]++;
        }

//        echo '<pre>'; print_r($removeSelected);
//        echo '<pre>'; print_r($insertBaoGia);


        // Them vao ke hoach
        if(!$this->isError())
        {
            $plan = $mCommon->getTableFetchOne('OKeHoachMuaSam', array('IOID'=>$params['planioid']));

            if($plan)
            {
                $i = 0;
                $insert = array();
                $insert['OKeHoachMuaSam'][0]['HinhThucSoSanh'] = (int)$params['type'];
                $insert['OKeHoachMuaSam'][0]['ioid']           = $params['planioid'];
                $insert['OKeHoachMuaSam'][0]['ifid']           = $plan->IFID_M716;

                if(isset($params['curcencycode']))
                {
                    foreach($params['curcencycode'] as $key=>$val)
                    {
                        $insert['OTyGiaKeHoachMua'][$i]['LoaiTien'] = $val;
                        $insert['OTyGiaKeHoachMua'][$i]['TyGia']    = ($params['currencies'][$key] === '')?1:$params['currencies'][$key];

                        $i++;
                    }
                }


                $service = $this->services->Form->Manual('M716',  $plan->IFID_M716,  $insert, false);
                if ($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
//                echo '<pre>'; print_r($insert);
            }
            else
            {
                $this->setError();
            }

        }
//        die;



        // Chuyen cac "Chon bao gia" da chon truoc do ve 0
        if(count($removeSelected))
        {
            foreach($removeSelected as $ifid=>$insert)
            {


                if(!$this->isError())
                {
                    $service = $this->services->Form->Manual('M406',  $ifid,  $insert, false);
                    if ($service->isError())
                    {
                        $this->setError();
                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }
                }
            }
        }

        // Chen bao gia
        if(count($insertBaoGia) && !$this->isError())
        {
            //echo '<pre>'; print_r($insertBaoGia); die;
            foreach($insertBaoGia as $ifid=>$insert)
            {
                if(!$this->isError())
                {
                    $service = $this->services->Form->Manual('M406',  $ifid,  $insert, false);
                    if ($service->isError())
                    {
                        $this->setError();
                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }
                }
            }
        }



    }
}
?>