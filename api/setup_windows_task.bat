@echo off
REM ไฟล์สำหรับตั้งค่า Windows Task Scheduler
REM ให้ทำงานทุก 1 นาทีเพื่ออัพเดทสถานะ pending bets อัตโนมัติ

echo Setting up Windows Task Scheduler for auto-update pending bets...
echo.

REM รับ path ปัจจุบัน
set SCRIPT_DIR=%~dp0
set PHP_PATH=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe
set BATCH_FILE=%SCRIPT_DIR%run_cron.bat

REM ตรวจสอบว่า PHP path ถูกต้องหรือไม่
if not exist "%PHP_PATH%" (
    echo Warning: PHP path not found at %PHP_PATH%
    echo Please update PHP_PATH in this file to match your PHP installation.
    echo.
)

REM สร้าง task ใน Windows Task Scheduler
REM ทำงานทุก 1 นาที
schtasks /create /tn "TikTok Auto Update Pending Bets" /tr "\"%BATCH_FILE%\"" /sc minute /mo 1 /ru SYSTEM /f

if %ERRORLEVEL% EQU 0 (
    echo.
    echo Success! Windows Task Scheduler has been set up.
    echo Task name: "TikTok Auto Update Pending Bets"
    echo The task will run every 1 minute automatically.
    echo.
    echo To check the task status, run:
    echo   schtasks /query /tn "TikTok Auto Update Pending Bets"
    echo.
    echo To delete the task, run:
    echo   schtasks /delete /tn "TikTok Auto Update Pending Bets" /f
    echo.
) else (
    echo.
    echo Error: Failed to create Windows Task Scheduler task.
    echo Please run this file as Administrator.
    echo.
)

pause

