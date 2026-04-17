import React, { useState } from 'react';
import {
  StyleSheet,
  View,
  Text,
  TextInput,
  TouchableOpacity,
  Image,
  KeyboardAvoidingView,
  Platform,
  ActivityIndicator,
  Alert
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import { User, Lock, ArrowRight } from 'lucide-react-native';
import * as SecureStore from 'expo-secure-store';
import { Colors } from '../theme/colors';
import apiClient from '../api/client';

export default function LoginScreen(props) {
  const { onLoginSuccess } = props;
  const insets = useSafeAreaInsets();
  const [nip, setNip] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!nip || !password) {
      Alert.alert('Error', 'Harap isi NIP dan Password.');
      return;
    }

    setLoading(true);
    try {
      const response = await apiClient.post('/auth/login', {
        nip,
        password,
      });

      const { token, user } = response.data.data;

      // Simpan token secara aman
      await SecureStore.setItemAsync('user_token', token);
      await SecureStore.setItemAsync('user_data', JSON.stringify(user));

      onLoginSuccess(user);
    } catch (error) {
      console.error(error);
      const msg = error.response?.data?.message || 'Login gagal. Periksa koneksi Anda.';
      Alert.alert('Gagal', msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      style={styles.container}
    >
      <LinearGradient
        colors={[Colors.primaryDark, Colors.primary]}
        style={[styles.header, { paddingTop: insets.top + 50 }]}
      >
        <View style={styles.logoContainer}>
          <Image
            source={{ uri: 'https://raw.githubusercontent.com/DedhyPraditya/logo/a42c1af7af478a41c31a69c77f6c270aa556b2f5/logo_unhas.png' }}
            style={styles.logo}
            resizeMode="contain"
          />
        </View>

        <Text style={styles.title}>SISTEM PRESENSI</Text>
        <Text style={styles.subtitle}>Satuan Pengamanan Universitas Hasanuddin</Text>
      </LinearGradient>

      <View style={styles.formCard}>
        <Text style={styles.cardTitle}>Selamat Datang</Text>
        <Text style={styles.cardSub}>Silakan login untuk memulai sesi Anda</Text>

        <View style={styles.inputWrap}>
          <User size={20} color={Colors.textMuted} style={styles.inputIcon} />
          <TextInput
            style={styles.input}
            placeholder="NIP / USERNAME"
            placeholderTextColor="#94a3b8"
            value={nip}
            onChangeText={setNip}
            autoCapitalize="none"
          />
        </View>

        <View style={styles.inputWrap}>
          <Lock size={20} color={Colors.textMuted} style={styles.inputIcon} />
          <TextInput
            style={styles.input}
            placeholder="PASSWORD"
            placeholderTextColor="#94a3b8"
            secureTextEntry
            value={password}
            onChangeText={setPassword}
          />
        </View>

        <TouchableOpacity
          style={[styles.button, loading && styles.buttonDisabled]}
          onPress={handleLogin}
          disabled={loading}
        >
          {loading ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <>
              <Text style={styles.buttonText}>Masuk Sekarang</Text>
              <ArrowRight size={20} color="#fff" />
            </>
          )}
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.registerLink}
          onPress={() => props.navigation.navigate('Register')}
        >
          <Text style={styles.registerText}>Belum punya akun? <Text style={styles.registerTextBold}>Daftar Sekarang</Text></Text>
        </TouchableOpacity>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  header: {
    paddingTop: 80,
    paddingBottom: 100,
    alignItems: 'center',
    borderBottomLeftRadius: 40,
    borderBottomRightRadius: 40,
  },
  logoContainer: {
    width: 100,
    height: 100,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 10,
  },
  logo: {
    width: 90,
    height: 90,
  },
  title: {
    color: '#fff',
    fontSize: 28,
    fontWeight: '800',
    letterSpacing: 1,
  },
  subtitle: {
    color: 'rgba(255,255,255,0.7)',
    fontSize: 14,
    fontWeight: '500',
  },
  formCard: {
    backgroundColor: Colors.white,
    marginHorizontal: 25,
    marginTop: -50,
    borderRadius: 30,
    padding: 30,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.1,
    shadowRadius: 20,
    elevation: 10,
  },
  cardTitle: {
    fontSize: 24,
    fontWeight: '800',
    color: Colors.text,
    textAlign: 'center',
    marginBottom: 5,
  },
  cardSub: {
    fontSize: 13,
    color: Colors.textMuted,
    textAlign: 'center',
    marginBottom: 25,
  },
  inputWrap: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f1f5f9',
    borderRadius: 15,
    marginBottom: 15,
    paddingHorizontal: 15,
    height: 55,
  },
  inputIcon: {
    marginRight: 10,
  },
  input: {
    flex: 1,
    fontSize: 14,
    color: Colors.text,
    fontWeight: '600',
  },
  button: {
    backgroundColor: Colors.primary,
    height: 55,
    borderRadius: 15,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 10,
  },
  buttonDisabled: {
    opacity: 0.7,
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '800',
    marginRight: 8,
  },
  registerLink: {
    marginTop: 25,
    alignItems: 'center',
  },
  registerText: {
    color: Colors.textMuted,
    fontSize: 14,
  },
  registerTextBold: {
    color: Colors.primary,
    fontWeight: '800',
  },
});
