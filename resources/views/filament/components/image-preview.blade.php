<div class="flex flex-col items-center justify-center w-full" style="gap: 1.5rem; padding: 1rem;">
    <div class="relative group flex items-center justify-center" style="width: 100%; max-width: 600px; min-height: 450px; overflow: hidden; border-radius: 0.75rem; border: 4px solid white; background-color: #f3f4f6; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);">
        <img
            src="{{ $url }}"
            alt="Preview {{ $name ?? 'Foto' }}"
            style="height: 100%; width: 100%; object-fit: contain; display: block; margin: 0 auto;"
        />
    </div>
    @if (!empty($name))
        <p style="text-align: center; font-size: 0.875rem; font-weight: 600; color: white; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5); margin: 0; padding: 0.5rem 1rem;">{{ $name }}</p>
    @endif
</div>
