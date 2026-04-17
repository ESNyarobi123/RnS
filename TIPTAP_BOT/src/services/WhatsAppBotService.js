const { default: makeWASocket, useMultiFileAuthState, DisconnectReason, Browsers } = require('@whiskeysockets/baileys');
const { Boom } = require('@hapi/boom');
const winston = require('winston');
const QRCode = require('qrcode');
const path = require('path');
const fs = require('fs');
const os = require('os');
const LaravelApiService = require('./LaravelApiService');
const WhatsAppSession = require('../models/WhatsAppSession');

const baseLogger = winston.createLogger({
  level: process.env.LOG_LEVEL || 'info',
  format: winston.format.combine(
    winston.format.timestamp(),
    winston.format.errors({ stack: true }),
    winston.format.json()
  ),
  transports: [
    new winston.transports.Console(),
    new winston.transports.File({ filename: 'logs/error.log', level: 'error' }),
    new winston.transports.File({ filename: 'logs/bot.log' })
  ]
});

const loggerLevelMap = {
  trace: 'debug',
  debug: 'debug',
  info: 'info',
  warn: 'warn',
  error: 'error',
  fatal: 'error',
};

function normalizeLogValue(value) {
  if (value instanceof Error) {
    return {
      name: value.name,
      message: value.message,
      stack: value.stack,
    };
  }

  if (Array.isArray(value)) {
    return value.map(normalizeLogValue);
  }

  if (value && typeof value === 'object') {
    return Object.fromEntries(
      Object.entries(value).map(([key, entry]) => [key, normalizeLogValue(entry)])
    );
  }

  return value;
}

function createBaileysLogger(bindings = {}) {
  const writeLog = (level, ...args) => {
    let metadata = { ...bindings };
    const messageParts = [];

    args.forEach((arg) => {
      if (arg === undefined || arg === null) {
        return;
      }

      if (typeof arg === 'string') {
        messageParts.push(arg);
        return;
      }

      if (arg instanceof Error) {
        metadata.error = normalizeLogValue(arg);
        return;
      }

      if (typeof arg === 'object') {
        metadata = {
          ...metadata,
          ...normalizeLogValue(arg),
        };
        return;
      }

      messageParts.push(String(arg));
    });

    baseLogger.log({
      level: loggerLevelMap[level] || 'info',
      message: messageParts.join(' ') || level,
      ...metadata,
    });
  };

  return {
    level: baseLogger.level,
    trace: (...args) => { writeLog('trace', ...args); },
    debug: (...args) => { writeLog('debug', ...args); },
    info: (...args) => { writeLog('info', ...args); },
    warn: (...args) => { writeLog('warn', ...args); },
    error: (...args) => { writeLog('error', ...args); },
    fatal: (...args) => { writeLog('fatal', ...args); },
    child: (childBindings = {}) => createBaileysLogger({
      ...bindings,
      ...normalizeLogValue(childBindings),
    }),
  };
}

const logger = createBaileysLogger();

class WhatsAppBotService {
  constructor() {
    this.sock = null;
    this.isConnected = false;
    this.reconnectAttempts = 0;
    this.maxReconnectAttempts = 5;
    this.saveCreds = null;
    this.paymentPollers = new Map();
    this.sessions = new Map(); // Store active sessions
    this.qrReceivedForCurrentAttempt = false;
    this.pairingCodeRequestedForCurrentAttempt = false;
    this.authDir = path.join(__dirname, '../../auth');
    this.hasRegisteredSession = false;
  }

  async initialize() {
    try {
      this.qrReceivedForCurrentAttempt = false;
      this.pairingCodeRequestedForCurrentAttempt = false;

      // Ensure auth directory exists
      if (!fs.existsSync(this.authDir)) {
        fs.mkdirSync(this.authDir, { recursive: true });
      }

      const { state, saveCreds } = await useMultiFileAuthState(this.authDir);
      this.saveCreds = saveCreds;
      this.hasRegisteredSession = Boolean(state.creds?.registered);
      const browserProfile = this.resolveBrowserProfile();

      this.sock = makeWASocket({
        auth: state,
        logger: logger,
        browser: browserProfile,
        connectTimeoutMs: 60000,
        qrTimeout: 0,
        defaultQueryTimeoutMs: undefined,
      });

      this.setupEventListeners();
      console.log(`ℹ️ Using Baileys browser profile: ${browserProfile.join(' / ')}`);
      logger.info('WhatsApp bot initialized');
    } catch (error) {
      logger.error('Failed to initialize WhatsApp bot:', error);
      throw error;
    }
  }

