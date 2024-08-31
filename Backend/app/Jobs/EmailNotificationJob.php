<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Member;
use Illuminate\Bus\Queueable;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Blade;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class EmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $templateData, $emailData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($templateData, $emailData)
    {
       $this->templateData = $templateData;
       $this->emailData = $emailData;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $subject = $this->templateData->email_subject;
        $email_body = $this->templateData->email_body;
        foreach ($this->emailData['variables'] as $key => $value) {
            $key = '[@'.$key.'@]';
            $email_body = str_replace($key,$value,$email_body);
        }

        // Evaluate the PHP code within the string
        ob_start();
        eval("?>".$email_body);
        $email_body = ob_get_clean();

        if(!empty($this->emailData['userId'])){

            // set smtp for the company

            //Send Email Notifications for every notification email user Have store
            $user = getUserDetails($this->emailData['userId']);
            if($user->email != null){
                $to = $user->email;
                Mail::to($to)->send(new NotificationMail($subject,$email_body));
            }

        }
    }
}
