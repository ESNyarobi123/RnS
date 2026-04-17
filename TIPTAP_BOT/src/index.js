require('dotenv').config();
const express = require('express');
const cors = require('cors');
const path = require('path');
const WhatsAppBotService = require('./services/WhatsAppBotService');

const app = express();
const HOST = process.env.HOST || '127.0.0.1';
const PORT = process.env.PORT || 3000;
const SESSION_FILE = path.resolve(__dirname, '..', process.env.BOT_SESSION_FILE || 'storage/sessions.json');

// Middleware
app.use(cors());
app.use(express.json());

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({ 
    status: 'OK', 
    timestamp: new Date().toISOString(),
    bot: WhatsAppBotService.isConnected ? 'connected' : 'disconnected'
  });
});

// Bot status endpoint
app.get('/bot/status', (req, res) => {
  res.json({
    isConnected: WhatsAppBotService.isConnected,
    reconnectAttempts: WhatsAppBotService.reconnectAttempts
  });
});

// Initialize database and bot
async function startServer() {
  try {
    console.log(`ℹ️ WhatsApp sessions will use local file storage: ${SESSION_FILE}`);

    // Initialize WhatsApp bot
    await WhatsAppBotService.initialize();
    console.log('✅ WhatsApp bot initialized');

    // Start Express server
    app.listen(PORT, HOST, () => {
      console.log(`🚀 Server running on http://${HOST}:${PORT}`);
      console.log(`📊 Health check: http://${HOST}:${PORT}/health`);
      console.log(`🤖 Bot status: http://${HOST}:${PORT}/bot/status`);
    });

  } catch (error) {
    console.error('❌ Failed to start server:', error);
    process.exit(1);
  }
}

// Graceful shutdown
process.on('SIGINT', async () => {
  console.log('\n🔄 Shutting down gracefully...');
  
  if (WhatsAppBotService.sock) {
    await WhatsAppBotService.sock.logout();
  }
  
  process.exit(0);
});

process.on('SIGTERM', async () => {
  console.log('\n🔄 Shutting down gracefully...');
  
  if (WhatsAppBotService.sock) {
    await WhatsAppBotService.sock.logout();
  }
  
  process.exit(0);
});

// Handle uncaught exceptions
process.on('uncaughtException', (error) => {
  console.error('❌ Uncaught Exception:', error);
  process.exit(1);
});

process.on('unhandledRejection', (reason, promise) => {
  console.error('❌ Unhandled Rejection at:', promise, 'reason:', reason);
  process.exit(1);
});

// Start the server
startServer();
