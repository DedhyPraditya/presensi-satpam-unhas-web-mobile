import { StyleSheet, View, Text, TouchableOpacity, ScrollView, Image } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import { User, Shield, Briefcase, LogOut, MapPin } from 'lucide-react-native';
import { Colors } from '../theme/colors';

export default function ProfileScreen({ user, onLogout }) {
  const insets = useSafeAreaInsets();
  const posData = user?.pos || { nama_pos: 'Personnel Security' };

  return (
    <View style={styles.container}>
      {/* HEADER KONSISTEN DENGAN HOMESCREEN */}
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

      <ScrollView 
        style={styles.content} 
        showsVerticalScrollIndicator={false}
      >
        {/* OVERLAPPING PROFILE CARD (Sesuai Gambar) */}
        <View style={styles.profileCard}>
          <View style={styles.avatarContainer}>
            <Image 
              source={{ uri: 'https://raw.githubusercontent.com/DedhyPraditya/logo/a42c1af7af478a41c31a69c77f6c270aa556b2f5/logo_unhas.png' }}
              style={styles.avatarImage}
              resizeMode="contain"
            />
          </View>
          <Text style={styles.userNameLarge}>{user?.nama}</Text>
          <View style={styles.badgeLarge}>
            <Text style={styles.badgeTextLarge}>{posData.nama_pos}</Text>
          </View>

          {/* DETAIL INFO LIST */}
          <View style={styles.infoList}>
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>NIP</Text>
              <Text style={styles.infoValue}>{user?.nip}</Text>
            </View>
            
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Unit Kerja</Text>
              <Text style={styles.infoValue}>Personnel Security UNHAS</Text>
            </View>

            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Jenis Kerja</Text>
              <Text style={styles.infoValue}>
                {user?.jenis_kerja === 'shift' ? 'Sistem Shift' : 'Non-Shift (Reguler)'}
              </Text>
            </View>

            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Status Akun</Text>
              <Text style={[styles.infoValue, { color: '#22c55e' }]}>Terverifikasi</Text>
            </View>
          </View>
        </View>

        <TouchableOpacity style={styles.logoutBtn} onPress={onLogout}>
          <LogOut size={20} color={Colors.primary} />
          <Text style={styles.logoutText}>Keluar Aplikasi</Text>
        </TouchableOpacity>

        <Text style={styles.versionText}>App Version 2.2.0 (Build 54)</Text>
        <View style={{ height: 100 }} />
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f8fafc' },
  header: {
    paddingBottom: 80,
    paddingHorizontal: 24,
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

  content: {
    flex: 1,
    marginTop: -50,
    paddingHorizontal: 20,
  },
  profileCard: {
    backgroundColor: '#fff',
    borderRadius: 30,
    padding: 24,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOpacity: 0.1,
    shadowRadius: 10,
    elevation: 5,
    marginBottom: 20,
  },
  avatarContainer: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: '#fff',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 15,
    borderWidth: 1,
    borderColor: '#f1f5f9',
    shadowColor: '#000',
    shadowOpacity: 0.05,
    shadowRadius: 15,
    elevation: 2,
  },
  avatarImage: {
    width: '80%',
    height: '80%',
  },
  userNameLarge: {
    fontSize: 22,
    fontWeight: '900',
    color: '#1e293b',
    textAlign: 'center',
  },
  badgeLarge: {
    backgroundColor: '#e0f2fe',
    paddingHorizontal: 16,
    paddingVertical: 6,
    borderRadius: 20,
    marginTop: 10,
    marginBottom: 25,
  },
  badgeTextLarge: {
    color: '#0369a1',
    fontSize: 13,
    fontWeight: '700',
  },

  infoList: {
    width: '100%',
    borderTopWidth: 1,
    borderTopColor: '#f1f5f9',
    paddingTop: 10,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#f8fafc',
  },
  infoLabel: {
    fontSize: 14,
    color: '#64748b',
    fontWeight: '500',
  },
  infoValue: {
    fontSize: 14,
    color: '#1e293b',
    fontWeight: '700',
    textAlign: 'right',
    flex: 1,
    marginLeft: 20,
  },

  logoutBtn: {
    backgroundColor: '#fff',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 18,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: '#fee2e2',
    marginBottom: 15,
  },
  logoutText: {
    color: Colors.primary,
    fontWeight: '800',
    marginLeft: 10,
  },
  versionText: {
    textAlign: 'center',
    color: '#94a3b8',
    fontSize: 12,
    marginTop: 10,
  },
});
