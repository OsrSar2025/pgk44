@echo off
REM ไฟล์ batch สำหรับเรียกใช้ cron job บน Windows
REM เรียกใช้ทุก 1 นาที (60 วินาที) เพื่อตรวจสอบและอัปเดต pending bets

:loop
echo Running cron job at %date% %time%
cd /d "%~dp0"
php cron_update_bets.php
timeout /t 60 /nobreak > nul
goto loop
