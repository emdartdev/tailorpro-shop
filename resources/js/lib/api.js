import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

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

export default api;
