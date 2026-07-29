// ═══════════════════════════════════════════════════════════════
// JobScout Real-time Statistics Server
// Powered by Node.js + Express + Socket.io
// ═══════════════════════════════════════════════════════════════

const express = require('express');
const http    = require('http');
const cors    = require('cors');
const { Server } = require('socket.io');
const mysql   = require('mysql2/promise');

const app    = express();
const server = http.createServer(app);
const io     = new Server(server, { cors: { origin: '*' } });

app.use(cors());
app.use(express.json());

// ── إعدادات قاعدة بيانات Laravel ─────────────────────────────────
const dbConfig = {
    host:     '127.0.0.1',
    user:     'root',
    password: '',
    database: 'laravel11_job_platform_app_DB',
};

// ── جلب الإحصائيات الحية ─────────────────────────────────────────
async function fetchLiveStats() {
    const conn = await mysql.createConnection(dbConfig);
    try {
        const [[jobs]]         = await conn.query('SELECT COUNT(*) AS c FROM jobs WHERE is_active = 1');
        const [[users]]        = await conn.query("SELECT COUNT(*) AS c FROM users WHERE role = 'user'");
        const [[companies]]    = await conn.query("SELECT COUNT(*) AS c FROM users WHERE role = 'company'");
        const [[applications]] = await conn.query('SELECT COUNT(*) AS c FROM job_applications');
        const [[today]]        = await conn.query('SELECT COUNT(*) AS c FROM jobs WHERE DATE(created_at) = CURDATE()');
        const [recentJobs]     = await conn.query('SELECT id, title, created_at FROM jobs ORDER BY created_at DESC LIMIT 5');

        return {
            jobs:         jobs.c,
            users:        users.c,
            companies:    companies.c,
            applications: applications.c,
            todayJobs:    today.c,
            recentJobs,
            timestamp:    new Date().toISOString(),
        };
    } finally {
        await conn.end();
    }
}

// ── REST API endpoint ────────────────────────────────────────────
app.get('/api/stats', async (req, res) => {
    try {
        res.json(await fetchLiveStats());
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// ── WebSocket: بث الإحصائيات كل 5 ثواني ─────────────────────────
io.on('connection', async (socket) => {
    console.log(`✓ Client connected: ${socket.id}`);

    // إرسال البيانات فور الاتصال
    try {
        socket.emit('stats', await fetchLiveStats());
    } catch (err) {
        socket.emit('error', err.message);
    }

    // تحديث دوري كل 5 ثواني
    const interval = setInterval(async () => {
        try {
            socket.emit('stats', await fetchLiveStats());
        } catch (err) {
            socket.emit('error', err.message);
        }
    }, 5000);

    socket.on('disconnect', () => {
        clearInterval(interval);
        console.log(`✗ Client disconnected: ${socket.id}`);
    });
});

// ── تشغيل السيرفر ────────────────────────────────────────────────
const PORT = 3001;
server.listen(PORT, () => {
    console.log(`╔══════════════════════════════════════════╗`);
    console.log(`║   JobScout Node.js Server Running        ║`);
    console.log(`║   http://localhost:${PORT}                   ║`);
    console.log(`╚══════════════════════════════════════════╝`);
});