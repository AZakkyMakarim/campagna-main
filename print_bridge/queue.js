const fs = require('fs');
const path = require('path');

const QUEUE_FILE = path.join(__dirname, 'jobs.json');

function loadQueue() {
    if (!fs.existsSync(QUEUE_FILE)) return [];
    return JSON.parse(fs.readFileSync(QUEUE_FILE, 'utf8'));
}

function saveQueue(queue) {
    fs.writeFileSync(QUEUE_FILE, JSON.stringify(queue, null, 2));
}

function addJob(job) {
    const queue = loadQueue();
    queue.push({
        id: Date.now() + '-' + Math.random().toString(36).slice(2),
        status: 'PENDING',
        tries: 0,
        created_at: new Date().toISOString(),
        ...job
    });
    saveQueue(queue);
}

function getNextJob() {
    const queue = loadQueue();
    return queue.find(j => j.status === 'PENDING');
}

function updateJob(id, data) {
    const queue = loadQueue();
    const idx = queue.findIndex(j => j.id === id);
    if (idx >= 0) {
        queue[idx] = { ...queue[idx], ...data };
        saveQueue(queue);
    }
}

module.exports = { addJob, getNextJob, updateJob };
