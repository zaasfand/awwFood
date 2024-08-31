<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Jobs\ScheduleLocationCheckJob;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    public function SendNotificationsApi(Request $request)
    {
        try {
            // Validate required fields
            $this->validate($request, [
                'templateCode' => 'required|string',

            ]);

            $templateCode = $request->templateCode;
           
            $emailData = $request->emailData;
            $webData = $request->webData;
            $appData = $request->appData;
            $smsData = $request->smsData;

            // Ensure at least one type of data is present
            if (empty($emailData) && empty($webData) && empty($appData) && empty($smsData)) {
                return response()->json([
                    'returnType' => 'FAILURE',
                    'returnMessage' => 'At least one of emailData, webData, appData, or smsData must be provided.'
                ], 400);
            }

            // Call the SendNotifications function
            sendNotifications($templateCode, $emailData, $smsData, $appData, $webData);

            // Return success response
            return response()->json([
                'returnType' => 'SUCCESS',
                'returnMessage' => 'Notifications sent successfully.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'returnType' => 'FAILURE',
                'returnMessage' => $e->getMessage(),
                'returnData' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'returnType' => 'FAILURE',
                'returnMessage' => 'An error occurred while sending notifications.',
                'returnData' => $e->getMessage()
            ], 500);
        }
    }


    public function scheduleLocationCheck(Request $request)
    {
        try {
            // Validate required fields
            $this->validate($request, [
                'userId' => 'required|integer',
                'locationId' => 'required|integer',
                'minToCheck' => 'required|integer|min:0', // Ensure minToCheck is provided and is a valid integer
            ]);

            $userId = $request->userId;
            $locationId = $request->locationId;
            $radius = $request->radius ?? 100; // Default radius if not provided
            $minToCheck = $request->minToCheck;

            // Schedule the job to run after $minToCheck minutes
            ScheduleLocationCheckJob::dispatch($userId, $locationId, $radius, $minToCheck)
                ->delay(Carbon::now()->addMinutes($minToCheck));

            // Return success response
            return response()->json([
                'returnType' => 'SUCCESS',
                'returnMessage' => 'Job scheduled successfully.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'returnType' => 'FAILURE',
                'returnMessage' => $e->getMessage(),
                'returnData' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'returnType' => 'FAILURE',
                'returnMessage' => 'An error occurred while scheduling the job.',
                'returnData' => $e->getMessage()
            ], 500);
        }
    }

}
