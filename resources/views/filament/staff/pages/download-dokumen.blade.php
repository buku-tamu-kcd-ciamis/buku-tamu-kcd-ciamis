<x-filament-panels::page>
    <div class="space-y-6">
        @php $documents = $this->getDocuments(); @endphp

        @if(count($documents) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($documents as $doc)
                    <div
                        class="rounded-2xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-md hover:shadow-lg hover:border-primary-300 dark:hover:border-primary-600 transition-all duration-300 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div
                                    class="shrink-0 w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center {{ $doc['color'] }} group-hover:scale-110 transition-transform duration-300">
                                    <x-dynamic-component :component="$doc['icon']" class="w-6 h-6" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm leading-tight truncate"
                                        title="{{ $doc['display_name'] }}">
                                        {{ $doc['display_name'] }}
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                            {{ $doc['extension'] }}
                                        </span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ $doc['size'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                        Diperbarui: {{ $doc['updated_at'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 pb-4">
                            <a href="{{ $doc['url'] }}" target="_blank"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/40 border border-primary-200 dark:border-primary-800 transition-all duration-200">
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                Download
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16">
                <div
                    class="w-20 h-20 mx-auto rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-5">
                    <x-heroicon-o-folder-open class="w-10 h-10 text-gray-300 dark:text-gray-600" />
                </div>
                <h3 class="text-lg font-semibold text-gray-600 dark:text-gray-300 mb-2">Belum Ada Dokumen</h3>
                <p class="text-gray-400 dark:text-gray-500 max-w-md mx-auto">
                    Dokumen internal belum tersedia saat ini. Administrator dapat menambahkan dokumen melalui folder <code
                        class="text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">storage/app/public/dokumen/</code>.
                </p>
            </div>
        @endif
    </div>
</x-filament-panels::page>