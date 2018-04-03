<?php
class Qss_Service_Button_M602_Serial_Save extends Qss_Service_Abstract
{
    // @todo: Kiểm tra trước khi save
    public function __doExecute($params)
    {
        $mInventory = new Qss_Model_Inventory_Inventory();
        $i          = 0;

        // Kiem tra xem param co truyen len hay khong
        if(!isset($params['ioid']) || !count($params['ioid']))
        {
            $this->setError();
            $this->setMessage('Bạn cần có ít nhất một bản ghi để cập nhật!');
        }
        else
        {
            // Kiem tra xem co 2 serial trung nhau hay khong
            $dupplicate = array_count_values($params['new_serial']);

            foreach($dupplicate as $val=>$count)
            {
                if($count > 1 && $val)
                {
                    $this->setError();
                    $this->setMessage('Số serial '.$val.' lặp lại '.$count.' lần!');
                }
            }

            // Bat buoc phai co serial khi danh moi
            foreach($params['old_serial'] as $oldSerial)
            {
                $oldSerial = trim($oldSerial);
                if(!$oldSerial && !$params['new_serial'][$i])
                {
                    $this->setError();
                    $this->setMessage('Số serial đánh mới trên dòng '.($i+1).' bắt buộc!');
                }
                $i++;
            }
        }

        if(!$this->isError())
        {
            $mInventory->updateSerial($params);
        }
    }
}