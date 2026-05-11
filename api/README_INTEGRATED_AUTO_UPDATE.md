# 🚀 Integrated Auto Update System

ระบบ Auto Update ที่ถูกย้ายและรวมเข้ากับหน้า Page ต่างๆ แล้ว

## 📁 ไฟล์ที่สำคัญ

### 1. **auto_update_worker.js**
- **ไฟล์หลัก** - JavaScript worker สำหรับทำงานในพื้นหลัง
- **เรียกใช้ API ทุก 30 วินาที** - ตรวจสอบ pending bets และอัปเดตสถานะ
- **จัดการสถานะและสถิติ** - ติดตามการทำงานแบบ real-time
- **เริ่มต้นอัตโนมัติ** - ทำงานทันทีเมื่อโหลดหน้า

### 2. **auto_update_pending_bets.php**
- **API หลัก** - อัปเดตสถานะอัตโนมัติ
- **ตรวจสอบเวลาหมด** - 3 นาที
- **สุ่มผลลัพธ์** - red win หรือ blue win
- **อัปเดตฐานข้อมูล** - บันทึกสถานะใหม่

## 🎯 หน้าที่รวม Auto Update แล้ว

### ✅ หน้าที่รวมแล้ว:
1. **`bank.html`** - หน้าบัตรธนาคาร
2. **`bindmpwd.html`** - หน้าผูกรหัสผ่านการถอน
3. **`cashout.html`** - หน้าถอนเงิน
4. **`changpwd.html`** - หน้าเปลี่ยนรหัสผ่านการถอน
5. **`donate.html`** - หน้าบริจาค
6. **`help.html`** - หน้าบันทึกการฝากและถอนเงิน
7. **`music-chart.html`** - หน้าชาร์ตเพลง
8. **`order.html`** - หน้าบันทึกการทำธุรกรรม
9. **`pkg44.html`** - หน้าหลัก PK44
10. **`profile.html`** - หน้าโปรไฟล์
11. **`pwd.html`** - หน้าเปลี่ยนรหัสผ่านบัญชี
12. **`recharge.html`** - หน้าเติมเงิน
13. **`security.html`** - หน้าความปลอดภัยของบัญชี
14. **`support.html`** - หน้าสนับสนุน
15. **`open.html`** - หน้าเปิดใช้งาน

## 🔧 วิธีการทำงาน

### 1. **Auto Start**
- ระบบจะเริ่มต้นอัตโนมัติเมื่อโหลดหน้าใดๆ ใน Page
- เริ่มต้นหลังจาก 3 วินาที
- ทำงานต่อเนื่องในพื้นหลัง

### 2. **Background Processing**
- เรียกใช้ `auto_update_pending_bets.php` ทุก 30 วินาที
- ตรวจสอบ pending bets ที่หมดเวลา (3 นาที)
- สุ่มผลลัพธ์ red win หรือ blue win
- อัปเดตฐานข้อมูลอัตโนมัติ

### 3. **Real-time Monitoring**
- แสดงสถิติใน console ทุก 5 วินาที
- ติดตามจำนวนการตรวจสอบ
- ติดตามจำนวนการอัปเดต
- แสดงเวลาทำงาน

## 📊 การตรวจสอบ

### 1. **Console Logs**
```javascript
// ดูใน Browser Console (F12)
Auto Update Service initialized in [page].html
Auto Update: 5 checks, 2 updated, uptime: 02:30
```

### 2. **Network Tab**
- ดู API calls ไปยัง `auto_update_pending_bets.php`
- ตรวจสอบ response และ status

### 3. **Database**
```sql
SELECT * FROM bet_history WHERE status = 'pending' ORDER BY date DESC;
```

## ⚙️ การตั้งค่า

### 1. **เปลี่ยนเวลา timeout**
ใน `auto_update_pending_bets.php`:
```php
$threeMinutesAgo = date('Y-m-d H:i:s', time() - 180); // 3 นาที = 180 วินาที
```

### 2. **เปลี่ยนช่วงเวลาการตรวจสอบ**
ใน `auto_update_worker.js`:
```javascript
this.intervalId = setInterval(() => {
    this.runUpdate();
}, 30000); // 30 วินาที
```

### 3. **เปลี่ยนกำไร**
ใน `auto_update_pending_bets.php`:
```php
$profit = 50; // กำไร 50 บาท
```

## 🌟 ข้อดี

✅ **ทำงานอัตโนมัติ** - ไม่ต้องเปิดหน้าเฉพาะ
✅ **ทำงานในพื้นหลัง** - ไม่รบกวนผู้ใช้
✅ **ทำงานต่อเนื่อง** - ตราบใดที่เปิดหน้าใดๆ
✅ **แสดงสถิติ** - ติดตามการทำงาน
✅ **ทำงานบน domain** - ไม่ต้องใช้ batch files
✅ **เริ่มต้นอัตโนมัติ** - ไม่ต้องกดปุ่ม

## 🔍 การแก้ไขปัญหา

### 1. **Service ไม่ทำงาน**
- ตรวจสอบ console logs
- ตรวจสอบ network requests
- ตรวจสอบ API response

### 2. **ไม่มีการอัปเดต**
- ตรวจสอบว่ามี pending bets
- ตรวจสอบเวลา timeout
- ตรวจสอบ database connection

### 3. **Performance Issues**
- ตรวจสอบจำนวน pending bets
- ตรวจสอบ database performance
- ตรวจสอบ server resources

## 📝 หมายเหตุ

- ระบบจะทำงานต่อเนื่องเมื่อเปิดหน้าใดๆ ใน Page
- สามารถเปิดหลายหน้าได้พร้อมกัน
- ระบบจะหยุดเมื่อปิดหน้าเว็บทั้งหมด
- สำหรับการทำงาน 24/7 ควรเปิดหน้าใดๆ ไว้

## 🚀 การใช้งาน

1. **เปิดหน้าใดๆ ใน Page** - ระบบจะเริ่มต้นอัตโนมัติ
2. **ดู console logs** - ตรวจสอบการทำงาน
3. **ตรวจสอบ database** - ดูการอัปเดตสถานะ
4. **ไม่ต้องทำอะไร** - ระบบทำงานเอง

ระบบนี้จะทำให้การอัปเดตสถานะ pending bets ทำงานอัตโนมัติโดยไม่ต้องใช้ batch files หรือ cron jobs! 🎉
