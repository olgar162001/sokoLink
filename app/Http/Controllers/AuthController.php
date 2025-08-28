<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Helpers\SMSHelper;

class AuthController extends Controller
{
    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|email|unique:users,email',
            'phone'    => 'nullable|string|unique:users,phone',
            'password' => 'nullable|string|min:6',
            'role'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $role = Role::where('name', $request->role)->first();
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role_id' => $role->id
        ]);


        return response()->json(['status' => true, 'message' => 'User registered successfully', 'data' => $user]);
    }

    
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'nullable|email|required_without:phone',
            'phone'    => 'nullable|string|required_without:email|exists:users,phone',
            'password' => 'nullable|string|required_if:email,!=,null',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Email/password login
        if ($request->filled('email')) {
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json(['status' => false, 'message' => 'Invalid credentials'], 401);
            }

            $user  = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token
            ]);
        }

        // OTP login via phone
        if ($request->filled('phone')) {
            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'User not found'], 404);
            }

            // Generate OTP
            $otp = rand(100000, 999999);
            $user->otp_code = $otp;
            $user->otp_expires_at = Carbon::now()->addMinutes(5);
            $user->save();

            // Send OTP via SMS
            SMSHelper::sendSMS($user->phone, "Your OTP is: $otp");

            return response()->json([
                'status' => true,
                'message' => 'OTP sent successfully',
                'debug_otp' => $otp // remove in production
            ]);
        }

        return response()->json(['status' => false, 'message' => 'Invalid request'], 400);
    }

    /**
     * Verify OTP for phone login
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,phone',
            'otp'   => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)
                    ->where('otp_code', $request->otp)
                    ->where('otp_expires_at', '>', Carbon::now())
                    ->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired OTP'], 401);
        }

        // Clear OTP after successful verification
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        // Generate token
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }
    
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['status' => true, 'message' => 'Logged out successfully']);
    }
}
