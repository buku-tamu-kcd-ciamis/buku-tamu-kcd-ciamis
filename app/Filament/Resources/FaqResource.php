<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Facades\Filament;

class FaqResource extends Resource
{
  protected static ?string $model = Faq::class;

  protected static ?string $slug = 'manajemen-faq';
  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';
  protected static ?string $navigationLabel = 'Manajemen FAQ';
  protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
  protected static ?string $modelLabel = 'FAQ';
  protected static ?string $pluralModelLabel = 'Manajemen FAQ';
  protected static ?int $navigationSort = 14;

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Filament::auth()->user();
    return $user && $user->hasRole('Super Admin');
  }

  public static function canViewAny(): bool
  {
    return static::shouldRegisterNavigation();
  }

  public static function form(Schema $schema): Schema
  {
    return $schema->components([
      Section::make('Konten FAQ')
        ->description('Isi pertanyaan dan jawaban yang akan ditampilkan di halaman FAQ.')
        ->icon('heroicon-o-chat-bubble-left-right')
        ->schema([
          Forms\Components\TextInput::make('question')
            ->label('Pertanyaan')
            ->required()
            ->maxLength(500)
            ->placeholder('Contoh: Bagaimana cara login?'),
          Forms\Components\RichEditor::make('answer')
            ->label('Jawaban')
            ->required()
            ->toolbarButtons([
              'bold',
              'italic',
              'underline',
              'orderedList',
              'bulletList',
              'link',
            ])
            ->placeholder('Tulis jawaban lengkap di sini...'),
        ]),
      Section::make('Pengaturan')
        ->description('Atur target panel dan urutan tampil FAQ.')
        ->icon('heroicon-o-cog-6-tooth')
        ->columns(3)
        ->schema([
          Forms\Components\Select::make('target')
            ->label('Target Panel')
            ->options(Faq::TARGET_LABELS)
            ->required()
            ->placeholder('Pilih opsi')
            ->native(false)
            ->helperText('Panel tujuan FAQ.'),
          Forms\Components\TextInput::make('sort_order')
            ->label('Urutan Tampil')
            ->numeric()
            ->helperText('Urutan tampil otomatis.'),
          Forms\Components\Toggle::make('is_active')
            ->label('Status Aktif')
            ->helperText('FAQ nonaktif tidak ditampilkan.')
            ->inline(false),
        ]),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('sort_order')
          ->label('#')
          ->state(function ($rowLoop) {
            return $rowLoop->iteration;
          })
          ->width('60px'),
        Tables\Columns\TextColumn::make('question')
          ->label('Pertanyaan')
          ->searchable()
          ->limit(60)
          ->wrap(),
        Tables\Columns\TextColumn::make('target')
          ->label('Target Panel')
          ->badge()
          ->color(fn(string $state): string => match ($state) {
            'semua' => 'primary',
            'admin' => 'warning',
            'piket' => 'success',
            default => 'gray',
          })
          ->formatStateUsing(fn(string $state) => Faq::TARGET_LABELS[$state] ?? $state),
        Tables\Columns\IconColumn::make('is_active')
          ->label('Aktif')
          ->boolean()
          ->sortable(),
        Tables\Columns\TextColumn::make('updated_at')
          ->label('Terakhir diubah')
          ->since()
          ->color('gray')
          ->tooltip(fn($record) => $record->updated_at?->format('d/m/Y H:i'))
          ->sortable(),
      ])
      ->defaultSort('sort_order')
      ->reorderable('sort_order')
      ->filters([
        Tables\Filters\SelectFilter::make('target')
          ->label('Target Panel')
          ->options(Faq::TARGET_LABELS),
        Tables\Filters\TernaryFilter::make('is_active')
          ->label('Status')
          ->trueLabel('Aktif')
          ->falseLabel('Nonaktif'),
      ])
      ->recordActions([])
      ->toolbarActions([]);
  }

  public static function getRelations(): array
  {
    return [];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListFaqs::route('/'),
      'create' => Pages\CreateFaq::route('/create'),
      'edit' => Pages\EditFaq::route('/{record}/edit'),
    ];
  }
}
