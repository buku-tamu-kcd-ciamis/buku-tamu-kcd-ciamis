<?php

namespace App\Filament\Staff\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class DownloadDokumen extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationLabel = 'Download Dokumen';
    protected static string|\UnitEnum|null $navigationGroup = 'Informasi';
    protected static ?string $title = 'Download Dokumen Internal';
    protected static ?int $navigationSort = 5;
    protected string $view = 'filament.staff.pages.download-dokumen';

    /**
     * Get list of available documents for download.
     */
    public function getDocuments(): array
    {
        $documents = [];
        $dokumenPath = 'public/dokumen';

        if (Storage::exists($dokumenPath)) {
            $files = Storage::files($dokumenPath);
            foreach ($files as $file) {
                $filename = basename($file);
                $extension = pathinfo($filename, PATHINFO_EXTENSION);

                // Get icon based on file extension
                $icon = match (strtolower($extension)) {
                    'pdf' => 'heroicon-o-document-text',
                    'doc', 'docx' => 'heroicon-o-document',
                    'xls', 'xlsx' => 'heroicon-o-table-cells',
                    'ppt', 'pptx' => 'heroicon-o-presentation-chart-bar',
                    'jpg', 'jpeg', 'png' => 'heroicon-o-photo',
                    default => 'heroicon-o-paper-clip',
                };

                $color = match (strtolower($extension)) {
                    'pdf' => 'text-red-500',
                    'doc', 'docx' => 'text-blue-500',
                    'xls', 'xlsx' => 'text-green-500',
                    'ppt', 'pptx' => 'text-orange-500',
                    default => 'text-gray-500',
                };

                $documents[] = [
                    'name' => $filename,
                    'display_name' => str_replace(['_', '-'], ' ', pathinfo($filename, PATHINFO_FILENAME)),
                    'extension' => strtoupper($extension),
                    'size' => $this->formatFileSize(Storage::size($file)),
                    'url' => Storage::url(str_replace('public/', '', $file)),
                    'icon' => $icon,
                    'color' => $color,
                    'updated_at' => date('d/m/Y H:i', Storage::lastModified($file)),
                ];
            }
        }

        return $documents;
    }

    protected function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