  setupEventListeners() {
    this.sock.ev.on('connection.update', async (update) => {
      const { connection, lastDisconnect, qr } = update;

      if (connection === 'connecting') {
        await this.maybeRequestPairingCode();
      }

      if (qr) {
        this.qrReceivedForCurrentAttempt = true;
        logger.info('QR Code received, please scan with WhatsApp');
        console.log('\n📱 Scan this WhatsApp QR code in your terminal to link the bot device:\n');

        try {
          const terminalQr = await QRCode.toString(qr, {
            type: 'terminal',
            small: true,
          });

          console.log(terminalQr);
        } catch (error) {
          logger.error('Failed to render QR code in terminal:', error);
          console.log('⚠️ QR was received but could not be rendered in terminal.');
        }
      }

      if (connection === 'close') {
        const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
        logger.info('Connection closed:', lastDisconnect?.error);

        if (!this.qrReceivedForCurrentAttempt) {
          console.log('\n⚠️ WhatsApp closed the registration before sending a QR code.');
          console.log('This usually means the current Baileys <-> WhatsApp pairing flow failed upstream.');
          console.log('I have kept the bot ready for QR mode, but this specific connection attempt did not receive a QR from WhatsApp.\n');

          if (!this.hasRegisteredSession) {
            this.clearPendingAuthState();
          }
        }

        if (shouldReconnect && this.reconnectAttempts < this.maxReconnectAttempts) {
          this.reconnectAttempts++;
          logger.info(`Reconnecting... Attempt ${this.reconnectAttempts}`);
          setTimeout(() => this.initialize(), 5000);
        } else {
          logger.info('Logged out or max reconnection attempts reached');
          this.isConnected = false;
        }
      } else if (connection === 'open') {
        this.isConnected = true;
        this.reconnectAttempts = 0;
        logger.info('WhatsApp connection opened');
      }
    });

    this.sock.ev.on('creds.update', this.saveCreds);

    this.sock.ev.on('messages.upsert', async (m) => {
      const message = m.messages[0];
      if (!message.message) return;

      await this.handleMessage(message);
    });
  }

  resolveBrowserProfile() {
    const forcedProfile = process.env.BOT_BROWSER_PROFILE;

    if (forcedProfile === 'ubuntu') {
      return Browsers.ubuntu('Chrome');
    }

    if (forcedProfile === 'windows') {
      return Browsers.windows('Chrome');
    }

    if (forcedProfile === 'macos') {
      return Browsers.macOS('Chrome');
    }

    const candidates = this.getBrowserCandidates();
    const index = Math.min(this.reconnectAttempts, candidates.length - 1);

    return candidates[index];
  }

  getBrowserCandidates() {
    if (os.platform() === 'darwin') {
      return [
        Browsers.macOS('Chrome'),
        Browsers.windows('Chrome'),
        Browsers.ubuntu('Chrome'),
      ];
    }

    if (os.platform() === 'win32') {
      return [
        Browsers.windows('Chrome'),
        Browsers.ubuntu('Chrome'),
        Browsers.macOS('Chrome'),
      ];
    }

    return [
      Browsers.ubuntu('Chrome'),
      Browsers.windows('Chrome'),
      Browsers.macOS('Chrome'),
    ];
  }

  clearPendingAuthState() {
    try {
      if (!fs.existsSync(this.authDir)) {
        return;
      }

      fs.rmSync(this.authDir, { recursive: true, force: true });
      fs.mkdirSync(this.authDir, { recursive: true });
      console.log('ℹ️ Cleared incomplete WhatsApp auth state before retrying.');
    } catch (error) {
      logger.warn({ error }, 'Failed to clear pending auth state');
    }
  }

  async maybeRequestPairingCode() {
    const phoneNumber = this.getPairingPhoneNumber();

    if (!phoneNumber || this.hasRegisteredSession || this.pairingCodeRequestedForCurrentAttempt || !this.sock?.requestPairingCode) {
      return;
    }

    this.pairingCodeRequestedForCurrentAttempt = true;

    try {
      const pairingCode = await this.sock.requestPairingCode(phoneNumber);

      console.log('\n🔐 WhatsApp pairing code generated.\n');
      console.log(`Link with phone number: ${phoneNumber}`);
      console.log(`Pairing code: ${pairingCode}\n`);
      console.log('On your phone go to WhatsApp -> Linked devices -> Link with phone number instead.\n');
    } catch (error) {
      logger.warn({ error }, 'Failed to request pairing code');
      console.log('\n⚠️ Pairing code request failed for this attempt. The bot will keep trying QR/login fallback.\n');
    }
  }

  getPairingPhoneNumber() {
    const rawPhoneNumber = process.env.BOT_PAIRING_PHONE?.trim();

    if (!rawPhoneNumber) {
      return null;
    }

    return rawPhoneNumber.replace(/\D/g, '');
  }

  async handleMessage(message) {
    try {
      if (message.key.fromMe) {
        return;
      }

      const phoneNumber = message.key.remoteJid;
      const messageText = this.extractMessageText(message).trim();

      if (messageText === '') {
        return;
      }

      logger.info(`Received message from ${phoneNumber}: ${messageText}`);

      // Get or create session
      let session = await WhatsAppSession.findOne({
        phoneNumber,
        isActive: true
      }).sort({ lastActivity: -1 });

      if (!session) {
        await this.handleNewUser(phoneNumber, messageText);
        return;
      }

      session.lastActivity = new Date();
      await session.save();

      await this.processMessage(session, messageText, phoneNumber);
    } catch (error) {
      logger.error('Error handling message:', error);
    }
  }

