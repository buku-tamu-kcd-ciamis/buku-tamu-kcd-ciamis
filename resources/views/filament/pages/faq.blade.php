<x-filament-panels::page>
    <div class="admin-faq-page" x-data="{ openFaq: null, searchQuery: '' }">
        <div class="admin-faq-list">
            @forelse($this->getFaqs() as $index => $faq)
                <article
                    class="admin-faq-item"
                    :class="{ 'is-open': openFaq === {{ $index }} }"
                    x-show="searchQuery === '' || '{{ strtolower($faq['question']) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower(strip_tags($faq['answer'])) }}'.includes(searchQuery.toLowerCase())"
                    x-transition.opacity.duration.150ms
                >
                    <button
                        type="button"
                        class="admin-faq-question"
                        @click="openFaq = (openFaq === {{ $index }} ? null : {{ $index }})"
                        :aria-expanded="openFaq === {{ $index }} ? 'true' : 'false'"
                    >
                        <span class="admin-faq-number">{{ $index + 1 }}</span>
                        <span class="admin-faq-question-text">{{ $faq['question'] }}</span>
                        <span class="admin-faq-chevron" :class="{ 'is-open': openFaq === {{ $index }} }" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </button>

                    <div class="admin-faq-answer-wrap" x-show="openFaq === {{ $index }}" x-collapse>
                        <div class="admin-faq-answer">
                            {!! $faq['answer'] !!}
                        </div>
                    </div>
                </article>
            @empty
                <div class="admin-faq-empty">
                    <p>Belum ada FAQ yang tersedia.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
