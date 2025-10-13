<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Faq::orderBy('order')->get()
        ]);
    }

    public function store(Request $request)
    {
        $faq = Faq::create($request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'order' => 'integer',
        ]));
        return response()->json([
            'data' => $faq
        ], 201);
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($request->validate([
            'question' => 'sometimes|required|string',
            'answer' => 'sometimes|required|string',
            'order' => 'integer',
        ]));
        return response()->json([
            'data' => $faq
        ]);
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return response()->json([
            'message' => 'FAQ deleted successfully'
        ]);
    }
}
