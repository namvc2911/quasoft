<?php

class Qss_Service_Extra_Production_Createmo_Requirement_Save extends Qss_Service_Abstract
{

        public function __doExecute($params)
        {
                $insert = array();
                $common = new Qss_Model_Extra_Extra();
                $workCenter = array();
                $pQtyArr = array();

                /// Lay so luong san xuat tren gio cua day chuyen 
                $pQtyInfo = $common->getObjectByIDArr($params['lineFilter']
                        , array('code' => 'M702'
                    , 'table' => 'OSanPhamCuaDayChuyen'
                    , 'main' => 'ODayChuyen'));
                foreach ($pQtyInfo as $item)
                {
                        $pQtyArr[$item->Ref_MaSanPham] = $item->SoLuongTrenGio;
                }

                /// Lay cong doan don vi thuc hien theo day chuyen									  		  
                $operationInfo = $common->getObjectByIDArr($params['lineFilter']
                        , array('code' => 'M702'
                    , 'table' => 'OCongDoanDayChuyen'
                    , 'main' => 'ODayChuyen'));

                foreach ($operationInfo as $item)
                {
                        $workCenter[$item->Ref_CongDoan]['Info'][$item->Ref_MaDonViThucHien]['Name'] = $item->DonViThucHien;
                        $workCenter[$item->Ref_CongDoan]['Info'][$item->Ref_MaDonViThucHien]['Percent'] = $item->HieuSuat;

                        if (isset($workCenter[$item->Ref_CongDoan]['Total']))
                        {
                                $workCenter[$item->Ref_CongDoan]['Total'] += $item->HieuSuat;
                        }
                        else
                        {
                                $workCenter[$item->Ref_CongDoan]['Total'] = $item->HieuSuat;
                        }
                }

                // @todo: Save lai so luong dat san xuat cho 
                // @todo: Tinh thoi gian can de san xuat
                // Lay dong chinh
                $i = 0;
                foreach ($params['bom'] as $bomID)
                {
                        $qtyPerHour = isset($pQtyArr[$params['RefItem'][$i]]) ? $pQtyArr[$params['RefItem'][$i]] : 0;

                        if ($qtyPerHour)
                        {
                                $insert['OSanXuat'][$i]['SanXuatSuaChua'] = 1;
                                $insert['OSanXuat'][$i]['TuNgay'] = $params['fromDateFilter'];
                                $insert['OSanXuat'][$i]['DenNgay'] = $params['toDateFilter'];
                                $insert['OSanXuat'][$i]['ThoiGian'] = $qtyPerHour ? $params['ItemQty'][$i] / $qtyPerHour : 0; // plus
                                $insert['OSanXuat'][$i]['ThietKe'] = $params['BOMName'][$i];
                                $insert['OSanXuat'][$i]['DayChuyen'] = $params['lineName'];
                                $insert['OSanXuat'][$i]['ThaoDo'] = $params['Assembly'][$i];
                                $insert['OSanXuat'][$i]['MaSP'] = $params['ItemCode'][$i];
                                $insert['OSanXuat'][$i]['DonViTinh'] = $params['ItemUOM'][$i];
                                $insert['OSanXuat'][$i]['ThuocTinh'] = $params['Attribute'][$i];
                                $insert['OSanXuat'][$i]['SoLuong'] = $params['ItemQty'][$i];
                                $insert['OSanXuat'][$i]['ioidlink'] = $params['IOID'][$i];
                                $i++;
                        }
                }

                $service = $this->services->Form->Manual('M710', 0, $insert, false);
                if ($service->isError())
                {
                        $this->setError();
                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }

                if (!$this->isError())
                {
                        $this->setMessage('Cập nhật thành công!');
                }
        }
}

?>