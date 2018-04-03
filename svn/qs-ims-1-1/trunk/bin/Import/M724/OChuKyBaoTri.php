<?php
class Qss_Bin_Import_M724_OChuKyBaoTri extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $sql = sprintf('
            CREATE TEMPORARY TABLE tmp3_OChuKyBaoTri
            SELECT * FROM tmp_OChuKyBaoTri;
        ');
        $this->_db->execute($sql);

        $sql = sprintf('          
            UPDATE  tmp_OChuKyBaoTri
            SET Error = 4, ErrorMessage = "Không hỗ trợ điều chỉnh theo phiếu bảo trì với trường hợp nhiều chu kỳ!"
            WHERE IFID_M724 IN (
                SELECT IFID_M724
                FROM (
                    SELECT IFID_M724, count(1) AS Total 
                    FROM tmp3_OChuKyBaoTri                    
                    GROUP BY IFID_M724
                    HAVING Total > 1
                ) AS t1
            ) AND IFNULL(DieuChinhTheoPBT, 0) = 1	
        ');
        $this->_db->execute($sql);

        $sql = sprintf('
            DROP TEMPORARY TABLE IF EXISTS tmp3_OChuKyBaoTri;
        ');
        $this->_db->execute($sql);
    }

}