# 📦 ไฟล์ที่ต้องอัพโหลดลง Domain สำหรับ Auto Refresh System

## ✅ ไฟล์ Frontend (Page/) - ระบบ Auto Refresh

### 1. **order.html** ✅ **ต้องอัพโหลด**
   - รับสัญญาณ refresh จากหน้าเว็บอื่นๆ
   - Auto refresh ทุก 3 วินาที
   - ฟัง `storage` event เพื่อรับสัญญาณ refresh

### 2. **pkg44.html** ✅ **ต้องอัพโหลด**
   - ส่งสัญญาณ refresh เมื่อเดิมพันสำเร็จ
   - ใช้ `localStorage.setItem('refresh_order_page', timestamp)`

### 3. **profile.html** ✅ **ต้องอัพโหลด**
   - ส่งสัญญาณ refresh เมื่อ balance เปลี่ยน
   - ส่งสัญญาณ refresh เมื่อคลิกไปที่ order.html
   - Auto refresh ทุก 3 วินาที

---

## ✅ ไฟล์ API - ระบบ Auto Update & Time

### 1. **api/db_connect.php** ✅ **ต้องอัพโหลด**
   - ตั้ง timezone เป็น +07:00 (เวลาไทย)

### 2. **api/add_bet_history.php** ✅ **ต้องอัพโหลด**
   - บันทึกเวลาไทยเมื่อเดิมพัน
   - ตั้ง timezone เป็น Asia/Bangkok

### 3. **api/update_bet_status_by_date.php** ✅ **ต้องอัพโหลด**
   - อัพเดทเวลาไทยเมื่อเปลี่ยนสถานะ
   - ตั้ง timezone เป็น Asia/Bangkok

### 4. **api/get_bet_history.php** ✅ **ต้องอัพโหลด**
   - แสดงเวลาไทย
   - ตั้ง timezone เป็น Asia/Bangkok

### 5. **api/get_user.php** ✅ **ต้องอัพโหลด**
   - แก้ไข error handling

### 6. **api/auto_update_pending_bets.php** ✅ **ต้องอัพโหลด**
   - Auto update pending bets เมื่อครบ 3 นาที
   - ตั้ง timezone เป็น Asia/Bangkok
   - อัพเดท date field เป็นเวลาที่เปลี่ยนสถานะ

### 7. **api/cron_update_bets.php** ✅ **ต้องอัพโหลด**
   - ไฟล์สำหรับ cron job
   - เรียกใช้ auto_update_pending_bets.php ทุก 1 นาที

---

## 📋 ไฟล์เอกสาร (ไม่จำเป็น แต่แนะนำ)

### 1. **api/SETUP_CRON_DOMAIN.md** ⚠️ **แนะนำ**
   - คู่มือการตั้งค่า cron job บน domain

---

## 🚫 ไฟล์ที่ไม่ต้องอัพโหลด (Windows เท่านั้น)

### 1. **api/run_cron.bat** ❌ **ไม่ต้องอัพโหลด**
   - ใช้ได้เฉพาะ Windows
   - บน domain ใช้ cron job แทน

### 2. **api/setup_windows_task.bat** ❌ **ไม่ต้องอัพโหลด**
   - ใช้ได้เฉพาะ Windows
   - บน domain ใช้ cron job แทน

---

## 📝 สรุปไฟล์ที่ต้องอัพโหลด

### Frontend (3 ไฟล์):
```
Page/
├── order.html          ✅ ต้องอัพโหลด
├── pkg44.html       ✅ ต้องอัพโหลด
└── profile.html        ✅ ต้องอัพโหลด
```

### API (7 ไฟล์):
```
api/
├── db_connect.php                    ✅ ต้องอัพโหลด
├── add_bet_history.php               ✅ ต้องอัพโหลด
├── update_bet_status_by_date.php     ✅ ต้องอัพโหลด
├── get_bet_history.php               ✅ ต้องอัพโหลด
├── get_user.php                      ✅ ต้องอัพโหลด
├── auto_update_pending_bets.php      ✅ ต้องอัพโหลด
└── cron_update_bets.php              ✅ ต้องอัพโหลด
```

### เอกสาร (1 ไฟล์ - แนะนำ):
```
api/
└── SETUP_CRON_DOMAIN.md              ⚠️ แนะนำ (คู่มือ)
```

---

## 🎯 วิธีอัพโหลด

1. **อัพโหลดไฟล์ Frontend** (3 ไฟล์):
   - `Page/order.html`
   - `Page/pkg44.html`
   - `Page/profile.html`

2. **อัพโหลดไฟล์ API** (7 ไฟล์):
   - `api/db_connect.php`
   - `api/add_bet_history.php`
   - `api/update_bet_status_by_date.php`
   - `api/get_bet_history.php`
   - `api/get_user.php`
   - `api/auto_update_pending_bets.php`
   - `api/cron_update_bets.php`

3. **ตั้งค่า Cron Job** (ตาม `api/SETUP_CRON_DOMAIN.md`):
   - ใช้ cPanel Cron Jobs หรือ SSH crontab
   - ทำงานทุก 1 นาที
   - Command: `/usr/bin/php /path/to/pkg44/api/cron_update_bets.php`

---

## ✅ ตรวจสอบหลังอัพโหลด

1. **ทดสอบ Auto Refresh**:
   - เปิดหน้า `order.html` และ `pkg44.html` ใน 2 แท็บ
   - เดิมพันในหน้า `pkg44.html`
   - ดูว่า `order.html` refresh อัตโนมัติหรือไม่

2. **ทดสอบ Auto Update**:
   - ตั้งค่า cron job ตามคู่มือ
   - ตรวจสอบ log: `api/cron_update_bets.log`
   - ทดสอบ API: `http://yourdomain.com/pkg44/api/auto_update_pending_bets.php`

3. **ทดสอบเวลาไทย**:
   - ตรวจสอบว่าเวลาที่แสดงเป็นเวลาไทย (UTC+7)
   - ตรวจสอบว่าเวลาอัพเดทเมื่อเปลี่ยนสถานะถูกต้อง

---

## ⚠️ หมายเหตุ

- **ไม่ต้องอัพโหลดไฟล์ .bat** (ใช้ได้เฉพาะ Windows)
- **ต้องตั้งค่า Cron Job** เพื่อให้ auto update ทำงานอัตโนมัติ
- **ตรวจสอบ PHP path** ใน cron job ให้ถูกต้อง
- **ตรวจสอบ timezone** ให้เป็นเวลาไทย (UTC+7)

