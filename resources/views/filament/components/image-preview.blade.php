<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; gap: 2.5rem; padding: 2rem 1rem;">
    <div style="position: relative; display: flex; align-items: center; justify-content: center; width: 100%; max-width: 650px; min-height: 480px; overflow: hidden; border-radius: 1.5rem; border: 5px solid white; background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
        <img
            src="{{ $url }}"
            alt="Preview {{ $name ?? 'Foto' }}"
            style="height: 100%; width: 100%; object-fit: contain; display: block; margin: 0 auto;"
        />
    </div>
    @if (!empty($name))
        <p style="text-align: center; font-size: 1.125rem; font-weight: 700; color: #ffffff; text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6); margin: 0; padding: 0.75rem 1.5rem; letter-spacing: 0.3px;">{{ $name }}</p>
    @endif
</div>
