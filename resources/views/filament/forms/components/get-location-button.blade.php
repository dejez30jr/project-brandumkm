<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            loading: false,
            success: false,
            errorMsg: null,
            lat: null,
            lng: null,

            init() {
                // RN WebView bridge: listen for GPS result from native
                window.addEventListener('__gpsOK', (e) => {
                    this.onSuccess(e.detail.coords.latitude, e.detail.coords.longitude);
                });
                window.addEventListener('__gpsFail', (e) => {
                    this.onError({ code: e.detail.code, message: e.detail.message });
                });
            },

            onSuccess(latitude, longitude) {
                this.lat = parseFloat(latitude).toFixed(8);
                this.lng = parseFloat(longitude).toFixed(8);
                $wire.$set('data.latitude', this.lat);
                $wire.$set('data.longitude', this.lng);
                $wire.$set('data.sharelock_url', 'https://www.google.com/maps?q=' + this.lat + ',' + this.lng);
                this.loading = false;
                this.success = true;
                this.errorMsg = null;
                console.log('[GPS] success:', this.lat, this.lng);
            },

            onError(error) {
                this.loading = false;
                this.success = false;
                console.log('[GPS] error code:', error.code, error.message);
                if (error.code === 1) {
                    this.errorMsg = 'Izin lokasi diblokir. Aktifkan izin lokasi untuk website ini di browser atau pengaturan HP.';
                } else if (error.code === 2) {
                    this.errorMsg = 'Lokasi tidak tersedia. Pastikan GPS atau Location Service aktif.';
                } else if (error.code === 3) {
                    this.errorMsg = 'Pengambilan lokasi terlalu lama. Pindah ke area terbuka dan coba lagi.';
                } else {
                    this.errorMsg = 'Gagal mengambil lokasi (kode: ' + error.code + ').';
                }
            },

            getLocation() {
                this.errorMsg = null;
                this.loading = true;
                this.success = false;

                console.log('[GPS] isSecureContext:', window.isSecureContext);
                console.log('[GPS] geolocation available:', !!navigator.geolocation);

                if (!window.isSecureContext) {
                    this.onError({ code: 0, message: 'non-secure' });
                    this.errorMsg = 'Website harus menggunakan HTTPS agar lokasi bisa diakses.';
                    return;
                }

                if (!navigator.geolocation) {
                    this.onError({ code: 0, message: 'not supported' });
                    this.errorMsg = 'Browser tidak mendukung geolocation.';
                    return;
                }

                // If RN bridge is ready, postMessage to native
                if (window.ReactNativeWebView || window.__rnBridgeReady) {
                    try { window.ReactNativeWebView.postMessage(JSON.stringify({ type: 'GPS_REQUEST' })); } catch(e) {}
                    return; // result comes via __gpsOK event
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => this.onSuccess(position.coords.latitude, position.coords.longitude),
                    (error) => this.onError(error),
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            }
        }"
        class="space-y-3"
    >
        <button
            type="button"
            x-on:click="getLocation()"
            x-bind:disabled="loading"
            class="fi-btn fi-btn-size-md fi-color-custom fi-btn-color-primary fi-color-primary"
            style="background-color: #f97316; color: white; padding: 8px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;"
        >
            <svg x-show="!loading" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span x-show="!loading">📍 Ambil Lokasi GPS</span>
            <span x-show="loading">Mengambil lokasi...</span>
        </button>

        <div x-show="success" class="flex items-center gap-2 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>✅ Lokasi berhasil: <strong x-text="lat"></strong>, <strong x-text="lng"></strong></span>
        </div>

        <div x-show="errorMsg" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
            <p x-text="errorMsg"></p>
            <p class="mt-1 text-xs text-red-500">
                Buka console browser (F12) untuk melihat detail error GPS.
            </p>
        </div>
    </div>
</x-dynamic-component>
