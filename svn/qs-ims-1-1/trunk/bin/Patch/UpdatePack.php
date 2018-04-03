<?php

include "../sysbase.php";
$db    = Qss_Db::getAdapter('main');
$pack = 'pro'; // basic, advance, pro

if(isset($argv[2]))
{
    $pack = $argv[2];
}

// -- UPDATE qsforms SET Effected = 1;
// Inactive các module ko sử dụng

$db->execute('UPDATE qsforms SET Effected = 1;');
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M002'; /* 1. Thùng rác */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M007'; /* 2. Thiết kế module */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M008'; /* 3. Thiết kế đối tượng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M009'; /* 4. Thiết kế module để in */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M010'; /* 5. Thiết kế mẫu gửi mail */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M011'; /* 6. JS, CSS */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M013'; /* 7. Cấu hình tham số hệ thống */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M014'; /* 8. Công việc & sự kiện */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M015'; /* 9. Phân loại sự kiện */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M016'; /* 10. Quản lý tài liệu */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M017'; /* 11. Cài đặt tài khoản e-mail */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M018'; /* 12. Quản lý danh sách gửi e-mail */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M020'; /* 13. Tài khoản quản trị */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M021'; /* 14. Thiết kế báo cáo động */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M025'; /* 15. Báo cáo động */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M101'; /* 16. Dân tộc */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M103'; /* 17. Quốc gia */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M104'; /* 18. Tỉnh thành */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M105'; /* 19. Lý do trả hàng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M109'; /* 20. Liên hệ cá nhân */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M111'; /* 21. Thuộc tính */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M115'; /* 22. Lĩnh vực hoạt động */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M116'; /* 23. Quy mô doanh nghiệp */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M117'; /* 24. Loại hình doanh nghiệp */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M119'; /* 25. Bảng thuộc tính */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M120'; /* 26. ĐVT chi phí */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M121'; /* 27. Bảng tỷ giá */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M122'; /* 28. Đặt lịch gửi email nhắc việc */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M124'; /* 29. Làm sạch dữ liệu rác */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M128'; /* 30. Thứ */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M129'; /* 31. Ngày */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M130'; /* 32. Tháng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M131'; /* 33. Phân loại hợp đồng mua bán */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M132'; /* 34. Tháng thứ */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M145'; /* 35. Kiểu làm thêm */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M147'; /* 36. Tin tức hoạt động */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M148'; /* 37. Lịch hoạt động */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M149'; /* 38. Báo cáo sản xuất */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M153'; /* 39. Temp */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M170'; /* 40. Temp */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M171'; /* 41. Temp */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M201'; /* 42. Loại tài khoản */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M202'; /* 43. Nhóm tài khoản */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M203'; /* 44. Bảng tài khoản */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M204'; /* 45. Phân loại kế toán */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M206'; /* 46. Danh mục thuế */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M207'; /* 47. Nhóm thuế */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M208'; /* 48. Loại hóa đơn */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M209'; /* 49. Loại bút toán */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M210'; /* 50. Năm tài chính */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M211'; /* 51. Kỳ kế toán */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M212'; /* 52. Bút toán */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M213'; /* 53. Mức tài khoản */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M214'; /* 54. Loại tài khoản thanh toán */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M215'; /* 55. Tài khoản thanh toán */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M216'; /* 56. Loại tài sản cố định */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M217'; /* 57. Kế hoạch ngân sách */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M218'; /* 58. Tài sản cố định */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M221'; /* 59. Thu tiền */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M222'; /* 60. Bảng cân đối */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M223'; /* 61. Cân đối tài khoản */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M224'; /* 62. Sổ tổng hợp */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M225'; /* 63. Báo cáo công nợ */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M301'; /* 64. Loại hợp đồng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M302'; /* 65. Hệ đào tạo */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M304'; /* 66. Thời hạn hợp đồng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M305'; /* 67. Công việc */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M306'; /* 68. Phân loại nghỉ */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M307'; /* 69. Loại tạm ứng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M308'; /* 70. Trình độ học vấn */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M309'; /* 71. Nhóm phụ cấp */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M310'; /* 72. Mức thuế thu nhập cá nhân */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M311'; /* 73. Chức danh */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M312'; /* 74. Phân loại chỉ số đánh giá nhân viên */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M313'; /* 75. Tình trạng hôn nhân */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M314'; /* 76. Quan hệ với nhân viên */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M315'; /* 77. Loại hình đào tạo */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M317'; /* 78. Quản lý chấm công */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M318'; /* 79. Hợp đồng lao động */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M319'; /* 80. Phòng ban */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M320'; /* 81. Khen thưởng/Kỷ luật */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M321'; /* 82. Theo dõi vào/ra */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M322'; /* 83. Bảo hiểm */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M323'; /* 84. Quản lý thẻ BHYT */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M324'; /* 85. Quản lý sổ BHXH */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M325'; /* 86. Thuế thu nhập cá nhân */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M326'; /* 87. Tính lương */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M327'; /* 88. Bảng lương */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M328'; /* 89. Đào tạo */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M329'; /* 90. Đánh giá năng lực nhân viên */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M330'; /* 91. Đợt tuyển dụng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M331'; /* 92. Danh sách ứng viên */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M332'; /* 93. Danh sách ứng viên đợt tuyển dụng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M333'; /* 94. Kế hoạch nhân sự */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M334'; /* 95. Quản lý thẻ chấm công */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M336'; /* 96. Báo cáo chấm công chi tiết */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M337'; /* 97. Lịch làm ngoài giờ */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M339'; /* 98. Chạy chấm công */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M340'; /* 99. Loại điều chỉnh */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M341'; /* 100. Điều chỉnh lịch nhân viên */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M342'; /* 101. Điều chỉnh lương */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M343'; /* 102. Bộ phận */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M344'; /* 103. Nhóm */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M345'; /* 104. Loại phụ cấp */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M346'; /* 105. Làm thêm giờ */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M347'; /* 106. Phụ cấp hàng tháng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M356'; /* 107. Báo cáo chấm công theo tháng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M357'; /* 108. Biểu đồ chấm công */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M403'; /* 109. Trả hàng cho nhà cung cấp */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M501'; /* 110. Loại giao dịch */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M502'; /* 111. Chính sách giá */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M503'; /* 112. Bảng giá */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M504'; /* 113. Cơ hội bán hàng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M505'; /* 114. Đơn bán hàng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M507'; /* 115. Nhận hàng trả lại */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M508'; /* 116. Hóa đơn bán hàng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M509'; /* 117. Báo giá bán hàng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M510'; /* 118. Hợp đồng bán hàng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M709'; /* 119. Quản lý nhu cầu vật tự */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M710'; /* 120. Lệnh sản xuất */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M711'; /* 121. Kiểm tra thông số thiết bị */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M712'; /* 122. Phiếu giao việc */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M715'; /* 123. Dừng máy */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M718'; /* 124. Đặt lịch: Tạo lệnh sản xuất */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M721'; /* 125. Danh sách lỗi */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M722'; /* 126. Nguyên nhân lỗi */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M737'; /* 127. Đặt lịch: Tạo yêu cầu mua hàng */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M740'; /* 128. Báo cáo chi phí theo khu vực */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M746'; /* 129. Báo cáo dừng máy theo khu vực */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M755'; /* 130. Báo cáo khả năng sẵn sàng của thiết bị theo kỳ */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M767'; /* 131. Báo cáo giá thành kế hoạch */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M769'; /* 132. Báo cáo giá thành thực tế */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M771'; /* 133. Phân loại chi phí chung */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M773'; /* 134. In phiếu bảo trì theo ngày */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M783'; /* 135. WIP */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M801'; /* 136. Loại dự án */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M802'; /* 137. Quy mô dự án */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M804'; /* 138. Các phân đoạn dự án */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M805'; /* 139. Phân đoạn dự án */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M806'; /* 140. Nhiệm vụ */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M808'; /* 141. Quản lý vật tư dự án */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M809'; /* 142. Quản lý thiết bị dự án */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M820'; /* 143. Loại sản xuất */"));
$db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M902'; /* 144. Theo dõi tiến độ cung ứng */"));
$db->execute(sprintf("/* UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M753';144. Phiếu Hiệu chuẩn/Kiểm định */"));
$db->execute(sprintf("/* UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M175';144. Kế hoạch hiệu chuẩn/Kiểm định */"));

