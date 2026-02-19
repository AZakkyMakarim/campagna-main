// Install Node.js
// Run bridge && worker  ex: node bridge.js

const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');

const { addJob } = require('./queue');

const escpos = require('escpos');
escpos.USB = require('escpos-usb');
escpos.Network = require('escpos-network');

const usb = require('usb');

const app = express();
// app.use(express.json());
app.use(cors());
app.use(bodyParser.json({ limit: '5mb' }));

// ===============================
// HELPER: LIST USB PRINTER
// ===============================
function listUsbPrinters() {
    const devices = usb.getDeviceList();
    return devices.map(d => ({
        busNumber: d.busNumber,
        deviceAddress: d.deviceAddress,
        vendorId: d.deviceDescriptor.idVendor,
        productId: d.deviceDescriptor.idProduct
    }));
}

// ===============================
// ENDPOINT: LIST PRINTER
// ===============================
app.get('/printers', (req, res) => {
    let usbPrinters = [];
    try {
        usbPrinters = escpos.USB.findPrinter().map((p, i) => ({
            id: 'usb-' + i,
            type: 'USB',
            name: `USB Printer ${i + 1}`,
            vendorId: p.deviceDescriptor.idVendor,
            productId: p.deviceDescriptor.idProduct
        }));
    } catch (e) {}

    res.json({
        success: true,
        data: [...usbPrinters]
    });
});

// ===============================
// ENDPOINT: PRINT
// ===============================
app.post('/print', (req, res) => {
    const { type, printer_ip, printer_port, receipt } = req.body;

    if (!receipt) {
        return res.status(400).json({ success: false, message: 'receipt wajib ada' });
    }

    addJob({
        type: type || 'USB',
        printer_ip,
        printer_port,
        receipt
    });

    res.json({ success: true, message: 'Job masuk queue' });
});

// ===============================
// ENDPOINT: OPEN CASH DRAWER
// ===============================
app.post('/open-drawer', (req, res) => {
    const { type, printer_ip, printer_port } = req.body;

    const cmd = Buffer.from('\x1B\x70\x00\x19\xFA', 'binary').toString('base64');

    addJob({
        type: type || 'USB',
        printer_ip,
        printer_port,
        receipt: cmd
    });

    res.json({ success: true, message: 'Drawer job masuk queue' });
});

// ===============================
const PORT = 3333;
app.listen(PORT, () => {
    console.log(`🖨️ Local Print Bridge running on http://localhost:${PORT}`);
});
