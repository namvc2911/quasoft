<?php

class Qss_Lib_Report
{
    /**
     * Chuyen doi don vi tu pixcel sang don vi do cua excel 2003
     * @param float $width
     * @return float
     */
    public static function convertPxToExcelUOM($width)
    {
        // truyen vao so px can chuyen sang don vi do cua excel
        // 64px = 8.43 excel uom // doi ra poin la 12 point thanh 24px
        return ($width * Qss_Lib_Extra_Const::RATE_EXCEL) / Qss_Lib_Extra_Const::RATE_PX;
    }
    
    
    /**
     *
     * @param array $widthArr do rong cac cot excel bang pixcel
     * @param int    $printType 1/not 1 - portrait/landscape
     * @return array tra ve mang do rong cac cot bang don vi do cua excel 2003
     */
    public static function convertPxColumnWidthToExcelWidth($widthArr, $printType = Qss_Lib_Extra_Const::PRINT_TYPE_PORTRAIT, $width = 0)
    {
        $retval     = array();
        $totalWidth = array_sum($widthArr);
        if($totalWidth == 0) return;

        if($width)
        {
            $defaultWidth = $width;
        }
        else
        {
            if($printType == Qss_Lib_Extra_Const::PRINT_TYPE_PORTRAIT)
            {
                $defaultWidth = Qss_Lib_Extra_Const::PORTRAIT_WIDTH_EXCEL;
            }
            elseif($printType == Qss_Lib_Extra_Const::PRINT_TYPE_LANDSCAPE)
            {
                $defaultWidth = Qss_Lib_Extra_Const::LANDSCAPE_WIDTH_EXCEL;
            }
        }

        foreach ($widthArr as $i=>$w)
        {
            $percent = $totalWidth?(($w) /$totalWidth):0;
            $retval[$i] = round($defaultWidth * $percent);  
        }
        return $retval;
    }
    
    /**
     * Chinh sua cho do rong cua cot ve voi do rong cua man hinh <Theo độ rộng của màn hình báo cáo>
     * @param type $widthArr
     * @param type $printType
     * @return type
     */
    public static function changeWidthToFitScreen($widthArr, $printType = Qss_Lib_Extra_Const::PRINT_TYPE_PORTRAIT)
    {
        $retval = array();
        $divPadding = 12;
        $totalWidth = array_sum($widthArr);
    
    
        if($printType == Qss_Lib_Extra_Const::PRINT_TYPE_PORTRAIT)
        {
            $defaultWidth = Qss_Lib_Extra_Const::PORTRAIT_WIDTH;
        }
        elseif($printType == Qss_Lib_Extra_Const::PRINT_TYPE_LANDSCAPE)
        {
            $defaultWidth = Qss_Lib_Extra_Const::LANDSCAPE_WIDTH;
        }
    
    
        foreach ($widthArr as $i=>$w)
        {
            $percent = $totalWidth?(($w) /$totalWidth):0;
            $retval[$i] = round($defaultWidth * $percent) - $divPadding;
        }
        return $retval;
    }

    /**
     * Chia tỷ lệ các cột theo một độ rộng tùy biến
     * @param $widthArr
     * @param int $width
     * @param int $padding
     * @return array
     */
    public static function changeWidthToFitScreen2($widthArr, $width = 1000, $padding = 0)
    {
        $retval       = array();
        $totalWidth   = array_sum($widthArr);
        $defaultWidth = $width;

        foreach ($widthArr as $i=>$w)
        {
            $percent = $totalWidth?(($w) /$totalWidth):0;
            $retval[$i] = round($defaultWidth * $percent) - $padding;
        }
        
        return $retval;
    }

    /**
     * Chia tỷ lệ các cột theo %
     * @param $widthArr
     * @param int $padding
     * @return array
     */
    public static function changeWidthToFitScreen3($widthArr)
    {
        $retval       = array();
        $totalWidth   = array_sum($widthArr);
        $defaultWidth = 100;

        foreach ($widthArr as $i=>$w)
        {
            $percent = $totalWidth?(($w) /$totalWidth):0;
            $retval[$i] = round($defaultWidth * $percent);
        }

        return $retval;
    }
}