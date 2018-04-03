<?php
include "../sysbase.php";
$db = Qss_Db::getAdapter('main');
//Update số lượng hiện có trong kho

if(!isset($argv[2]))
{
    echo 'Module update bắt buộc!';
    return;
    die;
}


if(!isset($argv[3]) || !$argv[3])
{
    echo 'Mã công tơ bắt buộc!';
    return;
    die;
}

if(!isset($argv[4]) || !is_numeric($argv[4]))
{
    echo 'Hệ số công tơ bắt buộc!';
    return;
    die;
}




if($argv[2] == 1) // Update cong to mua vao
{
    $sql = sprintf('
        UPDATE
            OQuanLyCongToMuaVao
        SET HeSoCongTo = %1$d     
        WHERE MaCongTo = %2$s    
    ', $argv[4], $db->quote($argv[3]));
    $db->execute($sql);
    
    $sql = sprintf('
        UPDATE
            OChiSoCongToMuaVao
        SET 
            HeSo = %1$d
            , TongSo = (ChiSoCuoi - ChiSoDau) * %1$d
            , TongSoCaoDiem = (ChiSoCuoiCaoDiem - ChiSoDauCaoDiem) * %1$d
            , TongSoThapDiem = (ChiSoCuoiThapDiem - ChiSoDauThapDiem) * %1$d
            , TongSoTrungBinh = (ChiSoCuoiTrungBinh - ChiSoDauTrungBinh) * %1$d
        WHERE MaCongTo = %2$s
    ', $argv[4], $db->quote($argv[3]));
    $db->execute($sql);    
}
elseif($argv[2] == 2) // Update cong to ban ra
{
    $sql = sprintf('
        UPDATE
            OCongToDien
        SET HeSo = %1$d
        WHERE Ma = %2$s
    ', $argv[4], $db->quote($argv[3]));
    $db->execute($sql);  

    // Ban ra
    $sql = sprintf('
        UPDATE
            OChiSoCongToDien
        SET
            HeSo = %1$d
            , TongSo = (ChiSoCuoi - ChiSoDau) * %1$d
            , TongSoCaoDiem = (ChiSoCuoiCaoDiem - ChiSoDauCaoDiem) * %1$d
            , TongSoThapDiem = (ChiSoCuoiThapDiem - ChiSoDauThapDiem) * %1$d
            , TongSoTrungBinh = (ChiSoCuoiTrungBinh - ChiSoDauTrungBinh) * %1$d
        WHERE MaCongTo = %2$s
    ', $argv[4], $db->quote($argv[3]));
    $db->execute($sql);    
    
    // Noi bo
    $sql = sprintf('
        UPDATE
            OChiSoCongToDienNoiBo
        SET
            HeSo = %1$d
            , TongSo = (ChiSoCuoi - ChiSoDau) * %1$d
        WHERE MaCongTo = %2$s
    ', $argv[4], $db->quote($argv[3]));
    $db->execute($sql);
}
