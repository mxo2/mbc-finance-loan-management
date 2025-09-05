<?php

namespace App\Http\Controllers;

use App\Models\Custom;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FAQRCode\Google2FA;
use Illuminate\Support\Facades\Cookie;

class SettingController extends Controller
{

    //    ---------------------- Account --------------------------------------------------------
    public function index()
    {
        $loginUser = \Auth::user();
        $settings = settings();
        $timezones = config('timezones');
        return view('settings.index', compact('loginUser', 'settings', 'timezones'));
    }

    public function accountData(Request $request)
    {
        $loginUser = \Auth::user();
        $user = User::find($loginUser->id);
        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $user->id,
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }


        if ($request->hasFile('profile')) {
            $uploadResult = handleFileUpload($request->file('profile'), 'upload/profile/');

            if ($uploadResult['flag'] == 0) {
                return redirect()->back()->with('error', $uploadResult['msg']);
            }

            if (!empty($user->profile)) {
                deleteOldFile($user->profile, 'upload/profile/');
            }
            $user->profile = $uploadResult['filename'];
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->save();


        return redirect()->back()->with('success', 'User profile settings successfully updated.')->with('tab', 'user_profile_settings');
    }

    public function accountDelete(Request $request)
    {
        $loginUser = \Auth::user();
        $loginUser->delete();

        return redirect()->back()->with('success', 'Your account successfully deleted.');
    }

    //    ---------------------- Password --------------------------------------------------------



