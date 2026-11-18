<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteBannerResource\Pages;
use App\Models\SiteBanner;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteBannerResource extends Resource
{
    protected static ?string $model = SiteBanner::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Banner web';

    protected static ?string $modelLabel = 'Banner';

    protected static ?string $pluralModelLabel = 'Banners';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Toggle::make('is_enabled')
                ->label('Activo')
                ->helperText('Desactívalo para ocultar el banner sin borrarlo.')
                ->default(true)
                ->columnSpanFull(),

            TextInput::make('title')
                ->label('Título')
                ->required()
                ->maxLength(150)
                ->placeholder('Ej: 20% dto. en cambio de pantalla para iPhone'),

            TextInput::make('description')
                ->label('Subtexto')
                ->maxLength(200)
                ->placeholder('Ej: iPhone 11, 12, 13, 14, 15 · Solo este mes')
                ->helperText('Se oculta en pantallas pequeñas.'),

            FileUpload::make('image')
                ->label('Imagen (opcional)')
                ->image()
                ->disk('public')
                ->directory('banners')
                ->nullable()
                ->columnSpanFull(),

            TextInput::make('cta_label')
                ->label('Texto del botón')
                ->maxLength(60)
                ->placeholder('Ej: Pedir cita'),

            TextInput::make('cta_link')
                ->label('Enlace del botón')
                ->maxLength(500)
                ->url()
                ->placeholder('Ej: /reparaciones  o  https://…')
                ->helperText('URL completa o ancla (#contacto). Deja vacío para no mostrar botón.'),

            DateTimePicker::make('active_from')
                ->label('Mostrar desde')
                ->nullable()
                ->helperText('Deja vacío para que empiece de inmediato.'),

            DateTimePicker::make('active_until')
                ->label('Mostrar hasta')
                ->nullable()
                ->helperText('Deja vacío para que no expire nunca.'),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->width(48)
                    ->height(32)
                    ->defaultImageUrl(null),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('description')
                    ->label('Subtexto')
                    ->limit(50)
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->state(function (SiteBanner $record): string {
                        if (! $record->is_enabled) {
                            return 'Desactivado';
                        }
                        $now = now();
                        if ($record->active_from && $record->active_from > $now) {
                            return 'Programado';
                        }
                        if ($record->active_until && $record->active_until < $now) {
                            return 'Expirado';
                        }
                        return 'Activo';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Activo'      => 'success',
                        'Programado'  => 'info',
                        'Expirado'    => 'gray',
                        'Desactivado' => 'danger',
                        default       => 'gray',
                    }),

                TextColumn::make('active_from')
                    ->label('Desde')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Inmediato')
                    ->sortable(),

                TextColumn::make('active_until')
                    ->label('Hasta')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Sin expiración')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSiteBanners::route('/'),
            'create' => Pages\CreateSiteBanner::route('/create'),
            'edit'   => Pages\EditSiteBanner::route('/{record}/edit'),
        ];
    }
}
