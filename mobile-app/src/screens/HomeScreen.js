import React, { useState, useEffect } from 'react';
import {
  StyleSheet,
  View,
  Text,
  TouchableOpacity,
  ScrollView,
  Image,
  ActivityIndicator,
  Alert,
  RefreshControl,
  Dimensions
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import { LogOut, Clock, MapPin, CheckCircle2, XCircle, Camera, BellRing, Navigation, RefreshCw } from 'lucide-react-native';
import MapView, { Circle, Marker, PROVIDER_GOOGLE } from 'react-native-maps';
import * as Location from 'expo-location';
import { Colors } from '../theme/colors';
import apiClient from '../api/client';
import * as ImagePicker from 'expo-image-picker';

const { width } = Dimensions.get('window');

export default function HomeScreen({ user, onLogout }) {
  const insets = useSafeAreaInsets();
  const [currentTime, setCurrentTime] = useState(new Date());
  const [loading, setLoading] = useState(false);
  const [history, setHistory] = useState([]);
  const [todayAttendance, setTodayAttendance] = useState(null);
  const [refreshing, setRefreshing] = useState(false);

  // Location State
  const [userLocation, setUserLocation] = useState(null);
  const [distance, setDistance] = useState(null);
  const [isInRange, setIsInRange] = useState(false);
  const [locationError, setLocationError] = useState(null);

  const posData = {
    latitude: Number(user?.pos?.latitude) || -5.13245,
    longitude: Number(user?.pos?.longitude) || 119.48671,
    radius: Number(user?.pos?.radius) || 100,
    nama_pos: user?.pos?.nama_pos || 'POS Utama'
  };

  useEffect(() => {
    const timer = setInterval(() => setCurrentTime(new Date()), 1000);
    loadStatus();
    startLocationTracking();
    return () => clearInterval(timer);
  }, []);

  const loadStatus = async () => {
    try {
      const resp = await apiClient.get('/attendance/history');
      const data = resp.data.data;
      setHistory(data);

      const today = new Date().toISOString().split('T')[0];
      const todayData = data.find(a => a.tanggal === today);
      setTodayAttendance(todayData);
    } catch (error) {
      // Ignored for production
    }
  };

  const startLocationTracking = async () => {
    let { status } = await Location.requestForegroundPermissionsAsync();
    if (status !== 'granted') {
      setLocationError('Izin lokasi diperlukan untuk absensi.');
      return;
    }

    // Ambil lokasi awal
    let location = await Location.getCurrentPositionAsync({ accuracy: Location.Accuracy.High });
    updateUserRange(location.coords);

    // Track terus menerus
    await Location.watchPositionAsync(
      { accuracy: Location.Accuracy.High, distanceInterval: 5 },
      (loc) => {
        updateUserRange(loc.coords);
      }
    );
  };

  const updateUserRange = (coords) => {
    setUserLocation(coords);
    const dist = calculateDistance(
      coords.latitude,
      coords.longitude,
      posData.latitude,
      posData.longitude
    );
    setDistance(dist);
    setIsInRange(dist <= posData.radius);
  };

  const calculateDistance = (lat1, lon1, lat2, lon2) => {
    const R = 6371000; // Radius Bumi dalam meter
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
      Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await loadStatus();
    setRefreshing(false);
  };

  const takePhoto = async () => {
    const { status } = await ImagePicker.requestCameraPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Gagal', 'Izin kamera diperlukan untuk absensi.');
      return null;
    }

    const result = await ImagePicker.launchCameraAsync({
      allowsEditing: true,
      aspect: [1, 1],
      quality: 0.5,
      base64: true,
    });

    if (!result.canceled) {
      return result.assets[0].base64;
    }
    return null;
  };

  const handleClockIn = async () => {
    if (!isInRange) {
      Alert.alert('Gagal', 'Anda berada di luar radius POS penugasan.');
      return;
    }

    const base64 = await takePhoto();
    if (!base64) return;

    setLoading(true);
    try {
      await apiClient.post('/attendance/clock-in', {
        latitude: userLocation.latitude,
        longitude: userLocation.longitude,
        foto: `data:image/jpeg;base64,${base64}`,
      });
      loadStatus();
      Alert.alert('Sukses', '✔ Ceklok MASUK berhasil.');
    } catch (e) {
      Alert.alert('Gagal', e.response?.data?.message || 'Gagal melakukan ceklok.');
    } finally {
      setLoading(false);
    }
  };

  const handleClockOut = async () => {
    if (!isInRange) {
      Alert.alert('Gagal', 'Anda berada di luar radius POS penugasan.');
      return;
    }

    const base64 = await takePhoto();
    if (!base64) return;

    setLoading(true);
    try {
      await apiClient.post('/attendance/clock-out', {
        latitude: userLocation.latitude,
        longitude: userLocation.longitude,
        foto: `data:image/jpeg;base64,${base64}`,
      });
      loadStatus();
      Alert.alert('Sukses', '✔ Ceklok PULANG berhasil.');
    } catch (e) {
      Alert.alert('Gagal', e.response?.data?.message || 'Gagal melakukan ceklok.');
    } finally {
      setLoading(false);
    }
  };

  const formattedDate = currentTime.toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });

  return (
    <View style={styles.container}>
      <LinearGradient
        colors={[Colors.primaryDark, Colors.primary]}
        style={[styles.header, { paddingTop: insets.top + 20 }]}
      >
        <View style={styles.topRow}>
          <View>
            <Text style={styles.greeting}>Selamat Bekerja,</Text>
            <Text style={styles.userName}>{user?.nama}</Text>
            <View style={styles.posBadge}>
              <MapPin size={12} color="#fff" opacity={0.8} />
              <Text style={styles.posText}>POS: {posData.nama_pos}</Text>
            </View>
          </View>
        </View>
      </LinearGradient>

      <ScrollView
        style={styles.content}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        showsVerticalScrollIndicator={false}
      >
        {/* Clock Card */}
        <View style={styles.card}>
          <Text style={styles.clockText}>
            {currentTime.toLocaleTimeString('id-ID', { hour12: false })}
          </Text>
          <Text style={styles.dateText}>{formattedDate}</Text>

          <View style={styles.actionRow}>
            <TouchableOpacity
              style={[styles.absenBtn, styles.btnIn, (todayAttendance?.jam_masuk || !isInRange) && styles.btnDisabled]}
              onPress={handleClockIn}
              disabled={!!todayAttendance?.jam_masuk || loading || !isInRange}
            >
              <Camera size={26} color="#fff" />
              <Text style={styles.btnLabel}>MASUK</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={[styles.absenBtn, styles.btnOut, (!todayAttendance?.jam_masuk || todayAttendance?.jam_pulang || !isInRange) && styles.btnDisabled]}
              onPress={handleClockOut}
              disabled={!todayAttendance?.jam_masuk || !!todayAttendance?.jam_pulang || loading || !isInRange}
            >
              <LogOut size={26} color="#fff" />
              <Text style={styles.btnLabel}>PULANG</Text>
            </TouchableOpacity>
          </View>

          {/* Location Status */}
          <View style={[styles.statusBox, isInRange ? styles.statusBlue : styles.statusRed]}>
            {isInRange ? (
              <CheckCircle2 size={16} color={Colors.accent} />
            ) : (
              <XCircle size={16} color={Colors.primary} />
            )}
            <Text style={[styles.statusText, isInRange ? { color: Colors.accent } : { color: Colors.primary }]}>
              {isInRange ? 'Dalam Jangkauan POS' : `Luar Radius (${Math.round(distance - posData.radius)}m)`}
            </Text>
            <TouchableOpacity onPress={startLocationTracking} style={{ marginLeft: 'auto' }}>
              <RefreshCw size={14} color={Colors.textMuted} />
            </TouchableOpacity>
          </View>
        </View>

        {/* Maps Section */}
        <View style={styles.cardMap}>
          <View style={styles.mapHeader}>
            <Navigation size={18} color={Colors.primary} />
            <Text style={styles.mapTitle}>Titik POS Penugasan</Text>
            <View style={styles.radiusBadge}>
              <Text style={styles.radiusBadgeText}>{posData.radius}m</Text>
            </div>
          </View>
          {posData.latitude && posData.longitude && (
            <MapView
              style={styles.map}
              region={{
                latitude: posData.latitude,
                longitude: posData.longitude,
                latitudeDelta: 0.005,
                longitudeDelta: 0.005,
              }}
              scrollEnabled={false}
            >
              <Circle
                center={{ latitude: posData.latitude, longitude: posData.longitude }}
                radius={posData.radius}
                fillColor="rgba(155, 28, 28, 0.1)"
                strokeColor="rgba(155, 28, 28, 0.3)"
                strokeWidth={2}
              />
              <Marker coordinate={{ latitude: posData.latitude, longitude: posData.longitude }} />
              {userLocation && (
                <Marker coordinate={{ latitude: userLocation.latitude, longitude: userLocation.longitude }}>
                  <View style={styles.userMarker} />
                </Marker>
              )}
            </MapView>
          )}
          <Text style={styles.mapHint}>
            Dekati area POS untuk membuka kunci tombol presensi.
          </Text>
        </View>

        {/* Today's Detail Card */}
        {todayAttendance && (
          <View style={styles.card}>
            <Text style={styles.sectionTitleSmall}>Detail Presensi Hari Ini</Text>
            <View style={styles.detailRow}>
              <View style={styles.detailCol}>
                <View style={styles.photoFrame}>
                  {todayAttendance.foto_masuk ? (
                    <Image source={{ uri: `${apiClient.defaults.baseURL.replace('/api/v1', '')}/storage/${todayAttendance.foto_masuk}` }} style={styles.presencePhoto} />
                  ) : (
                    <Camera size={20} color="#cbd5e1" />
                  )}
                </View>
                <Text style={styles.timeLabel}>Masuk</Text>
                <Text style={styles.timeVal}>{todayAttendance.jam_masuk}</Text>
                <View style={[styles.badge, todayAttendance.terlambat === 'Ya' ? styles.badgeRed : styles.badgeGreen]}>
                  <Text style={styles.badgeText}>{todayAttendance.terlambat === 'Ya' ? 'Terlambat' : 'Tepat Waktu'}</Text>
                </View>
              </View>

              <View style={styles.dividerLarge} />

              <View style={styles.detailCol}>
                <View style={styles.photoFrame}>
                  {todayAttendance.foto_pulang ? (
                    <Image source={{ uri: `${apiClient.defaults.baseURL.replace('/api/v1', '')}/storage/${todayAttendance.foto_pulang}` }} style={styles.presencePhoto} />
                  ) : (
                    <Camera size={20} color="#cbd5e1" />
                  )}
                </View>
                <Text style={styles.timeLabel}>Pulang</Text>
                <Text style={styles.timeVal}>{todayAttendance.jam_pulang || '--:--'}</Text>
                {todayAttendance.jam_pulang && (
                  <View style={[styles.badge, todayAttendance.cepat_pulang === 'Ya' ? styles.badgeRed : styles.badgeGreen]}>
                    <Text style={styles.badgeText}>{todayAttendance.cepat_pulang === 'Ya' ? 'Cepat Pulang' : 'Tepat Waktu'}</Text>
                  </View>
                )}
              </View>
            </View>
          </View>
        )}

        {/* History List */}
        <Text style={styles.sectionTitle}>Riwayat Terakhir (7 Hari)</Text>
        {history.filter(item => item.tanggal !== new Date().toISOString().split('T')[0]).map((item, idx) => (
          <View key={idx} style={styles.historyCard}>
            <View style={styles.histPhotos}>
              <View style={styles.histPhotoBox}>
                {item.foto_masuk && <Image source={{ uri: `${apiClient.defaults.baseURL.replace('/api/v1', '')}/storage/${item.foto_masuk}` }} style={styles.histImg} />}
              </View>
              <View style={styles.histPhotoBox}>
                {item.foto_pulang && <Image source={{ uri: `${apiClient.defaults.baseURL.replace('/api/v1', '')}/storage/${item.foto_pulang}` }} style={styles.histImg} />}
              </View>
            </View>
            <View style={styles.histInfo}>
              <Text style={styles.histDateText}>{item.tanggal}</Text>
              <Text style={styles.histTimeText}>{item.jam_masuk} - {item.jam_pulang || '--:--'}</Text>
            </View>
            <View style={[styles.histStatusBadge, item.terlambat === 'Ya' ? styles.bgRed : styles.bgGreen]}>
              <Text style={styles.histStatusText}>{item.terlambat === 'Ya' ? 'T' : 'O'}</Text>
            </View>
          </View>
        ))}

        <View style={{ height: 100 }} />
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  header: {
    paddingTop: 60,
    paddingBottom: 70,
    paddingHorizontal: 25,
    borderBottomLeftRadius: 40,
    borderBottomRightRadius: 40,
  },
  topRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  greeting: { color: 'rgba(255,255,255,0.7)', fontSize: 13, fontWeight: '500' },
  userName: { color: '#fff', fontSize: 24, fontWeight: '800' },
  posBadge: { flexDirection: 'row', alignItems: 'center', marginTop: 5 },
  posText: { color: 'rgba(255,255,255,0.8)', fontSize: 12, fontWeight: '600', marginLeft: 5 },

  content: { marginTop: -40, paddingHorizontal: 20 },
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
  clockText: { fontSize: 44, fontWeight: '900', color: Colors.primaryDark, textAlign: 'center', letterSpacing: -1 },
  dateText: { textAlign: 'center', color: Colors.textMuted, fontWeight: '700', fontSize: 13 },

  actionRow: { flexDirection: 'row', marginTop: 20 },
  absenBtn: { flex: 1, height: 90, borderRadius: 20, justifyContent: 'center', alignItems: 'center', marginHorizontal: 5 },
  btnIn: { backgroundColor: Colors.primary },
  btnOut: { backgroundColor: '#1e293b' },
  btnDisabled: { opacity: 0.3 },
  btnLabel: { color: '#fff', fontSize: 13, fontWeight: '800', marginTop: 6 },

  statusBox: { marginTop: 15, padding: 12, borderRadius: 12, flexDirection: 'row', alignItems: 'center', gap: 8 },
  statusRed: { backgroundColor: '#fef2f2', borderWidth: 1, borderColor: '#fecaca' },
  statusBlue: { backgroundColor: '#eff6ff', borderWidth: 1, borderColor: '#bfdbfe' },
  statusText: { fontSize: 12, fontWeight: '800' },

  cardMap: { backgroundColor: '#fff', borderRadius: 24, padding: 15, marginBottom: 15, elevation: 3 },
  mapHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 12, gap: 8 },
  mapTitle: { fontSize: 14, fontWeight: '800', color: Colors.text },
  radiusBadge: { backgroundColor: '#fee2e2', paddingHorizontal: 8, paddingVertical: 2, borderRadius: 6 },
  radiusBadgeText: { fontSize: 10, fontWeight: '800', color: Colors.primary },
  map: { width: '100%', height: 180, borderRadius: 15 },
  userMarker: { width: 14, height: 14, borderRadius: 7, backgroundColor: Colors.accent, borderWidth: 3, borderColor: '#fff' },
  mapHint: { fontSize: 11, color: Colors.textMuted, textAlign: 'center', marginTop: 10, fontStyle: 'italic' },

  sectionTitleSmall: { fontSize: 14, fontWeight: '800', color: Colors.text, marginBottom: 15, textAlign: 'center' },
  detailRow: { flexDirection: 'row', alignItems: 'center' },
  detailCol: { flex: 1, alignItems: 'center' },
  photoFrame: { width: 65, height: 65, borderRadius: 32.5, backgroundColor: '#f1f5f9', justifyContent: 'center', alignItems: 'center', marginBottom: 10, overflow: 'hidden', borderWidth: 2, borderColor: '#e2e8f0' },
  presencePhoto: { width: '100%', height: '100%' },
  timeLabel: { fontSize: 11, color: Colors.textMuted, fontWeight: '600' },
  timeVal: { fontSize: 16, fontWeight: '800', color: Colors.text, marginVertical: 2 },
  badge: { paddingHorizontal: 10, paddingVertical: 2, borderRadius: 8 },
  badgeGreen: { backgroundColor: '#dcfce7' },
  badgeRed: { backgroundColor: '#fee2e2' },
  badgeText: { fontSize: 9, fontWeight: '800', color: Colors.text },
  dividerLarge: { width: 1, height: 80, backgroundColor: '#e2e8f0' },

  sectionTitle: { fontSize: 17, fontWeight: '800', color: Colors.text, marginVertical: 15 },
  historyCard: {
    flexDirection: 'row',
    backgroundColor: '#fff',
    padding: 12,
    borderRadius: 20,
    marginBottom: 10,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#f1f5f9'
  },
  histPhotos: { flexDirection: 'row', gap: 5, marginRight: 15 },
  histPhotoBox: { width: 45, height: 45, borderRadius: 8, backgroundColor: '#f1f5f9', overflow: 'hidden' },
  histImg: { width: '100%', height: '100%' },
  histInfo: { flex: 1 },
  histDateText: { fontSize: 14, fontWeight: '700', color: Colors.text },
  histTimeText: { fontSize: 11, color: Colors.textMuted, marginTop: 2 },
  histStatusBadge: { width: 24, height: 24, borderRadius: 12, justifyContent: 'center', alignItems: 'center' },
  bgGreen: { backgroundColor: '#dcfce7' },
  bgRed: { backgroundColor: '#fee2e2' },
  histStatusText: { fontSize: 10, fontWeight: '900', color: Colors.text },
});