  async handleNewUser(phoneNumber, messageText) {
    const codeMatch = messageText.toUpperCase().match(/([A-Z0-9-]{6,20})/);

    if (codeMatch) {
      const code = codeMatch[1].toUpperCase();

      try {
        let entity = await this.safeLookup(() => LaravelApiService.getBusinessByCode(code));
        let sessionType = 'business';

        if (!entity) {
          entity = await this.safeLookup(() => LaravelApiService.getWorkerByCode(code));
          sessionType = 'worker';
        }

        if (!entity) {
          entity = await this.safeLookup(() => LaravelApiService.getTableByCode(code));
          sessionType = 'table';
        }

        if (entity) {
          const session = new WhatsAppSession({
            phoneNumber,
            sessionId: `${phoneNumber}_${Date.now()}`,
            businessId: sessionType === 'business' ? entity.id : entity.business_id,
            workerId: sessionType === 'worker' ? entity.id : null,
            tableId: sessionType === 'table' ? entity.id : null,
            sessionType,
            isActive: true,
            lastActivity: new Date(),
            metadata: {
              code,
              language: 'en',
              businessName: entity.business_name || entity.name,
              businessType: entity.business_type || entity.type,
              workerName: sessionType === 'worker' ? entity.name : null,
              tableName: sessionType === 'table' ? (entity.display_name || entity.name) : null,
              workerUserId: entity.worker_id || null,
              menuItems: [],
              orderDraft: { items: [] },
            }
          });

          await session.save();

          await this.sendWelcomeMessage(phoneNumber, session);
        } else {
          await this.sendMessage(phoneNumber, '❌ That code was not recognized. Please scan the QR again or ask the team for the correct code.');
        }
      } catch (error) {
        logger.error('Error validating code:', error);
        await this.sendMessage(phoneNumber, '❌ We could not validate that code right now. Please try again in a moment.');
      }
    } else {
      await this.sendMessage(phoneNumber, '👋 Welcome to Tipta WhatsApp Assistant!\n\nPlease scan a QR code or send your business, worker, or table code to get started.');
    }
  }

  async sendWelcomeMessage(phoneNumber, session) {
    await this.sendMessage(phoneNumber, this.getMainMenuText(session));
  }

  getMainMenuOptions(session) {
    const isWorkerSession = session.sessionType === 'worker';

    if (isWorkerSession) {
      return [
        { key: '1', labelEn: 'View Menu', labelSw: 'Angalia Menu' },
        { key: '2', labelEn: 'Rate Stylist', labelSw: 'Kadiria Stylist' },
        { key: '3', labelEn: 'Place Order', labelSw: 'Weka Oda' },
        { key: '4', labelEn: 'Pay Bill', labelSw: 'Lipa Bili' },
        { key: '5', labelEn: 'Tip Stylist', labelSw: 'Tuma Tip' },
        { key: '6', labelEn: 'Customer Support', labelSw: 'Msaada kwa Mteja' },
        { key: '7', labelEn: 'Change Language', labelSw: 'Badili Lugha' },
        { key: '8', labelEn: 'Exit', labelSw: 'Toka' },
      ];
    }

    return [
      { key: '1', labelEn: 'View Menu', labelSw: 'Angalia Menu' },
      { key: '2', labelEn: 'Rate Service', labelSw: 'Kadiria Huduma' },
      { key: '3', labelEn: 'Place Order', labelSw: 'Weka Oda' },
      { key: '4', labelEn: 'Pay Bill', labelSw: 'Lipa Bili' },
      { key: '5', labelEn: 'Call Staff', labelSw: 'Mwite Mhudumu' },
      { key: '6', labelEn: 'Customer Support', labelSw: 'Msaada kwa Mteja' },
      { key: '7', labelEn: 'Change Language', labelSw: 'Badili Lugha' },
      { key: '8', labelEn: 'Exit', labelSw: 'Toka' },
    ];
  }

  getMainMenuText(session) {
    const businessName = session.metadata?.businessName || 'Tipta';
    const businessType = session.metadata?.businessType || 'restaurant';
    const workerName = session.metadata?.workerName || null;
    const tableName = session.metadata?.tableName || null;
    const language = session.metadata?.language || 'en';
    const isSalon = businessType === 'salon';
    const isWorkerSession = session.sessionType === 'worker';
    const isTableSession = session.sessionType === 'table';
    const menuLabel = isSalon ? 'View Services' : 'View Our Menu';
    const menuLabelSw = isSalon ? 'Angalia Huduma' : 'Angalia Menu';
    const orderLabel = isSalon ? 'Request Service' : 'Place Order';
    const orderLabelSw = isSalon ? 'Omba Huduma' : 'Weka Oda';
    const payLabel = isSalon ? 'Pay for Service' : 'Pay Bill';
    const payLabelSw = isSalon ? 'Lipia Huduma' : 'Lipa Bili';
    const callLabel = isSalon ? 'Call Stylist' : 'Call Waiter';
    const callLabelSw = isSalon ? 'Mwite Stylist' : 'Mwite Mhudumu';
    const rateLabel = isWorkerSession
      ? `Rate ${workerName}`
      : isTableSession
        ? `Rate your experience at ${businessName}`
        : `Rate ${businessName}`;
    const rateLabelSw = isWorkerSession
      ? `Kadiria ${workerName}`
      : isTableSession
        ? `Kadiria huduma ya ${businessName}`
        : `Kadiria ${businessName}`;
    const tipLabel = workerName ? `Tip ${workerName}` : 'Tip Staff';
    const tipLabelSw = workerName ? `Tuma Tip kwa ${workerName}` : 'Tuma Tip';
    const locationText = tableName ? ` (${tableName})` : workerName ? ` (${workerName})` : '';
    const options = this.getMainMenuOptions(session);

    const renderedOptionsSw = options.map((option) => {
      const label = option.key === '1'
        ? menuLabelSw
        : option.key === '2'
          ? rateLabelSw
          : option.key === '3'
            ? orderLabelSw
            : option.key === '4'
              ? payLabelSw
              : option.key === '5'
                ? isWorkerSession ? tipLabelSw : callLabelSw
                : option.labelSw;

      return `${option.key}️⃣ ${label}`;
    }).join('\n');

    const renderedOptionsEn = options.map((option) => {
      const label = option.key === '1'
        ? menuLabel
        : option.key === '2'
          ? rateLabel
          : option.key === '3'
            ? orderLabel
            : option.key === '4'
              ? payLabel
              : option.key === '5'
                ? isWorkerSession ? tipLabel : callLabel
                : option.labelEn;

      return `${option.key}️⃣ ${label}`;
    }).join('\n');

    if (language === 'sw') {
      return `━━━━━━━━🏠✨━━━━━━━━
👋 Karibu *${businessName}*${locationText}
Chagua huduma:
_Andika 0 wakati wowote kurudi hapa._
${isSalon ? '💇 HUDUMA KUU' : '🍽️ HUDUMA KUU'}
${renderedOptionsSw}
━━━━━━━━━━━━━━━━
✅ Jibu na namba ya chaguo lako`;
    }

    return `━━━━━━━━🏠✨━━━━━━━━
👋 Welcome to *${businessName}*${locationText}
Choose a service:
_Type 0 anytime to come back here._
${isSalon ? '💇 MAIN SERVICES' : '🍽️ MAIN SERVICES'}
${renderedOptionsEn}
━━━━━━━━━━━━━━━━
✅ Reply with the number you choose`;
  }

