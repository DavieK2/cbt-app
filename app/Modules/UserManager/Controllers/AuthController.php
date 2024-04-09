<?php

namespace App\Modules\UserManager\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\CBT\Models\CheckInModel;
use App\Modules\CBT\Requests\StudentLoginRequest;
use App\Modules\SchoolManager\Models\StudentProfileModel;
use App\Modules\UserManager\Features\LoginFeature;
use App\Modules\UserManager\Features\TwoFactorAuthenticationFeature;
use App\Modules\UserManager\Requests\LoginRequest;
use App\Modules\UserManager\Requests\TwoFactorAuthenticationOTPRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        return $this->serve(new LoginFeature(), $request->validated());
    }

    public function logout()
    {
        $user = auth()->user();
        
        $user->tokens()->delete();

        auth()->logout($user);

        return response()->json(['message' => 'Success']);

    }

    public function  get2FA()
    {
        return $this->serve(new TwoFactorAuthenticationFeature( request()->user() ));
    }

    public function verify2FA(TwoFactorAuthenticationOTPRequest $request)
    {
        return $this->serve(new TwoFactorAuthenticationFeature( request()->user() ), $request->validated() );
    }

    public function CBTLogin(AssessmentModel $assessment, StudentLoginRequest $request)
    {
        $data = $request->validated();

        $student = StudentProfileModel::firstWhere('student_code', $data['studentCode']);

        if( ! $student ){
            return response()->json([
                'message' => 'Invalid Student ID'
            ], 404);
        }

        $checkIn = CheckInModel::where('student_profile_id', $student->uuid)->where('assessment_id', $assessment->uuid)->first();

        if( ( ! $assessment->is_standalone ) && ( ! $checkIn ) ){
            return response()->json([
                'message' => 'Have not Checked In'
            ], 403);
        }

        if( ( ! $assessment->is_standalone ) && Carbon::parse( $checkIn->checked_in_expires_at )->lt( now() ) ){
            return response()->json([
                'message' => 'Have not Checked In'
            ], 403);
        }

        Auth::guard('student')->login($student);

        $token = $student->createToken('student-exam')->plainTextToken;

        $url = url("/cbt/{$assessment->assessment_code}/");
        
        $url = $assessment->is_standalone ? "$url/s" : "$url/t";

        return response()->json([
            'token' => $token,
            'url'   => $url
        ]);

    }

    public function CBTLogout(AssessmentModel $assessment)
    {
        $student = auth()->guard('student')->user();
        
        $student->tokens()->delete();

        auth()->guard('student')->logout($student);

        return response()->json(['message' => 'Success']);
    }
}
