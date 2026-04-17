import React, { useState, useEffect } from 'react';
import {
  StyleSheet,
  View,
  Text,
  TextInput,
  TouchableOpacity,
  ScrollView,
  Image,
  ActivityIndicator,
  Alert,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import { Camera, Image as ImageIcon, Send, X, AlertTriangle } from 'lucide-react-native';
import { Colors } from '../theme/colors';
import * as ImagePicker from 'expo-image-picker';
import apiClient from '../api/client';

// URL dasar server (tanpa /api/v1)
const getStorageUrl = (path) => {
  const base = apiClient.defaults.baseURL.replace(/\/api\/v1$/, '').replace(/\/api$/, '');
  return `${base}/storage/${path}`;
};

export default function ReportScreen() {
  const insets = useSafeAreaInsets();
  const [deskripsi, setDeskripsi] = useState('');
  const [foto, setFoto] = useState(null);
  const [loading, setLoading] = useState(false);
  const [reportHistory, setReportHistory] = useState([]);

  useEffect(() => {
    fetchHistory();
  }, []);

  const fetchHistory = async () => {
    try {
      const resp = await apiClient.get('/reports');
      setReportHistory(resp.data.data ?? []);
    } catch (e) {
      console.log('Failed to fetch report history', e);
    }
  };

  const pickImage = async (useCamera = true) => {
    let result;
    if (useCamera) {
      const { status } = await ImagePicker.requestCameraPermissionsAsync();
      if (status !== 'granted') {
        Alert.alert('Gagal', 'Izin kamera diperlukan.');
        return;
      }
      result = await ImagePicker.launchCameraAsync({ allowsEditing: true, quality: 0.5, base64: true });
    } else {
      result = await ImagePicker.launchImageLibraryAsync({ allowsEditing: true, quality: 0.5, base64: true });
    }
    if (!result.canceled) setFoto(result.assets[0].base64);
  };

  const handleSubmit = async () => {
    if (!deskripsi.trim()) { Alert.alert('Error', 'Harap isi deskripsi kejadian.'); return; }
    if (!foto)              { Alert.alert('Error', 'Mohon ambil foto bukti kejadian.'); return; }

    setLoading(true);
    try {
      // Backend memerlukan field 'judul' – gunakan 3 kata pertama deskripsi sebagai judul otomatis
      const judulOtomatis = deskripsi.trim().split(' ').slice(0, 5).join(' ');
      await apiClient.post('/reports', {
        judul: judulOtomatis,
        deskripsi,
        foto: `data:image/jpeg;base64,${foto}`,
        latitude: -5.13245,
        longitude: 119.48671,
      });
      Alert.alert('Sukses', 'Laporan kejadian berhasil dikirim.');
      setDeskripsi('');
      setFoto(null);
      fetchHistory();
    } catch (e) {
      Alert.alert('Gagal', e.response?.data?.message || 'Terjadi kesalahan.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      {/* ── Header gradient – identik HomeScreen ── */}
      <LinearGradient
        colors={[Colors.primaryDark, Colors.primary]}
        style={[styles.header, { paddingTop: insets.top + 20 }]}
      >
        <Text style={styles.headerGreet}>Form Laporan</Text>
        <Text style={styles.headerTitle}>Laporkan Kejadian</Text>
      </LinearGradient>

      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={{ flex: 1 }}
      >
        {/* ── Content – sama persis HomeScreen ── */}
        <ScrollView
          style={styles.content}
          contentContainerStyle={{ paddingBottom: insets.bottom + 90 }}
          keyboardShouldPersistTaps="handled"
        >
          {/* ── Card Form – identik .card HomeScreen ── */}
          <View style={styles.card}>
            {/* Judul card */}
            <View style={styles.cardTitleRow}>
              <AlertTriangle size={20} color="#ef4444" />
              <Text style={styles.cardTitle}>Laporan Kejadian</Text>
            </View>
            <Text style={styles.cardSubtitle}>
              Laporkan kejadian atau temuan mencurigakan di area penugasan Anda.
            </Text>

            {/* Deskripsi */}
            <Text style={styles.formLabel}>Deskripsi Kejadian</Text>
            <TextInput
              style={styles.textarea}
              placeholder="Jelaskan kronologi kejadian secara singkat..."
              placeholderTextColor="#94a3b8"
              multiline
              numberOfLines={4}
              value={deskripsi}
              onChangeText={setDeskripsi}
              textAlignVertical="top"
            />

            {/* Foto Bukti */}
            <View style={{ marginTop: 16 }}>
              <Text style={styles.formLabel}>Foto Bukti</Text>

              {foto ? (
                <View style={styles.fotoPreviewWrap}>
                  <Image
                    source={{ uri: `data:image/jpeg;base64,${foto}` }}
                    style={styles.fotoPreview}
                  />
                  <TouchableOpacity style={styles.fotoDelBtn} onPress={() => setFoto(null)}>
                    <X size={16} color="#fff" />
                  </TouchableOpacity>
                </View>
              ) : (
                <TouchableOpacity style={styles.fotoPlaceholder} onPress={() => pickImage(true)}>
                  <Camera size={32} color="#94a3b8" />
                  <Text style={styles.fotoPlaceholderTitle}>Ambil Foto Bukti</Text>
                  <Text style={styles.fotoPlaceholderSub}>Klik untuk membuka kamera</Text>
                </TouchableOpacity>
              )}

              <TouchableOpacity style={styles.galleryBtn} onPress={() => pickImage(false)}>
                <ImageIcon size={16} color="#475569" />
                <Text style={styles.galleryBtnText}>Pilih dari Galeri</Text>
              </TouchableOpacity>
            </View>

            {/* Tombol Kirim */}
            <TouchableOpacity
              style={[styles.btnPrimary, loading && { opacity: 0.5 }]}
              onPress={handleSubmit}
              disabled={loading}
            >
              <LinearGradient
                colors={[Colors.primaryDark, Colors.primary]}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 0 }}
                style={styles.btnPrimaryGradient}
              >
                {loading
                  ? <ActivityIndicator color="#fff" />
                  : (<>
                      <Send size={18} color="#fff" />
                      <Text style={styles.btnPrimaryText}>Kirim Laporan</Text>
                    </>)
                }
              </LinearGradient>
            </TouchableOpacity>
          </View>

          {/* ── Riwayat Laporan ── */}
          <Text style={styles.sectionTitle}>Riwayat Laporan (7 Hari)</Text>

          {reportHistory.length > 0 ? (
            reportHistory.map((item, idx) => (
              <View key={idx} style={styles.laporanItem}>
                {item.foto ? (
                  <Image
                    source={{ uri: getStorageUrl(item.foto) }}
                    style={styles.laporanPhoto}
                  />
                ) : (
                  <View style={styles.laporanNoPhoto}>
                    <ImageIcon size={22} color="#94a3b8" />
                  </View>
                )}
                <View style={styles.laporanInfo}>
                  <Text style={styles.laporanDate}>{item.tanggal}</Text>
                  <Text style={styles.laporanDesc} numberOfLines={2}>{item.deskripsi}</Text>
                  <Text style={styles.laporanTime}>{item.jam} WIB</Text>
                </View>
              </View>
            ))
          ) : (
            <Text style={styles.emptyText}>Belum ada laporan dalam 7 hari terakhir.</Text>
          )}
        </ScrollView>
      </KeyboardAvoidingView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },

  /* ── Header – identik HomeScreen ── */
  header: {
    paddingBottom: 80,
    paddingHorizontal: 25,
    borderBottomLeftRadius: 40,
    borderBottomRightRadius: 40,
  },
  headerGreet: { color: 'rgba(255,255,255,0.7)', fontSize: 13, fontWeight: '500' },
  headerTitle: { color: '#fff', fontSize: 22, fontWeight: '800', marginTop: 2 },

  /* ── Content – identik HomeScreen ── */
  content: { marginTop: -50, paddingHorizontal: 20 },

  /* ── Card – identik HomeScreen ── */
  card: {
    backgroundColor: Colors.white,
    borderRadius: 24,
    padding: 20,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOpacity: 0.05,
    shadowRadius: 10,
    elevation: 3,
  },

  /* Judul card */
  cardTitleRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 6 },
  cardTitle: { fontSize: 17, fontWeight: '800', color: Colors.text, marginLeft: 8 },
  cardSubtitle: { fontSize: 13, color: Colors.textMuted, marginBottom: 18, lineHeight: 19 },

  /* Form label */
  formLabel: { fontSize: 13, fontWeight: '700', color: Colors.text, marginBottom: 8 },

  /* Textarea (.form-control) */
  textarea: {
    backgroundColor: '#f8fafc',
    borderWidth: 1.5,
    borderColor: '#e2e8f0',
    borderRadius: 14,
    padding: 12,
    fontSize: 14,
    color: Colors.text,
    minHeight: 100,
  },

  /* Foto placeholder (.foto-placeholder) */
  fotoPlaceholder: {
    width: '100%',
    padding: 30,
    borderWidth: 2.5,
    borderColor: '#cbd5e1',
    borderStyle: 'dashed',
    borderRadius: 14,
    backgroundColor: '#f8fafc',
    alignItems: 'center',
  },
  fotoPlaceholderTitle: { fontSize: 13, fontWeight: '700', color: '#94a3b8', marginTop: 8 },
  fotoPlaceholderSub:   { fontSize: 11, color: '#94a3b8', marginTop: 2 },

  /* Foto preview */
  fotoPreviewWrap: { position: 'relative', marginBottom: 0 },
  fotoPreview: {
    width: '100%',
    height: 200,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  fotoDelBtn: {
    position: 'absolute',
    top: 10,
    right: 10,
    backgroundColor: 'rgba(0,0,0,0.5)',
    width: 34,
    height: 34,
    borderRadius: 17,
    alignItems: 'center',
    justifyContent: 'center',
  },

  /* Tombol galeri (.gallery-btn) */
  galleryBtn: {
    width: '100%',
    paddingVertical: 10,
    borderWidth: 1.5,
    borderColor: '#e2e8f0',
    borderRadius: 12,
    backgroundColor: '#fff',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 8,
  },
  galleryBtnText: { fontSize: 13, color: '#475569', fontWeight: '600', marginLeft: 6 },

  /* Tombol kirim (.btn-primary-full) */
  btnPrimary: {
    borderRadius: 16,
    overflow: 'hidden',
    marginTop: 20,
    elevation: 4,
    shadowColor: Colors.primary,
    shadowOpacity: 0.25,
    shadowRadius: 8,
  },
  btnPrimaryGradient: {
    paddingVertical: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  btnPrimaryText: { color: '#fff', fontWeight: '800', fontSize: 16, marginLeft: 10 },

  /* ── Riwayat ── */
  sectionTitle: { fontSize: 16, fontWeight: '800', color: Colors.text, marginTop: 10, marginBottom: 12 },

  /* .laporan-item */
  laporanItem: {
    flexDirection: 'row',
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#f1f5f9',
    borderRadius: 16,
    padding: 15,
    marginBottom: 10,
  },
  laporanPhoto:   { width: 60, height: 60, borderRadius: 10, marginRight: 12 },
  laporanNoPhoto: {
    width: 60,
    height: 60,
    backgroundColor: '#e2e8f0',
    borderRadius: 10,
    marginRight: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  laporanInfo: { flex: 1 },
  laporanDate: { fontSize: 13, fontWeight: '700', color: Colors.text },
  laporanDesc: { fontSize: 12, color: '#475569', marginVertical: 3, lineHeight: 17 },
  laporanTime: { fontSize: 10, color: '#94a3b8' },

  emptyText: { textAlign: 'center', color: Colors.textMuted, marginTop: 20, marginBottom: 20 },
});
