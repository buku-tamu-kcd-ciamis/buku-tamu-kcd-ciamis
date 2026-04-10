<div class="flex flex-col items-center justify-center p-4">
    <div class="relative group max-w-full overflow-hidden rounded-xl shadow-2xl border-4 border-white bg-gray-50">
        <img 
            src="{{ $url }}" 
            alt="Preview {{ $name ?? 'Foto' }}" 
            class="max-h-[75vh] w-auto object-contain mx-auto transition-all duration-500"
        />
        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <p class="text-white text-sm font-medium text-center">{{ $name ?? '' }}</p>
        </div>
    </div>
</div>