  async processMessage(session, messageText, phoneNumber) {
    const choice = messageText.trim();

    // Handle menu navigation
    if (choice === '0') {
      await this.showMainMenu(session, phoneNumber);
      return;
    }

    switch (session.currentMenu) {
      case 'main':
        await this.handleMainMenu(session, choice, phoneNumber);
        break;
      case 'menu':
        await this.handleMenuNavigation(session, choice, phoneNumber);
        break;
      case 'order':
        await this.handleOrderProcess(session, choice, phoneNumber);
        break;
      case 'rate_score':
        await this.handleRatingScore(session, choice, phoneNumber);
        break;
      case 'rate_comment':
        await this.handleRatingComment(session, choice, phoneNumber);
        break;
      case 'tip_amount':
        await this.handleTipAmount(session, choice, phoneNumber);
        break;
      case 'tip_phone':
        await this.handleTipPhone(session, choice, phoneNumber);
        break;
      case 'pay_phone':
        await this.handleOrderPaymentPhone(session, choice, phoneNumber);
        break;
      case 'pay_amount':
        await this.handleOrderPaymentAmount(session, choice, phoneNumber);
        break;
      case 'support':
        await this.handleSupportRequest(session, choice, phoneNumber);
        break;
      case 'language':
        await this.handleLanguageSelection(session, choice, phoneNumber);
        break;
      default:
        await this.showMainMenu(session, phoneNumber);
    }
  }

  async handleMainMenu(session, choice, phoneNumber) {
    switch (choice) {
      case '1': // View Menu
        await this.showMenu(session, phoneNumber);
        break;
      case '2': // Rate Service
        await this.initiateRating(session, phoneNumber);
        break;
      case '3': // Make Order
        await this.startOrderProcess(session, phoneNumber);
        break;
      case '4': // Pay Bill
        await this.initiateOrderPayment(session, phoneNumber);
        break;
      case '5': // Tip or Call Staff
        if (session.sessionType === 'worker') {
          await this.initiateTip(session, phoneNumber);
        } else {
          await this.callWaiter(session, phoneNumber);
        }
        break;
      case '6': // Customer Support
        await this.connectToSupport(session, phoneNumber);
        break;
      case '7': // Change Language
        await this.changeLanguage(session, phoneNumber);
        break;
      case '8': // Exit
        await this.exitSession(session, phoneNumber);
        break;
      default:
        await this.sendMessage(phoneNumber, '❌ Invalid option. Please choose a number from the menu.');
    }
  }

  async showMenu(session, phoneNumber) {
    try {
      const menu = await LaravelApiService.getBusinessMenu(session.businessId);

      if (menu && menu.items && menu.items.length > 0) {
        const isSalon = (session.metadata.businessType || menu.business_type) === 'salon';
        let menuMessage = `${isSalon ? '💇' : '🍽️'} *${menu.business_name} ${isSalon ? 'Services' : 'Menu'}*\n\n`;

        menu.items.forEach((item, index) => {
          menuMessage += `${index + 1}. ${item.name} - ${Number(item.price).toLocaleString()} TZS\n`;
          if (item.description) {
            menuMessage += `   ${item.description}\n`;
          }
        });

        menuMessage += `\n💡 Reply with item number or name to add it.`;
        menuMessage += `\nExamples: 1, 2 x2, view, done, cancel, 0`;

        session.metadata = {
          ...session.metadata,
          businessType: session.metadata.businessType || menu.business_type,
          menuItems: menu.items,
          orderDraft: session.metadata.orderDraft || { items: [] },
        };
        session.currentMenu = 'menu';
        session.markModified('metadata');
        await session.save();

        if (menu.menu_image_url) {
          await this.sock.sendMessage(phoneNumber, {
            image: { url: menu.menu_image_url },
            caption: `${isSalon ? '💇' : '🍽️'} ${menu.business_name}`,
          });
        }

        await this.sendMessage(phoneNumber, menuMessage);
      } else {
        await this.sendMessage(phoneNumber, '❌ The menu is not available right now. Please try again shortly.');
      }
    } catch (error) {
      logger.error('Error fetching menu:', error);
      await this.sendMessage(phoneNumber, '❌ We could not load the menu right now. Please try again later.');
    }
  }

  extractMessageText(message) {
    return message.message.conversation ||
      message.message.extendedTextMessage?.text ||
      message.message.imageMessage?.caption ||
      message.message.videoMessage?.caption ||
      message.message.buttonsResponseMessage?.selectedButtonId ||
      message.message.listResponseMessage?.singleSelectReply?.selectedRowId ||
      '';
  }

