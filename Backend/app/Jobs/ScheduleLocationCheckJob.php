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
use Illuminate\Support\Facades\DB;

class ScheduleLocationCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $userId;
    protected $locationId;
    protected $radius;
    protected $minToCheck;

    public function __construct($userId, $locationId, $radius, $minToCheck)
    {
        $this->userId = $userId;
        $this->locationId = $locationId;
        $this->radius = $radius ? $radius : 100;
        $this->minToCheck = $minToCheck ? $minToCheck : 40;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $attendanceStatus = $this->getAttendanceStatus();

            if ($attendanceStatus === 'Login') {
                if (!$this->isLocationAvailableAndValid()) {
                    $isLoggedOut = $this->markUserLogout();
                    if($isLoggedOut === 'Logout'){
                        $this->sendNotification();
                    }

                }
            }
        } catch (\Exception $e) {
            Log::error('Schedule Location Check Job Error: ', [$e->getMessage()]);
        }
    }

    /**
     * Get attendance status.
     *
     * @return string
     */
    private function getAttendanceStatus()
    {
        date_default_timezone_set('Asia/Karachi');
        $currentDate = date('Y-m-d');

        $attendanceCount = DB::table('hrms_biometric_attendance_logs')
            ->where('user_id', $this->userId)
            ->whereDate('attendance_date_time', $currentDate)
            ->count();

        return ($attendanceCount % 2 == 0) ? 'Logout' : 'Login';
    }

    /**
     * Check if the location is available and valid.
     *
     * @return bool
     */
    private function isLocationAvailableAndValid()
    {
        $locationRow = DB::table('hrms_staff_remote_location')
            ->where('id', $this->locationId)
            ->where('user_id', $this->userId)
            ->first();

        if (!$locationRow) {
            return false;
        }

        $lastLocationRow = DB::table('hrms_staff_remote_location')
            ->where('user_id', $this->userId)
            ->where('id', '>', $this->locationId)
            ->orderBy('id', 'asc')
            ->first();

        if (!$lastLocationRow) {
            return false;
        }

        $timeDifference = strtotime($lastLocationRow->date_time) - strtotime($locationRow->date_time);
        if ($timeDifference > $this->minToCheck * 60) { // 40 minutes
            return false;
        }

        $distance = $this->calculateDistance(
            $locationRow->lat, $locationRow->long,
            $lastLocationRow->lat, $lastLocationRow->long
        );

        return $distance <= $this->radius; // 100 meters
    }

    /**
     * Calculate the distance between two locations.
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function markUserLogout()
    {
        try {
            // Check attendance status
            $attendanceStatus = $this->getAttendanceStatus();

            if ($attendanceStatus === 'Logout') {
                return $attendanceStatus; // Exit if the status is already 'Logout'
            }

            $userId = $this->userId;

            // Set the time zone to Pakistan
            date_default_timezone_set('Asia/Karachi');
            $currentDateTime = now()->subMinutes($this->minToCheck > 10 ? 10 : 1); // Subtract 10 minutes from the current date and time

            // Insert the new attendance record
            DB::table('hrms_biometric_attendance_logs')->insert([
                'user_id' => $userId,
                'attendance_date_time' => $currentDateTime,
                'status' => '0',
                'attendance_type' => '2', // 2 for mobile
                'workcode' => '0',
                'reserved' => '0',
                'deleted_at' => null,
            ]);

            // Get the count of attendance records for the same date after insertion
            $attendanceCount = DB::table('hrms_biometric_attendance_logs')
                ->where('user_id', $userId)
                ->whereDate('attendance_date_time', $currentDateTime->toDateString())
                ->count();

            // Determine response based on the count
            $response = ($attendanceCount % 2 == 0) ? 'Logout' : 'Login';

            return $response;

        } catch (\Exception $e) {
            Log::error('Error in markUserLogout: ', [$e->getMessage()]);
            return 'Error'; // Or handle it according to your application's logic
        }
    }


    /**
     * Send a notification to the user.
     *
     * @return void
     */
    private function sendNotification()
    {
        $staff = getStaffDetails($this->userId);

        $notificationData = array(
            "userId" => $staff->userId,
            "variables" => array(
                "userFullName"  => $staff->userFullName,
            )
        );

        $templateCode = "force_remote_attendance_logout";

        SendNotifications($templateCode, $notificationData, $notificationData, $notificationData, $notificationData );
    }
}

