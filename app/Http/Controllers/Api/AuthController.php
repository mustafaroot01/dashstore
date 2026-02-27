<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => ['required', 'string', 'max:20']]);

        $result = $this->otp->sendOtp($request->phone);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال رمز التحقق',
            'channel' => Setting::get('otp_channel', 'whatsapp'),
            'data'    => $result,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone'    => ['required'],
            'password' => ['required'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الهاتف أو كلمة المرور غير صحيحة',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json(['success' => false, 'message' => 'حسابك موقوف، تواصل مع الدعم'], 403);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'user'    => $user->load('governorate', 'district'),
            'token'   => $token,
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'phone'          => ['required', 'unique:users,phone'],
            'password'       => ['required', 'string', 'min:6'],
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'gender'         => ['required', 'in:male,female'],
            'governorate_id' => ['required', 'exists:governorates,id'],
            'district_id'    => ['nullable', 'exists:districts,id'],
            'address'        => ['nullable', 'string'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $user  = User::create($data);
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'success' => true, 
            'user'    => $user->load('governorate', 'district'), 
            'token'   => $token
        ], 201);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['phone' => ['required', 'exists:users,phone']]);
        $result = $this->otp->sendResetOtp($request->phone);
        return response()->json(['success' => true, 'data' => $result]);
    }

    public function verifyResetCode(Request $request)
    {
        $request->validate(['phone' => ['required'], 'code' => ['required']]);
        $result = $this->otp->verifyOtp($request->phone, $request->code);
        if (! ($result['success'] ?? false)) {
            return response()->json(['success' => false, 'message' => 'الكود غير صحيح'], 422);
        }
        return response()->json(['success' => true]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'phone'    => ['required', 'exists:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::where('phone', $data['phone'])->update(['password' => Hash::make($data['password'])]);
        return response()->json(['success' => true, 'message' => 'تم تغيير كلمة المرور']);
    }

    public function deleteAccount(Request $request)
    {
        $request->user()->tokens()->delete();
        $request->user()->delete();
        return response()->json(['success' => true]);
    }
}
