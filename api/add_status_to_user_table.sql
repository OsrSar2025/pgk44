-- เพิ่มคอลัมน์ status ในตาราง user
-- ใช้สำหรับเก็บสถานะของผู้ใช้ เช่น 'active', 'inactive', 'banned' เป็นต้น

ALTER TABLE `user` 
ADD COLUMN `status` VARCHAR(50) DEFAULT 'active' 
AFTER `number`;

-- อัปเดตข้อมูลที่มีอยู่แล้วให้มี status เป็น 'active'
UPDATE `user` SET `status` = 'active' WHERE `status` IS NULL;

-- เพิ่ม index เพื่อเพิ่มความเร็วในการค้นหา
CREATE INDEX `idx_status` ON `user`(`status`);

-- แสดงโครงสร้างตารางใหม่
DESCRIBE `user`;

