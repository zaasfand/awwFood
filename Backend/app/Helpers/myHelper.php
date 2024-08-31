<?php
use Carbon\Carbon;
use App\Models\User;
use App\Jobs\AppNotificationJob;
use App\Jobs\SMSNotificationJob;
use App\Jobs\EmailNotificationJob;
use Illuminate\Support\Facades\Log;
use App\Events\WebNotificationEvent;
use Faker\Provider\bg_BG\PhoneNumber;

/**
 * Created By Muhammad Rizwan Khalil
 * Purpose Send Different Type of notification like like email, SMS, APP, and web ortal notification
 * @param mixed $templateCode
 * @param mixed $emailData
 * @param mixed $smsData
 * @param mixed $appData
 * @param mixed $webData
 * @return [Void]
 */

 function SendNotifications($templateCode, $emailData, $smsData, $appData, $webData){
    try {
        $templateData = DB::table('hrms_notifications_templates')->where('template_code',$templateCode)->get()->first();
        if($templateData != null){
            if ($emailData != null && $templateData->email_status) {
                SendMailNotification($templateData, $emailData);
            }

            if ($smsData != null && $templateData->sms_status) {
                SendSmsNotification($templateData,$smsData);
            }

            if ($appData != null && $templateData->app_status) {
                SendAppNotification($templateData,$appData);
            }

            if ($webData != null && $templateData->web_status) {
                SendWebNotification($templateData,$webData);
            }

            $returnData = array(
                'returnType' => 'Success',
                'returnMessage' => 'Notification sent successfully.',
            );
        }else{
            $returnData = array(
                'returnType' => 'Failure',
                'returnMessage' => 'Notification template not found.',
            );
        }
        return $returnData;
    } catch (Exception $e) {
        $returnData = array(
            'returnType' => 'Failure',
            'returnMessage' => $e->getMessage(),
        );
        Log::error($e->getMessage());
        return $returnData;
    }
}


/**
 * Created by Muhammmad Rizwan khalil
 * Purpose Send Email Notification
 * @param mixed $templatecode OR TemplateData
 * @param mixed $emailData Array
 * The emailData Array should containonly one key userId OR memberID OR appUserId and variables array with template variable data.
 * @return void
 */
function SendMailNotification($template, $emailData){
    // The below condition is to check if template data is passed or Template code is passed;
    if (is_array($template) || is_object($template)) {
        $templateData = $template;
        EmailNotificationJob::dispatch($templateData, $emailData);
    }else{
        $templateData = DB::table('hrms_notifications_templates')->where('template_code',$template)->get()->first();
        if($templateData != null){
            EmailNotificationJob::dispatch($templateData, $emailData);
        }
    }
}


/**
* Created by Muhammmad Rizwan khalil
* Purpose Send SMS Notification Driver Member And other user Like Safr, NEMt, Admin
* @param mixed $templatecode OR TemplateData
* @param mixed $smsData Array
* The smsData Array should contain the key userId OR memberID or appUserId
* only one of the key is required and variables array with template variable data.
* @return void
*/
function SendSmsNotification($template,$smsData){

    // The below condition is to check if template data is passed or Template code is passed;
    if (is_array($template) || is_object($template)) {
        $templateData = $template;
        SMSNotificationJob::dispatch($templateData,$smsData);
    }else{
        $templateData = DB::table('hrms_notifications_templates')->where('template_code',$template)->get()->first();
        if($templateData != null){
            SMSNotificationJob::dispatch($templateData,$smsData);
        }
    }


}


/**
* Created by Muhammmad Rizwan khalil
* Purpose Send App Notification
* @param mixed $templatecode OR TemplateData
* @param mixed $appData Array
* The appData Array should contain the key appUserId and variables array with template variable data.
* @return void
*/
function SendAppNotification($template,$appData){
    // The below condition is to check if template data is passed or Template code is passed;
    if (is_array($template) || is_object($template)) {
        $templateData = $template;
        AppNotificationJob::dispatch($templateData,$appData);
    }else{
        $templateData = DB::table('hrms_notifications_templates')->where('template_code',$template)->get()->first();
        if($templateData != null){
            AppNotificationJob::dispatch($templateData,$appData);
        }
    }
}


/**
 * Created by Muhammad Rizwan Khalil
 * Purpose: Send Web Notification
 * @param mixed $template OR TemplateData
 * @param array $webData Array
 * The webData array should contain the keys userId, assignerUserId (optional), link (optional), and variables array with template variable data.
 * @return void
 */
