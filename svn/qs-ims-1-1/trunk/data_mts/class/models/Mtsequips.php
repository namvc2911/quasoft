<?php
class Qss_Model_Mtsequips extends Qss_Model_Abstract
{
	public function getThietBiTheoNhom($nhom)
    {
    	$where = '';
    	if($nhom)
    	{
    		$where = sprintf('AND ThietBi.Ref_NhomThietBi = %1$d',$nhom);
    	}
        $sql = sprintf('
            SELECT 
                ThietBi.*
                , ThietBi.IOID AS ThietBiIOID
                , KhuVucRoot.IOID AS KhuVucRootIOID
                , KhuVucRoot.MaKhuVuc
                , KhuVucRoot.Ten AS TenKhuVuc   
                , LoaiThietBi.IOID AS LoaiThietBiIOID
                , LoaiThietBi.MaLoai
                , LoaiThietBi.TenLoai
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0
            LEFT JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
            LEFT JOIN OKhuVuc AS KhuVucRoot ON KhuVucRoot.lft <= KhuVucThietBi.lft AND KhuVucRoot.rgt >= KhuVucThietBi.rgt
                AND IFNULL(KhuVucRoot.Ref_TrucThuoc, 0) = 0                
            WHERE  
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0 
                ThietBi.DeptID in (%1$s)
                %2$s
            ORDER BY KhuVucRoot.lft, LoaiThietBi.TenLoai, ThietBi.IOID'
            , $this->_user->user_dept_list
            , $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
    
    public function getThietBiVanTaiBo()
    {
        $sql = sprintf('
            SELECT 
                ThietBi.*
                , ThietBi.IOID AS ThietBiIOID
                , KhuVucRoot.IOID AS KhuVucRootIOID
                , KhuVucRoot.MaKhuVuc
                , KhuVucRoot.Ten AS TenKhuVuc   
                , LoaiThietBi.IOID AS LoaiThietBiIOID
                , LoaiThietBi.MaLoai
                , LoaiThietBi.TenLoai
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0
            LEFT JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
            LEFT JOIN OKhuVuc AS KhuVucRoot ON KhuVucRoot.lft <= KhuVucThietBi.lft AND KhuVucRoot.rgt >= KhuVucThietBi.rgt
                AND IFNULL(KhuVucRoot.Ref_TrucThuoc, 0) = 0                
            WHERE (LoaiThietBiRoot.MaLoai = "VTB" OR LoaiThietBi.MaLoai = "VTB") 
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0 
                AND ThietBi.DeptID in (%1$s)                     
            ORDER BY KhuVucRoot.lft, LoaiThietBi.TenLoai, ThietBi.IOID'
            , $this->_user->user_dept_list);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getDacTinhThietBiVanTaiBo()
    {
        $sql = sprintf('
            SELECT 
                DacTinh.*
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0          
            INNER JOIN ODacTinhThietBi AS DacTinh ON ThietBi.IFID_M705 = DacTinh.IFID_M705                
            WHERE (LoaiThietBiRoot.MaLoai = "VTB" OR LoaiThietBi.MaLoai = "VTB") 
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0 
                AND ThietBi.DeptID in (%1$s)'
            , $this->_user->user_dept_list);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getThietBiVanTaiThuy()
    {
        $sql = sprintf('
            SELECT 
                ThietBi.*
                , ThietBi.IOID AS ThietBiIOID
                , KhuVucRoot.IOID AS KhuVucRootIOID
                , KhuVucRoot.MaKhuVuc
                , KhuVucRoot.Ten AS TenKhuVuc   
                , LoaiThietBi.IOID AS LoaiThietBiIOID
                , LoaiThietBi.MaLoai
                , LoaiThietBi.TenLoai
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0
            LEFT JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
            LEFT JOIN OKhuVuc AS KhuVucRoot ON KhuVucRoot.lft <= KhuVucThietBi.lft AND KhuVucRoot.rgt >= KhuVucThietBi.rgt
                AND IFNULL(KhuVucRoot.Ref_TrucThuoc, 0) = 0                                        
            WHERE (LoaiThietBiRoot.MaLoai = "VTT" OR LoaiThietBi.MaLoai = "VTT") 
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0 
                AND ThietBi.DeptID in (%1$s)            
            ORDER BY KhuVucRoot.lft, LoaiThietBi.TenLoai, ThietBi.IOID'
            , $this->_user->user_dept_list);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getDacTinhThietBiVanTaiThuy()
    {
        $sql = sprintf('
            SELECT 
                DacTinh.*, LoaiThietBi.IOID AS LoaiThietBiIOID
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0          
            INNER JOIN ODacTinhThietBi AS DacTinh ON ThietBi.IFID_M705 = DacTinh.IFID_M705                
            WHERE (LoaiThietBiRoot.MaLoai = "VTT" OR LoaiThietBi.MaLoai = "VTT") 
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0 
                AND ThietBi.DeptID in (%1$s)'
            , $this->_user->user_dept_list);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getGauNgoam()
    {
        $sql = sprintf('
            SELECT 
                ThietBi.*
                , ThietBi.IOID AS ThietBiIOID
                , KhuVucRoot.IOID AS KhuVucRootIOID
                , KhuVucRoot.MaKhuVuc
                , KhuVucRoot.Ten AS TenKhuVuc   
                , LoaiThietBi.IOID AS LoaiThietBiIOID
                , LoaiThietBi.MaLoai
                , LoaiThietBi.TenLoai
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0
            LEFT JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
            LEFT JOIN OKhuVuc AS KhuVucRoot ON KhuVucRoot.lft <= KhuVucThietBi.lft AND KhuVucRoot.rgt >= KhuVucThietBi.rgt
                AND IFNULL(KhuVucRoot.Ref_TrucThuoc, 0) = 0                                        
            WHERE (LoaiThietBiRoot.MaLoai = "GAU" OR LoaiThietBi.MaLoai = "GAU") 
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0 
                AND ThietBi.DeptID in (%1$s)            
            ORDER BY KhuVucRoot.lft, LoaiThietBi.TenLoai, ThietBi.IOID'
            , $this->_user->user_dept_list);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getDacTinhGauNgoam()
    {
        $sql = sprintf('
            SELECT 
                DacTinh.*, LoaiThietBi.IOID AS LoaiThietBiIOID
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0          
            INNER JOIN ODacTinhThietBi AS DacTinh ON ThietBi.IFID_M705 = DacTinh.IFID_M705                
            WHERE (LoaiThietBiRoot.MaLoai = "GAU" OR LoaiThietBi.MaLoai = "GAU") 
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0 
                AND ThietBi.DeptID in (%1$s)'
            , $this->_user->user_dept_list);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getDungTichStec() {
        $sql = sprintf('
            SELECT DacTinh.*
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0          
            INNER JOIN ODacTinhThietBi AS DacTinh ON ThietBi.IFID_M705 = DacTinh.IFID_M705                
            WHERE (LoaiThietBiRoot.MaLoai = "STEC" OR LoaiThietBi.MaLoai = "STEC") 
                AND TRIM(DacTinh.Ten) = "Dung tích"
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0 
                AND ThietBi.DeptID in (%1$s)
            GROUP BY DacTinh.GiaTri ASC
            ORDER BY DacTinh.GiaTri ASC'
            , $this->_user->user_dept_list);
        // echo '<pre>'; print_r($sql); die;
        $data   = $this->_o_DB->fetchAll($sql);
        $retval = array();

        foreach($data as $item) {
            if(!isset($retval[$item->GiaTri])) {
                $retval[$item->GiaTri] = "Loại ".$item->GiaTri." m3";
            }
        }

        return $retval;

    }

    public function getStecXangDau( $TinhTrang)
    {
        $sql = sprintf('
            SELECT 
                ThietBi.*
                , ThietBi.IOID AS ThietBiIOID
                , LoaiThietBi.IOID AS LoaiThietBiIOID
                , LoaiThietBi.MaLoai
                , LoaiThietBi.TenLoai
                , DacTinh.Ten AS TenDachTinh
                , DacTinh.GiaTri AS GiaTriDacTinh
                , KhuVucThietBi.Ten
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0      
            INNER JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
            INNER JOIN ODacTinhThietBi AS DacTinh ON ThietBi.IFID_M705 = DacTinh.IFID_M705 AND TRIM(DacTinh.Ten) = "Dung tích"
            WHERE (LoaiThietBiRoot.MaLoai = "STEC" OR LoaiThietBi.MaLoai = "STEC") 
                AND IFNULL(ThietBi.TrangThai, 0) = %2$d 
                AND ThietBi.DeptID in (%1$s)            
            ORDER BY KhuVucThietBi.lft, LoaiThietBiRoot.lft, LoaiThietBi.MaLoai'
            , $this->_user->user_dept_list, $TinhTrang);
        // echo '<pre>'; print_r($sql); die;
        $data   = $this->_o_DB->fetchAll($sql);
        $retval = array();

        foreach ($data as $item) {
            if(strpos($item->MaLoai, 'STEC.XANGDAU') !== false)
            {
                $retval[$item->Ref_MaKhuVuc]['Info'] = $item;

                if(!isset($retval[$item->Ref_MaKhuVuc]['Count'][$item->GiaTriDacTinh])) {
                    $retval[$item->Ref_MaKhuVuc]['Count'][$item->GiaTriDacTinh] = 0;
                }

                if(!isset($retval[$item->Ref_MaKhuVuc]['Total'])) {
                    $retval[$item->Ref_MaKhuVuc]['Total'] = 0;
                }

                if(!isset($retval[$item->Ref_MaKhuVuc]['Stec'])) {
                    $retval[$item->Ref_MaKhuVuc]['Stec'] = 0;
                }

                $retval[$item->Ref_MaKhuVuc]['Total']                += $item->SoLuong * $item->GiaTriDacTinh;
                $retval[$item->Ref_MaKhuVuc]['Stec']                 += $item->SoLuong;
                $retval[$item->Ref_MaKhuVuc]['Count'][$item->GiaTriDacTinh] += $item->SoLuong;
            }
        }

        // echo '<pre>'; print_r($retval); die;
        return $retval;
    }


    public function getStecNuoc( $TinhTrang)
    {
        $sql = sprintf('
            SELECT 
                ThietBi.*
                , ThietBi.IOID AS ThietBiIOID
                , LoaiThietBi.IOID AS LoaiThietBiIOID
                , LoaiThietBi.MaLoai
                , LoaiThietBi.TenLoai
                , DacTinh.Ten AS TenDachTinh
                , DacTinh.GiaTri AS GiaTriDacTinh
                , KhuVucThietBi.Ten
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0      
            INNER JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
            INNER JOIN ODacTinhThietBi AS DacTinh ON ThietBi.IFID_M705 = DacTinh.IFID_M705 AND TRIM(DacTinh.Ten) = "Dung tích"
            WHERE (LoaiThietBiRoot.MaLoai = "STEC" OR LoaiThietBi.MaLoai = "STEC") 
                AND IFNULL(ThietBi.TrangThai, 0) = %2$d 
                AND ThietBi.DeptID in (%1$s)            
            ORDER BY KhuVucThietBi.lft, LoaiThietBiRoot.lft, LoaiThietBi.MaLoai'
            , $this->_user->user_dept_list, $TinhTrang);
        // echo '<pre>'; print_r($sql); die;
        $data   = $this->_o_DB->fetchAll($sql);
        $retval = array();

        foreach ($data as $item) {
            if(strpos($item->MaLoai, 'STEC.NUOC') !== false)
            {
                $retval[$item->Ref_MaKhuVuc]['Display'] = $item->Ten;

                if(!isset($retval[$item->Ref_MaKhuVuc]['Count'][$item->GiaTriDacTinh])) {
                    $retval[$item->Ref_MaKhuVuc]['Count'][$item->GiaTriDacTinh] = 0;
                }

                if(!isset($retval[$item->Ref_MaKhuVuc]['Total'])) {
                    $retval[$item->Ref_MaKhuVuc]['Total'] = 0;
                }

                if(!isset($retval[$item->Ref_MaKhuVuc]['Stec'])) {
                    $retval[$item->Ref_MaKhuVuc]['Stec'] = 0;
                }

                $retval[$item->Ref_MaKhuVuc]['Total']                += $item->SoLuong * $item->GiaTriDacTinh;
                $retval[$item->Ref_MaKhuVuc]['Stec']                 += $item->SoLuong;
                $retval[$item->Ref_MaKhuVuc]['Count'][$item->GiaTriDacTinh] += $item->SoLuong;
            }
        }

        // echo '<pre>'; print_r($retval); die;
        return $retval;
    }

    public function getGps()
    {
        $sql = sprintf('
            SELECT 
                ThietBi.*
                , ThietBi.IOID AS ThietBiIOID
                , LoaiThietBi.IOID AS LoaiThietBiIOID
                , LoaiThietBi.MaLoai
                , LoaiThietBi.TenLoai
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0           
            WHERE (LoaiThietBiRoot.MaLoai = "GPS" OR LoaiThietBi.MaLoai = "GPS") 
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0 
                AND ThietBi.DeptID in (%1$s)            
            ORDER BY LoaiThietBiRoot.lft, LoaiThietBi.TenLoai, ThietBi.IOID'
            , $this->_user->user_dept_list);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getPlc()
    {
        $sql = sprintf('
            SELECT 
                ThietBi.*
                , ThietBi.IOID AS ThietBiIOID
                , LoaiThietBi.IOID AS LoaiThietBiIOID
                , LoaiThietBi.MaLoai
                , LoaiThietBi.TenLoai
                , KhuVucThietBi.Ten
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0      
            LEFT JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
            WHERE (LoaiThietBiRoot.MaLoai = "PLC" OR LoaiThietBi.MaLoai = "PLC") 
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0
                AND ThietBi.DeptID in (%1$s)            
            ORDER BY KhuVucThietBi.lft, LoaiThietBiRoot.lft, LoaiThietBi.MaLoai'
            , $this->_user->user_dept_list);
        // echo '<pre>'; print_r($sql); die;
        $data   = $this->_o_DB->fetchAll($sql);
        $retval = array();

        foreach ($data as $item) {
            {
                $retval[$item->Ref_MaKhuVuc]['Info'] = $item;

                if(!isset($retval[$item->Ref_MaKhuVuc]['Count'][$item->MaLoai])) {
                    $retval[$item->Ref_MaKhuVuc]['Count'][$item->MaLoai] = 0;
                }

                if(!isset($retval[$item->Ref_MaKhuVuc]['Total'])) {
                    $retval[$item->Ref_MaKhuVuc]['Total'] = 0;
                }

                $retval[$item->Ref_MaKhuVuc]['Total']                += $item->SoLuong;
                $retval[$item->Ref_MaKhuVuc]['Count'][$item->MaLoai] += $item->SoLuong;
            }
        }

        return $retval;
    }

    public function getThietBiNangVaApLuc()
    {
        $sql = sprintf('
            SELECT 
                ThietBi.*
                , ThietBi.IOID AS ThietBiIOID
                , KhuVucRoot.IOID AS KhuVucRootIOID
                , KhuVucRoot.MaKhuVuc
                , KhuVucRoot.Ten AS TenKhuVuc   
                , LoaiThietBi.IOID AS LoaiThietBiIOID
                , LoaiThietBi.MaLoai
                , LoaiThietBi.TenLoai
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0
            LEFT JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
            LEFT JOIN OKhuVuc AS KhuVucRoot ON KhuVucRoot.lft <= KhuVucThietBi.lft AND KhuVucRoot.rgt >= KhuVucThietBi.rgt
                AND IFNULL(KhuVucRoot.Ref_TrucThuoc, 0) = 0                                        
            WHERE (LoaiThietBiRoot.MaLoai = "NANG" OR LoaiThietBi.MaLoai = "NANG" OR 
                LoaiThietBiRoot.MaLoai = "APLUC" OR LoaiThietBi.MaLoai = "APLUC") 
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0 
                AND ThietBi.DeptID in (%1$s)            
            ORDER BY KhuVucRoot.lft, LoaiThietBi.TenLoai, ThietBi.IOID'
            , $this->_user->user_dept_list);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}