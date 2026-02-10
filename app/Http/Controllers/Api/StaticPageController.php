<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => StaticPage::all()
        ]);
    }

    public function show($slug)
    {
        $page = StaticPage::where('slug', $slug)->first();
        
        if (!$page) {
            return response()->json([
                'message' => 'Page not found'
            ], 404);
        }

        return response()->json([
            'data' => $page
        ]);
    }

    public function update(Request $request, StaticPage $staticPage)
    {
        $staticPage->update($request->validate([
            'title' => 'sometimes|required|string',
            'content' => 'sometimes|required|string',
        ]));

        return response()->json([
            'data' => $staticPage
        ]);
    }
}
