<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRoleEnum;
use App\Enums\VerifyOtpForEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Authentication\ChangePasswordRequest;
use App\Http\Requests\V1\Authentication\ForgotPasswordRequest;
use App\Http\Requests\V1\Authentication\LoginRequest;
use App\Http\Requests\V1\Authentication\RegisterRequest;
use App\Http\Requests\V1\Authentication\ResendOtpRequest;
use App\Http\Requests\V1\Authentication\ResetPasswordRequest;
use App\Http\Requests\V1\Authentication\VerifyOtpRequest;
use App\Http\Resources\User\V1\UserResource;
use App\Mail\Auth\VerifyEmail;
use App\Models\User;
use App\Services\OtpService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Throwable;

class AuthenticationController extends Controller
{
    public function __construct(public OtpService $otpService) {}

    /**
     *  Register
     *
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            $userResource = new UserResource($user);
            $otp = $this->otpService->saveOtpToDatabase($user->email);

            $userResource['token'] = $user->createToken('authToken')->plainTextToken;

            Mail::to($user->email)->queue(new VerifyEmail($otp, $user));
        } catch (Exception $exception) {
            DB::rollBack();

            return Response::error($exception);
        }
        DB::commit();

        return Response::success($userResource, 'User register successful');
    }

    /**
     * Login
     *
     * @throws Throwable
     */
    public function login(LoginRequest $request): JsonResponse
    {
        DB::beginTransaction();
        $credentials = $request->only('email', 'password');
        try {
            if (! auth()->attempt($credentials)) {
                DB::rollBack();
                return Response::custom([], 403, 'Email or password is incorrect');
            }
            $user = auth()->user();
            $token = $user->createToken('authToken')->plainTextToken;
            $user['token'] = $token;
            $userResource = new UserResource($user);
        } catch (\Exception $exception) {
            DB::rollBack();

            return Response::error($exception);
        }
        DB::commit();

        return Response::success($userResource, 'Login successful');
    }

    /**
     * Verify OTP
     *
     * @throws Throwable
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $isValidOtp = $this->otpService->verifyOtp($request->email, $request->otp);
            if (! $isValidOtp) {
                return Response::custom([], 403, 'Invalid OTP');
            }
            if ($request->verify_for == VerifyOtpForEnum::REGISTRATION->value) {
                $user = User::where('email', $request->email)->first();
                if ($user) {
                    $user->update(['email_verified_at' => now()]);
                }
                $this->otpService->deleteOtp($request->email);
            }
        } catch (\Exception $exception) {
            return Response::error($exception);
        }

        return Response::success([], 'OTP verified successfully');
    }

    /**
     * Forgot Password
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();
            if (! $user) {
                return Response::custom([], 404, 'User not found');
            }
            $otp = $this->otpService->saveOtpToDatabase($user->email);
            // Send OTP to user's email
            Mail::to($user->email)->queue(new VerifyEmail($otp, $user));
        } catch (Exception $exception) {
            return Response::error($exception);
        }

        return Response::success([], 'OTP sent to your email successfully');
    }

    /**
     * Reset Password
     *
     * @throws Throwable
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = User::where('email', $request->email)->first();
            if (! $user) {
                return Response::custom([], 404, 'User not found');
            }
            $isValidOtp = $this->otpService->verifyOtp($request->email, $request->otp);
            if (! $isValidOtp) {
                return Response::custom([], 403, 'Invalid OTP');
            }
            $this->otpService->deleteOtp($user->email);
            $user->password = bcrypt($request->password);
            $user->save();

        } catch (Exception $exception) {
            DB::rollBack();

            return Response::error($exception);
        }
        DB::commit();

        return Response::success([], 'Password reset successfully');
    }

    /**
     * Resend OTP
     */
    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();
            if (! $user) {
                return Response::custom([], 404, 'User not found');
            }
            $otp = $this->otpService->saveOtpToDatabase($user->email);
            // Send OTP to user's email
            Mail::to($user->email)->queue(new VerifyEmail($otp, $user));
        } catch (Exception $exception) {
            return Response::error($exception);
        }

        return Response::success([], 'OTP resent successfully');

    }

    /**
     * Change Password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();
            if (! Hash::check($request->old_password, $user->password)) {
                return Response::custom([], 404, 'Old password is incorrect');
            }
            $user->update(['password' => Hash::make($request->new_password)]);
        } catch (\Exception $exception) {
            return Response::error($exception);
        }

        return Response::success([], 'Password changed successfully');
    }

    /**
     * Logout
     */
    public function logout(): JsonResponse
    {
        try {
            auth()->user()->tokens()->delete();
        } catch (\Exception $exception) {
            return Response::error($exception);
        }

        return Response::success([], 'Logout successful');
    }
}
