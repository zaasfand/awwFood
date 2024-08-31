<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class AppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $templateData,$appData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($templateData,$appData)
    {
       $this->templateData = $templateData;
       $this->appData = $appData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $user = getUserDetails($this->appData['userId']);

            if($user && $user->pushyDeviceToken){
                $token = $user->pushyDeviceToken;
                $apiKey =  config('constant.PUSHY_SECRET_KEY');
                $title = $this->templateData->app_notification_title;
                $notificationType = $this->templateData->app_notification_type;
                $notificationBody = $this->templateData->app_notification_body;

                foreach ($this->appData['variables'] as $key => $value) {
                    $key = '[@'.$key.'@]';
                    $notificationBody = str_replace($key,$value,$notificationBody);
                }

                $data = array(
                    'notificationType'  =>  $notificationType,
                    'message' => $notificationBody
                );

                $data = array_merge($data,$this->appData['variables']);
                $to = array($token);

                $options = array(
                    'notification' => array(
                        'badge' => (int) 1,
                        'sound' => 'ping.aiff',
                        'title' => $title,
                        'body'  => $notificationBody
                    )
                );

                $this->sendPushNotification($data, $to, $options,$apiKey);
            }
          } catch (\Exception $e) {
            Log::error('App Notification job Error.',[$e->getMessage()]);
          }
    }

    public function sendPushNotification($data, $to, $options, $apiKey)
    {
        try {
            $post = $options ?: array();
            $post['to'] = $to;
            $post['data'] = $data;
            $post['options'] = $options;
            $headers = array(
                'Content-Type: application/json'
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.pushy.me/push?api_key=' . $apiKey);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post, JSON_UNESCAPED_UNICODE));
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                error_log(curl_error($ch));
            }
            curl_close($ch);
        } catch (Exception $e) {
            // Handle the exception
            Log::error('App Notification job error push.',[$e->getMessage()]);
        }
    }
}
