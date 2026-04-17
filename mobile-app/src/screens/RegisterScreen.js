import React, { useState, useEffect } from 'react';
import {
  StyleSheet,
  View,
  Text,
  TextInput,
  TouchableOpacity,
  ScrollView,
  KeyboardAvoidingView,
  Platform,
  ActivityIndicator,
  Alert,
  Modal,
  FlatList
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import { User, Lock, MapPin, Briefcase, ChevronDown, ArrowLeft, CheckCircle } from 'lucide-react-native';
import { Colors } from '../theme/colors';
import apiClient from '../api/client';

export default function RegisterScreen({ navigation }) {
  const insets = useSafeAreaInsets();
  
  // Form State
  const [nama, setNama] = useState('');
  const [nip, setNip] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [idPos, setIdPos] = useState(null);
  const [namaPos, setNamaPos] = useState('Pilih POS Jaga');
  const [jenisKerja, setJenisKerja] = useState('non_shift');
  
  // UI State
  const [loading, setLoading] = useState(false);
  const [posList, setPosList] = useState([]);
  const [loadingPos, setLoadingPos] = useState(false);
  const [posError, setPosError] = useState(null);
  const [showPosModal, setShowPosModal] = useState(false);
  const [showTypeModal, setShowTypeModal] = useState(false);

  useEffect(() => {
    fetchPositions();
  }, []);

  const fetchPositions = async () => {
    setLoadingPos(true);
    setPosError(null);
    try {
      const response = await apiClient.get('/positions');
      if (response.data && Array.isArray(response.data.data)) {
        setPosList(response.data.data);
      } else {
        setPosError('Format data tidak sesuai.');
      }
    } catch (error) {
      const errMsg = error.response?.data?.message
        || error.message
        || 'Tidak bisa menghubungi server.';
      console.error('Fetch positions error:', errMsg);
      setPosError(errMsg);
      Alert.alert(
        'Gagal Memuat Pos Jaga',
        'Detail: ' + errMsg + '\n\nPastikan HP Anda terhubung ke internet.',
        [{ text: 'Coba Lagi', onPress: fetchPositions }, { text: 'Batal' }]
      );
    } finally {
      setLoadingPos(false);
    }
  };

  const handleRegister = async () => {
    if (!nama || !nip || !password || !idPos) {
      Alert.alert('Error', 'Harap lengkapi semua data pendaftaran.');
      return;
    }

    if (password !== passwordConfirmation) {
      Alert.alert('Error', 'Konfirmasi password tidak cocok.');
      return;
    }

    setLoading(true);
    try {
      await apiClient.post('/auth/register', {
        nama,
        nip,
        password,
        password_confirmation: passwordConfirmation,
        id_pos: idPos,
        jenis_kerja: jenisKerja
      });

      Alert.alert(
        'Berhasil', 
        'Pendaftaran berhasil dikirim. Silakan hubungi admin di kantor utama untuk verifikasi dan aktivasi akun Anda.',
        [{ text: 'OK', onPress: () => navigation.navigate('Login') }]
      );
    } catch (error) {
      const msg = error.response?.data?.message || 'Pendaftaran gagal. NIP mungkin sudah terdaftar.';
      Alert.alert('Gagal', msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <LinearGradient
        colors={[Colors.primaryDark, Colors.primary]}
        style={[styles.header, { paddingTop: insets.top + 20 }]}
      >
        <TouchableOpacity 
          style={styles.backButton} 
          onPress={() => navigation.goBack()}
        >
          <ArrowLeft size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.title}>Registrasi Baru</Text>
        <Text style={styles.subtitle}>Lengkapi data diri Anda sebagai Personel</Text>
      </LinearGradient>

      <KeyboardAvoidingView 
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={{ flex: 1 }}
      >
        <ScrollView 
          style={[styles.content, { marginTop: -70 }]}
          contentContainerStyle={styles.scrollContent}
          showsVerticalScrollIndicator={false}
        >
          <View style={styles.formCard}>
            
            <View style={styles.inputGroup}>
              <Text style={styles.label}>Nama Lengkap</Text>
              <View style={styles.inputWrap}>
                <User size={18} color={Colors.textMuted} style={styles.inputIcon} />
                <TextInput
                  style={styles.input}
                  placeholder="Masukkan nama asli"
                  value={nama}
                  onChangeText={setNama}
                />
              </View>
            </View>

            <View style={styles.inputGroup}>
              <Text style={styles.label}>NIP / ID Personel</Text>
              <View style={styles.inputWrap}>
                <Briefcase size={18} color={Colors.textMuted} style={styles.inputIcon} />
                <TextInput
                  style={styles.input}
                  placeholder="Masukkan NIP"
                  value={nip}
                  onChangeText={setNip}
                  keyboardType="numeric"
                />
              </View>
            </View>

            <View style={styles.inputGroup}>
              <Text style={styles.label}>Lokasi POS Jaga Utama</Text>
              <TouchableOpacity 
                style={styles.inputWrap} 
                onPress={() => setShowPosModal(true)}
              >
                <MapPin size={18} color={Colors.textMuted} style={styles.inputIcon} />
                <Text style={[styles.input, !idPos && { color: '#94a3b8' }]}>{namaPos}</Text>
                <ChevronDown size={18} color={Colors.textMuted} />
              </TouchableOpacity>
            </View>

            <View style={styles.inputGroup}>
              <Text style={styles.label}>Sistem Jam Kerja</Text>
              <TouchableOpacity 
                style={styles.inputWrap} 
                onPress={() => setShowTypeModal(true)}
              >
                <Briefcase size={18} color={Colors.textMuted} style={styles.inputIcon} />
                <Text style={styles.input}>
                  {jenisKerja === 'non_shift' ? 'Non-Shift (Reguler)' : 'Shift (Gantian)'}
                </Text>
                <ChevronDown size={18} color={Colors.textMuted} />
              </TouchableOpacity>
            </View>

            <View style={styles.inputGroup}>
              <Text style={styles.label}>Password</Text>
              <View style={styles.inputWrap}>
                <Lock size={18} color={Colors.textMuted} style={styles.inputIcon} />
                <TextInput
                  style={styles.input}
                  placeholder="Minimal 6 karakter"
                  secureTextEntry
                  value={password}
                  onChangeText={setPassword}
                />
              </View>
            </View>

            <View style={styles.inputGroup}>
              <Text style={styles.label}>Konfirmasi Password</Text>
              <View style={styles.inputWrap}>
                <Lock size={18} color={Colors.textMuted} style={styles.inputIcon} />
                <TextInput
                  style={styles.input}
                  placeholder="Ulangi password"
                  secureTextEntry
                  value={passwordConfirmation}
                  onChangeText={setPasswordConfirmation}
                />
              </View>
            </View>

            <TouchableOpacity
              style={[styles.btnRegister, loading && styles.btnDisabled]}
              onPress={handleRegister}
              disabled={loading}
            >
              {loading ? (
                <ActivityIndicator color="#fff" />
              ) : (
                <>
                  <CheckCircle size={20} color="#fff" style={{ marginRight: 8 }} />
                  <Text style={styles.btnText}>Daftar Sekarang</Text>
                </>
              )}
            </TouchableOpacity>

            <TouchableOpacity 
              style={styles.backLink}
              onPress={() => navigation.goBack()}
            >
              <Text style={styles.backText}>Sudah punya akun? <Text style={styles.backTextBold}>Login Disini</Text></Text>
            </TouchableOpacity>

          </View>
        </ScrollView>
      </KeyboardAvoidingView>

      {/* POS Selection Modal */}
      <Modal visible={showPosModal} transparent animationType="slide">
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>Pilih POS Jaga Anda</Text>
            {loadingPos ? (
              <ActivityIndicator size="large" color={Colors.primary} style={{ marginVertical: 30 }} />
            ) : posError ? (
              <View style={{ alignItems: 'center', paddingVertical: 20 }}>
                <Text style={{ color: '#ef4444', textAlign: 'center', marginBottom: 15 }}>
                  Gagal memuat data.{"\n"}(Error: {posError})
                </Text>
                <TouchableOpacity
                  style={[styles.btnRegister, { paddingHorizontal: 20, height: 44, marginTop: 0 }]}
                  onPress={fetchPositions}
                >
                  <Text style={styles.btnText}>Coba Lagi</Text>
                </TouchableOpacity>
              </View>
            ) : posList.length === 0 ? (
              <View style={{ alignItems: 'center', paddingVertical: 20 }}>
                <Text style={{ color: '#94a3b8', textAlign: 'center', marginBottom: 15 }}>
                  Belum ada Pos Jaga terdaftar.{"\n"}Hubungi Admin.
                </Text>
                <TouchableOpacity
                  style={[styles.btnRegister, { paddingHorizontal: 20, height: 44, marginTop: 0 }]}
                  onPress={fetchPositions}
                >
                  <Text style={styles.btnText}>Muat Ulang</Text>
                </TouchableOpacity>
              </View>
            ) : (
              <FlatList
                data={posList}
                keyExtractor={(item) => item.id.toString()}
                renderItem={({ item }) => (
                  <TouchableOpacity
                    style={styles.modalItem}
                    onPress={() => {
                      setIdPos(item.id);
                      setNamaPos(item.nama_pos);
                      setShowPosModal(false);
                    }}
                  >
                    <MapPin size={18} color={Colors.primary} style={{ marginRight: 10 }} />
                    <Text style={styles.modalItemText}>{item.nama_pos}</Text>
                  </TouchableOpacity>
                )}
              />
            )}
            <TouchableOpacity style={styles.modalClose} onPress={() => setShowPosModal(false)}>
              <Text style={styles.modalCloseText}>Tutup</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>

      {/* Type Selection Modal */}
      <Modal visible={showTypeModal} transparent animationType="slide">
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>Pilih Sistem Kerja</Text>
            <TouchableOpacity 
              style={styles.modalItem}
              onPress={() => { setJenisKerja('non_shift'); setShowTypeModal(false); }}
            >
              <Briefcase size={18} color={Colors.primary} style={{ marginRight: 10 }} />
              <Text style={styles.modalItemText}>Non-Shift (Administrasi/Reguler)</Text>
            </TouchableOpacity>
            <TouchableOpacity 
              style={styles.modalItem}
              onPress={() => { setJenisKerja('shift'); setShowTypeModal(false); }}
            >
              <Briefcase size={18} color={Colors.primary} style={{ marginRight: 10 }} />
              <Text style={styles.modalItemText}>Shift (Operasional/Layanan)</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.modalClose} onPress={() => setShowTypeModal(false)}>
              <Text style={styles.modalCloseText}>Tutup</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>

    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  header: {
    paddingBottom: 90,
    paddingHorizontal: 25,
    borderBottomLeftRadius: 35,
    borderBottomRightRadius: 35,
  },
  backButton: {
    width: 40,
    height: 40,
    borderRadius: 12,
    backgroundColor: 'rgba(255,255,255,0.2)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 20,
  },
  title: {
    fontSize: 26,
    fontWeight: '800',
    color: '#fff',
  },
  subtitle: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.8)',
    marginTop: 5,
  },
  content: {
    flex: 1,
  },
  scrollContent: {
    paddingBottom: 60,
  },
  formCard: {
    backgroundColor: '#fff',
    marginHorizontal: 20,
    borderRadius: 30,
    padding: 25,
    zIndex: 999,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.1,
    shadowRadius: 20,
    elevation: 15, // Lebih tinggi agar pasti di atas
  },
  inputGroup: {
    marginBottom: 18,
  },
  label: {
    fontSize: 13,
    fontWeight: '700',
    color: Colors.text,
    marginBottom: 8,
    marginLeft: 4,
  },
  inputWrap: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f1f5f9',
    borderRadius: 12,
    paddingHorizontal: 15,
    height: 52,
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
  btnRegister: {
    backgroundColor: Colors.primary,
    height: 55,
    borderRadius: 15,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 20,
    shadowColor: Colors.primary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 5,
  },
  btnDisabled: {
    opacity: 0.7,
  },
  btnText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '800',
  },
  backLink: {
    marginTop: 20,
    alignItems: 'center',
  },
  backText: {
    color: Colors.textMuted,
    fontSize: 13,
  },
  backTextBold: {
    color: Colors.primary,
    fontWeight: '800',
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: '#fff',
    borderTopLeftRadius: 30,
    borderTopRightRadius: 30,
    padding: 25,
    maxHeight: '70%',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: Colors.text,
    marginBottom: 20,
    textAlign: 'center',
  },
  modalItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 15,
    borderBottomWidth:1,
    borderBottomColor: '#f1f5f9',
  },
  modalItemText: {
    fontSize: 15,
    color: Colors.text,
    fontWeight: '600',
  },
  modalClose: {
    marginTop: 15,
    paddingVertical: 15,
    alignItems: 'center',
    backgroundColor: '#f1f5f9',
    borderRadius: 15,
  },
  modalCloseText: {
    fontWeight: '700',
    color: Colors.textMuted,
  }
});
