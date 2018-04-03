<?php
class Qss_Bin_Trigger_OCauTrucThietBi extends Qss_Lib_Trigger
{

    /**
     * onInserted
     */
    public function onInserted($object)
    {
        // parent::init();
        //$this->insertSparepart($object);
    }

    public function onUpdated($object)
    {
        // parent::init();
        // $this->insertSparepart($object);
    }

    /**
     * Không sử dụng nữa vì phụ tùng đã chuyển sang cấu trúc thiết bị
     * @param Qss_Model_Object $object
     */
    public function insertSparepart(Qss_Model_Object $object)
    {
        if(Qss_Lib_Extra::checkFieldExists('OCauTrucThietBi', 'PhuTung')
            && Qss_Lib_System::fieldActive('OCauTrucThietBi', 'PhuTung'))
        {
            $phuTung = $object->getFieldByCode('PhuTung')->getValue();
            $viTri   = $object->getFieldByCode('ViTri')->getValue();
            $refViTri = $object->getFieldByCode('ViTri')->getRefIOID();
            $masp = (int)$object->getFieldByCode('MaSP')->getRefIOID();
            $dvt = (int)$object->getFieldByCode('DonViTinh')->getRefIOID();
            $soluong = $object->getFieldByCode('SoLuong')->getValue();
            $insert  = array();
            $common  = new Qss_Model_Extra_Extra();
            $exists  = $common->getTableFetchAll('ODanhSachPhuTung', array('Ref_ViTri'=>$refViTri, 'IFID_M705'=>$this->_params->IFID_M705));

            if($phuTung == 1)
            {

                if(count($exists)) // update lai
                {
                    foreach($exists as $item)
                    {
                        $insert['ODanhSachPhuTung'][0]['ViTri'] = $viTri;
                        $insert['ODanhSachPhuTung'][0]['MaSP'] = $masp;
                        $insert['ODanhSachPhuTung'][0]['DonViTinh'] = $dvt;
                        $insert['ODanhSachPhuTung'][0]['SoLuongHC'] = $soluong;
                        $insert['ODanhSachPhuTung'][0]['ioid']  = $item->IOID;

                        $services =  $this->services->Form->Manual('M705'
                            , $this->_params->IFID_M705
                            , $insert
                            , false);

                        if($services->isError())
                        {
                            $this->setError();
                            $this->setMessage($services->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                        }
                    }
                }
                else // insert moi
                {
                    $insert['ODanhSachPhuTung'][0]['ViTri'] = $viTri;
					$insert['ODanhSachPhuTung'][0]['MaSP'] = $masp;
					$insert['ODanhSachPhuTung'][0]['DonViTinh'] = $dvt;
					$insert['ODanhSachPhuTung'][0]['SoLuongHC'] = $soluong;
                    $services =  $this->services->Form->Manual('M705'
                        , $this->_params->IFID_M705
                        , $insert
                        , false);

                    if($services->isError())
                    {
                        $this->setError();
                        $this->setMessage($services->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }
                }


            }
        }
    }
}