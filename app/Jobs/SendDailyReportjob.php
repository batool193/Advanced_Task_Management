<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\DailyReportMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendDailyReportjob implements ShouldQueue
{
    use Queueable,Dispatchable,InteractsWithQueue,SerializesModels;
 protected $tasks;
    /**
     * Create a new job instance.
     */
    public function __construct($tasks)
    {
         $this->tasks = $tasks;
    }

    /**
     * Execute the job.
     *
     */
    public function handle()
    {
        // Get all the user emails from the database
        $user_emails = User::pluck('email');

        // Loop through each user email and send a daily report email
        foreach ($user_emails as $key => $email) {
            Mail::to($email)->send(new DailyReportMail($this->tasks));
        }
    }
}
