<?php
//@moved
class Qss_Bin_Bash_UpdateMoving extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $ifid = $this->_params->IFID_M706?$this->_params->IFID_M706:0;

        if($ifid)
        {
            // Thiết bị cập nhật về thì phải có tick vào cập nhật
            // Phải chắc chắn rằng thiết bị không ở một điều động khác trong tương lai thì mới cập nhật

            $insert = array();
            $sql    = sprintf('

                SELECT *
                FROM
                (
                    SELECT DanhSachDieuDong.*, ThietBi.IFID_M705
                    FROM OLichThietBi AS DieuDongThietBi
                    INNER JOIN ODanhSachDieuDongThietBi AS DanhSachDieuDong ON DieuDongThietBi.IFID_M706 = DanhSachDieuDong.IFID_M706
                    INNER JOIN ODanhSachThietBi AS ThietBi ON DanhSachDieuDong.Ref_MaThietBi = ThietBi.IOID
                    WHERE DieuDongThietBi.IFID_M706 = %1$d
                ) AS DieuDongThietBiCuaBanGhi

                /*
                LEFT JOIN
                (
                    SELECT *
                    (
                        SELECT DanhSachDieuDong.*
                        FROM OLichThietBi AS DieuDongThietBi
                        INNER JOIN ODanhSachDieuDongThietBi AS DanhSachDieuDong ON DieuDongThietBi.IFID_M706 = DanhSachDieuDong.IFID_M706
                        WHERE DieuDongThietBi.IFID_M706 = %1$d
                    ) AS ThietBi
                    INNER JOIN ODanhSachDieuDongThietBi AS DanhSachDieuDong2 ON ThietBi.Ref_MaThietBi = DanhSachDieuDong2.Ref_MaThietBi
                    INNER JOIN OLichThietBi AS DieuDongThietBi2 ON DanhSachDieuDong2.IFID_M706 = DieuDongThietBi2.IFID_M706
                    WHERE
                ) AS DieuDongThietBiHienThoi
                */
                '
                , $ifid
            );
            $dataSQL = $this->_db->fetchAll($sql);

            foreach($dataSQL as $item)
            {
                if((int)$item->DaVe)
                {
                    $insert['ODanhSachThietBi'][0]['ioid'] = $item->Ref_MaThietBi;
                    $insert['ODanhSachThietBi'][0]['DuAn'] = (int)0;

                    $service = $this->services->Form->Manual('M705', $item->IFID_M705, $insert, true);

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