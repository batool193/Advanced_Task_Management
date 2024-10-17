<?php

namespace App\Services;

use App\Jobs\SendDailyReportjob;
use App\Mail\DailyReportMail;
use Exception;

use Carbon\Carbon;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class TaskStatusUpdateService
{
    public function dailyReport()
    /**
     * Sends a daily report with the tasks created or updated today
     *
     * @return string
     */
    {
        try {
            // Get the start and end of today
            $startOfDay = Carbon::today()->startOfDay();
            $endOfDay = Carbon::today()->endOfDay();

            $tasks = Task::whereBetween('created_at', [$startOfDay, $endOfDay])
            // Get the tasks created or updated today
                ->orWhereBetween('updated_at', [$startOfDay, $endOfDay])
                ->get();

            // Dispatch the daily report job
            SendDailyReportjob::dispatch($tasks);

            return 'send successfuly';
            // Return a success message

        } catch (Exception $e) {
            // Log the error
            Log::error('error: ' . $e->getMessage());
            return false;

            // Return false
        }
    }


    public function Update(array $data, $user)
    {
    }

    public function Show($user)
    {

    }
    public function Delete($user)
    {
    }

}
