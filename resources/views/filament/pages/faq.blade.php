<x-filament-panels::page>
    <div class="staff-faq-page" x-data="{ openFaq: null, searchQuery: '' }">
        <div class="staff-faq-search">
            <label for="staff-faq-search-input" class="staff-faq-search-label">Cari Pertanyaan</label>
            <div class="staff-faq-search-input-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6 6a7.5 7.5 0 0 0 10.65 10.65Z" />
                </svg>
                <input
                    id="staff-faq-search-input"
                    type="text"
                    x-model.debounce.200ms="searchQuery"
                    placeholder="Cari pertanyaan atau kata kunci jawaban..."
                >
            </div>
        </div>

        <div class="staff-faq-list">
            @forelse($this->getFaqs() as $index => $faq)
                <article
                    class="staff-faq-item"
                    :class="{ 'is-open': openFaq === {{ $index }} }"
                    x-show="searchQuery === '' || '{{ strtolower($faq['question']) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower(strip_tags($faq['answer'])) }}'.includes(searchQuery.toLowerCase())"
                    x-transition:enter="transition ease-out duration-160"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                >
                    <button
                        type="button"
                        class="staff-faq-question"
                        @click="openFaq = (openFaq === {{ $index }} ? null : {{ $index }})"
                        :aria-expanded="openFaq === {{ $index }} ? 'true' : 'false'"
                    >
                        <span class="staff-faq-number">{{ $index + 1 }}</span>
                        <span class="staff-faq-question-text">{{ $faq['question'] }}</span>
                        <span class="staff-faq-chevron" :class="{ 'is-open': openFaq === {{ $index }} }" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </button>

                    <div
                        class="staff-faq-answer-wrap"
                        x-show="openFaq === {{ $index }}"
                        x-collapse
                    >
                        <div class="staff-faq-answer prose prose-sm dark:prose-invert max-w-none">
                            {!! $faq['answer'] !!}
                        </div>
                    </div>
                </article>
            @empty
                <div class="staff-faq-empty">
                    <p>Belum ada FAQ yang tersedia.</p>
                    <p class="staff-faq-empty-hint">FAQ akan ditambahkan oleh administrator.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
