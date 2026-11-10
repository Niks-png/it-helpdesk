<?php

namespace App\Filament\Resources\Tickets;

use App\Enums\TicketCategory;
use App\Enums\TicketStatus;
use App\Filament\Resources\Tickets\Pages\CreateTicket;
use App\Filament\Resources\Tickets\Pages\ListTickets;
use App\Filament\Resources\Tickets\Pages\ViewTicket;
use App\Models\Ticket;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $modelLabel = 'pieteikums';

    protected static ?string $pluralModelLabel = 'Pieteikumi'; // shown as the menu item, page title and breadcrumb

    // Latvian does not use English Title Case ("Jauns pieteikums", not "Jauns Pieteikums").
    protected static bool $hasTitleCaseModelLabel = false;

    // Employees only ever see their own tickets; IT admins see all of them.
    // Filament also loads single records (the view page) through this query, so other people's tickets 404.
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return auth()->user()->isAdmin() ? $query : $query->whereBelongsTo(auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Select::make('category')
                    ->label('Kategorija')
                    ->options(TicketCategory::class)
                    ->required(),
                TextInput::make('title')
                    ->label('Temats')
                    ->placeholder('Piemēram: Dators nevar pieslēgties tīklam')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Problēmas apraksts')
                    ->helperText('Kas notika, kad tas sākās un ko jau mēģinājāt?')
                    ->required()
                    ->rows(6)
                    ->maxLength(5000),
                FileUpload::make('screenshot')
                    ->label('Ekrānuzņēmums')
                    ->image()
                    ->maxSize(5120) // KB
                    ->disk('local') // private: storage/app/private, never publicly reachable
                    ->directory('screenshots')
                    ->visibility('private'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('number')->label('Numurs'),
                TextEntry::make('status')->label('Statuss')->badge(),
                TextEntry::make('category')->label('Kategorija'),
                TextEntry::make('created_at')->label('Izveidots')->dateTime('d.m.Y H:i'),
                TextEntry::make('user.name')->label('Pieteicējs'),
                TextEntry::make('title')->label('Temats'),
                TextEntry::make('description')->label('Problēmas apraksts')->columnSpanFull(),
                ImageEntry::make('screenshot')
                    ->label('Ekrānuzņēmums')
                    ->disk('local')
                    ->visibility('private')
                    ->imageHeight(300)
                    ->visible(fn (Ticket $record): bool => filled($record->screenshot))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')->label('Numurs')->searchable()->sortable(),
                TextColumn::make('title')->label('Temats')->searchable()->limit(50),
                TextColumn::make('category')->label('Kategorija')->badge()->color('gray'),
                TextColumn::make('status')->label('Statuss')->badge()->sortable(),
                TextColumn::make('user.name')->label('Pieteicējs')->visible(fn (): bool => auth()->user()->isAdmin()),
                TextColumn::make('created_at')->label('Izveidots')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->emptyStateHeading('Vēl nav neviena pieteikuma');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'view' => ViewTicket::route('/{record}'),
        ];
    }
}
