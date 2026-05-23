# Konfigurasi GPS Permission untuk WebView APK (WebIntoApp)

## Prasyarat Wajib

### 1. HTTPS (SSL) — WAJIB
GPS/Geolocation **tidak akan berfungsi** tanpa HTTPS. Ini adalah requirement dari browser/WebView.
- Production harus pakai SSL (Let's Encrypt gratis)
- Tanpa HTTPS, `navigator.geolocation` akan return error atau tidak tersedia sama sekali

### 2. Konfigurasi WebIntoApp / Custom WebView

Jika menggunakan **WebIntoApp** (webintoapp.com), pastikan setting berikut diaktifkan:

#### Di WebIntoApp Dashboard:
- ✅ **Location Permission** → Enable
- ✅ **GPS Access** → Enable  
- ✅ **Geolocation** → Enable
- ✅ **File Upload** → Enable (untuk foto & video)
- ✅ **Camera Access** → Enable (jika mau langsung foto dari kamera)

#### Di AndroidManifest.xml (jika custom build):
```xml
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.CAMERA" />
<uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE" />
<uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE" />
```

#### Di WebView Activity (Java/Kotlin) — KRITIS:
```java
// WebView settings
webView.getSettings().setJavaScriptEnabled(true);
webView.getSettings().setGeolocationEnabled(true);  // ← WAJIB
webView.getSettings().setDomStorageEnabled(true);
webView.getSettings().setAllowFileAccess(true);
webView.getSettings().setMediaPlaybackRequiresUserGesture(false);

// WebChromeClient — WAJIB untuk handle permission prompt
webView.setWebChromeClient(new WebChromeClient() {
    @Override
    public void onGeolocationPermissionsShowPrompt(String origin, GeolocationPermissions.Callback callback) {
        // Auto-grant geolocation permission untuk domain kita
        callback.invoke(origin, true, false);
    }
});
```

**Tanpa `onGeolocationPermissionsShowPrompt`, GPS tidak akan pernah muncul permission dialog di WebView.**

#### Kotlin equivalent:
```kotlin
webView.settings.apply {
    javaScriptEnabled = true
    setGeolocationEnabled(true)  // ← WAJIB
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

APK juga harus request runtime permission saat pertama kali dibuka:

```java
// Di Activity onCreate atau saat pertama kali user buka fitur lokasi
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

## Troubleshooting

| Masalah | Penyebab | Solusi |
|---|---|---|
| GPS tidak muncul permission dialog | `onGeolocationPermissionsShowPrompt` tidak di-override | Tambahkan WebChromeClient dengan override method tersebut |
| GPS error "Izin ditolak" | User deny permission di Android | Tampilkan instruksi: Pengaturan > Aplikasi > [Nama App] > Izin > Lokasi |
| GPS error "Lokasi tidak tersedia" | GPS device mati | Tampilkan instruksi: Nyalakan GPS di notification bar |
| GPS tidak berfungsi sama sekali | HTTP (bukan HTTPS) | Deploy dengan SSL. Geolocation API wajib HTTPS. |
| GPS timeout | Signal lemah / indoor | Retry otomatis (sudah ada di kode, max 3x). Fallback: input manual. |

---

## Flow GPS di Aplikasi

```
User buka form UMKM
    ↓
Step 3 "Lokasi" terbuka
    ↓
Auto-detect GPS (delay 800ms)
    ↓
┌─ Berhasil → latitude/longitude terisi otomatis, map preview muncul
│
└─ Gagal → 
    ├─ Retry otomatis (max 3x untuk timeout)
    ├─ Tampilkan error message yang jelas
    ├─ Tombol "Ambil Ulang Lokasi" muncul
    └─ User bisa isi manual (field latitude/longitude editable)
```

---

## Checklist Sebelum Build APK

- [ ] Domain sudah HTTPS (SSL aktif)
- [ ] WebIntoApp: Location Permission = ON
- [ ] WebIntoApp: File Upload = ON
- [ ] Test di HP Android: permission dialog muncul saat pertama buka
- [ ] Test di HP Android: setelah allow, koordinat terisi otomatis
- [ ] Test di HP Android: jika deny, pesan error jelas + bisa isi manual
- [ ] Test upload foto dari kamera langsung
- [ ] Test upload video dari galeri
