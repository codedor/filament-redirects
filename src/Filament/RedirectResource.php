<?php

namespace Codedor\FilamentRedirects\Filament;

use Codedor\FilamentRedirects\Enums\RedirectStatus;
use Codedor\FilamentRedirects\Filament\RedirectResource\Pages\ManageRedirects;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class RedirectResource extends Resource
{
    protected static ?string $model = \Codedor\FilamentRedirects\Models\Redirect::class;

    protected static ?string $navigationGroup = 'SEO';

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('from')
                    ->label(__('filament-redirects::admin.from'))
                    ->rules(config('filament-redirects.input-validation', ['required']))
                    ->required(),

                Forms\Components\TextInput::make('to')
                    ->label(__('filament-redirects::admin.to'))
                    ->rules(config('filament-redirects.input-validation', ['required']))
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label(__('filament-redirects::admin.status'))
                    ->required()
                    ->options(RedirectStatus::class),

                Forms\Components\Toggle::make('pass_query_string')
                    ->label(__('filament-redirects::admin.pass query string'))
                    ->default(false),

                Forms\Components\Toggle::make('online')
                    ->label(__('filament-redirects::admin.online'))
                    ->default(false),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('from')
                    ->label(__('filament-redirects::admin.from'))
                    ->searchable()
                    ->url(fn ($record) => Str::replace('*', '', $record->from), true),

                Tables\Columns\TextColumn::make('to')
                    ->label(__('filament-redirects::admin.to'))
                    ->searchable()
                    ->url(fn ($record) => Str::replace('*', '', $record->to), true),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament-redirects::admin.status'))
                    ->formatStateUsing(fn (int $state): string => RedirectStatus::tryFrom($state)->getLabel()),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
