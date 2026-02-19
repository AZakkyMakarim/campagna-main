const Service = require('node-windows').Service;

const svc = new Service({
    name: 'Local Print Bridge',
    description: 'Local Print Bridge with Queue for POS',
    script: require('path').join(__dirname, 'worker.js')
});

svc.on('install', function () {
    svc.start();
    console.log('Service installed & started');
});

svc.install();
