// Background Worker สำหรับ Auto Update
// ไฟล์นี้จะทำงานในพื้นหลังและอัปเดตสถานะอัตโนมัติ

class AutoUpdateWorker {
    constructor() {
        this.isRunning = false;
        this.intervalId = null;
        this.totalChecks = 0;
        this.totalUpdated = 0;
        this.startTime = null;
    }
    
    async start() {
        if (this.isRunning) {
            console.log('Service is already running');
            return;
        }
        
        console.log('Starting Auto Update Worker...');
        this.isRunning = true;
        this.startTime = Date.now();
        
        // เรียกใช้ทันทีครั้งแรก
        await this.runUpdate();
        
        // ตั้ง interval ทุก 30 วินาที
        this.intervalId = setInterval(() => {
            this.runUpdate();
        }, 30000);
        
        console.log('Auto Update Worker started successfully');
    }
    
    stop() {
        if (!this.isRunning) {
            console.log('Service is not running');
            return;
        }
        
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        
        this.isRunning = false;
        console.log('Auto Update Worker stopped');
    }
    
    async runUpdate() {
        this.totalChecks++;
        const timestamp = new Date().toLocaleString('th-TH');
        
        try {
            console.log(`[${timestamp}] Running update check #${this.totalChecks}...`);
            
            const response = await fetch('auto_update_pending_bets.php');
            const data = await response.json();
            
            if (data.success) {
                if (data.updated_count > 0) {
                    this.totalUpdated += data.updated_count;
                    console.log(`[${timestamp}] ✅ Updated ${data.updated_count} bets:`, data.results);
                } else {
                    console.log(`[${timestamp}] ℹ️ No expired bets found (${data.expired_bets_found} pending bets checked)`);
                }
            } else {
                console.log(`[${timestamp}] ❌ Update failed: ${data.message}`);
            }
        } catch (error) {
            console.log(`[${timestamp}] ❌ Error running update: ${error.message}`);
        }
    }
    
    getStats() {
        const uptime = this.startTime ? Math.floor((Date.now() - this.startTime) / 1000) : 0;
        const minutes = Math.floor(uptime / 60);
        const seconds = uptime % 60;
        
        return {
            isRunning: this.isRunning,
            totalChecks: this.totalChecks,
            totalUpdated: this.totalUpdated,
            uptime: `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
        };
    }
}

// สร้าง instance ของ worker
const autoUpdateWorker = new AutoUpdateWorker();

// เริ่มต้นอัตโนมัติเมื่อโหลดไฟล์
console.log('Auto Update Worker loaded');
console.log('Starting service automatically...');

// เริ่มต้นหลังจาก 3 วินาที
setTimeout(() => {
    autoUpdateWorker.start();
}, 3000);

// Export สำหรับใช้ในไฟล์อื่น
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AutoUpdateWorker;
}
