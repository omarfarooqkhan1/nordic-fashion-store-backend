<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Handle contact form submission
     */
    public function submit(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'subject' => 'required|string|max:255',
                'message' => 'required|string|max:5000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Extract validated data
            $contactData = $validator->validated();
            
            // Store the contact form submission in database
            DB::table('contact_forms')->insert([
                'first_name' => $contactData['firstName'],
                'last_name' => $contactData['lastName'],
                'email' => $contactData['email'],
                'subject' => $contactData['subject'],
                'message' => $contactData['message'],
                'status' => 'new',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Log the contact form submission// Note: Emails are not sent automatically
            // Admin will reply to user later via email
            // User gets immediate feedback via frontend toast

            return response()->json([
                'message' => 'Thank you for your message! We have received it and will reply to you via email soon.',
                'success' => true
            ], 200);

        } catch (\Exception $e) { return response()->json([
                'message' => 'Sorry, something went wrong. Please try again later.',
                'success' => false
            ], 500);
        }
    }


}
