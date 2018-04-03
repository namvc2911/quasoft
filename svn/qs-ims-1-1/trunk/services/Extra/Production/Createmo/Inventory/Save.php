<?php

class Qss_Service_Extra_Production_Createmo_Inventory_Save extends Qss_Service_Abstract
{

        public function __doExecute($params)
        {
                $model = new Qss_Model_Extra_Production();
                $common = new Qss_Model_Extra_Extra();
                $insert = array();
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

                foreach ($params['operation'] as $item)
                {
                        //$temp = unserialize(Qss_Lib_Extra::formatUnSerialize($item));
                        $temp = unserialize($item);
                        $item2 = $temp[0];

                        //$conLai = $params['itemAndAttrQty'][$item2['Key']];
                        //$perWC = (int)$params['itemAndAttrQty'][$item2['Key']]/$params['countWorkCenter'][$item2['ID']];
//				foreach ($params['lineOperation'][$item2['ID']] as $wcKey=>$item3)
//				{
                        $qtyPerHour = isset($pQtyArr[$item2['RefItem']]) ? $pQtyArr[$item2['RefItem']] : 0;
                        $ifid = 0;
                        $insert['OSanXuat'][0]['SanXuatSuaChua'] = 1;
                        $insert['OSanXuat'][0]['TuNgay'] = $params['dateFilter'];
                        $insert['OSanXuat'][0]['DenNgay'] = $params['endDateFilter'];
                        $insert['OSanXuat'][0]['DayChuyen'] = $params['line'];
                        //$insert['OSanXuat'][0]['CaSX'] = $params['shift'];
                        //$insert['OSanXuat'][0]['CongDoan'] = $item2['Name'];
                        ///$insert['OSanXuat'][0]['DonViSanXuat'] = $item3;
                        $insert['OSanXuat'][0]['ThietKe'] = $item2['BOM'];
                        $insert['OSanXuat'][0]['ThaoDo'] = (int) $item2['Assembly'];
                        $insert['OSanXuat'][0]['MaSP'] = $item2['ItemCode'];
                        $insert['OSanXuat'][0]['DonViTinh'] = $item2['ItemUOM'];
                        $insert['OSanXuat'][0]['ThuocTinh'] = $item2['Attribute'];
                        //$insert['OSanXuat'][0]['SoLuong'] = (isset($params['groupPercent'][$item2['ID']]) && $params['groupPercent'][$item2['ID']])?round(($params['itemAndAttrQty'][$item2['Key']] * $params['percentWC'][$item2['ID']][$item3])/$params['groupPercent'][$item2['ID']]):0;
                        $insert['OSanXuat'][0]['SoLuong'] = $params['itemAndAttrQty'][$item2['Key']];
//$conLai = $params['itemAndAttrQty'][$item2['Key']] - $perWC;
                        // date (Y-m-d), line, shift, operation, workcenter, assembly, item, attr (ID)
                        $filterArr = array('date' => Qss_Lib_Date::displaytomysql($params['dateFilter'])
                            , 'start' => Qss_Lib_Date::displaytomysql($params['dateFilter'])
                            , 'end' => Qss_Lib_Date::displaytomysql($params['endDateFilter'])
                            , 'line' => $params['lineFilter']
                            //, 'shift'=>$params['shiftFilter']
                            //, 'operation'=>$item2['ID']
                            //, 'workcenter'=>$item3
                            , 'assembly' => @(int) $item2['Assembly']
                            , 'item' => $item2['RefItem']
                            , 'attr' => @(int) $item2['RefAttribute']);
                        $getExists = $model->getExistsProductionOrder($filterArr);
                        if ($getExists)
                        {
                                $insert['OSanXuat'][0]['ioid'] = $getExists->IOID;
                                $insert['OSanXuat'][0]['SoLuong'] += $getExists->SoLuong;
                                $ifid = $getExists->IFID_M710;
                        }
                        $insert['OSanXuat'][0]['ThoiGian'] = $qtyPerHour ? $insert['OSanXuat'][0]['SoLuong'] / $qtyPerHour : 0;

                        $service = $this->services->Form->Manual('M710', $ifid, $insert, false);
                        if ($service->isError())
                        {
                                $this->setError();
                                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                        }
                }
//			}


                if (!$this->isError())
                {
                        $this->setMessage($this->_translate(1));
                }
        }
}

?>