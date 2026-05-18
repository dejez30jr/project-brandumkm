<x-filament-panels::page>
    <div class="flex flex-col items-center justify-center p-8 text-center bg-white rounded-xl shadow-sm dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
        <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-full text-amber-600 dark:text-amber-400 mb-4">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </div>
        
        <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Backup Seluruh Data Aplikasi</h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
            Fitur ini akan mengompres seluruh data di database beserta seluruh berkas media (gambar UMKM, design, dan video) ke dalam satu file ZIP yang aman.
        </p>

        <div class="mt-6">
            <x-filament::button 
                wire:click="downloadBackup" 
                color="warning" 
                icon="heroicon-m-arrow-down-tray"
                size="lg"
            >
                Mulai Unduh Backup (.ZIP)
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>