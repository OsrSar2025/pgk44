// Auto Update Worker - Background Service
// ไฟล์นี้จะทำงานในพื้นหลังและอัปเดตสถานะอัตโนมัติ

class AutoUpdateWorker {
    constructor() {
        this.isRunning = false;
        this.intervalId = null;
        this.totalChecks = 0;
        this.totalUpdated = 0;
        this.startTime = null;
        this.logs = [];
        this.maxLogs = 100; // จำกัดจำนวน log
    }
    
    async start() {
        if (this.isRunning) {
            this.log('⚠️ Service is already running', 'warning');
            return;
        }
        
        this.log('🚀 Starting Auto Update Worker...', 'info');
        this.isRunning = true;
        this.startTime = Date.now();
        
        // เรียกใช้ทันทีครั้งแรก
        await this.runUpdate();
        
        // ตั้ง interval ทุก 30 วินาที
        this.intervalId = setInterval(() => {
            this.runUpdate();
        }, 30000);
        
        this.log('✅ Auto Update Worker started successfully', 'success');
    }
    
    stop() {
        if (!this.isRunning) {
            this.log('⚠️ Service is not running', 'warning');
            return;
        }
        
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        
        this.isRunning = false;
        this.log('⏹️ Auto Update Worker stopped', 'info');
    }
    
    async runUpdate() {
        this.totalChecks++;
        const timestamp = new Date().toLocaleString('th-TH');
        
        try {
            this.log(`🔄 Running update check #${this.totalChecks}...`, 'info');
            
            const response = await fetch('../api/auto_update_pending_bets.php');
            const data = await response.json();
            
            if (data.success) {
                if (data.updated_count > 0) {
                    this.totalUpdated += data.updated_count;
                    this.log(`✅ Updated ${data.updated_count} bets: ${JSON.stringify(data.results)}`, 'success');
                } else {
                    this.log(`ℹ️ No expired bets found (${data.expired_bets_found} pending bets checked)`, 'info');
                }
            } else {
                this.log(`❌ Update failed: ${data.message}`, 'error');
            }
        } catch (error) {
            this.log(`❌ Error running update: ${error.message}`, 'error');
        }
    }
    
    log(message, type = 'info') {
        const timestamp = new Date().toLocaleString('th-TH');
        const logEntry = {
            timestamp,
            message,
            type
        };
        
        this.logs.push(logEntry);
        
        // จำกัดจำนวน log
        if (this.logs.length > this.maxLogs) {
            this.logs.shift();
        }
        
        // แสดง log ใน console
        console.log(`[${timestamp}] ${message}`);
    }
    
    getStats() {
        const uptime = this.startTime ? Math.floor((Date.now() - this.startTime) / 1000) : 0;
        const minutes = Math.floor(uptime / 60);
        const seconds = uptime % 60;
        
        return {
            isRunning: this.isRunning,
            totalChecks: this.totalChecks,
            totalUpdated: this.totalUpdated,
            uptime: `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`,
            logs: this.logs
        };
    }
    
    getRecentLogs(count = 10) {
        return this.logs.slice(-count);
    }
}

// สร้าง instance ของ worker
const autoUpdateWorker = new AutoUpdateWorker();

// เริ่มต้นอัตโนมัติเมื่อโหลดไฟล์
console.log('Auto Update Worker loaded');

// เริ่มต้นหลังจาก 3 วินาที
setTimeout(() => {
    autoUpdateWorker.start();
}, 3000);

// Export สำหรับใช้ในไฟล์อื่น
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AutoUpdateWorker;
}