$db->execute($generalInactive);


/* Inactive cac module theo goi */
if($pack == 'basic')
{
    
    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M172'; /* 1. Class hư hỏng */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M176'; /* 2. Công việc nhân viên */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M019'; /* 3. Đặt lịch gửi email */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M023'; /* 4. Đặt lịch gửi SMS */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M835'; /* 5. Phân tích công việc nhân viên */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M780'; /* 6. Cây thiết bị */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M757'; /* 7. Tình trạng bảo hành thiết bị */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M726'; /* 8. Báo cáo danh sách thiết bị */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M738'; /* 9. Báo cáo vật tư theo thiết bị */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M794'; /* 10. Báo cáo khấu hao thiết bị */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M160'; /* 11. Báo cáo phụ tùng thiết bị */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M723'; /* 12. Đặt lịch tạo phiếu bảo trì */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M747'; /* 13. Yêu cầu bảo trì */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M753'; /* 14. Phiếu Hiệu chuẩn/Kiểm định */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M175'; /* 15. Kế hoạch hiệu chuẩn/Kiểm định */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M728'; /* 16. Theo dõi bảo trì */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M707'; /* 17. Dừng máy */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M787'; /* 18. Đo chỉ số tự động */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M717'; /* 19. Thống kế sản xuất */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M816'; /* 20. Nhập chỉ số hoạt động */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M703'; /* 21. Công đoạn */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M114'; /* 22. Thiết kế sản phẩm */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M602'; /* 24. Tồn kho */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M402'; /* 25. Nhập kho */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M506'; /* 26. Xuất kho */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M607'; /* 27. Giao dịch kho */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M616'; /* 28. Sắp xếp kho */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M612'; /* 29. Kiểm kê kho */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M604'; /* 30. Tính giá vốn */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M618'; /* 31. Thẻ kho */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M608'; /* 32. Báo cáo tồn kho */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M619'; /* 33. Báo cáo nhập kho theo nhà cung cấp */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M621'; /* 34. Báo cáo xuất nhập tồn số lượng */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M784'; /* 35. Tồn kho theo vị trí */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M752'; /* 36. Báo cáo xuất nhập tồn theo loại */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M760'; /* 37. Xuất nhập tồn theo giá trị */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M786'; /* 38. Báo cáo tuổi thọ phụ tùng */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M790'; /* 39. Đối chiếu sử dụng vật tư bảo trì */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M734'; /* 40. Báo cáo về sử dụng vật tư */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M756'; /* 41. Báo cáo lịch sử sử dụng vật tư */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M768'; /* 42. Kế hoạch sử dụng vật tư */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M613'; /* 43. Phân loại nhập kho */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M614'; /* 44. Phân loại xuất kho */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M601'; /* 45. Danh sách kho */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M412'; /* 46. Yêu cầu mua sắm */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M716'; /* 47. Kế hoạch mua sắm */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M406'; /* 48. Báo giá mua hàng */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M401'; /* 49. Đơn mua hàng */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M408'; /* 50. Nhận hàng */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M404'; /* 51. Hóa đơn mua hàng */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M220'; /* 52. Thanh toán */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M410'; /* 53. Quản lý bảo hành của nhà cung cấp */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M407'; /* 54. Hợp đồng nguyên tắc mua hàng */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M106'; /* 55. Phương thức thanh toán */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M739'; /* 56. Phân tích hiệu suất thiết bị tổng thể */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M742'; /* 57. Phân tích chi phí */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M750'; /* 58.  MTBF và MTTR */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M001'; /* 59.  Quản lý tiền tệ */"));
}
elseif($pack == 'advance')
{
    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M172'; /* 1. Class hư hỏng */"));
    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M023'; /* 2. Đặt lịch gửi SMS */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M794'; /* 3. Báo cáo khấu hao thiết bị */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M723'; /* 4. Đặt lịch tạo phiếu bảo trì */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M787'; /* 5. Đo chỉ số tự động */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M717'; /* 6. Thống kế sản xuất */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M703'; /* 7. Công đoạn */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M114'; /* 8. Thiết kế sản phẩm */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M612'; /* 9. Kiểm kê kho */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M604'; /* 10. Tính giá vốn */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M716'; /* 11. Kế hoạch mua sắm */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M406'; /* 12. Báo giá mua hàng */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M401'; /* 13. Đơn mua hàng */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M408'; /* 14. Nhận hàng */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M404'; /* 15. Hóa đơn mua hàng */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M220'; /* 16. Thanh toán */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M410'; /* 17. Quản lý bảo hành của nhà cung cấp */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M407'; /* 18. Hợp đồng nguyên tắc mua hàng */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M106'; /* 19. Phương thức thanh toán */"));

    $db->execute(sprintf("UPDATE qsforms SET Effected = 0 WHERE FormCode = 'M739'; /* 20. Phân tích hiệu suất thiết bị tổng thể */"));
}