<x-filament-panels::page>
    <div class="w-full space-y-6 pb-8" x-data="{ openFaq: null, searchQuery: '' }">
        
        <!-- FAQ Items -->
        <div class="space-y-4">
            @foreach($this->getFaqs() as $index => $faq)
                <div 
                    x-show="searchQuery === '' || '{{ strtolower($faq['question']) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower(strip_tags($faq['answer'])) }}'.includes(searchQuery.toLowerCase())"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                >
                    <div class="relative rounded-2xl border-2 bg-white dark:bg-gray-800 transition-all duration-300 overflow-hidden"
                         :class="openFaq === {{ $index }} ? 'border-primary-400 dark:border-primary-500 shadow-xl shadow-primary-100 dark:shadow-primary-900/30' : 'border-gray-200 dark:border-gray-700 shadow-md hover:shadow-lg hover:border-gray-300 dark:hover:border-gray-600'">
                        
                        <!-- Animated Background Gradient -->
                        <div 
                            class="absolute inset-0 bg-gradient-to-r from-primary-50/50 to-transparent dark:from-primary-900/20 opacity-0 transition-opacity duration-300"
                            :class="{ 'opacity-100': openFaq === {{ $index }} }"
                        ></div>

                        <button
                            @click="openFaq = (openFaq === {{ $index }} ? null : {{ $index }})"
                            class="relative z-10 w-full flex items-center justify-between px-6 py-5 text-left hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-all duration-200"
                        >
                            <div class="flex items-center gap-4 flex-1">
                                <div class="flex-shrink-0 relative z-20">
                                    <div class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-base transition-all duration-300"
                                         :class="openFaq === {{ $index }} ? 'bg-primary-500 text-white shadow-xl' : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-white'"
                                         style="isolation: isolate;">
                                        <span style="position: relative; z-index: 50;">{{ $index + 1 }}</span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <span class="font-semibold text-gray-900 dark:text-white text-base leading-relaxed block"
                                          :class="openFaq === {{ $index }} ? 'text-primary-900 dark:text-primary-100' : ''">
                                        {{ $faq['question'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 ml-4">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-300"
                                     :class="openFaq === {{ $index }} ? 'bg-primary-100 dark:bg-primary-900/40 ring-2 ring-primary-200 dark:ring-primary-800' : 'bg-gray-100 dark:bg-gray-700'">
                                    <svg class="w-5 h-5 transform transition-all duration-300"
                                         :class="openFaq === {{ $index }} ? 'rotate-180 text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </button>
                        
                        <div x-show="openFaq === {{ $index }}" 
                             x-collapse
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100">
                            <div class="relative px-6 pb-6 pt-2">
                                <div class="pl-14 pr-4">
                                    <div class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800/50 rounded-2xl p-6 border-l-4 border-primary-500 shadow-inner">
                                        <div class="text-gray-700 dark:text-gray-200 leading-relaxed space-y-3 prose prose-sm dark:prose-invert max-w-none">
                                            {!! $faq['answer'] !!}
                                        </div>

                                        <!-- Badge -->
                                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                                            <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                                                </svg>
                                                Informasi FAQ
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</x-filament-panels::page>
