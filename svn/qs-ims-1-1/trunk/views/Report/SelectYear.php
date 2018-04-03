<?php
class Qss_View_Report_SelectYear extends Qss_View_Abstract
{
	public function __doExecute ($id = 'year', $range = 50, $pass = true, $future = true
                                    , $baseOnStartOfProduct = true)
	{
            $model = new Qss_Model_Extra_Extra();
            
            // *****************************************************************
            // === $id, name and id cua ele select year
            // *****************************************************************
            
            // *****************************************************************
            // === $range, khoang thoi gian cong them ve hien tai va tuong lai
            // *****************************************************************            
            
            
            // *****************************************************************
            // === $pass, co tinh nam trong qua khu hay khong?
            // *****************************************************************
            
            
            // *****************************************************************
            // === $future, co tinh nam trong tuong lai khong?
            // *****************************************************************
            
            
            // *****************************************************************
            // === $baseOnStartOfProduct, Co tinh dua tren ngay bat dau su dung
            // === he thong hay khong? (Neu ko su dung nam hien tai)
            // *****************************************************************
            
            
            // *****************************************************************
            // === $range, khoang cach cac nam, tu nam bat dau tro ve qua khu va
            // === den tuong lai
            // *****************************************************************
            
            
            
            // *****************************************************************
            // === Lay nam can cu de tinh so nam hien thi
            // *****************************************************************
            if($baseOnStartOfProduct)
            {
                $baseOnYear = 1995;
            }
            else
            {
                $baseOnYear = date('Y');
            }
            
            
            
            // *****************************************************************
            // === Tinh khoang nam hien thi 
            // *****************************************************************
            $startOf = $baseOnYear;
            $endOf   = $baseOnYear;
            
            
            if($pass)
            {
                $startOf =  (int)$baseOnYear - $range;
            }
            
            if($future)
            {
                $endOf = (int)$baseOnYear + $range;
            }
            
            
            // *****************************************************************
            // === Truyen tham so
            // *****************************************************************
            $this->html->name  = $id;
            $this->html->start = $startOf;
            $this->html->end   = $endOf;
	}
}

?>