# Konfigurasi GPS Permission untuk WebView APK (WebIntoApp)

## Prasyarat Wajib

### 1. HTTPS (SSL) — WAJIB
GPS/Geolocation **tidak akan berfungsi** tanpa HTTPS.
- Production harus pakai SSL (Let's Encrypt gratis)
- Tanpa HTTPS, `navigator.geolocation` tidak tersedia

### 2. Konfigurasi WebIntoApp / Custom WebView

#### Di WebIntoApp Dashboard:
- ✅ **Location Permission** → Enable
- ✅ **GPS Access** → Enable
- ✅ **Geolocation** → Enable
- ✅ **File Upload** → Enable (untuk foto & video)
- ✅ **Camera Access** → Enable

#### Di AndroidManifest.xml (jika custom build):
```xml
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.CAMERA" />
<uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE" />
<uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE" />
```

#### Di WebView Activity — KRITIS:
```java
webView.getSettings().setJavaScriptEnabled(true);
webView.getSettings().setGeolocationEnabled(true);  // ← WAJIB
webView.getSettings().setDomStorageEnabled(true);
webView.getSettings().setAllowFileAccess(true);
webView.getSettings().setMediaPlaybackRequiresUserGesture(false);

webView.setWebChromeClient(new WebChromeClient() {
    @Override
    public void onGeolocationPermissionsShowPrompt(String origin, GeolocationPermissions.Callback callback) {
        callback.invoke(origin, true, false); // Auto-grant untuk domain kita
    }
});
```

**Tanpa `onGeolocationPermissionsShowPrompt`, GPS tidak akan pernah berfungsi di WebView.**

#### Kotlin:
```kotlin
webView.settings.apply {
    javaScriptEnabled = true
    setGeolocationEnabled(true)
    domStorageEnabled = true
    allowFileAccess = true
    mediaPlaybackRequiresUserGesture = false
}

webView.webChromeClient = object : WebChromeClient() {
    override fun onGeolocationPermissionsShowPrompt(
        origin: String?,
        callback: GeolocationPermissions.Callback?
    ) {
        callback?.invoke(origin, true, false)
    }
}
```

### 3. Runtime Permission (Android 6.0+)

```java
if (ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
        != PackageManager.PERMISSION_GRANTED) {
    ActivityCompat.requestPermissions(this,
        new String[]{
            Manifest.permission.ACCESS_FINE_LOCATION,
            Manifest.permission.ACCESS_COARSE_LOCATION
        },
        LOCATION_PERMISSION_REQUEST_CODE);
}
```

---

## Behavior GPS di Aplikasi (Implementasi Saat Ini)

### Kebijakan: GPS WAJIB — Tidak Ada Input Manual

PIC Lapangan **tidak bisa** melewati step lokasi tanpa GPS aktif. Ini kebijakan perusahaan untuk validasi lokasi gerobak.

### Flow:
```
PIC buka form "Tambah UMKM"
    ↓
Step 3 "Lokasi" terbuka
    ↓
FULLSCREEN OVERLAY muncul — menutupi seluruh layar
    ↓
Auto-request GPS
    ↓
┌─ GPS Berhasil:
│   → Koordinat terisi otomatis (readOnly, tidak bisa diedit)
│   → Google Maps URL otomatis terisi
│   → Overlay hilang, PIC bisa lanjut ke step berikutnya
│
├─ GPS Ditolak (user deny permission):
│   → Overlay TETAP MUNCUL — tidak bisa dismiss
│   → Tampilkan instruksi cara reset permission
│   → Polling setiap 2 detik cek apakah permission berubah
│   → Tombol "MUAT ULANG HALAMAN" untuk force re-prompt
│   → PIC TIDAK BISA lanjut sampai GPS di-allow
│
├─ GPS Tidak Aktif (device GPS off):
│   → Overlay TETAP MUNCUL
│   → Instruksi nyalakan GPS
│   → Tombol retry
│
└─ Timeout:
    → Auto-retry sampai 5x (interval 2 detik)
    → Jika tetap gagal, tampilkan error + tombol retry
```

### Field yang Terpengaruh:
- `latitude` — **required, readOnly** (hanya dari GPS)
- `longitude` — **required, readOnly** (hanya dari GPS)
- `sharelock_url` — **required, readOnly** (auto-generate dari koordinat)

---

## Troubleshooting

| Masalah | Penyebab | Solusi |
|---|---|---|
| Overlay muncul terus, GPS tidak jalan | HTTP (bukan HTTPS) | Deploy dengan SSL |
| Permission dialog tidak muncul di WebView | `onGeolocationPermissionsShowPrompt` tidak di-override | Tambahkan di WebChromeClient |
| User deny → tidak bisa re-prompt | Browser cache permission "denied" | Overlay tetap block + instruksi reset + tombol reload |
| GPS timeout terus | Indoor / signal lemah | Minta PIC ke area terbuka, retry otomatis 5x |
| Koordinat tidak akurat | `enableHighAccuracy: false` | Sudah di-set `true` + timeout 30 detik |

---

## Checklist Sebelum Build APK

- [ ] Domain sudah HTTPS (SSL aktif)
- [ ] WebIntoApp: Location Permission = ON
- [ ] WebIntoApp: File Upload = ON
- [ ] WebIntoApp: Camera = ON
- [ ] Test: overlay GPS muncul saat buka form UMKM
- [ ] Test: setelah allow, koordinat terisi + overlay hilang
- [ ] Test: setelah deny, overlay tetap block + instruksi muncul
- [ ] Test: upload 5 foto berfungsi
- [ ] Test: upload video (max 50MB) berfungsi
- [ ] Test: submit UMKM berhasil tersimpan dengan koordinat
