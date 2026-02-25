<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name'  => ['sometimes', 'string', 'max:100'],
            'gender'     => ['sometimes', 'in:male,female'],
            'address'    => ['sometimes', 'string'],
        ]);

        $request->user()->update($data);

        return response()->json([
            'success' => true,
            'data'    => $request->user()->fresh(),
        ]);
    }
}
