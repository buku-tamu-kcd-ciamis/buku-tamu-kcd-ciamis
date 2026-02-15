<?php

namespace App\Filament\Piket\Pages;

use App\Models\Faq as FaqModel;
use Filament\Pages\Page;

class Faq extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'FAQ';
    protected static ?string $title = 'Pertanyaan Umum (FAQ)';
    protected static ?string $navigationGroup = 'Bantuan';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.piket.pages.faq';

    public function getFaqs(): array
    {
        return FaqModel::getForPiket();
    }
}
