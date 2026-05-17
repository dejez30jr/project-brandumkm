

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div x-data="{ isOpen: false, imgSrc: '' }"
     x-on:open-preview-modal.window="isOpen = true; imgSrc = $event.detail.src"
     x-on:keydown.escape.window="isOpen = false"
     class="relative z-50"
     x-cloak>
    
    <!-- Overlay Gelap -->
    <div class="fixed inset-0 bg-black/85 backdrop-blur-md transition-opacity" 
         x-show="isOpen" 
         x-transition.opacity
         x-on:click="isOpen = false"></div>

    <!-- Jendela Popup -->
    <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4"
         x-show="isOpen">
        
        <!-- Container Utama Gambar + Tombol -->
        <div class="relative max-w-4xl w-full flex flex-col items-center" 
             x-show="isOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <!-- Pembungkus Relatif Agar Tombol Mengikuti Pojok Gambar -->
            <div class="relative inline-block max-h-[85vh] max-w-full">
                
                <!-- TOMBOL CLOSE (Pojok Kanan Atas Premium) -->
                <button type="button" 
                        x-on:click="isOpen = false" style="pointer-events: auto; background-color: rgba(210, 41, 41, 0.8);"
                        class="absolute -top-4 -right-4 z-50 flex h-10 w-10 items-center justify-center rounded-full text-white shadow-lg ring-4 ring-white transition duration-200 ease-in-out hover:bg-red-700 hover:scale-110 active:scale-95 dark:ring-gray-900 focus:outline-none"
                        title="Tutup (Esc)">
                    <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Gambar Besar -->
                <img :src="imgSrc" 
                     x-on:click.away="isOpen = false"
                     class="max-h-[85vh] max-w-full rounded-xl shadow-2xl border border-gray-200/20 dark:border-gray-800 object-contain block">
            </div>

        </div>
    </div>
</div>
</body>
</html>