<?php
class Qss_Service_Inventory_Output_CreateFromWorkOrder extends Qss_Lib_Service
{

    public function __doExecute ($params)
    {
        $mInv    = new Qss_Model_Inventory_Inventory();
        $mCommon = new Qss_Model_Extra_Extra();
        $exist   = $mCommon->getTableFetchOne('OXuatKho', array(' IFNULL(Ref_PhieuBaoTri, 0)'=>$params['orderIOID']));

        if(!$exist)
        {
            $dataLoaiXuatKho = $mCommon->getTableFetchOne('OLoaiXuatKho', array(' Loai'=>Qss_Lib_Extra_Const::OUTPUT_TYPE_MAINTAIN));
            $dataKho         = $mInv->getWarehouseByWorkcenter($params['workCenter']);

            $insert = array();

            if(!$dataKho)
            {
                $this->setError();
                $this->setMessage('Cần có ít nhất một kho vật tư để tạo phiếu xuất kho!');
                return;
            }

            // Main Obj
            $insert['OXuatKho'][0]['Kho']         = (int) $dataKho->IOID;
            $insert['OXuatKho'][0]['LoaiXuatKho'] = (int) $dataLoaiXuatKho->IOID;
            $insert['OXuatKho'][0]['NgayChungTu'] = $params['startDate'];
            $insert['OXuatKho'][0]['PhieuBaoTri'] = (int)$params['orderIOID'];

            $i = 0;
            foreach ($params['materials'] as $item)
            {
                $insert['ODanhSachXuatKho'][$i]['MaSP']      = (int) $item->Ref_MaVatTu;
                $insert['ODanhSachXuatKho'][$i]['DonViTinh'] = (int) $item->Ref_DonViTinh;
                $insert['ODanhSachXuatKho'][$i]['SoLuong']   = $item->SoLuongDuKien;
                $i++;
            }

            $service = $this->services->Form->Manual('M506' ,0,$insert,false);


            if(!$this->isError())
            {
                $form = new Qss_Model_Form();
                $form->initData($service->getData(), $params['user']->user_dept_id);
                $service2 = $this->services->Form->Request($form, 4, $params['user'] , '');

                if ($service2->isError())
                {
                    $this->setError();
                    $this->setMessage($service2->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }


            if($service->isError())
            {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
//            else
//            {
//                // Qss_Service_Abstract::$_redirect = '/user/form/edit?ifid='.$service->getData().'&deptid=1';
//
//                $this->setMessage('Yêu cầu đã được gửi đi.  <a href="/user/form/detail?ifid='.$service->getData().'&deptid=1">Click để xem chi tiết</a>');
//            }
        }
        else
        {
            $this->setMessage('Yêu cầu đã được gửi trước đó.  <a href="/user/form/detail?ifid='.$exist->IFID_M506.'&deptid=1">Click để xem chi tiết</a>');
        }
    }
}
?>