function SendWebNotification($template, $webData){
    date_default_timezone_set("UTC");

    // Check if template data is passed or template code is passed
    $templateData = is_array($template) || is_object($template) ? $template : DB::table('hrms_notifications_templates')->where('template_code', $template)->first();

    if($templateData && !empty($webData['userId'])){
        $title = $templateData->web_notification_title;
        $body = $templateData->web_notification_text;
        foreach ($webData['variables'] as $key => $value) {
            $body = str_replace('[@' . $key . '@]', $value, $body);
        }

        // Insert the notification and get the inserted ID
        $notificationId = DB::table('hrms_web_notifications')->insertGetId([
            'hrms_notifications_template_id' => $templateData->hrms_notifications_template_id,
            'assigner_user_id'               => $webData['assignerUserId'] ?? null,
            'assignee_user_id'               => $webData['userId'],
            'title'                          => $title,
            'body'                           => $body,
            'link'                           => $webData['link'] ?? null,
            'created_at'                     => Carbon::now(),
        ]);

        // Get notification details
        $notification = getNotification($notificationId);
        $notification->created_at = Carbon::parse($notification->created_at)->diffForHumans();

        // Trigger web notification event
        event(new WebNotificationEvent($notification));
    }
}

/**
 * Created by Muhammad Rizwan Khalil
 * Purpose: Return Notification details
 * @param int $notificationId
 * @return object
 */
if (!function_exists('getNotification')) {
    function getNotification($notificationId) {
        return DB::table('hrms_web_notifications')
            ->select(
                'hrms_web_notifications.*',
                'hrms_users.user_ID as userId',
                'hrms_staff.staff_FirstName as firstName',
                'hrms_staff.staff_LastName as lastName',
                'hrms_users.user_picture as profilePicture',
                'hrms_staff.staff_Email as email',
                'hrms_staff.staff_primary_contact as phone',
                DB::raw("CONCAT(hrms_staff.staff_FirstName, ' ', hrms_staff.staff_MiddleName, ' ', hrms_staff.staff_LastName) as userFullName")
            )
            ->join('hrms_users', 'hrms_users.user_ID', '=', 'hrms_web_notifications.assignee_user_id')
            ->join('hrms_staff', 'hrms_users.user_emp_id', '=', 'hrms_staff.staff_id')
            ->where('hrms_web_notifications.hrms_web_notification_id', $notificationId)
            ->first();
    }
}




/**
 * Created by Muhammad Rizwan khalil
 * Purpose to return user details;
 * @param mixed $templateCode
 * @param mixed $variables
 *@author Muhammad Rizwan Khalil
 */
if(!function_exists('getUserDetails')) {
    function getUserDetails($userId) {
        $userDetails = DB::table('hrms_users')
            ->join('hrms_staff', 'hrms_users.user_emp_id', '=', 'hrms_staff.staff_id')
            ->where('hrms_users.user_ID', $userId)
            ->select(
                'hrms_staff.staff_Email as email',
                'hrms_staff.staff_branch_id as branchId',
                'hrms_staff.staff_company_id as companyId',
                'hrms_staff.staff_primary_contact as phone',
                DB::raw("CONCAT(hrms_staff.staff_FirstName, ' ', hrms_staff.staff_MiddleName, ' ', hrms_staff.staff_LastName) as userFullName"),
                'pushy_device_token_id as pushyDeviceToken'
            )
            ->first();

        return $userDetails;
    }

}

/**
 * Created by Muhammad Rizwan khalil
 * Purpose to return user details;
 * @param mixed $templateCode
 * @param mixed $variables
 *@author Muhammad Rizwan Khalil
 */
if(!function_exists('getStaffDetails')) {
    function getStaffDetails($staffId) {
        $userDetails = DB::table('hrms_users')
            ->join('hrms_staff', 'hrms_users.user_emp_id', '=', 'hrms_staff.staff_id')
            ->where('hrms_staff.staff_id', $staffId)
            ->select(
                'hrms_users.user_ID as userId',
                'hrms_staff.staff_Email as email',
                'hrms_staff.staff_primary_contact as phone',
                DB::raw("CONCAT(hrms_staff.staff_FirstName, ' ', hrms_staff.staff_MiddleName, ' ', hrms_staff.staff_LastName) as userFullName"),
                'pushy_device_token_id as pushyDeviceToken'
            )
            ->first();

        return $userDetails;
    }

}


/**
 * Created by Muhammad Rizwan khalil
 * Purpose to return the start and end date of the previous date range with the startDate and endDate;
 * @param mixed $templateCode
 * @param mixed $variables
 *
 */
if(!function_exists('getPreviousDateRange')) {
    function getPreviousDateRange($startDate, $endDate) {
        if(!$startDate || !$startDate){
            return [$startDate, $startDate];
        }else{
            $diff = Carbon::parse($startDate)->diffInDays($endDate);
            $previousStartDate = Carbon::parse($startDate)->subDays($diff + 1)->format('Y-m-d');
            $previousEndDate = Carbon::parse($endDate)->subDays($diff + 1)->format('Y-m-d');
            return [$previousStartDate, $previousEndDate];
        }
    }
}


?>
