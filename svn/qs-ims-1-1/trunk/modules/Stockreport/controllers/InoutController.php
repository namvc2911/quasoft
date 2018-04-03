<?php

class Stockreport_InoutController extends Qss_Lib_Controller
{

    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
    }

    /**
     * BÁO CÁO CHI TIẾT THEO ĐỐI TƯỢNG
     */
    public function costByequipAction()
    {

    }

    public function costByequip1Action()
    {
        $common    = new Qss_Model_Extra_Extra();
        $startDate = $this->params->requests->getParam('start', '');
        $endDate   = $this->params->requests->getParam('end', '');
        $equipment = $this->params->requests->getParam('equipment', '');
        $mSDate    = Qss_Lib_Date::displaytomysql($startDate);
        $mEDate    = Qss_Lib_Date::displaytomysql($endDate);

        $this->html->report = $this->getChiTietTheoDoiTuong($mSDate, $mEDate, $equipment);
        $this->html->start  = $startDate;
        $this->html->end    = $endDate;
        $this->html->equip  = $common->getTableFetchOne('ODanhSachThietBi', array('IOID'=>$equipment));
    }

    public function getChiTietTheoDoiTuong($mSDate, $mEDate, $equipment)
    {
        $model     = new Qss_Model_Warehouse_Inout();
        $input     = $model->getMaintainReturnInputCostByEquip($mSDate, $mEDate, $equipment);
        $output    = $model->getMaintainOutputCostByEquip($mSDate, $mEDate, $equipment);
        $retval    = array();
        $i         = 0;
        $retval['TotalByMay']['Qty']   = 0;
        $retval['TotalByMay']['Total'] = 0;

        foreach($input as $item)
        {

            $itemKey = @(int)$item->Ref_MaSanPham.'_'.@(int)$item->Ref_ThuocTinh.'_'.@(int)$item->Ref_DonViTinh;

            $retval['Input'][$item->Ref_ViTri]['ID']   = $item->Ref_ViTri;
            $retval['Input'][$item->Ref_ViTri]['Code'] = $item->ViTri;
            $retval['Input'][$item->Ref_ViTri]['Name'] = $item->BoPhan;
            $retval['Input'][$item->Ref_ViTri]['Item'][$itemKey][$i]['ID']    = $item->Ref_MaSanPham;
            $retval['Input'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Code']  = $item->MaSanPham;
            $retval['Input'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Name']  = $item->TenSanPham;
            $retval['Input'][$item->Ref_ViTri]['Item'][$itemKey][$i]['UOM']   = $item->DonViTinh;
            $retval['Input'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Attr']  = @$item->ThuocTinh;
            $retval['Input'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Price'] = $item->DonGia?Qss_Lib_Util::formatMoney(@$item->DonGia):0;
            $retval['Input'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Qty']   = @$item->SoLuong;
            $retval['Input'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Total'] = $item->ThanhTien?Qss_Lib_Util::formatMoney(@$item->ThanhTien):0;
            $retval['Input'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Total2']= $item->ThanhTien?$item->ThanhTien:0;
            $retval['Input'][$item->Ref_ViTri]['Item'][$itemKey][$i]['DocNo'] = @$item->SoChungTu;
            $retval['Input'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Date']  = Qss_Lib_Date::mysqltodisplay(@$item->NgayChungTu);
            $retval['Input'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Note']  = @$item->GhiChu;
            $i++;


        }



        foreach($output as $item)
        {
            $itemKey = @(int)$item->Ref_MaSP.'_'.@(int)$item->Ref_ThuocTinh.'_'.@(int)$item->Ref_DonViTinh;

            $retval['Output'][$item->Ref_ViTri]['ID']   = $item->Ref_ViTri;
            $retval['Output'][$item->Ref_ViTri]['Code'] = $item->ViTri;
            $retval['Output'][$item->Ref_ViTri]['Name'] = $item->BoPhan;
            $retval['Output'][$item->Ref_ViTri]['Item'][$itemKey][$i]['ID']    = $item->Ref_MaSP;
            $retval['Output'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Code']  = $item->MaSP;
            $retval['Output'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Name']  = $item->TenSP;
            $retval['Output'][$item->Ref_ViTri]['Item'][$itemKey][$i]['UOM']   = $item->DonViTinh;
            $retval['Output'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Attr']  = @$item->ThuocTinh;
            $retval['Output'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Price'] = $item->DonGia?Qss_Lib_Util::formatMoney(@$item->DonGia):0;
            $retval['Output'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Qty']   = @$item->SoLuong;
            $retval['Output'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Total'] = $item->ThanhTien?Qss_Lib_Util::formatMoney(@$item->ThanhTien):0;
            $retval['Output'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Total2']= $item->ThanhTien?$item->ThanhTien:0;
            $retval['Output'][$item->Ref_ViTri]['Item'][$itemKey][$i]['DocNo'] = @$item->SoChungTu;
            $retval['Output'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Date']  = Qss_Lib_Date::mysqltodisplay(@$item->NgayChungTu);
            $retval['Output'][$item->Ref_ViTri]['Item'][$itemKey][$i]['Note']  = @$item->GhiChu;
            $i++;
        }

        if(isset($retval['Output']))
        {
            $total['QtyByPosition']    = array();
            $total['TotalByPosition']  = array();
            $QtyByPosition   = 0;
            $TotalByPosition = 0;



            foreach($retval['Output'] as $key=>$position)
            {
                foreach($position['Item'] as $items)
                {
                    foreach($items as $item)
                    {
                        if(!isset($total['TotalByPosition'][$key][$item['ID']])) $total['TotalByPosition'][$key][$item['ID']] = 0;
                        if(!isset($total['QtyByPosition'][$key][$item['ID']])) $total['QtyByPosition'][$key][$item['ID']] = 0;

                        $total['TotalByPosition'][$key][$item['ID']] += $item['Total2'];
                        $total['QtyByPosition'][$key][$item['ID']]   += $item['Qty'];

                        $QtyByPosition   += $item['Qty'];
                        $TotalByPosition += $item['Total2'];

                        $retval['TotalByMay']['Qty']   += $item['Qty'];
                        $retval['TotalByMay']['Total'] += $item['Total2'];
                    }
                }
            }

            $retval['Output']['Total'] = $total;
            $retval['Output']['TotalByNghiepVu']['Qty']   = $QtyByPosition;
            $retval['Output']['TotalByNghiepVu']['Total'] = $TotalByPosition;
        }

        if(isset($retval['Input']))
        {
            $total['QtyByPosition']    = array();
            $total['TotalByPosition']  = array();
            $QtyByPosition   = 0;
            $TotalByPosition = 0;

            foreach($retval['Input'] as $key=>$position)
            {
                foreach($position['Item'] as $items)
                {
                    foreach($items as $item)
                    {
                        if(!isset($total['TotalByPosition'][$key][$item['ID']])) $total['TotalByPosition'][$key][$item['ID']] = 0;
                        if(!isset($total['QtyByPosition'][$key][$item['ID']])) $total['QtyByPosition'][$key][$item['ID']] = 0;

                        $total['TotalByPosition'][$key][$item['ID']] += $item['Total2'];
                        $total['QtyByPosition'][$key][$item['ID']]   += $item['Qty'];

                        $QtyByPosition   += $item['Qty'];
                        $TotalByPosition += $item['Total2'];

                        $retval['TotalByMay']['Qty']   += $item['Qty'];
                        $retval['TotalByMay']['Total'] += $item['Total2'];
                    }

                }
            }

            $retval['Input']['Total'] = $total;
            $retval['Input']['TotalByNghiepVu']['Qty']   = $QtyByPosition;
            $retval['Input']['TotalByNghiepVu']['Total'] = $TotalByPosition;
        }

        return $retval;
    }

}