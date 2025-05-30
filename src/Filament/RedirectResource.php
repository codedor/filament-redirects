<?php

namespace Codedor\FilamentRedirects\Filament;

use Codedor\FilamentRedirects\Filament\RedirectResource\Pages\ManageRedirects;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class RedirectResource extends Resource
{
    protected static ?string $model = \Codedor\FilamentRedirects\Models\Redirect::class;

    protected static string|\UnitEnum|null $navigationGroup = 'SEO';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('from')
                    ->rules(config('filament-redirects.input-validation', ['required']))
                    ->required(),

                Forms\Components\TextInput::make('to')
                    ->rules(config('filament-redirects.input-validation', ['required']))
                    ->required(),

                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        301 => __('301 - Permanent redirect'),
                        302 => __('302 - Temporary redirect'),
                        410 => __('410 - Gone (for page that once existed, but is gone now)'),
                    ]),

                Forms\Components\Toggle::make('pass_query_string')
                    ->default(false),

                Forms\Components\Toggle::make('online')
                    ->default(false),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('from')
                    ->searchable()
                    ->url(fn ($record) => Str::replace('*', '', $record->from), true),
                Tables\Columns\TextColumn::make('to')
                    ->searchable()
                    ->url(fn ($record) => Str::replace('*', '', $record->to), true),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRedirects::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->ordered();
    }
}
