<?php

namespace App\Console\Commands;

use App\Models\Participant;
use App\Models\ActualEventParticipant;
use Illuminate\Console\Command;

class RecalculateParticipantAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'participants:recalculate-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate attendance count for all participants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recalculating attendance counts...');

        $participants = Participant::all();
        $bar = $this->output->createProgressBar($participants->count());
        $bar->start();

        foreach ($participants as $participant) {
            $count = ActualEventParticipant::where('participant_id', $participant->id)->count();
            $participant->update(['attendance_count' => $count]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Attendance counts recalculated successfully!');

        return Command::SUCCESS;
    }
}
