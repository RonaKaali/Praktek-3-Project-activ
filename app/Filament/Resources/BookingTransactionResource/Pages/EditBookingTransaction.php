<?php

namespace App\Filament\Resources\BookingTransactionResource\Pages;

use App\Filament\Resources\BookingTransactionResource;
use App\Models\WorkshopParticipant;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditBookingTransaction extends EditRecord
{
    protected static string $resource = BookingTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Check if participants exist, and map only if not null
        if ($this->record->participants) {
            $data['participants'] = $this->record->participants->map(function ($participant) {
                return [
                    'name' => $participant->name,
                    'occupation' => $participant->occupation,
                    'email' => $participant->email,
                ];
            })->toArray();
        } else {
            $data['participants'] = []; // Set as empty array if no participants
        }
    
        return $data;
    }

    protected function afterSave(): void
    {
        DB::transaction(function () {
            $record = $this->record;

            // Clear existing participants
            $record->participants()->delete();

            // Get participants data from the form state
            $participants = $this->form->getState()['participants'] ?? [];

            // Loop through participants and create new records
            foreach ($participants as $participant) {
                WorkshopParticipant::create([
                    'workshop_id' => $record->workshop_id,
                    'booking_transaction_id' => $record->id,
                    'name' => $participant['name'],
                    'occupation' => $participant['occupation'],
                    'email' => $participant['email'],
                ]);
            }
        });
    }
}