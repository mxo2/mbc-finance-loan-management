<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\LoggedHistory;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        if (!file_exists(setup())) {
            header('location:install');
            die;
        }

        $user = \App\Models\User::find(1);
        if ($user) {
            \App::setLocale($user->lang ?? 'en');
        } else {
            \App::setLocale('en');
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $google_recaptcha = getSettingsValByName('google_recaptcha');
        if ($google_recaptcha == 'on') {
            $validation['g-recaptcha-response'] = 'required|captcha';
        } else {
            $validation = [];
        }
        $this->validate($request, $validation);

        $request->authenticate();
        $request->session()->regenerate();
        $loginUser = Auth::user();
        
        // Determine if this is a PWA request by checking the URL or Referer header
        $referer = $request->header('Referer');
        $isPwa = $request->is('pwa/*') || 
                 (strpos($referer, '/pwa/') !== false) || 
                 $request->has('source') && $request->input('source') === 'pwa';
        
        if ($loginUser->is_active == 0) {
            auth()->logout();
            return redirect()->route('login')->with('error', __('Your account is temporarily inactive. Please contact your administrator to reactivate your account.'));
        }
        if (empty($loginUser->email_verified_at)) {
            auth()->logout();
            return redirect()->route('login')->with('error', __('Verification required: Please check your email to verify your account before continuing.'));
        }
        if ($loginUser->type == 'owner') {
            if ($loginUser->subscription_expire_date != null && date('Y-m-d') > $loginUser->subscription_expire_date) {
                assignSubscription(1);
                return redirect()->intended($isPwa ? '/pwa/' : RouteServiceProvider::HOME)->with('error', __('Your subscription has ended, and access to premium features is now restricted. To continue using our services without interruption, please renew your plan or upgrade to a higher-tier package.'));
            }
        }
        userLoggedHistory();
        
        // Handle redirection based on user type and source
        if ($isPwa) {
            // Redirect to PWA dashboard
            return redirect('/pwa/');
        } else {
            // Redirect to main Laravel app dashboard
            return redirect()->intended(RouteServiceProvider::HOME);
        }
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
