<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminContactController extends Controller
{
    /**
     * Get all contact form submissions
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('contact_forms')
                ->select([
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'subject',
                    'message',
                    'status',
                    'admin_notes',
                    'created_at',
                    'updated_at'
                ]);

            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->has('from_date') && $request->from_date) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->has('to_date') && $request->to_date) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            // Search by email, subject, or message
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', '%' . $search . '%')
                      ->orWhere('subject', 'like', '%' . $search . '%')
                      ->orWhere('message', 'like', '%' . $search . '%');
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            if (in_array($sortBy, ['created_at', 'email', 'status'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $contactForms = $query->paginate($perPage);

            // Transform the data to match frontend expectations
            $transformedForms = $contactForms->map(function ($form) {
                return [
                    'id' => $form->id,
                    'firstName' => $form->first_name,
                    'lastName' => $form->last_name,
                    'email' => $form->email,
                    'subject' => $form->subject,
                    'message' => $form->message,
                    'status' => $form->status,
                    'admin_notes' => $form->admin_notes,
                    'created_at' => $form->created_at,
                    'updated_at' => $form->updated_at,
                ];
            });

            return response()->json([
                'data' => $transformedForms,
                'pagination' => [
                    'current_page' => $contactForms->currentPage(),
                    'last_page' => $contactForms->lastPage(),
                    'per_page' => $contactForms->perPage(),
                    'total' => $contactForms->total(),
                    'has_more_pages' => $contactForms->hasMorePages(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch contact forms', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to fetch contact forms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update contact form status and admin notes
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:new,read,replied,closed',
                'admin_notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $contactForm = DB::table('contact_forms')->where('id', $id)->first();
            
            if (!$contactForm) {
                return response()->json([
                    'message' => 'Contact form not found'
                ], 404);
            }

            DB::table('contact_forms')
                ->where('id', $id)
                ->update([
                    'status' => $request->status,
                    'admin_notes' => $request->admin_notes,
                    'updated_at' => now(),
                ]);

            Log::info('Contact form status updated', [
                'contact_form_id' => $id,
                'status' => $request->status,
                'admin_notes' => $request->admin_notes
            ]);

            return response()->json([
                'message' => 'Contact form updated successfully',
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update contact form', [
                'error' => $e->getMessage(),
                'contact_form_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to update contact form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete contact form
     */
    public function destroy($id)
    {
        try {
            $contactForm = DB::table('contact_forms')->where('id', $id)->first();
            
            if (!$contactForm) {
                return response()->json([
                    'message' => 'Contact form not found'
                ], 404);
            }

            DB::table('contact_forms')->where('id', $id)->delete();

            Log::info('Contact form deleted', [
                'contact_form_id' => $id,
                'email' => $contactForm->email
            ]);

            return response()->json([
                'message' => 'Contact form deleted successfully',
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete contact form', [
                'error' => $e->getMessage(),
                'contact_form_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to delete contact form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send reply to contact form submission
     */
    public function reply(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:2000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $contactForm = DB::table('contact_forms')->where('id', $id)->first();
            
            if (!$contactForm) {
                return response()->json([
                    'message' => 'Contact form not found'
                ], 404);
            }

            // Send reply email to customer
            $this->sendReplyEmail($contactForm, $request->message);

            // Update status to replied
            DB::table('contact_forms')
                ->where('id', $id)
                ->update([
                    'status' => 'replied',
                    'admin_notes' => "Admin replied: " . $request->message,
                    'updated_at' => now(),
                ]);

            Log::info('Reply sent to contact form', [
                'contact_form_id' => $id,
                'customer_email' => $contactForm->email,
                'reply_message' => $request->message
            ]);

            return response()->json([
                'message' => 'Reply sent successfully',
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send reply', [
                'error' => $e->getMessage(),
                'contact_form_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to send reply',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send reply email to customer
     */
    private function sendReplyEmail($contactForm, $replyMessage)
    {
        try {
            Mail::send('emails.contact-reply', [
                'contactForm' => $contactForm,
                'replyMessage' => $replyMessage,
                'timestamp' => now()
            ], function ($message) use ($contactForm, $replyMessage) {
                $message->to($contactForm->email)
                        ->subject('Re: ' . $contactForm->subject)
                        ->from(env('MAIL_FROM_ADDRESS', 'noreply@nordflex.shop'), env('MAIL_FROM_NAME', 'Nord Flex Support'));
            });

            Log::info('Reply email sent successfully', [
                'contact_form_id' => $contactForm->id,
                'customer_email' => $contactForm->email
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send reply email', [
                'error' => $e->getMessage(),
                'contact_form_id' => $contactForm->id,
                'customer_email' => $contactForm->email
            ]);
            throw $e;
        }
    }
}
