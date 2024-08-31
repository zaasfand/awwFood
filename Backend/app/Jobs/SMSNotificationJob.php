<?php

namespace App\Jobs;

use Twilio\Rest\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SMSNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $templateData,$smsData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($templateData,$smsData)
    {
       $this->templateData = $templateData;
       $this->smsData = $smsData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {
            $message = $this->templateData->sms_body;
            foreach ($this->smsData['variables'] as $key => $value) {
                $key = '[@'.$key.'@]';
                $message = str_replace($key,$value,$message);
            }

            if(!empty($this->smsData['userId'])){
                $user = getUserDetails($this->smsData['userId']);
                if($user->phone != null){
                    $this->SendMessage($user->phone,$message);
                }
            }
        } catch (\Exception $e) {
            Log::error('SMS Notification Error.',[$e->getMessage()]);
        }

    }

    public function SendMessage($to,$message){
        try {
            $to = '+'.$to;
            $token = config("constant.TWILIO_AUTH_TOKEN");
            $twilio_sid = config("constant.TWILIO_SID");
            $client = new Client($twilio_sid, $token);
            $client->messages->create($to, [
                "from" => '+'.config("constant.TWILIO_FROM"),
                'body' => $message]);
        } catch (\Services_Twilio_RestException $e) {
            echo $e->getStatus() . "<br>";
        }
    }

}
