# 🚀 วิธีตั้งค่า Auto Update System บน Domain (Linux/cPanel)

ระบบนี้จะอัพเดทสถานะ pending bets อัตโนมัติเมื่อครบ 3 นาที โดยทำงานที่ backend ไม่ต้องเปิดหน้าเว็บ

## ⚠️ หมายเหตุสำคัญ

**บน Domain (Linux/cPanel) ไม่สามารถใช้ไฟล์ .bat ได้** เพราะ .bat เป็น Windows batch file

ให้ใช้ **Cron Job** แทน

## 📋 วิธีตั้งค่า Cron Job

### วิธีที่ 1: ใช้ cPanel (แนะนำ - ง่ายที่สุด)

1. **เข้าสู่ cPanel**
   - เข้าสู่ระบบ cPanel ของคุณ

2. **ไปที่ Cron Jobs**
   - ค้นหา "Cron Jobs" ใน cPanel
   - หรือไปที่: `Advanced` → `Cron Jobs`

3. **เพิ่ม Cron Job ใหม่**
   - คลิก "Add New Cron Job" หรือ "Create Cron Job"

4. **ตั้งค่าดังนี้**:
   - **Minute**: `*` (ทุกนาที)
   - **Hour**: `*` (ทุกชั่วโมง)
   - **Day**: `*` (ทุกวัน)
   - **Month**: `*` (ทุกเดือน)
   - **Weekday**: `*` (ทุกวันในสัปดาห์)

5. **Command**: 
   ```bash
   /usr/bin/php /home/username/public_html/pkg44/api/cron_update_bets.php
   ```
   
   **หรือใช้ curl** (ถ้า PHP path ไม่ถูกต้อง):
   ```bash
   curl -s http://yourdomain.com/pkg44/api/auto_update_pending_bets.php > /dev/null 2>&1
   ```

6. **บันทึก**
   - คลิก "Add New Cron Job" หรือ "Create"

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

   **หรือใช้ curl**:
   ```bash
   * * * * * curl -s http://yourdomain.com/pkg44/api/auto_update_pending_bets.php > /dev/null 2>&1
   ```

4. **บันทึกและออก**
   - กด `Esc` แล้วพิมพ์ `:wq` (สำหรับ vi/vim)
   - หรือ `Ctrl+X` แล้ว `Y` แล้ว `Enter` (สำหรับ nano)

## ✅ การตรวจสอบ

### ตรวจสอบว่า Cron Job ทำงานหรือไม่:

1. **ดู Log**:
   ```bash
   tail -f /path/to/pkg44/api/cron_update_bets.log
   ```

2. **ทดสอบ API โดยตรง**:
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

3. **ตรวจสอบ Cron Job ใน cPanel**:
   - ไปที่ Cron Jobs
   - ดู "Last Run Time" ว่าทำงานหรือไม่

## 🔧 แก้ไขปัญหา

### ถ้า Cron Job ไม่ทำงาน:

1. **ตรวจสอบ PHP path**:
   ```bash
   which php
   ```
   หรือ
   ```bash
   /usr/bin/php -v
   ```

2. **ตรวจสอบว่าไฟล์มีสิทธิ์ execute**:
   ```bash
   chmod +x /path/to/pkg44/api/cron_update_bets.php
   ```

3. **ตรวจสอบว่า cron service ทำงาน**:
   ```bash
   service cron status
   ```
   หรือ
   ```bash
   systemctl status cron
   ```

4. **ทดสอบ cron job โดยตรง**:
   ```bash
   /usr/bin/php /path/to/pkg44/api/cron_update_bets.php
   ```

### ถ้าใช้ curl แล้วยังไม่ทำงาน:

1. **ตรวจสอบว่า curl ติดตั้งแล้ว**:
   ```bash
   which curl
   ```

2. **ทดสอบ curl โดยตรง**:
   ```bash
   curl -s http://yourdomain.com/pkg44/api/auto_update_pending_bets.php
   ```

## 📝 ไฟล์ที่ต้องอัพโหลด

- `api/cron_update_bets.php` ✅
- `api/auto_update_pending_bets.php` ✅
- `api/db_connect.php` ✅

## 🎯 วิธีการทำงาน

1. Cron Job จะทำงานทุก 1 นาที
2. เรียกใช้ `cron_update_bets.php`
3. `cron_update_bets.php` จะเรียกใช้ `auto_update_pending_bets.php`
4. ตรวจสอบ pending bets ที่ผ่านไปแล้ว 3 นาที
5. อัพเดทสถานะตาม auto_win ของผู้ใช้
6. คำนวณ profit และอัพเดท balance
7. อัพเดท date field เป็นเวลาที่เปลี่ยนสถานะ (เวลาไทย)

## ⚠️ หมายเหตุ

- ระบบจะทำงานทุก 1 นาทีอัตโนมัติ
- ไม่ต้องเปิดหน้าเว็บ
- ทำงานได้แม้ปิดหน้าเว็บหรือปิด browser
- ใช้เวลาไทย (UTC+7) อย่างถูกต้อง
- อัพเดท date field เป็นเวลาที่เปลี่ยนสถานะ

