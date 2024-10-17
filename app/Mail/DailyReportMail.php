<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyReportMail extends Mailable
{
    use Queueable, SerializesModels;
   protected $tasks;
   public function __construct($tasks)
   {
    $this->tasks = $tasks;
   }
   /**
     * Build the message.
     *
     * @return $this
     */
   public function build()
   {
    return $this->subject('daily report')
    ->view('emails.daily_report')
    ->with('tasks', $this->tasks);
   }

   /**
     * Get the data to be passed to the view.
     *
     * @return array
     */
   protected function data()
   {
    return [
     'tasks' => $this->tasks,
    ];
   }
}
