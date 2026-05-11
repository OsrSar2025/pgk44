-- เพิ่มคอลัมน์ auto_win ในตาราง user

ALTER TABLE `user`
ADD COLUMN `auto_win` TINYINT(1) DEFAULT 0
AFTER `status`;

-- อัปเดตข้อมูลที่มีอยู่แล้วให้มีค่า auto_win เป็น 0
UPDATE `user` SET `auto_win` = 0 WHERE `auto_win` IS NULL;

-- แสดงโครงสร้างตารางเพื่อยืนยัน
DESCRIBE `user`;


