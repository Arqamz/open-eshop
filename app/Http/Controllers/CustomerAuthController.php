<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Exception;

class CustomerAuthController extends BaseController
{
    public function register(Request $request)
    {
        try {

            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:customers',
                'password' => 'required|string|min:8',
                'phone_number' => 'nullable|string|max:20',
                'address_line1' => 'nullable|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:255',
            ]);
    
            $user = Customer::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'address_line1' => $request->address_line1,
                'address_line2' => $request->address_line2,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'country' => $request->country,
            ]);
    
            return $this->sendSuccess("Customer created successfully", $user, 201);

        } catch (Exception $e) {
            return $this->handleException($e);
        }

    }

    public function login(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
                return $this->sendError("Unauthorized", null, 401);
            }

            return $this->sendSuccess("Logged in successfully", $token, 200);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return $this->sendSuccess("User fetched successfully", $user);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (Exception $e) {
            return $this->handleException($e);
        }
        return $this->sendSuccess("Logged out successfully");
    }

    public function forgotPassword(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);

            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return $this->sendSuccess('Password reset link sent successfully');
            }

            return $this->sendError('Unable to send password reset link', null, 400);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user) use ($request) {
                    $user->password = Hash::make($request->password);
                    $user->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return $this->sendSuccess('Password has been successfully reset');
            }

            return $this->sendError('Unable to reset password', null, 400);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function showResetForm($token)
    {
        try {
            return $this->sendSuccess('Reset password form', [
                'message' => 'Please provide your new password.',
                'token' => $token
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

}