  async showMainMenu(session, phoneNumber) {
    session.currentMenu = 'main';
    await session.save();
    await this.sendMessage(phoneNumber, this.getMainMenuText(session));
  }

  getOrderDraft(session) {
    return session.metadata?.orderDraft || { items: [] };
  }

  sanitizePhoneNumber(phoneNumber) {
    return phoneNumber.replace('@s.whatsapp.net', '').replace(/\D+/g, '');
  }

  formatCurrency(amount) {
    return `${Number(amount).toLocaleString()} TZS`;
  }

  parseMenuSelection(choice, menuItems) {
    const normalizedChoice = choice.trim();
    const quantityMatch = normalizedChoice.match(/^(.+?)\s*x\s*(\d+)$/i);
    const lookupValue = quantityMatch ? quantityMatch[1].trim() : normalizedChoice;
    const quantity = quantityMatch ? Number.parseInt(quantityMatch[2], 10) : 1;
    const index = Number.parseInt(lookupValue, 10);

    let item = null;

    if (!Number.isNaN(index) && index >= 1 && index <= menuItems.length) {
      item = menuItems[index - 1];
    } else {
      const loweredLookup = lookupValue.toLowerCase();
      item = menuItems.find((menuItem) => menuItem.name.toLowerCase() === loweredLookup)
        || menuItems.find((menuItem) => menuItem.name.toLowerCase().includes(loweredLookup));
    }

    if (!item || quantity < 1) {
      return null;
    }

    return { item, quantity };
  }

  async addItemToOrderDraft(session, selectedItem, quantity) {
    const orderDraft = this.getOrderDraft(session);
    const items = Array.isArray(orderDraft.items) ? orderDraft.items : [];
    const existingItem = items.find((item) => item.product_id === selectedItem.id);

    if (existingItem) {
      existingItem.quantity += quantity;
    } else {
      items.push({
        product_id: selectedItem.id,
        name: selectedItem.name,
        quantity,
        unit_price: selectedItem.price,
      });
    }

    session.metadata = {
      ...session.metadata,
      orderDraft: { items },
    };
    session.markModified('metadata');
    await session.save();
  }

  async clearOrderDraft(session) {
    session.metadata = {
      ...session.metadata,
      orderDraft: { items: [] },
    };
    session.markModified('metadata');
    await session.save();
  }

  buildOrderSummary(session) {
    const orderDraft = this.getOrderDraft(session);
    const items = Array.isArray(orderDraft.items) ? orderDraft.items : [];

    if (items.length === 0) {
      return '🛒 Your order draft is empty.';
    }

    const lines = items.map((item, index) => {
      const lineTotal = Number(item.unit_price) * Number(item.quantity);
      return `${index + 1}. ${item.name} x${item.quantity} - ${this.formatCurrency(lineTotal)}`;
    });
    const total = items.reduce((sum, item) => sum + (Number(item.unit_price) * Number(item.quantity)), 0);

    return `🧾 *Order Summary*\n\n${lines.join('\n')}\n\nTotal: ${this.formatCurrency(total)}`;
  }

  async createOrderFromDraft(session, phoneNumber) {
    const orderDraft = this.getOrderDraft(session);
    const items = Array.isArray(orderDraft.items) ? orderDraft.items : [];

    if (items.length === 0) {
      await this.sendMessage(phoneNumber, '❌ Your order is empty. Please add at least one item first.');
      session.currentMenu = 'menu';
      await session.save();
      return;
    }

    const payload = {
      business_id: session.businessId,
      worker_id: session.workerId,
      customer_phone: this.sanitizePhoneNumber(phoneNumber),
      items: items.map((item) => ({
        product_id: item.product_id,
        quantity: item.quantity,
      })),
    };

    const order = await LaravelApiService.createOrder(payload);
    await this.clearOrderDraft(session);
    session.metadata = {
      ...session.metadata,
      lastOrderId: order.order_id,
      lastOrderNumber: order.order_number,
      lastOrderTotal: order.total,
      lastOrderRemainingAmount: order.total,
    };
    session.currentMenu = 'main';
    session.markModified('metadata');
    await session.save();

    await this.sendMessage(phoneNumber, `✅ Order received successfully.\n\nOrder No: *${order.order_number}*\nTotal: *${this.formatCurrency(order.total)}*\nStatus: *${order.status}*`);
    await this.sendMessage(phoneNumber, '💳 If you want to pay now, reply with *4* from the main menu.');
    await this.sendMessage(phoneNumber, this.getMainMenuText(session));
  }

  normalizeCustomerPhoneInput(value) {
    let digits = value.replace(/\D+/g, '');

    if (digits.startsWith('0')) {
      digits = `255${digits.slice(1)}`;
    }

    if (!digits.startsWith('255') && digits.length === 9) {
      digits = `255${digits}`;
    }

    return digits;
  }

  isValidCustomerPhone(phoneNumber) {
    return /^255\d{9}$/.test(phoneNumber);
  }

  async startPaymentFlow(session, phoneNumber, currentMenu, prompt, metadataUpdates = {}) {
    session.currentMenu = currentMenu;
    session.metadata = {
      ...session.metadata,
      ...metadataUpdates,
    };
    session.markModified('metadata');
    await session.save();
    await this.sendMessage(phoneNumber, prompt);
  }

