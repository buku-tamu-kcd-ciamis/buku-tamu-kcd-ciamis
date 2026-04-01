<?php

namespace App\Filament\Staff\Pages;

use App\Filament\Staff\Concerns\ChecksStaffPermission;
use App\Models\Faq as FaqModel;
use Filament\Pages\Page;

class Faq extends Page
{
    use ChecksStaffPermission;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'FAQ';
    protected static ?string $title = 'Pertanyaan Umum (FAQ)';
    protected static string|\UnitEnum|null $navigationGroup = 'Bantuan';
    protected static ?int $navigationSort = 99;
    protected string $view = 'filament.pages.faq';

    public static function shouldRegisterNavigation(): bool
    {
        return static::hasStaffPermission('riwayat_tamu');
    }

    public static function canAccess(): bool
    {
        return static::hasStaffPermission('riwayat_tamu');
    }

    public function getFaqs(): array
    {
        return FaqModel::getForStaff();
    }
}
