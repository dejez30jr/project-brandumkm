<div
    x-data="{
        open:false,
        image:''
    }"

    @open-image.window="
        open = true;
        image = $event.detail;
        document.body.classList.add('overflow-hidden');
    "
>

    <!-- BACKDROP -->
    <template x-teleport="body">

        <div
            x-show="open"
            x-transition.opacity
            class="fixed inset-0 z-[999999] bg-black/95 flex items-center justify-center p-5"
            style="display:none"
        >

            <!-- CLOSE -->
            <button
                @click="
                    open = false;
                    document.body.classList.remove('overflow-hidden');
                "
                class="absolute top-5 right-5 bg-white text-black w-12 h-12 rounded-full text-xl font-bold shadow-lg"
            >
                ✕
            </button>

            <!-- IMAGE -->
            <img
                :src="image"
                class="max-w-full max-h-[95vh] rounded-2xl shadow-2xl object-contain"
            >

        </div>

    </template>

</div>