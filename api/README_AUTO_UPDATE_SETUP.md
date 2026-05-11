# 🚀 ระบบอัพเดทสถานะอัตโนมัติ (Auto Update System)

ระบบนี้จะอัพเดทสถานะ pending bets อัตโนมัติเมื่อครบ 3 นาที โดยทำงานที่ backend ไม่ต้องเปิดหน้าเว็บ

## 📋 วิธีการตั้งค่า

### สำหรับ Windows (Laragon/XAMPP)

#### วิธีที่ 1: ใช้ Windows Task Scheduler (แนะนำ)

1. เปิด Command Prompt **เป็น Administrator**
2. ไปที่โฟลเดอร์ `api`
   ```
   cd C:\laragon\www\pgk44\api
   ```
3. รันไฟล์ setup:
   ```
   setup_windows_task.bat
   ```
4. ระบบจะสร้าง Task ที่ทำงานทุก 1 นาทีอัตโนมัติ

#### วิธีที่ 2: ใช้ Batch File (สำหรับทดสอบ)

1. ดับเบิลคลิก `run_cron.bat`
2. ระบบจะทำงานทุก 1 นาที
3. กด Ctrl+C เพื่อหยุด

### สำหรับ Linux/Unix (cPanel, VPS, etc.)

1. ตั้งค่า cron job:
   ```bash
   crontab -e
   ```

2. เพิ่มบรรทัดนี้ (ทำงานทุก 1 นาที):
   ```
   * * * * * /usr/bin/php /path/to/pkg44/api/cron_update_bets.php >> /path/to/pkg44/api/cron.log 2>&1
   ```

   หรือใช้ curl:
   ```
   * * * * * curl -s http://yourdomain.com/pkg44/api/auto_update_pending_bets.php > /dev/null 2>&1
   ```

## 🔧 ไฟล์ที่เกี่ยวข้อง

- **auto_update_pending_bets.php** - API หลักสำหรับอัพเดทสถานะ
- **cron_update_bets.php** - ไฟล์ที่ถูกเรียกโดย cron job
- **run_cron.bat** - Batch file สำหรับ Windows
- **setup_windows_task.bat** - ไฟล์สำหรับตั้งค่า Windows Task Scheduler

## ✅ การตรวจสอบ

### ตรวจสอบว่า Task ทำงานหรือไม่ (Windows):
```cmd
schtasks /query /tn "TikTok Auto Update Pending Bets"
```

### ตรวจสอบ Log:
- Windows: ดูไฟล์ `cron_update_bets.log` ในโฟลเดอร์ `api`
- Linux: ดูไฟล์ `cron.log` ที่กำหนดไว้

### ทดสอบ API โดยตรง:
เปิดเบราว์เซอร์ไปที่:
```
http://yourdomain.com/pkg44/api/auto_update_pending_bets.php
```

ควรเห็น JSON response:
```json
{
  "success": true,
  "message": "Auto-update completed",
  "updated_count": 0,
  "expired_bets_found": 0,
  "timestamp": "2024-12-01 20:10:00"
}
```

## 🎯 วิธีการทำงาน

1. ระบบจะตรวจสอบ pending bets ที่ผ่านไปแล้ว 3 นาที
2. อัพเดทสถานะตาม auto_win ของผู้ใช้
3. คำนวณ profit และอัพเดท balance
4. อัพเดท date field เป็นเวลาที่เปลี่ยนสถานะ (เวลาไทย)

## ⚠️ หมายเหตุ

- ระบบจะทำงานทุก 1 นาทีอัตโนมัติ
- ไม่ต้องเปิดหน้าเว็บ
- ทำงานได้แม้ปิดหน้าเว็บหรือปิด browser
- ใช้เวลาไทย (UTC+7) อย่างถูกต้อง

