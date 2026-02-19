const escpos = require('escpos');
escpos.USB = require('escpos-usb');

const device = new escpos.USB(); // auto detect
const printer = new escpos.Printer(device);

device.open(function (err) {
    if (err) {
        console.error('❌ Gagal buka USB printer:', err);
        return;
    }

    console.log('✅ USB printer opened');

    printer
        .text("Hello\n")
        .cut()
        .close();
});
