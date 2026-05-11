# 🚀 วิธีตั้งค่า Auto Update System (Cron Job)

ระบบนี้จะอัพเดทสถานะ pending bets อัตโนมัติเมื่อครบ 3 นาที โดยทำงานที่ backend ไม่ต้องเปิดหน้าเว็บ

## 📋 สำหรับ Windows (Laragon/XAMPP)

### วิธีที่ 1: ใช้ Windows Task Scheduler (แนะนำ)

1. **เปิด Command Prompt เป็น Administrator**
   - คลิกขวาที่ Command Prompt → Run as Administrator

2. **ไปที่โฟลเดอร์ api**
   ```cmd
   cd C:\laragon\www\pgk44\api
   ```

3. **รันไฟล์ setup**
   ```cmd
   setup_windows_task.bat
   ```

4. **ตรวจสอบว่า Task ถูกสร้างแล้ว**
   ```cmd
   schtasks /query /tn "TikTok Auto Update Pending Bets"
   ```

### วิธีที่ 2: ใช้ Batch File (สำหรับทดสอบ)

1. ดับเบิลคลิก `run_cron.bat`
2. ระบบจะทำงานทุก 1 นาที
3. กด Ctrl+C เพื่อหยุด

## 📋 สำหรับ Linux/Unix (cPanel, VPS, etc.)

### วิธีที่ 1: ใช้ cPanel Cron Jobs

1. เข้าสู่ cPanel
2. ไปที่ **Cron Jobs**
3. เพิ่ม Cron Job ใหม่:
   - **Minute**: `*` (ทุกนาที)
   - **Hour**: `*` (ทุกชั่วโมง)
   - **Day**: `*` (ทุกวัน)
   - **Month**: `*` (ทุกเดือน)
   - **Weekday**: `*` (ทุกวันในสัปดาห์)
   - **Command**: 
     ```bash
     /usr/bin/php /home/username/public_html/pkg44/api/cron_update_bets.php
     ```
     หรือใช้ curl:
     ```bash
     curl -s http://yourdomain.com/pkg44/api/auto_update_pending_bets.php > /dev/null 2>&1
     ```

### วิธีที่ 2: ใช้ SSH (VPS/Dedicated Server)

1. **เข้าสู่ SSH**
   ```bash
   ssh user@yourdomain.com
   ```

2. **แก้ไข crontab**
   ```bash
   crontab -e
   ```

3. **เพิ่มบรรทัดนี้** (ทำงานทุก 1 นาที):
   ```bash
   * * * * * /usr/bin/php /path/to/pkg44/api/cron_update_bets.php >> /path/to/pkg44/api/cron.log 2>&1
   ```

   หรือใช้ curl:
   ```bash
   * * * * * curl -s http://yourdomain.com/pkg44/api/auto_update_pending_bets.php > /dev/null 2>&1
   ```

4. **บันทึกและออก** (กด Esc แล้วพิมพ์ :wq สำหรับ vi/vim)

## ✅ การตรวจสอบ

### ตรวจสอบว่า Task ทำงานหรือไม่ (Windows):
```cmd
schtasks /query /tn "TikTok Auto Update Pending Bets"
```

### ตรวจสอบ Log:
- **Windows**: ดูไฟล์ `cron_update_bets.log` ในโฟลเดอร์ `api`
- **Linux**: ดูไฟล์ `cron.log` ที่กำหนดไว้

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

1. ระบบจะตรวจสอบ pending bets ที่ผ่านไปแล้ว 3 นาที (ใช้เวลาไทย UTC+7)
2. อัพเดทสถานะตาม auto_win ของผู้ใช้
3. คำนวณ profit และอัพเดท balance
4. อัพเดท date field เป็นเวลาที่เปลี่ยนสถานะ (เวลาไทย)

## ⚠️ หมายเหตุ

- ระบบจะทำงานทุก 1 นาทีอัตโนมัติ
- ไม่ต้องเปิดหน้าเว็บ
- ทำงานได้แม้ปิดหน้าเว็บหรือปิด browser
- ใช้เวลาไทย (UTC+7) อย่างถูกต้อง
- อัพเดท date field เป็นเวลาที่เปลี่ยนสถานะ

## 🔧 แก้ไขปัญหา

### ถ้า Task ไม่ทำงาน (Windows):
1. ตรวจสอบว่า PHP path ถูกต้องใน `setup_windows_task.bat`
2. ตรวจสอบว่า Task ถูกสร้างแล้ว:
   ```cmd
   schtasks /query /tn "TikTok Auto Update Pending Bets"
   ```
3. ลบ Task เก่าและสร้างใหม่:
   ```cmd
   schtasks /delete /tn "TikTok Auto Update Pending Bets" /f
   setup_windows_task.bat
   ```

### ถ้า Cron Job ไม่ทำงาน (Linux):
1. ตรวจสอบว่า PHP path ถูกต้อง:
   ```bash
   which php
   ```
2. ตรวจสอบว่า cron service ทำงาน:
   ```bash
   service cron status
   ```
3. ตรวจสอบ log:
   ```bash
   tail -f /path/to/pkg44/api/cron.log
   ```

