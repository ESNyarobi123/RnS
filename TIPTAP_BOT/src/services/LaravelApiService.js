const axios = require('axios');
const crypto = require('crypto');

class LaravelApiService {
  constructor() {
    this.baseURL = process.env.LARAVEL_URL || 'http://localhost:8000';
    this.secretKey = process.env.BOT_SECRET_KEY;
    this.timeout = parseInt(process.env.API_TIMEOUT) || 30000;
    this.retries = parseInt(process.env.API_RETRIES) || 3;

    this.axios = axios.create({
      baseURL: this.baseURL,
      timeout: this.timeout,
      headers: {
        'Content-Type': 'application/json',
        'X-Bot-Secret': this.secretKey,
        'User-Agent': 'TiptapBot/1.0'
      }
    });
  }

  generateSignature(data) {
    return crypto
      .createHmac('sha256', this.secretKey)
      .update(JSON.stringify(data))
      .digest('hex');
  }

  async request(method, endpoint, data = null, params = null) {
    try {
      const config = {
        method,
        url: endpoint,
        params
      };

      if (data && method.toLowerCase() !== 'get') {
        config.data = data;
      }

      // Add signature for POST/PUT requests
      if (data && ['post', 'put', 'patch'].includes(method.toLowerCase())) {
        config.headers = {
          ...config.headers,
          'X-Bot-Signature': this.generateSignature(data)
        };
      }

      const response = await this.axios(config);
      return response.data;
    } catch (error) {
      console.error(`API Error (${method} ${endpoint}):`, error.response?.data || error.message);
      throw error;
    }
  }

  // Business related endpoints
  async getBusinessByCode(code) {
    return this.request('GET', `/api/bot/business/${code}`);
  }

  async getBusinessMenu(businessId) {
    return this.request('GET', `/api/bot/business/${businessId}/menu`);
  }

  async getBusinessTables(businessId) {
    return this.request('GET', `/api/bot/business/${businessId}/tables`);
  }

  async getBusinessWorkers(businessId) {
    return this.request('GET', `/api/bot/business/${businessId}/workers`);
  }

  // Worker related endpoints
  async getWorkerByCode(code) {
    return this.request('GET', `/api/bot/worker/${code}`);
  }

  async getWorkerTips(workerId, period = 'today') {
    return this.request('GET', `/api/bot/worker/${workerId}/tips`, null, { period });
  }

  async getWorkerFeedbacks(workerId) {
    return this.request('GET', `/api/bot/worker/${workerId}/feedbacks`);
  }

  async getWorkerCustomersServed(workerId) {
    return this.request('GET', `/api/bot/worker/${workerId}/customers`);
  }

  // Table related endpoints
  async getTableByCode(code) {
    return this.request('GET', `/api/bot/table/${code}`);
  }

  // Order related endpoints
  async createOrder(data) {
    return this.request('POST', '/api/bot/orders', data);
  }

  async getOrderStatus(orderId) {
    return this.request('GET', `/api/bot/orders/${orderId}`);
  }

  // Payment related endpoints
  async initiatePayment(data) {
    return this.request('POST', '/api/bot/payments/initiate', data);
  }

  async checkPaymentStatus(paymentId) {
    return this.request('GET', `/api/bot/payments/${paymentId}/status`);
  }

  // Feedback related endpoints
  async submitFeedback(data) {
    return this.request('POST', '/api/bot/feedbacks', data);
  }

  async createTip(data) {
    return this.request('POST', '/api/bot/tips', data);
  }

  // Call waiter related endpoints
  async callWaiter(data) {
    return this.request('POST', '/api/bot/call-waiter', data);
  }

  // Customer support related endpoints
  async createSupportTicket(data) {
    return this.request('POST', '/api/bot/support', data);
  }

  // Health check
  async healthCheck() {
    return this.request('GET', '/api/bot/health');
  }
}

module.exports = new LaravelApiService();
