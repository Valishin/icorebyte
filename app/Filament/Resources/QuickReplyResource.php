<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuickReplyResource\Pages;
use App\Models\QuickReply;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuickReplyResource extends Resource
{
    protected static ?string $model = QuickReply::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationLabel = 'Respuestas rápidas';

    protected static ?string $modelLabel = 'Respuesta rápida';

    protected static ?string $pluralModelLabel = 'Respuestas rápidas';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('shortcut')
                ->label('Atajo (empieza por /)')
                ->required()
                ->maxLength(50)
                ->regex('/^\/[a-z0-9_-]+$/')
                ->helperText('Ejemplo: /horario  —  escribe "/" en el campo de respuesta para ver la lista.')
                ->unique(ignoreRecord: true),

            TextInput::make('title')
                ->label('Título')
                ->required()
                ->maxLength(100),

            Textarea::make('body')
                ->label('Texto del mensaje')
                ->required()
                ->rows(4)
                ->maxLength(2000)
                ->columnSpanFull(),

            TextInput::make('sort_order')
                ->label('Orden')
                ->integer()
                ->default(0),

            Toggle::make('active')
                ->label('Activo')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shortcut')
                    ->label('Atajo')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('body')
                    ->label('Texto')
                    ->limit(60)
                    ->wrap(),

                IconColumn::make('active')
                    ->label('Activo')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListQuickReplies::route('/'),
            'create' => Pages\CreateQuickReply::route('/create'),
            'edit'   => Pages\EditQuickReply::route('/{record}/edit'),
        ];
    }
}
