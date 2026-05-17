<div class="py-2">
    @php
        $videoUrl = $getState();
    @endphp

    @if($videoUrl)
        <div class="w-full max-w-xl">
            <div class="h-[200px] overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:scale-105 transition duration-300">
                <video controls class="w-full h-full object-cover">
                    <source src="{{ asset('storage/' . $videoUrl) }}" type="video/mp4">
                    Browser kamu tidak mendukung pemutar video.
                </video>
            </div>
        </div>
    @endif
</div>