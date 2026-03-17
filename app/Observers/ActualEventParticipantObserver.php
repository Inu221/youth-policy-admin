<?php

namespace App\Observers;

use App\Models\ActualEventParticipant;
use App\Models\Participant;

class ActualEventParticipantObserver
{
    /**
     * Handle the ActualEventParticipant "created" event.
     */
    public function created(ActualEventParticipant $eventParticipant): void
    {
        $this->updateParticipantAttendanceCount($eventParticipant->participant_id);
    }

    /**
     * Handle the ActualEventParticipant "deleted" event.
     */
    public function deleted(ActualEventParticipant $eventParticipant): void
    {
        $this->updateParticipantAttendanceCount($eventParticipant->participant_id);
    }

    /**
     * Update the attendance count for a participant.
     */
    protected function updateParticipantAttendanceCount(int $participantId): void
    {
        $participant = Participant::find($participantId);

        if ($participant) {
            $count = ActualEventParticipant::where('participant_id', $participantId)->count();
            $participant->update(['attendance_count' => $count]);
        }
    }
}
