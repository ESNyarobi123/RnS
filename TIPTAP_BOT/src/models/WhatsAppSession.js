const fs = require('fs');
const path = require('path');

const sessionFilePath = path.resolve(__dirname, '..', '..', process.env.BOT_SESSION_FILE || 'storage/sessions.json');

const defaultSessionState = () => ({
  phoneNumber: null,
  sessionId: null,
  businessId: null,
  workerId: null,
  tableId: null,
  sessionType: null,
  isActive: true,
  lastActivity: new Date(),
  currentMenu: 'main',
  metadata: {},
  createdAt: new Date(),
  updatedAt: new Date(),
});

const localSessions = new Map();
let fileStoreLoaded = false;

class MemorySessionQuery {
  constructor(filter = {}) {
    this.filter = filter;
  }

  async sort(sortDefinition = {}) {
    const [sortField, sortDirection = 1] = Object.entries(sortDefinition)[0] || ['updatedAt', -1];

    const matches = Array.from(getSessionStore().values())
      .filter((session) => Object.entries(this.filter).every(([key, value]) => session[key] === value))
      .sort((left, right) => {
        const leftValue = left[sortField];
        const rightValue = right[sortField];

        if (leftValue === rightValue) {
          return 0;
        }

        if (sortDirection < 0) {
          return leftValue > rightValue ? -1 : 1;
        }

        return leftValue > rightValue ? 1 : -1;
      });

    return matches[0] || null;
  }
}

class MemoryWhatsAppSession {
  constructor(attributes = {}) {
    Object.assign(this, defaultSessionState(), attributes);

    this.id = attributes.id || attributes._id || this.sessionId || `${Date.now()}_${Math.random().toString(36).slice(2, 10)}`;
    this._id = this.id;
    this.lastActivity = attributes.lastActivity ? new Date(attributes.lastActivity) : new Date();
    this.createdAt = attributes.createdAt ? new Date(attributes.createdAt) : new Date();
    this.updatedAt = attributes.updatedAt ? new Date(attributes.updatedAt) : new Date();
  }

  static findOne(filter = {}) {
    return new MemorySessionQuery(filter);
  }

  static async findById(id) {
    return getSessionStore().get(String(id)) || null;
  }

  static __resetStore() {
    getSessionStore().clear();
    persistSessionStore();
  }

  markModified() {
  }

  async save() {
    this.updatedAt = new Date();

    if (!this.createdAt) {
      this.createdAt = this.updatedAt;
    }

    getSessionStore().set(String(this.id), this);
    persistSessionStore();

    return this;
  }
}

function getSessionStore() {
  if (fileStoreLoaded) {
    return localSessions;
  }

  fileStoreLoaded = true;

  if (!fs.existsSync(sessionFilePath)) {
    return localSessions;
  }

  try {
    const fileContents = fs.readFileSync(sessionFilePath, 'utf8');
    const serializedSessions = JSON.parse(fileContents);

    if (Array.isArray(serializedSessions)) {
      serializedSessions.forEach((session) => {
        const hydratedSession = new MemoryWhatsAppSession(session);
        localSessions.set(String(hydratedSession.id), hydratedSession);
      });
    }
  } catch (error) {
    console.error('Failed to load local WhatsApp sessions:', error.message);
  }

  return localSessions;
}

function persistSessionStore() {
  try {
    const directoryPath = path.dirname(sessionFilePath);

    if (!fs.existsSync(directoryPath)) {
      fs.mkdirSync(directoryPath, { recursive: true });
    }

    const serializedSessions = Array.from(getSessionStore().values()).map((session) => ({
      id: session.id,
      _id: session._id,
      phoneNumber: session.phoneNumber,
      sessionId: session.sessionId,
      businessId: session.businessId,
      workerId: session.workerId,
      tableId: session.tableId,
      sessionType: session.sessionType,
      isActive: session.isActive,
      lastActivity: session.lastActivity,
      currentMenu: session.currentMenu,
      metadata: session.metadata,
      createdAt: session.createdAt,
      updatedAt: session.updatedAt,
    }));

    fs.writeFileSync(sessionFilePath, JSON.stringify(serializedSessions, null, 2));
  } catch (error) {
    console.error('Failed to persist local WhatsApp sessions:', error.message);
  }
}

module.exports = MemoryWhatsAppSession;
