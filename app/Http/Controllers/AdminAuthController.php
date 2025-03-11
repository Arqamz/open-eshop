<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Customer;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Exception;

class AdminAuthController extends BaseController
{
    /**
     * Register a new admin
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8',
            ]);
        
            $admin = Admin::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return $this->sendSuccess('Admin registered successfully', $admin, 201);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Login for admins
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $token = auth('admin')->attempt($request->only('email', 'password'));

            if (!$token) {
                return $this->sendError('Unauthorized', null, 401);
            }

            return $this->sendSuccess('Login successful', $token, 200);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function me()
    {
        try {
            $admin = JWTAuth::parseToken()->authenticate();
            Log::info("Parsing admin", ['admin' => $admin]);

            return $this->sendSuccess('Admin details', $admin);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request)
    {

        try{
            $request->validate(['email' => 'required|email']);

            $status = Password::broker('admins')->sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return $this->sendSuccess('Password reset link sent successfully', null, 200);
            }

            return $this->sendError('Unable to send password reset link', null, 400);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed',
            ]);
    
            $status = Password::broker('admins')->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($admin) use ($request) {
                    $admin->password = Hash::make($request->password);
                    $admin->save();
                }
            );
    
            if ($status === Password::PASSWORD_RESET) {
                return $this->sendSuccess('Password has been successfully reset.', null, 200);
            }    
            return $this->sendError('Unable to reset password.', null, 400);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Show the reset form token
     */
    public function showResetForm($token)
    {
        return $this->sendSuccess('Please provide your new password.', $token);
    }

    /**
     * View all admins
     */
    public function viewAllAdmins()
    {
        try {
            $admins = Admin::all();
            return $this->sendSuccess('All admins retrieved successfully', $admins, 200);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * View all customers sorted by date
     */
    public function viewAllCustomers()
    {
        try {
            $customers = Customer::orderBy('created_at', 'desc')->get();
            return $this->sendSuccess('All customers retrieved successfully', $customers, 200);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Delete an admin account
     */
    public function deleteAdmin($id)
    {
        try {
            $admin = Admin::find($id);

            if (!$admin) {
                return $this->sendError('Admin not found', null, 404);
            }

            $admin->delete();
            return $this->sendSuccess('Admin deleted successfully', null, 200);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
