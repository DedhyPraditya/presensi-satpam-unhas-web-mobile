import axios from 'axios';
import * as SecureStore from 'expo-secure-store';
import { Alert } from 'react-native';

/**
 * KONFIGURASI PRODUKSI
 * Ganti BASE_URL dengan domain HTTPS Anda saat sudah online.
 */
const API_CONFIG = {
  PRODUCTION_DOMAIN: 'security-unhas.madignet.cloud',
  LOCAL_DOMAIN: '127.0.0.1:8000', // Ganti dengan IP laptop jika testing di HP fisik
  VERSION: '/api/v1'
};

// Gunakan HTTP untuk lokal dan HTTPS untuk produksi
const BASE_URL = __DEV__ 
  ? `http://${API_CONFIG.LOCAL_DOMAIN}${API_CONFIG.VERSION}`
  : `https://${API_CONFIG.PRODUCTION_DOMAIN}${API_CONFIG.VERSION}`;

const apiClient = axios.create({
  baseURL: BASE_URL,
  timeout: 15000,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Interceptor Request: Menyisipkan Token otomatis
apiClient.interceptors.request.use(async (config) => {
  const token = await SecureStore.getItemAsync('user_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, (error) => Promise.reject(error));

// Interceptor Response: Penanganan Error Global
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    // Jika Unauthorized (Token mati/invalid)
    if (error.response?.status === 401) {
      console.log('Session expired or unauthorized');
      // Anda bisa menambahkan logika logout otomatis di sini jika perlu
    }

    // Penanganan error timeout/network
    if (!error.response) {
      console.log('Network Error / Server Offline');
    }

    return Promise.reject(error);
  }
);

export default apiClient;
