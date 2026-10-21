import { ref, computed, reactive, watchEffect } from 'vue';
import api from './api';

const token = ref(localStorage.getItem('tailorpro_token') || null);
const user = ref(JSON.parse(localStorage.getItem('tailorpro_user') || 'null'));
const loading = ref(false);

const isViewer = computed(() => user.value?.role === 'viewer');
const isAdmin = computed(() => user.value?.role === 'admin' || user.value?.role === 'superadmin' || user.value?.role === 'manager');
const isEditor = computed(() => user.value?.role === 'editor' || user.value?.role === 'operator' || isAdmin.value);

export const authState = reactive({
  token,
  user,
  loading,
  isViewer,
  isAdmin,
  isEditor,
});

watchEffect(() => {
  if (typeof document !== 'undefined') {
    if (isViewer.value) {
      document.body.setAttribute('data-viewer', 'true');
    } else {
      document.body.removeAttribute('data-viewer');
    }
  }
});

export async function login(email, password) {
  loading.value = true;
  try {
    const response = await api.post('/auth/login', { email, password });
    token.value = response.data.token;
    user.value = response.data.user;
    localStorage.setItem('tailorpro_token', token.value);
    localStorage.setItem('tailorpro_user', JSON.stringify(user.value));
    return { success: true, user: user.value };
  } catch (error) {
    const message = error.response?.data?.message || error.response?.data?.error || 'Login failed';
    throw new Error(message);
  } finally {
    loading.value = false;
  }
}

export async function logout() {
  try {
    if (token.value) {
      await api.post('/auth/logout').catch(() => {});
    }
  } finally {
    token.value = null;
    user.value = null;
    localStorage.removeItem('tailorpro_token');
    localStorage.removeItem('tailorpro_user');
    window.location.href = '/login';
  }
}

export function setAuth(newUser, newToken) {
  user.value = newUser;
  token.value = newToken;
  if (newToken) {
    localStorage.setItem('tailorpro_token', newToken);
  } else {
    localStorage.removeItem('tailorpro_token');
  }
  if (newUser) {
    localStorage.setItem('tailorpro_user', JSON.stringify(newUser));
  } else {
    localStorage.removeItem('tailorpro_user');
  }
}

export async function initAuth() {
  if (!token.value) return null;
  try {
    const response = await api.get('/auth/user');
    user.value = response.data;
    localStorage.setItem('tailorpro_user', JSON.stringify(user.value));
    return user.value;
  } catch (error) {
    // If token invalid, clear
    if (error.response?.status === 401) {
      token.value = null;
      user.value = null;
      localStorage.removeItem('tailorpro_token');
      localStorage.removeItem('tailorpro_user');
    }
    return null;
  }
}

export function useAuth() {
  return {
    user,
    token,
    loading,
    isViewer,
    isAdmin,
    isEditor,
    login,
    logout,
    initAuth,
  };
}