  async initiateMobileMoneyPayment(session, phoneNumber, payload, successMessage) {
    const payment = await LaravelApiService.initiatePayment(payload);

    session.currentMenu = 'main';
    session.metadata = {
      ...session.metadata,
      pendingPaymentId: payment.payment_id,
      pendingPaymentType: payload.type,
      pendingPaymentPhone: payload.customer_phone,
    };
    session.markModified('metadata');
    await session.save();

    await this.sendMessage(phoneNumber, successMessage(payment));
    await this.sendMessage(phoneNumber, '⏳ I am now checking the payment status. Please authorize the push on your phone.');
    this.startPaymentStatusPolling(session.id, phoneNumber, payment.payment_id);
  }

  startPaymentStatusPolling(sessionId, phoneNumber, paymentId, attempt = 1) {
    const pollKey = `${phoneNumber}:${paymentId}`;

    if (attempt === 1 && this.paymentPollers.has(pollKey)) {
      clearTimeout(this.paymentPollers.get(pollKey));
    }

    const timer = setTimeout(async () => {
      try {
        const status = await LaravelApiService.checkPaymentStatus(paymentId);
        const session = await WhatsAppSession.findById(sessionId);

        if (status.status === 'completed') {
          this.paymentPollers.delete(pollKey);
          await this.handleCompletedPayment(session, phoneNumber, status);
          return;
        }

        if (status.status === 'failed') {
          this.paymentPollers.delete(pollKey);
          if (session) {
            session.metadata = {
              ...session.metadata,
              pendingPaymentId: null,
              pendingPaymentType: null,
            };
            session.markModified('metadata');
            await session.save();
          }
          await this.sendMessage(phoneNumber, '❌ Payment failed or was cancelled. You can try again from the main menu.');
          return;
        }

        if (attempt >= 12) {
          this.paymentPollers.delete(pollKey);
          await this.sendMessage(phoneNumber, '⌛ Payment is still pending. Reply after authorizing the push and I will continue checking when you try again.');
          return;
        }

        this.startPaymentStatusPolling(sessionId, phoneNumber, paymentId, attempt + 1);
      } catch (error) {
        logger.error('Error polling payment status:', error);

        if (attempt < 12) {
          this.startPaymentStatusPolling(sessionId, phoneNumber, paymentId, attempt + 1);
        } else {
          this.paymentPollers.delete(pollKey);
          await this.sendMessage(phoneNumber, '❌ I could not confirm the payment status right now. Please try again shortly.');
        }
      }
    }, 15000);

    this.paymentPollers.set(pollKey, timer);
  }

  async handleCompletedPayment(session, phoneNumber, status) {
    if (!session) {
      await this.sendMessage(phoneNumber, '✅ Payment completed successfully.');
      return;
    }

    session.metadata = {
      ...session.metadata,
      pendingPaymentId: null,
      pendingPaymentType: null,
      lastOrderRemainingAmount: status.remaining_amount ?? session.metadata?.lastOrderRemainingAmount ?? null,
    };
    session.markModified('metadata');
    await session.save();

    if (status.purpose === 'tip') {
      await this.sendMessage(phoneNumber, `✅ Tip payment completed successfully.\nAmount: *${this.formatCurrency(status.amount)}*`);
      await this.sendMessage(phoneNumber, this.getMainMenuText(session));
      return;
    }

    if (status.purpose === 'order') {
      if ((status.remaining_amount ?? 0) > 0) {
        await this.sendMessage(phoneNumber, `✅ Payment received successfully.\nPaid: *${this.formatCurrency(status.amount)}*\nRemaining balance: *${this.formatCurrency(status.remaining_amount)}*`);
      } else {
        await this.sendMessage(phoneNumber, `✅ Payment completed successfully.\nPaid: *${this.formatCurrency(status.amount)}*\nYour bill is now fully paid.`);
      }

      await this.sendMessage(phoneNumber, this.getMainMenuText(session));
    }
  }

  async sendMessage(phoneNumber, message) {
    try {
      await this.sock.sendMessage(phoneNumber, { text: message });
      logger.info(`Message sent to ${phoneNumber}`);
    } catch (error) {
      logger.error('Error sending message:', error);
    }
  }

  async safeLookup(fn) {
    try {
      return await fn();
    } catch (error) {
      if (error.response?.status === 404) {
        return null;
      }

      throw error;
    }
  }

  async initiateRating(session, phoneNumber) {
    const subject = session.sessionType === 'worker' ? session.metadata.workerName : session.metadata.businessName;
    session.currentMenu = 'rate_score';
    await session.save();
    await this.sendMessage(phoneNumber, `⭐ Please rate *${subject}* from 1 to 5.\n\n1 - Poor\n2 - Fair\n3 - Good\n4 - Very Good\n5 - Excellent`);
  }

  async startOrderProcess(session, phoneNumber) {
    const hasMenuItems = Array.isArray(session.metadata?.menuItems) && session.metadata.menuItems.length > 0;

    if (!hasMenuItems) {
      await this.showMenu(session, phoneNumber);
      return;
    }

    session.currentMenu = 'menu';
    await session.save();
    await this.sendMessage(phoneNumber, `${this.buildOrderSummary(session)}\n\nReply with an item number or name to add more, type *view* to review your cart, or *done* to submit.`);
  }

  async initiateTip(session, phoneNumber) {
    if (!session.workerId) {
      await this.sendMessage(phoneNumber, '❌ Tip payments require a worker QR code. Please scan the worker QR you want to tip and try again.');
      return;
    }

    const subject = session.metadata.workerName || session.metadata.businessName;
    await this.startPaymentFlow(
      session,
      phoneNumber,
      'tip_phone',
      `💵 You are sending a tip for *${subject}*.\n\nPlease reply with the customer phone number to receive the payment push.\nExample: 255700000000`,
      {
        pendingPaymentType: 'tip',
        pendingPaymentPhone: null,
      }
    );
  }

