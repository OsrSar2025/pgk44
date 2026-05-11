# 🚀 Auto Update System for Domain

ระบบอัปเดตสถานะอัตโนมัติที่ทำงานได้บน domain โดยไม่ต้องใช้ batch files

## 📁 ไฟล์ที่สำคัญ

### 1. **auto_update_pending_bets.php**
- API หลักสำหรับอัปเดตสถานะ
- ตรวจสอบ pending bets ที่หมดเวลา (3 นาที)
- สุ่มผลลัพธ์ red win หรือ blue win
- อัปเดตฐานข้อมูลอัตโนมัติ

### 2. **auto_update_service.html**
- หน้าจัดการแบบเต็มรูปแบบ
- เริ่ม/หยุด service ได้
- แสดงสถิติและ log
- ทำงานอัตโนมัติเมื่อเปิดหน้า

### 3. **background_dashboard.html**
- Dashboard สำหรับ background service
- ใช้ background worker
- แสดงสถานะแบบ real-time
- เริ่มต้นอัตโนมัติ

### 4. **background_worker.js**
- JavaScript worker สำหรับทำงานในพื้นหลัง
- เรียกใช้ API ทุก 30 วินาที
- จัดการสถานะและสถิติ

## 🚀 วิธีการใช้งาน

### วิธีที่ 1: ใช้ Auto Update Service (แนะนำ)
```
http://yourdomain.com/api/auto_update_service.html
```
1. เปิดหน้าเว็บ
2. คลิก "Start Service"
3. ระบบจะทำงานทุก 30 วินาที

### วิธีที่ 2: ใช้ Background Dashboard
```
http://yourdomain.com/api/background_dashboard.html
```
1. เปิดหน้าเว็บ
2. ระบบจะเริ่มต้นอัตโนมัติ
3. แสดงสถานะแบบ real-time

### วิธีที่ 3: ใช้ Background Worker โดยตรง
```html
<script src="background_worker.js"></script>
<script>
    const worker = new AutoUpdateWorker();
    worker.start();
</script>
```

## ⚙️ การตั้งค่า

### 1. เปลี่ยนเวลา timeout
ใน `auto_update_pending_bets.php`:
```php
$threeMinutesAgo = date('Y-m-d H:i:s', time() - 180); // 3 นาที = 180 วินาที
```

### 2. เปลี่ยนช่วงเวลาการตรวจสอบ
ใน `background_worker.js`:
```javascript
this.intervalId = setInterval(() => {
    this.runUpdate();
}, 30000); // 30 วินาที
```

### 3. เปลี่ยนกำไร
ใน `auto_update_pending_bets.php`:
```php
$profit = 50; // กำไร 50 บาท
```

## 📊 การตรวจสอบ

### 1. ดู Log
- เปิดหน้า dashboard
- ดู log ในส่วนล่าง
- แสดงผลการอัปเดตแบบ real-time

### 2. ตรวจสอบฐานข้อมูล
```sql
SELECT * FROM bet_history WHERE status = 'pending' ORDER BY date DESC;
```

### 3. ทดสอบ API
```
http://yourdomain.com/api/auto_update_pending_bets.php
```

## 🔧 การแก้ไขปัญหา

### 1. Service ไม่ทำงาน
- ตรวจสอบว่า API ทำงานได้
- ดู console log ใน browser
- ตรวจสอบ network connection

### 2. ไม่มีการอัปเดต
- ตรวจสอบว่ามี pending bets
- ตรวจสอบเวลา timeout
- ดู error log ใน browser

### 3. ฐานข้อมูลไม่ถูกอัปเดต
- ตรวจสอบ database connection
- ตรวจสอบ SQL query
- ดู error log ใน server

## 🌟 ข้อดี

✅ **ทำงานบน domain** - ไม่ต้องใช้ batch files
✅ **ทำงานอัตโนมัติ** - ไม่ต้องอยู่ในหน้าเว็บ
✅ **แสดงสถานะ real-time** - ดูผลลัพธ์ทันที
✅ **จัดการง่าย** - เริ่ม/หยุดได้ด้วยปุ่ม
✅ **แสดงสถิติ** - ดูจำนวนการอัปเดต
✅ **Log ครบถ้วน** - ดูประวัติการทำงาน

## 📝 หมายเหตุ

- ระบบจะทำงานต่อเนื่องเมื่อเปิดหน้าเว็บ
- สามารถเปิดหลายหน้าได้พร้อมกัน
- ระบบจะหยุดเมื่อปิดหน้าเว็บ
- สำหรับการทำงาน 24/7 ควรใช้ cron job หรือ scheduled task
