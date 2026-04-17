import axios from 'axios';
import * as SecureStore from 'expo-secure-store';
import { Alert } from 'react-native';

/**
 * KONFIGURASI PRODUKSI
 * Ganti BASE_URL dengan domain HTTPS Anda saat sudah online.
 */
const API_CONFIG = {
  LOCAL_IP: '10.10.10.244', // IP Laptop Anda sekarang
  PRODUCTION_DOMAIN: 'absen-satpam.unhas.ac.id', // Contoh domain nanti
  PORT: '8000',
  VERSION: '/api/v1'
};

const BASE_URL = `http://${API_CONFIG.LOCAL_IP}:${API_CONFIG.PORT}${API_CONFIG.VERSION}`;

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
