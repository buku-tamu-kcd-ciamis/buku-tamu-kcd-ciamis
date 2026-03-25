<?php

namespace App\Filament\Pages;

use App\Models\Faq as FaqModel;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Faq extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'FAQ';
    protected static string|\UnitEnum|null $navigationGroup = 'Bantuan';
    protected static ?string $title = 'Pertanyaan Umum (FAQ)';
    protected static ?int $navigationSort = 99;
    protected string $view = 'filament.pages.faq';

    public function getFaqs(): array
    {
        return FaqModel::getForAdmin();
    }
}
