# Tiptap WhatsApp Bot

A powerful WhatsApp bot system built with Baileys for the Tipta business management platform.

## Features

### 🤖 Core Bot Features
- **Multi-entity Support**: Handles businesses, workers, and tables
- **QR Code Integration**: Automatic QR code generation and scanning
- **Interactive Menus**: Beautiful emoji-rich menus for restaurants and salons
- **Session Management**: Persistent user sessions with context
- **Real-time Communication**: Instant responses and status updates

### 📱 WhatsApp Functionality
- **Business Interactions**: View menu, rate service, make orders, tip staff
- **Worker Support**: Individual worker codes, tips tracking, customer feedback
- **Table Service**: Table-specific QR codes, call waiter functionality
- **Multi-language Support**: English, Swahili, French (extensible)

### 🔧 Technical Features
- **Laravel Integration**: Seamless API communication with Tipta backend
- **API-First Architecture**: Business data comes from the Laravel web app APIs
- **Local Session Storage**: No database required for bot sessions
- **Error Handling**: Comprehensive error handling and logging
- **Health Monitoring**: Health checks and status endpoints
- **Graceful Shutdown**: Clean connection management

## Installation

1. **Install Dependencies**
   ```bash
   cd TIPTAP_BOT
   npm install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

3. **Start the Bot**
   ```bash
   # Development
   npm run dev
   
   # Production
   npm start
   ```

## Environment Variables

```env
# Laravel Application URL
LARAVEL_URL=http://localhost:8000

# WhatsApp Bot Configuration
BOT_SECRET_KEY=your-secret-key-here

# Local Session Storage
BOT_SESSION_FILE=storage/sessions.json

# API Configuration
API_TIMEOUT=30000
API_RETRIES=3

# Logging
LOG_LEVEL=info
LOG_FILE=logs/bot.log
```

## How It Works

- The bot links to WhatsApp by showing a QR code in the terminal on first start.
- After you scan and link the device, Baileys stores the WhatsApp auth files inside `auth/`.
- Customer, business, worker, table, order, payment, tip, and feedback data are fetched from the Laravel web app APIs.
- The bot only stores lightweight conversation session state locally in `storage/sessions.json`.

## API Endpoints

### Health & Status
- `GET /health` - Service health check
- `GET /bot/status` - Bot connection status

### Bot API (Laravel Integration)
The bot communicates with your Laravel application through these endpoints:

#### Business APIs
- `GET /api/bot/business/{code}` - Get business by code
- `GET /api/bot/business/{id}/menu` - Get business menu
- `GET /api/bot/business/{id}/tables` - Get business tables
- `GET /api/bot/business/{id}/workers` - Get business workers

#### Worker APIs
- `GET /api/bot/worker/{code}` - Get worker by code
- `GET /api/bot/worker/{id}/tips` - Get worker tips
- `GET /api/bot/worker/{id}/feedbacks` - Get worker feedbacks
- `GET /api/bot/worker/{id}/customers` - Get worker customers served

#### Table APIs
- `GET /api/bot/table/{code}` - Get table by code

#### Order & Payment APIs
- `POST /api/bot/orders` - Create order
- `GET /api/bot/orders/{id}` - Get order status
- `POST /api/bot/payments/initiate` - Initiate payment
- `GET /api/bot/payments/{id}/status` - Check payment status

#### Service APIs
- `POST /api/bot/feedbacks` - Submit feedback
- `POST /api/bot/call-waiter` - Call waiter
- `POST /api/bot/support` - Create support ticket

## QR Code System

### Business QR Codes
- Generated automatically when business is created
- Contains unique 6-10 character code
- Links to main business menu

### Worker QR Codes
- Generated for each worker (waiter/stylist)
- Contains worker-specific code
- Links to worker-specific interactions

### Table QR Codes
- Generated for each table/service station
- Contains table-specific code
- Links to table-specific service menu

## WhatsApp Menu Flow

### Main Menu
```
━━━━━━━━🏠✨━━━━━━━━
👋 Welcome to *BUSINESS_NAME*
Choose service:
_Type 0 anytime to go back here._
🍽️ MAIN SERVICES
1️⃣🍽️ View Our Menu
2️⃣⭐ Rate Service
3️⃣💳 Make Order
4️⃣💵 Tip
5️⃣🔔 Call Staff
6️⃣📞 Customer Support
7️⃣🌐 Change language
8️⃣❌ Exit
━━━━━━━━━━━━━━━━
✅ReplyNumberToChoose
```

### Session Types
1. **Business Session**: General business interactions
2. **Worker Session**: Worker-specific interactions (tips, ratings)
3. **Table Session**: Table-specific service (call waiter, order)

## Logging

The bot uses Winston for comprehensive logging:

- **Console**: Real-time output
- **Error Log**: `logs/error.log` - Error events only
- **Bot Log**: `logs/bot.log` - All bot activities

## Security

- **API Authentication**: Secret key and HMAC signatures
- **Request Validation**: All API requests are validated
- **Session Management**: Secure session handling
- **Error Handling**: No sensitive data exposure

## Development

### Project Structure
```
TIPTAP_BOT/
├── src/
│   ├── models/
│   │   └── WhatsAppSession.js   # Local session storage model
│   ├── services/
│   │   ├── LaravelApiService.js # Laravel API client
│   │   └── WhatsAppBotService.js # Core bot logic
│   └── index.js                 # Application entry point
├── auth/                        # WhatsApp auth files (auto-generated)
├── logs/                        # Log files
├── storage/                     # Local bot session storage
├── package.json
├── .env.example
└── README.md
```

### Adding New Features
1. Add API endpoints in `LaravelApiService.js`
2. Implement bot logic in `WhatsAppBotService.js`
3. Update session schema if needed
4. Add tests for new functionality

## Troubleshooting

### Common Issues
1. **QR Code Not Scanning**: Ensure WhatsApp Web is not active elsewhere
2. **Connection Issues**: Check network and restart bot
3. **API Errors**: Verify Laravel application is running and accessible
4. **Auth Not Working**: Delete the `auth/` folder only if you want to relink the WhatsApp device from scratch

### Health Checks
```bash
# Check bot status
curl http://localhost:3000/bot/status

# Check service health
curl http://localhost:3000/health
```

## Deployment

### Production Setup
1. Use PM2 for process management
2. Configure environment variables
3. Set up proper logging rotation
4. Monitor bot health and restart if needed

### PM2 Configuration
```json
{
  "name": "tiptap-bot",
  "script": "src/index.js",
  "instances": 1,
  "autorestart": true,
  "watch": false,
  "max_memory_restart": "1G",
  "env": {
    "NODE_ENV": "production"
  }
}
```

## Support

For issues and questions:
1. Check logs for error details
2. Verify Laravel API endpoints
3. Test WhatsApp connection
4. Review environment configuration

---

**Tipta WhatsApp Bot** - Connecting businesses with customers through WhatsApp 🚀
