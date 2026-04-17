import React, { useState, useEffect } from 'react';
import { StyleSheet, View, Text, TextInput, TouchableOpacity, ScrollView, Image, ActivityIndicator, Alert } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import { Camera, Send, AlertTriangle, Image as ImageIcon, CheckCircle, Clock, MapPin } from 'lucide-react-native';
import * as ImagePicker from 'expo-image-picker';
import { Colors } from '../theme/colors';
import apiClient from '../api/client';

export default function LaporanScreen({ user }) {
  const insets = useSafeAreaInsets();
  const posData = user?.pos || { nama_pos: 'Personnel Security' };
  const [deskripsi, setDeskripsi] = useState('');
  const [foto, setFoto] = useState(null);
  const [loading, setLoading] = useState(false);
  const [history, setHistory] = useState([]);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    fetchHistory();
  }, []);

  const fetchHistory = async () => {
    try {
      const response = await apiClient.get('/reports');
      setHistory(response.data.data);
    } catch (error) {
      // Quiet fail in production
    }
  };

  const takePhoto = async () => {
    const { status } = await ImagePicker.requestCameraPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Gagal', 'Izin kamera diperlukan.');
      return;
    }

    const result = await ImagePicker.launchCameraAsync({
      allowsEditing: true,
      aspect: [4, 3],
      quality: 0.5,
      base64: true,
    });

    if (!result.canceled) {
      setFoto(result.assets[0]);
    }
  };

  const pickImage = async () => {
    const result = await ImagePicker.launchImageLibraryAsync({
      allowsEditing: true,
      aspect: [4, 3],
      quality: 0.5,
      base64: true,
    });

    if (!result.canceled) {
      setFoto(result.assets[0]);
    }
  };

  const handleSubmit = async () => {
    if (!deskripsi) {
      Alert.alert('Error', 'Harap isi deskripsi kejadian.');
      return;
    }

    setLoading(true);
    try {
      await apiClient.post('/reports', {
        judul: 'Laporan Lapangan Personel',
        deskripsi: deskripsi,
        foto: foto ? `data:image/jpeg;base64,${foto.base64}` : null,
      });

      Alert.alert('Sukses', 'Laporan kejadian berhasil dikirim ke Admin.');
      setDeskripsi('');
      setFoto(null);
      fetchHistory();
    } catch (error) {
      Alert.alert('Gagal', error.response?.data?.message || 'Gagal mengirim laporan.');
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
        <View style={styles.topHeaderContent}>
          <Text style={styles.greeting}>Selamat Bekerja,</Text>
          <Text style={styles.userNameHeader}>{user?.nama}</Text>
          <View style={styles.posBadgeHeader}>
            <MapPin size={12} color="#fff" opacity={0.8} />
            <Text style={styles.posTextHeader}>POS: {posData.nama_pos}</Text>
          </View>
        </View>
      </LinearGradient>

      <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.formCard}>
          <Text style={styles.label}>Deskripsi Kejadian</Text>
          <TextInput
            style={styles.textArea}
            placeholder="Jelaskan apa yang terjadi secara detail..."
            multiline
            numberOfLines={4}
            value={deskripsi}
            onChangeText={setDeskripsi}
            textAlignVertical="top"
          />

          <View style={styles.photoSection}>
            <Text style={styles.label}>Foto Bukti</Text>
            {foto ? (
              <View style={styles.previewContainer}>
                <Image source={{ uri: foto.uri }} style={styles.preview} />
                <TouchableOpacity style={styles.removeBtn} onPress={() => setFoto(null)}>
                  <Text style={{ color: '#fff', fontWeight: '800' }}>X</Text>
                </TouchableOpacity>
              </View>
            ) : (
              <View style={styles.photoActions}>
                <TouchableOpacity style={styles.photoBtn} onPress={takePhoto}>
                  <Camera size={24} color={Colors.textMuted} />
                  <Text style={styles.photoBtnText}>Kamera</Text>
                </TouchableOpacity>
                <TouchableOpacity style={styles.photoBtn} onPress={pickImage}>
                  <ImageIcon size={24} color={Colors.textMuted} />
                  <Text style={styles.photoBtnText}>Galeri</Text>
                </TouchableOpacity>
              </View>
            )}
          </View>

          <TouchableOpacity 
            style={[styles.submitBtn, loading && { opacity: 0.7 }]} 
            onPress={handleSubmit}
            disabled={loading}
          >
            {loading ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <>
                <Send size={18} color="#fff" style={{ marginRight: 10 }} />
                <Text style={styles.submitText}>Kirim Laporan</Text>
              </>
            )}
          </TouchableOpacity>
        </View>

        <Text style={styles.sectionTitle}>Riwayat Laporan Seminggu</Text>
        {history.map((item, idx) => (
          <View key={idx} style={styles.historyItem}>
            <View style={styles.historyContent}>
              <View style={styles.historyInfo}>
                <View style={styles.historyHeader}>
                  <View style={styles.timeLabel}>
                    <Clock size={12} color={Colors.textMuted} />
                    <Text style={styles.timeText}>{item.tanggal} • {item.jam}</Text>
                  </View>
                  <View style={styles.statusBadge}>
                    <CheckCircle size={10} color="#22c55e" />
                    <Text style={styles.statusText}>Terkirim</Text>
                  </View>
                </View>
                <Text style={styles.historyDesc}>{item.deskripsi}</Text>
              </View>
              {item.foto && (
                <View style={styles.historyPhotoBox}>
                  <Image 
                    source={{ uri: `${apiClient.defaults.baseURL.replace('/api/v1', '')}/storage/${item.foto}` }} 
                    style={styles.historyImg} 
                  />
                </View>
              )}
            </View>
          </View>
        ))}
        
        {history.length === 0 && (
          <View style={styles.emptyState}>
            <Text style={styles.emptyText}>Belum ada laporan kejadian.</Text>
          </View>
        )}
        <View style={{ height: 100 }} />
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f8fafc' },
  header: {
    paddingBottom: 80,
    paddingHorizontal: 25,
    borderBottomLeftRadius: 40,
    borderBottomRightRadius: 40,
  },
  topHeaderContent: {
    alignItems: 'flex-start',
  },
  greeting: {
    color: 'rgba(255,255,255,0.7)',
    fontSize: 13,
    fontWeight: '500',
  },
  userNameHeader: {
    color: '#fff',
    fontSize: 24,
    fontWeight: '800',
    marginTop: 2,
  },
  posBadgeHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 6,
    backgroundColor: 'rgba(255,255,255,0.15)',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  posTextHeader: {
    color: '#fff',
    fontSize: 11,
    fontWeight: '600',
    marginLeft: 6,
  },
  content: { flex: 1, padding: 20, marginTop: -50 },
  formCard: {
    backgroundColor: '#fff',
    borderRadius: 24,
    padding: 20,
    marginTop: 0,
    shadowColor: '#000',
    shadowOpacity: 0.05,
    shadowRadius: 10,
    elevation: 3,
    marginBottom: 25,
  },
  label: { fontSize: 13, fontWeight: '700', color: Colors.text, marginBottom: 8 },
  textArea: {
    backgroundColor: '#f1f5f9',
    borderRadius: 15,
    padding: 15,
    height: 120,
    fontSize: 14,
    color: Colors.text,
  },
  photoSection: { marginTop: 20 },
  photoActions: { flexDirection: 'row', gap: 15 },
  photoBtn: {
    flex: 1,
    height: 80,
    backgroundColor: '#f1f5f9',
    borderRadius: 15,
    borderWidth: 2,
    borderColor: '#e2e8f0',
    borderStyle: 'dashed',
    justifyContent: 'center',
    alignItems: 'center',
    gap: 5,
  },
  photoBtnText: { fontSize: 12, fontWeight: '600', color: Colors.textMuted },
  previewContainer: { position: 'relative' },
  preview: { width: '100%', height: 200, borderRadius: 15 },
  removeBtn: {
    position: 'absolute',
    top: 10,
    right: 10,
    backgroundColor: 'rgba(0,0,0,0.5)',
    width: 30,
    height: 30,
    borderRadius: 15,
    justifyContent: 'center',
    alignItems: 'center',
  },
  submitBtn: {
    backgroundColor: Colors.primary,
    height: 55,
    borderRadius: 15,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 25,
  },
  submitText: { color: '#fff', fontSize: 16, fontWeight: '800' },
  sectionTitle: { fontSize: 16, fontWeight: '800', color: Colors.text, marginBottom: 15 },
  historyItem: {
    backgroundColor: '#fff',
    borderRadius: 18,
    padding: 15,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  historyContent: {
    flexDirection: 'row',
  },
  historyInfo: {
    flex: 1,
    marginRight: 10,
  },
  historyPhotoBox: {
    width: 60,
    height: 60,
    borderRadius: 12,
    backgroundColor: '#f1f5f9',
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  historyImg: {
    width: '100%',
    height: '100%',
  },
  historyHeader: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 8 },
  timeLabel: { flexDirection: 'row', alignItems: 'center', gap: 5 },
  timeText: { fontSize: 11, color: Colors.textMuted, fontWeight: '600' },
  statusBadge: { 
    flexDirection: 'row', 
    alignItems: 'center', 
    gap: 4, 
    backgroundColor: '#f0fdf4', 
    paddingHorizontal: 8, 
    paddingVertical: 2, 
    borderRadius: 8 
  },
  statusText: { fontSize: 10, fontWeight: '800', color: '#166534' },
  historyDesc: { fontSize: 13, color: Colors.text, lineHeight: 18 },
  emptyState: { padding: 40, alignItems: 'center' },
  emptyText: { color: Colors.textMuted, fontSize: 13, fontStyle: 'italic' },
});
