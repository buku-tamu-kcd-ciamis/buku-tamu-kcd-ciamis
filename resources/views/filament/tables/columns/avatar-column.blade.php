@php
    $record = $getRecord();
    $raw = $record->getRawOriginal('foto_selfie');
    $name = $record->nama_lengkap ?? 'Tamu';
    $initial = strtoupper(mb_substr($name, 0, 1));
    $fallback = "https://ui-avatars.com/api/?name={$initial}&background=0F9455&color=fff&size=80";

    // Resolve the image URL
    if (!empty($raw)) {
        if (str_starts_with($raw, 'data:image/')) {
            $imageUrl = $raw;
        } else {
            // File path like "buku-tamu/uuid.jpg" → "/storage/buku-tamu/uuid.jpg"
            $imageUrl = '/storage/' . ltrim($raw, '/');
        }
    } else {
        $imageUrl = null;
    }
@endphp

<div class="flex items-center justify-center">
    @if($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $name }}" class="w-10 h-10 rounded-full object-cover" loading="lazy"
            onerror="this.onerror=null;this.src='{{ $fallback }}';" />
    @else
        <img src="{{ $fallback }}" alt="{{ $name }}" class="w-10 h-10 rounded-full object-cover" />
    @endif
</div>