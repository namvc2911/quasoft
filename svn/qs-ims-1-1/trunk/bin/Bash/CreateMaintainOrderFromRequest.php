<?php
class Qss_Bin_Bash_CreateMaintainOrderFromRequest extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $mOrder   = new Qss_Model_Maintenance_Workorder();
        $mRequest = new Qss_Model_Maintenance_Request();
        $ifid     = $this->_params->IFID_M747;
        $request  = $mRequest->getRequestByIFID($ifid);
        $created  = ($request && $request->SoLuongPhieuBaoTri > 0)?true:false;
        //$priority = $mOrder->getDefaultPriority();
        $type     = $mOrder->getDefaultCorrective();
        $user     = Qss_Register::get('userinfo');
        $deptid   = $user->user_dept_id;

        $createdIFID = ($request && $request->BaoTriIFIDs)? explode(',', str_replace(' ', '', $request->BaoTriIFIDs)):array();
        $createdNo   = ($request && $request->BaoTriNos)? explode(',', str_replace(' ', '', $request->BaoTriNos)):array();

        if(!$created)
        {
            if($request)
            {
                $insert                                           = array();
                $insert['OPhieuBaoTri'][0]['MaKhuVuc']           = (int)$request->Ref_MaKhuVuc;
                $insert['OPhieuBaoTri'][0]['MaThietBi']           = (int)$request->Ref_MaThietBi;
                $insert['OPhieuBaoTri'][0]['TenThietBi']           = $request->TenThietBi;
                $insert['OPhieuBaoTri'][0]['MucDoUuTien']         = 2;
                $insert['OPhieuBaoTri'][0]['LoaiBaoTri']          = (int)$type->IOID;
                $insert['OPhieuBaoTri'][0]['NgayYeuCau']          = $request->Ngay;
                $insert['OPhieuBaoTri'][0]['NgayBatDauDuKien']    = $request->Ngay;

                if(Qss_Lib_System::fieldActive('OPhieuBaoTri', 'NgayDuKienHoanThanh')) {
                    $insert['OPhieuBaoTri'][0]['NgayDuKienHoanThanh'] = @$request->NgayDuKienHoanThanh;
                }

                if(Qss_Lib_System::fieldActive('OPhieuBaoTri', 'ThoiGianKetThucDuKien')) {
                    $insert['OPhieuBaoTri'][0]['ThoiGianKetThucDuKien'] = @$request->ThoiGianKetThucDuKien;
                }

                $insert['OPhieuBaoTri'][0]['PhieuYeuCau']         = (int)$request->IOID;
                $insert['OPhieuBaoTri'][0]['NguoiThucHien']         = (int)$request->Ref_NguoiChiuTranhNhiem;
                $insert['OPhieuBaoTri'][0]['MoTa']         		= $request->SoPhieu . ': ' . $request->MoTa;
                
                $insert['OPhieuBaoTri'][0]['LoaiSuCo'] = 2;
                $insert['OPhieuBaoTri'][0]['NgayDungMay'] = @$request->NgayDungMay?$request->NgayDungMay:$request->Ngay;
                $insert['OPhieuBaoTri'][0]['ThoiGianBatDauDungMay'] = @$request->ThoiGianDungMay?$request->ThoiGianDungMay:$request->ThoiGian;
                //$insert['OPhieuBaoTri'][0]['NgayKetThucDungMay'] = date('Y-m-d');
                //$insert['OPhieuBaoTri'][0]['ThoiGianKetThucDungMay'] = date('HH:mm');

                $service = $this->services->Form->Manual('M759',  0,  $insert, false);
                if ($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }

                if(!$this->isError())
                {
                    $order = $mOrder->getOrderByIFID($service->getData());
                    $this->setMessage('Phiếu bảo trì "'. $order->SoPhieu.'" đã được tạo! <a href="/user/form/edit?ifid='.$order->IFID_M759.'&deptid='.$deptid.'" style="color:blue;">Click để xem chi tiết!</a>!');
                }
            }
        }
        else
        {
            $this->setError();

            $msg = 'Yêu cầu đã được tạo phiếu bảo trì trước đó! <br/> ';
            $i   = 0;



            foreach($createdIFID as $item)
            {
                $msg .= '<a href="/user/form/edit?ifid='.$item.'&deptid='.$deptid.'" style="color:blue;" target="_blank">Click để xem chi tiết phiếu '.$createdNo[$i].'!</a><br/>';
                $i++;
            }

            $this->setMessage($msg);
        }
    }
}