import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// High-speed in-memory cache and in-flight request deduplicator
const apiCache = new Map();
const inFlightRequests = new Map();

function getTtlForUrl(url) {
  if (url.includes('/catalogs') || url.includes('/running-years')) {
    return 300000; // 5 minutes for static catalogs and running years
  }
  if (url.includes('/workers')) {
    return 60000; // 1 minute for workers list
  }
  if (url.includes('/customers')) {
    return 30000; // 30 seconds for customers
  }
  if (url.includes('/dashboard')) {
    return 15000; // 15 seconds for dashboard metrics
  }
  return 10000; // 10 seconds default for general GET requests
}

export function clearApiCache(pattern) {
  if (!pattern) {
    apiCache.clear();
    return;
  }
  for (const key of apiCache.keys()) {
    if (key.includes(pattern)) {
      apiCache.delete(key);
    }
  }
}

api.clearCache = clearApiCache;

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('tailorpro_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, (error) => {
  return Promise.reject(error);
});

api.interceptors.response.use(
  (response) => {
    if (response.data !== null && response.data !== undefined) {
      if (Array.isArray(response.data)) {
        response.data.data = response.data;
      } else if (typeof response.data === 'object' && !('data' in response.data)) {
        response.data.data = response.data;
      }
    }
    return response;
  },
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('tailorpro_token');
      localStorage.removeItem('tailorpro_user');
      if (window.location.pathname !== '/login') {
        window.location.href = '/login?expired=1';
      }
    }
    return Promise.reject(error);
  }
);

// High-performance cached & deduplicated GET method
const originalGet = api.get.bind(api);
api.get = function (url, config = {}) {
  if (config.skipCache) {
    return originalGet(url, config);
  }

  const key = `${url}?${config.params ? JSON.stringify(config.params) : ''}`;
  const now = Date.now();

  // 1. Check in-memory cache
  const cached = apiCache.get(key);
  if (cached && now < cached.expiresAt) {
    return Promise.resolve(cached.response);
  }

  // 2. Check in-flight duplicate request
  if (inFlightRequests.has(key)) {
    return inFlightRequests.get(key);
  }

  // 3. Dispatch network request
  const requestPromise = originalGet(url, config)
    .then((response) => {
      const ttl = getTtlForUrl(url);
      apiCache.set(key, {
        expiresAt: Date.now() + ttl,
        response,
      });
      return response;
    })
    .finally(() => {
      inFlightRequests.delete(key);
    });

  inFlightRequests.set(key, requestPromise);
  return requestPromise;
};

// Automatic smart cache busting on state mutations
['post', 'put', 'patch', 'delete'].forEach((method) => {
  const originalMethod = api[method].bind(api);
  api[method] = function (url, ...args) {
    if (url.includes('/orders')) {
      clearApiCache('/orders');
      clearApiCache('/dashboard');
      clearApiCache('/customers');
    } else if (url.includes('/workers') || url.includes('/worker-entries') || url.includes('/staff-payments')) {
      clearApiCache('/workers');
      clearApiCache('/worker-entries');
      clearApiCache('/staff-payments');
    } else if (url.includes('/customers')) {
      clearApiCache('/customers');
      clearApiCache('/orders');
    } else if (url.includes('/catalogs')) {
      clearApiCache('/catalogs');
    } else if (url.includes('/running-years')) {
      clearApiCache('/running-years');
    } else {
      apiCache.clear();
    }
    return originalMethod(url, ...args);
  };
});

export default api;
