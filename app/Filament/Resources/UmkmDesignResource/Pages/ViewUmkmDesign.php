<?php

namespace App\Filament\Resources\UmkmDesignResource\Pages;

use App\Filament\Resources\UmkmDesignResource;
use App\Models\Umkm;
use App\Models\UmkmDesign;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewUmkmDesign extends ViewRecord
{
    protected static string $resource = UmkmDesignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve_design')
                ->label('Approve Design')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve design ini?')
                ->visible(fn () =>
                    in_array($this->record->status, ['pending', 'revised']) &&
                    auth()->user()->isClient()
                )
                ->action(function () {
                    $this->record->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => auth()->id(),
                    ]);
                    \Filament\Notifications\Notification::make()->title('Design Disetujui ✅')->success()->send();
                    $this->redirect(UmkmDesignResource::getUrl('index'));
                }),

            Actions\Action::make('revisi_design')
                ->label('Minta Revisi')
                ->icon('heroicon-o-pencil')
                ->color('warning')
                ->form([
                    Forms\Components\Textarea::make('catatan_revisi')
                        ->label('Catatan Revisi')
                        ->placeholder('Tuliskan catatan revisi untuk designer...')
                        ->required(),
                ])
                ->visible(fn () =>
                    in_array($this->record->status, ['pending', 'revised']) &&
                    auth()->user()->isClient()
                )
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'revision_needed',
                        'catatan_revisi' => $data['catatan_revisi'],
                    ]);
                    if ($this->record->umkm) {
                        $this->record->umkm->update(['status' => Umkm::STATUS_REVISION_NEEDED]);
                    }
                    \Filament\Notifications\Notification::make()->title('Revisi Diminta ✏️')->warning()->send();
                    $this->redirect(UmkmDesignResource::getUrl('index'));
                }),
        ];
    }
}