    public function passwordData(Request $request)
    {
        if (\Auth::Check()) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'current_password' => 'required',
                    'new_password' => 'required|min:6',
                    'confirm_password' => 'required|same:new_password',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $loginUser = \Auth::user();
            $data = $request->All();

            $current_password = $loginUser->password;
            if (Hash::check($data['current_password'], $current_password)) {
                $user_id = $loginUser->id;
                $user = User::find($user_id);
                $user->password = Hash::make($data['new_password']);;
                $user->save();

                return redirect()->back()->with('success', __('Password successfully updated.'))->with('tab', 'password_settings');
            } else {
                return redirect()->back()->with('error', __('Please enter valid current password.'))->with('tab', 'password_settings');
            }
        } else {
            return redirect()->back()->with('error', __('Invalid user.'))->with('tab', 'password_settings');
        }
    }

    //    ---------------------- General --------------------------------------------------------


    public function generalData(Request $request)
    {
        $user = \Auth::user();
        $userType = $user->type;
        $parentId = parentId(); // Assume 1 for super admin

        $validator = \Validator::make($request->all(), [
            'application_name' => 'required',
        ]);

        $fileFields = ['logo', 'favicon', 'light_logo', 'landing_logo'];


        // Add file validation for each file field
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $validator->addRules([$field => 'mimes:png']);
            }
        }

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        // Save App Name
        if (!empty($request->application_name)) {
            Custom::setCommon(['APP_NAME' => $request->application_name]);

            \DB::insert(
                'INSERT INTO settings (`value`, `name`, `parent_id`) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                [$request->application_name, 'app_name', $parentId]
            );
        }

        // Save Copyright
        if (!empty($request->copyright)) {
            \DB::insert(
                'INSERT INTO settings (`value`, `name`, `parent_id`) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                [$request->copyright, 'copyright', $parentId]
            );
        }

        // Upload and store logo file names in DB
        foreach ($fileFields as $key => $field) {
            if ($request->hasFile($field)) {

                $upload = uploadLogoFile($request->file($field), $field, $parentId, $userType);

                if ($upload['flag'] != 1) {
                    return redirect()->back()->with('error', __($upload['msg']));
                }

                $filename = $upload['filename'];
                $settingKey = ($userType === 'super admin') ? $field : 'company_' . $field;

                \DB::insert(
                    'INSERT INTO settings (`value`, `name`, `parent_id`) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                    [$filename, $settingKey, $parentId]
                );
            }
        }

        // Extra toggles for super admin
        if ($userType === 'super admin') {
            $toggles = [
                'landing_page'              => $request->landing_page ?? 'off',
                'register_page'             => $request->register_page ?? 'off',
                'owner_email_verification'  => $request->owner_email_verification ?? 'off',
                'pricing_feature'           => $request->pricing_feature,
            ];

            foreach ($toggles as $key => $val) {
                \DB::insert(
                    'INSERT INTO settings (`value`, `name`, `type`, `parent_id`) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                    [$val, $key, 'common', $parentId]
                );
            }
        }

        return redirect()->back()
            ->with('success', __('General setting successfully saved.'))
            ->with('tab', 'general_settings');
    }


    //    ---------------------- SMTP --------------------------------------------------------



    public function smtpData(Request $request)
    {
        if (\Auth::Check()) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'sender_name' => 'required',
                    'sender_email' => 'required',
                    'server_driver' => 'required',
                    'server_host' => 'required',
                    'server_port' => 'required',
                    'server_username' => 'required',
                    'server_password' => 'required',
                    'server_encryption' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $smtpArray = [
                'FROM_NAME' => $request->sender_name,
                'FROM_EMAIL' => $request->sender_email,
                'SERVER_DRIVER' => $request->server_driver,
                'SERVER_HOST' => $request->server_host,
                'SERVER_PORT' => $request->server_port,
                'SERVER_USERNAME' => $request->server_username,
                'SERVER_PASSWORD' => $request->server_password,
                'SERVER_ENCRYPTION' => $request->server_encryption,
            ];
            foreach ($smtpArray as $key => $val) {
                if (!empty($val)) {
                    \DB::insert(
                        'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $val,
                            $key,
                            'smtp',
                            parentId(),
                        ]
                    );
                }
            }

            return redirect()->back()->with('success', __('SMTP settings successfully saved.'))->with('tab', 'email_SMTP_settings');
        } else {
            return redirect()->back()->with('error', __('Invalid user.'))->with('tab', 'email_SMTP_settings');
        }
    }

    public function smtpTest(Request $request)
    {
        return view('settings.testmail');
    }

    public function smtpTestMailSend(Request $request)
    {
        if (\Auth::check()) {
            $to = $request->email;
            $errorMessage = '';
            // Data for email
            $data = [
                'module' => 'test_mail',
                'subject' => 'Test Mail',
                'message' => __('This is a test mail.'),
            ];

            // Send email
            $response = sendEmail($to, $data);
            if ($response['status'] == 'error') {
                $errorMessage = $response['message'];
                return redirect()->back()->with('error', $errorMessage)->with('tab', 'email_SMTP_settings');;
            } else {
                $errorMessage = $response['message'];
                return redirect()->back()->with('success', $errorMessage)->with('tab', 'email_SMTP_settings');;
            }
        }
    }

    //    ---------------------- Payment --------------------------------------------------------



    public function paymentData(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'CURRENCY' => 'required',
                'CURRENCY_SYMBOL' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $currencyArray = [
            'CURRENCY' => $request->CURRENCY,
            'CURRENCY_SYMBOL' => $request->CURRENCY_SYMBOL,
            'bank_transfer_payment' => $request->bank_transfer_payment ?? 'off',
            'STRIPE_PAYMENT' => $request->stripe_payment ?? 'off',
            'paypal_payment' => $request->paypal_payment ?? 'off',
            'paystack_payment' => $request->paystack_payment ?? 'off',
        ];
        foreach ($currencyArray as $key => $val) {
            if (!empty($val)) {

                \DB::insert(
                    'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        'payment',
                        parentId(),
                    ]
                );
            }
        }

        //        For Bank Transfer Settings
        if (isset($request->bank_transfer_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'bank_name' => 'required',
                    'bank_holder_name' => 'required',
                    'bank_account_number' => 'required',
                    'bank_ifsc_code' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $bankArray = [
                'bank_transfer_payment' => $request->bank_transfer_payment ?? 'off',
                'bank_name' => $request->bank_name,
                'bank_holder_name' => $request->bank_holder_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_ifsc_code' => $request->bank_ifsc_code,
                'bank_other_details' => !empty($request->bank_other_details) ? $request->bank_other_details : '',
            ];

            foreach ($bankArray as $key => $val) {
                if (!empty($val)) {

                    \DB::insert(
                        'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $val,
                            $key,
                            'payment',
                            parentId(),
                        ]
                    );
                }
            }
        }

        // For Strip Settings
        if (isset($request->stripe_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'stripe_key' => 'required',
                    'stripe_secret' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $stripeArray = [
                'STRIPE_PAYMENT' => $request->stripe_payment ?? 'off',
                'STRIPE_KEY' => $request->stripe_key,
                'STRIPE_SECRET' => $request->stripe_secret,
            ];

            foreach ($stripeArray as $key => $val) {
                if (!empty($val)) {

                    \DB::insert(
                        'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $val,
                            $key,
                            'payment',
                            parentId(),
                        ]
                    );
                }
            }
        }


        // For Paypal Settings
        if (isset($request->paypal_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'paypal_mode' => 'required',
                    'paypal_client_id' => 'required',
                    'paypal_secret_key' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $paypalArray = [
                'paypal_payment' => $request->paypal_payment ?? 'off',
                'paypal_mode' => $request->paypal_mode,
                'paypal_client_id' => $request->paypal_client_id,
                'paypal_secret_key' => $request->paypal_secret_key,
            ];

            foreach ($paypalArray as $key => $val) {
                if (!empty($val)) {

                    \DB::insert(
                        'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $val,
                            $key,
                            'payment',
                            parentId(),
                        ]
                    );
                }
            }
        }


        // For Flutterwave Settings
        if (isset($request->flutterwave_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'flutterwave_public_key' => 'required',
                    'flutterwave_secret_key' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $flutterwaveArray = [
                'flutterwave_payment' => $request->flutterwave_payment ?? 'off',
                'flutterwave_public_key' => $request->flutterwave_public_key,
                'flutterwave_secret_key' => $request->flutterwave_secret_key,
            ];

            foreach ($flutterwaveArray as $key => $val) {
                if (!empty($val)) {

                    \DB::insert(
                        'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $val,
                            $key,
                            'payment',
                            parentId(),
                        ]
                    );
                }
            }
        }

        // For paystack Settings
        if (isset($request->paystack_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'paystack_public_key' => 'required',
                    'paystack_secret_key' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $paystackArray = [
                'paystack_payment' => $request->paystack_payment ?? 'off',
                'paystack_public_key' => $request->paystack_public_key,
                'paystack_secret_key' => $request->paystack_secret_key,
            ];

            foreach ($paystackArray as $key => $val) {
                if (!empty($val)) {

                    \DB::insert(
                        'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $val,
                            $key,
                            'payment',
                            parentId(),
                        ]
                    );
                }
            }
        }

        return redirect()->back()->with('success', __('Payment successfully saved.'))->with('tab', 'payment_settings');
    }

    //    ---------------------- Company  --------------------------------------------------------



    public function companyData(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'company_name' => 'required',
                'company_email' => 'required',
                'company_phone' => 'required',
                'company_address' => 'required',
                'timezone' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $settings = $request->all();
        unset($settings['_token']);

        foreach ($settings as $key => $val) {
            if (!empty($val)) {

                \DB::insert(
                    'insert into settings (`value`, `name`,`parent_id`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        parentId(),
                    ]
                );
            }
        }
        return redirect()->back()->with('success', __('Company setting successfully saved.'))->with('tab', 'company_settings');
    }

    //    ---------------------- Language --------------------------------------------------------

    public function lanquageChange($lang)
    {
        $user = \Auth::user();
        $user->lang = $lang;
        $user->save();

        return redirect()->back()->with('success', __('Language successfully changed.'));
    }

    public function themeSettings(Request $request)
    {
         $themeSettings = $request->all();
        unset($themeSettings['_token']);

        foreach ($themeSettings as $key => $val) {
            if (!empty($val)) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        'common',
                        parentId(),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', __('Theme settings save successfully.'));
    }

    //    ---------------------- SEO Settings --------------------------------------------------------



    public function siteSEOData(Request $request)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'meta_seo_title' => 'required',
                'meta_seo_keyword' => 'required',
                'meta_seo_description' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $settings = $request->except(['_token', 'meta_seo_image']);

        if ($request->hasFile('meta_seo_image')) {
            $setting = \DB::table('settings')
                ->where('name', 'meta_seo_image')
                ->where('parent_id', parentId())
                ->first();


            $uploadResult = handleFileUpload($request->file('meta_seo_image'), 'upload/seo/');

            if ($uploadResult['flag'] === 0) {
                return redirect()->back()->with('error', $uploadResult['msg']);
            }

            if (!empty($setting) && !empty($setting->value)) {
                deleteOldFile($setting->value, 'upload/seo/');
            }
            \DB::insert(
                'INSERT INTO settings (`value`, `name`, `type`, `parent_id`) VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                [
                    $uploadResult['path'],
                    'meta_seo_image',
                    'SEO',
                    parentId(),
                ]
            );
        }
        // Save other SEO settings
        foreach ($settings as $key => $val) {
            if (!empty($val)) {
                \DB::insert(
                    'INSERT INTO settings (`value`, `name`, `type`, `parent_id`) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                    [
                        $val,
                        $key,
                        'SEO',
                        parentId(),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', __('Site SEO settings save successfully.'))->with('tab', 'site_SEO_settings');
    }

    // ---------------------- Google ReCaptcha Settings ---------------------------------------------
    public function googleRecaptchaData(Request $request)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'recaptcha_key' => 'required',
                'recaptcha_secret' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $settings = $request->all();
        unset($settings['_token']);

        $recaptchaArray = [
            'google_recaptcha' => $request->google_recaptcha ?? 'off',
            'recaptcha_key' => $request->recaptcha_key,
            'recaptcha_secret' => $request->recaptcha_secret,
        ];

        foreach ($recaptchaArray as $key => $val) {
            if (!empty($val)) {
                \DB::insert(
                    'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        'recaptcha',
                        parentId(),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', __('Google Recaptcha settings save successfully.'))->with('tab', 'google_recaptcha_settings');
    }

    // ---------------------- Footer Setting ---------------------------------------------
    public function footerSetting(Request $request)
    {
        if (!Auth::user()->can('manage footer')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $loginUser = Auth::user();
        $pages = Page::where('enabled', 1)->pluck('title', 'id');
        return view('home_pages.footerSetting', compact('loginUser', 'pages'));
    }

    public function footerData(Request $request)
    {
        $settings = $request->all();
        unset($settings['_token']);
        unset($settings['tab']);
        foreach ($settings as $s_key => $s_value) {
            if (in_array($s_key, ['footer_column_1_pages', 'footer_column_2_pages', 'footer_column_3_pages', 'footer_column_4_pages'])) {
                $s_value = json_encode($s_value);
            }
            if (!empty($s_value)) {
                \DB::insert(
                    'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $s_value,
                        $s_key,
                        'footer',
                        parentId(),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', __('Footer settings save successfully.'))->with('tab', $request->tab);
    }


    // ---------------------- 2FA Setting --------------------------------
    public function twofaEnable(Request $request)
    {
        $google2fa = new Google2FA();

        // retrieve secret from the session
        $secret = session("2fa_secret");
        $user = Auth::user();
        if ($google2fa->verify($request->input('otp'), $secret)) {
            // store the secret in the user profile
            // this will enable 2FA for this user
            $user->twofa_secret = $secret;
            $user->save();

            // avoid double OTP check
            session(["2fa_checked" => true]);

            return redirect()->back()->with('success', __('2 FA successfully enabled.'));
        }

        throw ValidationException::withMessages(['otp' => 'Incorrect value. Please try again...']);
    }

    // ---------------------- Storage Setting --------------------------------
    public function storageSetting(Request $request)
    {

        $storageSetting = $request->all();
        unset($storageSetting['_token']);

        if (!empty($storageSetting['local_file_type'])) {
            $storageSetting['local_file_type'] = implode(',', $storageSetting['local_file_type']);
        }
        if (!empty($storageSetting['aws_s3_file_type'])) {
            $storageSetting['aws_s3_file_type'] = implode(',', $storageSetting['aws_s3_file_type']);
        }
        if (!empty($storageSetting['wasabi_file_type'])) {
            $storageSetting['wasabi_file_type'] = implode(',', $storageSetting['wasabi_file_type']);
        }

        foreach ($storageSetting as $key => $val) {
            if (!empty($val)) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        'storage',
                        parentId(),
                        ]
                    );
                }
            }

            return redirect()->back()->with('success', __('Storage settings save successfully.'))->with('tab', 'storage');
        }


        // ---------------------- Twilio Setting --------------------------------
    public function twilio(Request $request)
    {
        if (!\Auth::check()) {
            return redirect()->back()->with('error', __('Invalid user.'));
        }

        $validator = \Validator::make($request->all(), [
            'twilio_sid'   => 'required',
            'twilio_token' => 'required',
            'twilio_from_number'  => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->messages()->first());
        }

        foreach (['twilio_sid', 'twilio_token', 'twilio_from_number'] as $key) {
            \DB::insert(
                'INSERT INTO settings (`value`, `name`, `parent_id`) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                [$request->$key, $key, parentId()]
            );
        }

        return redirect()->back()->with('success', 'twilio settings updated successfully.')->with('tab', 'twilio');
    }
}