  async initiateOrderPayment(session, phoneNumber) {
    const lastOrderId = session.metadata?.lastOrderId;
    const lastOrderNumber = session.metadata?.lastOrderNumber;
    const remainingAmount = Number(session.metadata?.lastOrderRemainingAmount ?? session.metadata?.lastOrderTotal ?? 0);

    if (!lastOrderId || remainingAmount <= 0) {
      await this.sendMessage(phoneNumber, '❌ I do not see an unpaid order in this session yet. Please place your order first, then choose Pay Bill.');
      return;
    }

    await this.startPaymentFlow(
      session,
      phoneNumber,
      'pay_phone',
      `💳 Paying order *${lastOrderNumber}*.\nRemaining balance: *${this.formatCurrency(remainingAmount)}*\n\nPlease reply with the phone number to receive the payment push.\nExample: 255700000000`,
      {
        pendingPaymentType: 'order',
        pendingPaymentPhone: null,
      }
    );
  }

  async callWaiter(session, phoneNumber) {
    try {
      await LaravelApiService.callWaiter({
        business_id: session.businessId,
        table_id: session.tableId,
        customer_phone: this.sanitizePhoneNumber(phoneNumber),
        customer_name: null,
        message: session.sessionType === 'table'
          ? `Customer needs help at ${session.metadata.tableName}.`
          : 'Customer requested help from WhatsApp.',
      });
      
      await this.sendMessage(phoneNumber, '🔔 Your request has been sent to the team. Someone will assist you shortly.');
    } catch (error) {
      await this.sendMessage(phoneNumber, '❌ We could not notify the team just now. Please try again.');
    }
  }

  async connectToSupport(session, phoneNumber) {
    session.currentMenu = 'support';
    await session.save();
    await this.sendMessage(phoneNumber, '📞 Customer support is ready.\n\nPlease type your issue in one message and our team will follow up.');
  }

  async changeLanguage(session, phoneNumber) {
    session.currentMenu = 'language';
    await session.save();
    await this.sendMessage(phoneNumber, '🌐 Choose your language:\n\n1. English\n2. Kiswahili');
  }

  async exitSession(session, phoneNumber) {
    session.isActive = false;
    await session.save();
    await this.sendMessage(phoneNumber, '👋 Thank you for using Tipta.\n\nScan another QR code any time to start again.');
  }

  async handleMenuNavigation(session, choice, phoneNumber) {
    const normalizedChoice = choice.trim().toLowerCase();
    const menuItems = Array.isArray(session.metadata?.menuItems) ? session.metadata.menuItems : [];

    if (normalizedChoice === 'view') {
      await this.sendMessage(phoneNumber, this.buildOrderSummary(session));
      return;
    }

    if (normalizedChoice === 'cancel') {
      await this.clearOrderDraft(session);
      await this.sendMessage(phoneNumber, '🗑️ Your order draft has been cleared.');
      return;
    }

    if (normalizedChoice === 'done') {
      await this.createOrderFromDraft(session, phoneNumber);
      return;
    }

    const selection = this.parseMenuSelection(choice, menuItems);

    if (!selection) {
      await this.sendMessage(phoneNumber, '❌ I could not match that item. Reply with the item number or name, or type *view*, *done*, or *cancel*.');
      return;
    }

    await this.addItemToOrderDraft(session, selection.item, selection.quantity);
    const lineTotal = Number(selection.item.price) * selection.quantity;
    await this.sendMessage(phoneNumber, `✅ Added *${selection.item.name}* x${selection.quantity} to your order.\nLine total: ${this.formatCurrency(lineTotal)}\n\n${this.buildOrderSummary(session)}\n\nReply with another item, type *done* to submit, or *view* to review.`);
  }

  async handleOrderProcess(session, choice, phoneNumber) {
    await this.handleMenuNavigation(session, choice, phoneNumber);
  }

  async handleRatingScore(session, choice, phoneNumber) {
    const rating = Number.parseInt(choice, 10);

    if (Number.isNaN(rating) || rating < 1 || rating > 5) {
      await this.sendMessage(phoneNumber, '❌ Please reply with a rating from 1 to 5.');
      return;
    }

    session.metadata = {
      ...session.metadata,
      pendingRating: rating,
    };
    session.currentMenu = 'rate_comment';
    session.markModified('metadata');
    await session.save();

    await this.sendMessage(phoneNumber, '📝 Thanks. Add a short comment, or type *skip* to finish without a comment.');
  }

  async handleRatingComment(session, choice, phoneNumber) {
    const rating = session.metadata?.pendingRating;

    if (!rating) {
      await this.showMainMenu(session, phoneNumber);
      return;
    }

    const comment = choice.trim().toLowerCase() === 'skip' ? null : choice.trim();

    try {
      await LaravelApiService.submitFeedback({
        business_id: session.businessId,
        worker_id: session.workerId,
        rating,
        comment,
      });

      session.metadata = {
        ...session.metadata,
        pendingRating: null,
      };
      session.currentMenu = 'main';
      session.markModified('metadata');
      await session.save();

      await this.sendMessage(phoneNumber, '✅ Thank you for your feedback. Your rating has been recorded.');
      await this.sendMessage(phoneNumber, this.getMainMenuText(session));
    } catch (error) {
      logger.error('Error submitting feedback:', error);
      await this.sendMessage(phoneNumber, '❌ We could not save your feedback right now. Please try again later.');
    }
  }

