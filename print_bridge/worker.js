const escpos = require('escpos');
escpos.USB = require('escpos-usb');
escpos.Network = require('escpos-network');
let isProcessing = false;

const { getNextJob, updateJob } = require('./queue');

function processQueue() {
    if (isProcessing) return; // 🔒 lagi proses, skip

    const job = getNextJob();
    if (!job) return;

    isProcessing = true; // 🔒 lock

    updateJob(job.id, { status: 'PROCESSING' });

    console.log('🖨️ Processing job:', job.id);

    let device;

    if (job.type === 'LAN') {
        device = new escpos.Network(job.printer_ip, job.printer_port || 9100);
    } else {
        device = new escpos.USB();
    }

    const printer = new escpos.Printer(device);

    device.open(function (error) {
        if (error) {
            console.error('❌ Print error:', error.message);
            const tries = (job.tries || 0) + 1;

            if (tries >= 3) {
                updateJob(job.id, { status: 'FAILED', tries });
            } else {
                updateJob(job.id, { status: 'PENDING', tries });
            }

            isProcessing = false; // 🔓 unlock
            return;
        }

        const data = Buffer.from(job.receipt, 'base64');
        try {
            printer
                .raw(data)   // <-- kirim RAW ESC/POS bytes
                .cut()
                .close();

            updateJob(job.id, { status: 'DONE' });
            console.log('✅ Job done:', job.id);
        } catch (e) {
            console.error('❌ Print exception:', e.message);
            updateJob(job.id, { status: 'FAILED' });
        }

        isProcessing = false; // 🔓 unlock
    });
}

// Loop tiap 3 detik
setInterval(processQueue, 3000);
