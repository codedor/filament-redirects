<?php

namespace Codedor\FilamentRedirects\Filament\RedirectResource\Pages;

use Codedor\FilamentRedirects\Filament\RedirectResource;
use Codedor\FilamentRedirects\Imports\RedirectsImport;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ManageRedirects extends ManageRecords
{
    protected static string $resource = RedirectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('import')
                ->label(__('filament-redirects::admin.import'))
                ->icon('heroicon-o-arrow-up-on-square')
                ->action(fn (array $data) => $this->importRedirects($data))
                ->visible(fn (): bool => RedirectResource::canCreate())
                ->form([
                    FileUpload::make('file')
                        ->label(__('filament-redirects::admin.file'))
                        ->disk('local'),
                ]),
        ];
    }

    public function importRedirects(array $data): void
    {
        try {
            Excel::import(
                new RedirectsImport,
                new UploadedFile(Storage::disk('local')->path($data['file']), $data['file'])
            );

            $this->dispatch('refreshTable');

            Notification::make()
                ->title(__('filament-redirects::admin.import succesful'))
                ->success()
                ->send();
        } catch (\Throwable $th) {
            Notification::make()
                ->title(__('filament-redirects::admin.import error'))
                ->body($th->getMessage())
                ->danger()
                ->send();
        }
    }
}
