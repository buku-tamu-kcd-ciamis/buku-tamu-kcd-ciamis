<div x-data="signaturePad" wire:ignore>
    <input type="hidden" {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}" x-model="signature">
    
    <!-- Instruksi -->
    <div class="mb-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
        <div class="flex items-start gap-2">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm text-blue-700 dark:text-blue-300">
                <strong>Petunjuk:</strong> Gambar tanda tangan Anda di kotak di bawah ini menggunakan mouse atau jari (touchscreen). Klik tombol "Selesai" jika sudah selesai.
            </div>
        </div>
    </div>
    
    <!-- Canvas Area -->
    <div x-show="!signature" class="mb-3">
        <div class="relative">
            <canvas 
                x-ref="canvas" 
                class="w-full border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-crosshair bg-white hover:border-blue-400 dark:hover:border-blue-500 transition-colors"
                style="height: 180px; touch-action: none;"
            ></canvas>
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none" x-show="!hasDrawn">
                <div class="text-gray-400 dark:text-gray-500 text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    <p class="text-xs">Mulai menggambar tanda tangan</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Preview Area -->
    <div x-show="signature" class="mb-3">
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tanda Tangan Tersimpan</span>
            </div>
            <img :src="signature" class="w-full max-w-sm mx-auto rounded border border-gray-300 dark:border-gray-600 bg-white" style="max-height: 150px; object-fit: contain;">
        </div>
    </div>
    
    <!-- Buttons -->
    <div class="flex flex-wrap gap-3 mt-3">
        <template x-if="!signature">
            <div class="flex gap-3">
                <button 
                    type="button"
                    @click="clear"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Hapus
                </button>
                <button 
                    type="button"
                    @click="saveSignature"
                    x-show="hasDrawn"
                    style="background-color: #059669 !important; color: white !important;"
                    class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-green-600 border-0 rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-md transition-opacity"
                >
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Konfirmasi TTD
                </button>
            </div>
        </template>
        
        <template x-if="signature">
            <button 
                type="button"
                @click="reset"
                style="background-color: #2563eb !important; color: white !important;"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border-0 rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-md transition-opacity"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Gambar Ulang
            </button>
        </template>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('signaturePad', () => ({
        signature: @entangle($getStatePath()),
        canvas: null,
        ctx: null,
        isDrawing: false,
        hasDrawn: false,
        
        init() {
            this.$nextTick(() => {
                if (this.$refs.canvas) {
                    this.canvas = this.$refs.canvas;
                    this.ctx = this.canvas.getContext('2d');
                    this.resizeCanvas();
                    
                    // Mouse events
                    this.canvas.addEventListener('mousedown', this.startDrawing.bind(this));
                    this.canvas.addEventListener('mousemove', this.draw.bind(this));
                    this.canvas.addEventListener('mouseup', this.stopDrawing.bind(this));
                    this.canvas.addEventListener('mouseleave', this.stopDrawing.bind(this));
                    
                    // Touch events
                    this.canvas.addEventListener('touchstart', this.handleTouch.bind(this));
                    this.canvas.addEventListener('touchmove', this.handleTouch.bind(this));
                    this.canvas.addEventListener('touchend', this.stopDrawing.bind(this));
                    
                    window.addEventListener('resize', () => this.resizeCanvas());
                }
            });
        },
        
        resizeCanvas() {
            if (!this.canvas) return;
            const rect = this.canvas.getBoundingClientRect();
            this.canvas.width = rect.width;
            this.canvas.height = 180;
            this.ctx.strokeStyle = '#000';
            this.ctx.lineWidth = 2.5;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
        },
        
        getPosition(e) {
            const rect = this.canvas.getBoundingClientRect();
            const scaleX = this.canvas.width / rect.width;
            const scaleY = this.canvas.height / rect.height;
            
            if (e.touches && e.touches[0]) {
                return {
                    x: (e.touches[0].clientX - rect.left) * scaleX,
                    y: (e.touches[0].clientY - rect.top) * scaleY
                };
            }
            return {
                x: (e.clientX - rect.left) * scaleX,
                y: (e.clientY - rect.top) * scaleY
            };
        },
        
        startDrawing(e) {
            this.isDrawing = true;
            this.hasDrawn = true;
            const pos = this.getPosition(e);
            this.ctx.beginPath();
            this.ctx.moveTo(pos.x, pos.y);
        },
        
        draw(e) {
            if (!this.isDrawing) return;
            const pos = this.getPosition(e);
            this.ctx.lineTo(pos.x, pos.y);
            this.ctx.stroke();
        },
        
        stopDrawing() {
            if (this.isDrawing) {
                this.isDrawing = false;
            }
        },
        
        handleTouch(e) {
            e.preventDefault();
            if (e.type === 'touchstart') {
                this.startDrawing(e);
            } else if (e.type === 'touchmove') {
                this.draw(e);
            }
        },
        
        saveSignature() {
            if (this.hasDrawn) {
                this.signature = this.canvas.toDataURL('image/png');
            }
        },
        
        clear() {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.hasDrawn = false;
        },
        
        reset() {
            this.signature = null;
            this.hasDrawn = false;
            this.$nextTick(() => {
                this.resizeCanvas();
            });
        }
    }));
});
</script>
