<x-filament-panels::page>
    <div class="space-y-4">
        @foreach($this->getFaqs() as $index => $faq)
            <div x-data="{ open: false }" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                <button
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-sm">
                            {{ $index + 1 }}
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm">
                            {{ $faq['question'] }}
                        </span>
                    </div>
                    <svg
                        class="w-5 h-5 text-gray-400 transform transition-transform duration-200 flex-shrink-0 ml-4"
                        :class="{ 'rotate-180': open }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div
                    x-show="open"
                    x-collapse
                    class="px-5 pb-4"
                >
                    <div class="pl-11 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                        {!! $faq['answer'] !!}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