  async handleTipAmount(session, choice, phoneNumber) {
    const normalizedChoice = choice.replace(/,/g, '').trim();
    const amount = Number.parseFloat(normalizedChoice);

    if (!Number.isFinite(amount) || amount <= 0) {
      await this.sendMessage(phoneNumber, '❌ Please enter a valid amount in TZS, for example 5000.');
      return;
    }

    try {
      await this.initiateMobileMoneyPayment(session, phoneNumber, {
        type: 'tip',
        business_id: session.businessId,
        worker_id: session.workerId,
        amount,
        method: 'mobile_money',
        customer_phone: session.metadata?.pendingPaymentPhone,
      }, (payment) => `✅ Tip payment push sent successfully.\nAmount: *${this.formatCurrency(amount)}*\nReference: *${payment.provider_order_id}*`);
    } catch (error) {
      logger.error('Error initiating tip payment:', error);
      await this.sendMessage(phoneNumber, '❌ We could not send the tip payment push right now. Please try again later.');
    }
  }

  async handleTipPhone(session, choice, phoneNumber) {
    const customerPhone = this.normalizeCustomerPhoneInput(choice);

    if (!this.isValidCustomerPhone(customerPhone)) {
      await this.sendMessage(phoneNumber, '❌ Please enter a valid Tanzanian mobile number, for example 255700000000.');
      return;
    }

    session.metadata = {
      ...session.metadata,
      pendingPaymentPhone: customerPhone,
    };
    session.currentMenu = 'tip_amount';
    session.markModified('metadata');
    await session.save();

    await this.sendMessage(phoneNumber, '💵 Great. Now reply with the tip amount in TZS.\nExamples:\n5000\n10000\n20000');
  }

  async handleOrderPaymentPhone(session, choice, phoneNumber) {
    const customerPhone = this.normalizeCustomerPhoneInput(choice);

    if (!this.isValidCustomerPhone(customerPhone)) {
      await this.sendMessage(phoneNumber, '❌ Please enter a valid Tanzanian mobile number, for example 255700000000.');
      return;
    }

    session.metadata = {
      ...session.metadata,
      pendingPaymentPhone: customerPhone,
    };
    session.currentMenu = 'pay_amount';
    session.markModified('metadata');
    await session.save();

    const remainingAmount = Number(session.metadata?.lastOrderRemainingAmount ?? session.metadata?.lastOrderTotal ?? 0);
    await this.sendMessage(phoneNumber, `💳 Great. Now reply with the amount to pay in TZS.\nRemaining balance: *${this.formatCurrency(remainingAmount)}*`);
  }

  async handleOrderPaymentAmount(session, choice, phoneNumber) {
    const normalizedChoice = choice.replace(/,/g, '').trim();
    const amount = Number.parseFloat(normalizedChoice);
    const remainingAmount = Number(session.metadata?.lastOrderRemainingAmount ?? session.metadata?.lastOrderTotal ?? 0);

    if (!Number.isFinite(amount) || amount <= 0) {
      await this.sendMessage(phoneNumber, '❌ Please enter a valid amount in TZS, for example 5000.');
      return;
    }

    if (remainingAmount > 0 && amount > remainingAmount) {
      await this.sendMessage(phoneNumber, `❌ The amount cannot be more than the remaining balance of *${this.formatCurrency(remainingAmount)}*.`);
      return;
    }

    try {
      await this.initiateMobileMoneyPayment(session, phoneNumber, {
        type: 'order',
        order_id: session.metadata?.lastOrderId,
        amount,
        method: 'mobile_money',
        customer_phone: session.metadata?.pendingPaymentPhone,
      }, (payment) => `✅ Payment push sent successfully.\nAmount: *${this.formatCurrency(amount)}*\nReference: *${payment.provider_order_id}*`);
    } catch (error) {
      logger.error('Error initiating order payment:', error);
      await this.sendMessage(phoneNumber, '❌ We could not send the payment push right now. Please try again later.');
    }
  }

  async handleSupportRequest(session, choice, phoneNumber) {
    try {
      const ticket = await LaravelApiService.createSupportTicket({
        business_id: session.businessId,
        customer_name: null,
        phone_number: this.sanitizePhoneNumber(phoneNumber),
        issue: choice.trim(),
      });

      session.currentMenu = 'main';
      await session.save();

      await this.sendMessage(phoneNumber, `✅ Support request received.\nTicket: *${ticket.ticket_id}*\nOur team will follow up shortly.`);
      await this.sendMessage(phoneNumber, this.getMainMenuText(session));
    } catch (error) {
      logger.error('Error creating support ticket:', error);
      await this.sendMessage(phoneNumber, '❌ We could not create your support request right now. Please try again later.');
    }
  }

  async handleLanguageSelection(session, choice, phoneNumber) {
    const language = choice === '2' ? 'sw' : choice === '1' ? 'en' : null;

    if (!language) {
      await this.sendMessage(phoneNumber, '❌ Please choose 1 for English or 2 for Kiswahili.');
      return;
    }

    session.metadata = {
      ...session.metadata,
      language,
    };
    session.currentMenu = 'main';
    session.markModified('metadata');
    await session.save();

    await this.sendMessage(phoneNumber, language === 'sw' ? '✅ Lugha imebadilishwa kuwa Kiswahili.' : '✅ Language changed to English.');
    await this.sendMessage(phoneNumber, this.getMainMenuText(session));
  }
}

module.exports = new WhatsAppBotService();
