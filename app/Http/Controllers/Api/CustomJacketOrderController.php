<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomJacketOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomJacketOrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'color' => 'required|string',
            'size' => 'required|string',
            'material' => 'required|string',
            'lining' => 'required|string',
            'monogram' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = CustomJacketOrder::create([
            'user_id' => Auth::id(),
            'color' => $request->color,
            'size' => $request->size,
            'material' => $request->material,
            'lining' => $request->lining,
            'monogram' => $request->monogram,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'order' => $order], 201);
    }
}
