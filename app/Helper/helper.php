<?php

use App\Mail\Common;
use App\Mail\EmailVerification;
use App\Mail\TestMail;
use App\Models\Account;
use App\Models\AuthPage;
use App\Models\Custom;
use App\Models\Customer;
use App\Models\FAQ;
use App\Models\HomePage;
use App\Models\Loan;
use App\Models\LoggedHistory;
use App\Models\Notification;
use App\Models\Page;
use App\Models\Repayment;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PragmaRX\Google2FAQRCode\Google2FA;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Twilio\Rest\Client;


if (!function_exists('settingsKeys')) {
    function settingsKeys()
    {
        return $settingsKeys = [
            "app_name" => "",
            "theme_mode" => "light",
            "layout_font" => "Roboto",
            "accent_color" => "preset-1",
            "color_type" => "preset",
            "custom_color" => "--primary-rgb: 0,0,0",
            "custom_color_code" => "#000000",
            "sidebar_caption" => "true",
            "theme_layout" => "ltr",
            "layout_width" => "false",
            "owner_email_verification" => "off",
            "landing_page" => "on",
            "register_page" => "on",
            "company_logo" => "logo.png",
            "company_favicon" => "favicon.png",
            "landing_logo" => "landing_logo.png",
            "light_logo" => "light_logo.png",
            "meta_seo_title" => "",
            "meta_seo_keyword" => "",
            "meta_seo_description" => "",
            "meta_seo_image" => "",
            "company_date_format" => "M j, Y",
            "company_time_format" => "g:i A",
            "company_name" => "",
            "company_phone" => "",
            "company_address" => "",
            "company_email" => "",
            "company_email_from_name" => "",
            "google_recaptcha" => "off",
            "recaptcha_key" => "",
            "recaptcha_secret" => "",
            'SERVER_DRIVER' => "",
            'SERVER_HOST' => "",
            'SERVER_PORT' => "",
            'SERVER_USERNAME' => "",
            'SERVER_PASSWORD' => "",
            'SERVER_ENCRYPTION' => "",
            'FROM_EMAIL' => "",
            'FROM_NAME' => "",
            "customer_number_prefix" => "#CST-000",
            "account_number_prefix" => "#ACC-000",
            "loan_number_prefix" => "#LON-000",
            "expense_number_prefix" => "#EXP-000",
            "invoice_number_prefix" => "#INV-000",
            "expense_number_prefix" => "#EXP-000",
            'CURRENCY' => "USD",
            'CURRENCY_SYMBOL' => "$",
            'STRIPE_PAYMENT' => "off",
            'STRIPE_KEY' => "",
            'STRIPE_SECRET' => "",
            "paypal_payment" => "off",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "bank_transfer_payment" => "on",
            "bank_name" => "Test Bank",
            "bank_holder_name" => "Bank Holder Name",
            "bank_account_number" => "123456",
            "bank_ifsc_code" => "123456",
            "bank_other_details" => "",
            "flutterwave_payment" => "off",
            "flutterwave_public_key" => "",
            "flutterwave_secret_key" => "",
            "timezone" => "",
            "footer_column_1" => "Quick Links",
            "footer_column_1_enabled" => "active",
            "footer_column_2" => "Help",
            "footer_column_2_enabled" => "active",
            "footer_column_3" => "OverView",
            "footer_column_3_enabled" => "active",
            "footer_column_4" => "Core System",
            "footer_column_4_enabled" => "active",
            "pricing_feature" => "on",
            "copyright" => "",
            "paystack_payment" => "off",
            "paystack_public_key" => "",
            "paystack_secret_key" => "",
            "aws_s3_key" => "",
            "aws_s3_secret" => "",
            "aws_s3_region" => "",
            "aws_s3_bucket" => "",
            "aws_s3_url" => "",
            "aws_s3_endpoint" => "",
            "aws_s3_file_type" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "local_file_type" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_file_type" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "storage_type" => "local",
            'twilio_sid' => '',
            'twilio_token' => '',
            'twilio_from_number' => '',
        ];
    }
}

if (!function_exists('settings')) {
    function settings()
    {
        $settingData = DB::table('settings');
        if (\Auth::check()) {
            $userId = parentId();
            $settingData = $settingData->where('parent_id', $userId);
        } else {
            $settingData = $settingData->where('parent_id', 1);
        }
        $settingData = $settingData->get();
        $details = settingsKeys();

        foreach ($settingData as $row) {
            $details[$row->name] = $row->value;
        }

        config(
            [
                'captcha.secret' => $details['recaptcha_secret'],
                'captcha.sitekey' => $details['recaptcha_key'],
                'options' => [
                    'timeout' => 30,
                ]
            ]
        );

        return $details;
    }
}

if (!function_exists('settingsById')) {

    function settingsById($userId)
    {
        $data = DB::table('settings');
        $data = $data->where('parent_id',  $userId);
        $data = $data->get();
        $settings = settingsKeys();

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        config(
            [
                'captcha.secret' => $settings['recaptcha_key'],
                'captcha.sitekey' => $settings['recaptcha_secret'],
                'options' => [
                    'timeout' => 30,
                ],
            ]
        );

        return $settings;
    }
}


if (!function_exists('subscriptionPaymentSettings')) {
    function subscriptionPaymentSettings()
    {
        $settingData = DB::table('settings')->where('type', 'payment')->where('parent_id', '=', 1)->get();
        $result = [
            'CURRENCY' => "USD",
            'CURRENCY_SYMBOL' => "$",
            'STRIPE_PAYMENT' => "off",
            'STRIPE_KEY' => "",
            'STRIPE_SECRET' => "",
            "paypal_payment" => "off",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "bank_transfer_payment" => "off",
            "bank_name" => "",
            "bank_holder_name" => "",
            "bank_account_number" => "",
            "bank_ifsc_code" => "",
            "bank_other_details" => "",
            "flutterwave_payment" => "off",
            "flutterwave_public_key" => "",
            "flutterwave_secret_key" => "",
            "paystack_payment" => "off",
            "paystack_public_key" => "",
            "paystack_secret_key" => "",
        ];

        foreach ($settingData as $setting) {
            $result[$setting->name] = $setting->value;
        }

        return $result;
    }
}

if (!function_exists('invoicePaymentSettings')) {
    function invoicePaymentSettings($id)
    {
        $settingData = DB::table('settings')->where('type', 'payment')->where('parent_id', $id)->get();
        $result = [
            'CURRENCY' => "USD",
            'CURRENCY_SYMBOL' => "$",
            'STRIPE_PAYMENT' => "off",
            'STRIPE_KEY' => "",
            'STRIPE_SECRET' => "",
            "paypal_payment" => "off",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "bank_transfer_payment" => "off",
            "bank_name" => "",
            "bank_holder_name" => "",
            "bank_account_number" => "",
            "bank_ifsc_code" => "",
            "bank_other_details" => "",
            "flutterwave_payment" => "off",
            "flutterwave_public_key" => "",
            "flutterwave_secret_key" => "",
            "paystack_payment" => "off",
            "paystack_public_key" => "",
            "paystack_secret_key" => "",
        ];

        foreach ($settingData as $row) {
            $result[$row->name] = $row->value;
        }
        return $result;
    }
}

if (!function_exists('getSettingsValByName')) {
    function getSettingsValByName($key)
    {
        $setting = settings();
        if (!isset($setting[$key]) || empty($setting[$key])) {
            $setting[$key] = '';
        }

        return $setting[$key];
    }
}

if (!function_exists('getSettingsValByIdName')) {
    function getSettingsValByIdName($id, $key)
    {
        $setting = settingsById($id);
        if (!isset($setting[$key]) || empty($setting[$key])) {
            $setting[$key] = '';
        }

        return $setting[$key];
    }
}

if (!function_exists('settingDateFormat')) {
    function settingDateFormat($settings, $date)
    {
        return date($settings['company_date_format'], strtotime($date));
    }
}
if (!function_exists('settingPriceFormat')) {
    function settingPriceFormat($settings, $price)
    {
        return $settings['CURRENCY_SYMBOL'] . $price;
    }
}
if (!function_exists('settingTimeFormat')) {
    function settingTimeFormat($settings, $time)
    {
        return date($settings['company_time_format'], strtotime($time));
    }
}
if (!function_exists('dateFormat')) {
    function dateFormat($date)
    {
        // Handle NULL or empty dates
        if (empty($date) || is_null($date)) {
            return '-';
        }
        
        $settings = settings();

        return date($settings['company_date_format'], strtotime($date));
    }
}
if (!function_exists('timeFormat')) {
    function timeFormat($time)
    {
        $settings = settings();

        return date($settings['company_time_format'], strtotime($time));
    }
}
if (!function_exists('priceFormat')) {
    function priceFormat($price)
    {
        $settings = settings();

        return $settings['CURRENCY_SYMBOL'] . $price;
    }
}

if (!function_exists('currency_format_with_sym')) {
    function currency_format_with_sym($amount)
    {
        $settings = settings();
        return $settings['CURRENCY_SYMBOL'] . number_format($amount);
    }
}
if (!function_exists('parentId')) {
    function parentId()
    {
        if (\Auth::user()->type == 'owner' || \Auth::user()->type == 'super admin') {
            return \Auth::user()->id;
        } else {
            return \Auth::user()->parent_id;
        }
    }
}
if (!function_exists('assignSubscription')) {
    function assignSubscription($id)
    {
        $subscription = Subscription::find($id);
        if ($subscription) {
            \Auth::user()->subscription = $subscription->id;
            if ($subscription->interval == 'Monthly') {
                \Auth::user()->subscription_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Quarterly') {
                \Auth::user()->subscription_expire_date = Carbon::now()->addMonths(3)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Yearly') {
                \Auth::user()->subscription_expire_date = Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
            } else {
                \Auth::user()->subscription_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            }
            \Auth::user()->save();

            $users = User::where('parent_id', '=', parentId())->whereNotIn('type', ['super admin', 'owner'])->get();

            if ($subscription->user_limit == 0) {
                foreach ($users as $user) {
                    $user->is_active = 1;
                    $user->save();
                }
            } else {
                $userCount = 0;
                foreach ($users as $user) {
                    $userCount++;
                    if ($userCount <= $subscription->user_limit) {
                        $user->is_active = 1;
                        $user->save();
                    } else {
                        $user->is_active = 0;
                        $user->save();
                    }
                }

                SetLimit($subscription, auth()->user()->id);
            }
            return [
                'is_success' => true,
            ];
        } else {
            return [
                'is_success' => false,
                'error' => 'Subscription is deleted.',
            ];
        }
    }
}

if (!function_exists('assignManuallySubscription')) {
    function assignManuallySubscription($id, $userId)
    {
        $owner = User::find($userId);
        $subscription = Subscription::find($id);
        if ($subscription) {
            $owner->subscription = $subscription->id;
            if ($subscription->interval == 'Monthly') {
                $owner->subscription_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Quarterly') {
                $owner->subscription_expire_date = Carbon::now()->addMonths(3)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Yearly') {
                $owner->subscription_expire_date = Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
            } else {
                $owner->subscription_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            }
            $owner->save();

            $users = User::where('parent_id', '=', parentId())->whereNotIn('type', ['super admin', 'owner'])->get();

            if ($subscription->user_limit == 0) {
                foreach ($users as $user) {
                    $user->is_active = 1;
                    $user->save();
                }
            } else {
                $userCount = 0;
                foreach ($users as $user) {
                    $userCount++;
                    if ($userCount <= $subscription->user_limit) {
                        $user->is_active = 1;
                        $user->save();
                    } else {
                        $user->is_active = 0;
                        $user->save();
                    }
                }

                SetLimit($subscription, $userId);
            }
            return [
                'is_success' => true,
            ];
        } else {
            return [
                'is_success' => false,
                'error' => 'Subscription is deleted.',
            ];
        }
    }
}

if (!function_exists('SetLimit')) {
    function SetLimit($subscription, $id)
    {
        // set user_limit
        $user_limit = $subscription->user_limit;
        $users = User::where('parent_id', '=', $id)->whereNotIn('type', ['super admin', 'owner', 'customer'])->update(['is_active' => 0]);
        $users = User::where('parent_id', '=', $id)->whereNotIn('type', ['super admin', 'owner', 'customer'])->take($user_limit)->get()->each(function ($user) {
            $user->update(['is_active' => 1]);
        });

        // set customer_limit
        $customer_limit = $subscription->customer_limit;
        $customer = User::where('parent_id', '=', $id)->whereIn('type', ['customer'])->update(['is_active' => 0]);
        $customer = User::where('parent_id', '=', $id)->whereIn('type', ['customer'])->take($customer_limit)->get()->each(function ($customer) {
            $customer->update(['is_active' => 1]);
        });
    }
}

if (!function_exists('smtpDetail')) {
    function smtpDetail($id)
    {
        $settings = emailSettings($id);

        $smtpDetail = config(
            [
                'mail.mailers.smtp.transport' => $settings['SERVER_DRIVER'],
                'mail.mailers.smtp.host' => $settings['SERVER_HOST'],
                'mail.mailers.smtp.port' => $settings['SERVER_PORT'],
                'mail.mailers.smtp.encryption' => $settings['SERVER_ENCRYPTION'],
                'mail.mailers.smtp.username' => $settings['SERVER_USERNAME'],
                'mail.mailers.smtp.password' => $settings['SERVER_PASSWORD'],
                'mail.from.address' => $settings['FROM_EMAIL'],
                'mail.from.name' => $settings['FROM_NAME'],
            ]
        );

        return $smtpDetail;
    }
}

if (!function_exists('invoicePrefix')) {
    function invoicePrefix()
    {
        $settings = settings();
        return $settings["invoice_number_prefix"];
    }
}
if (!function_exists('expensePrefix')) {
    function expensePrefix()
    {
        $settings = settings();
        return $settings["expense_number_prefix"];
    }
}
if (!function_exists('customerPrefix')) {
    function customerPrefix()
    {
        $settings = settings();
        return $settings["customer_number_prefix"];
    }
}
if (!function_exists('accountPrefix')) {
    function accountPrefix()
    {
        $settings = settings();
        return $settings["account_number_prefix"];
    }
}
if (!function_exists('loanPrefix')) {
    function loanPrefix()
    {
        $settings = settings();
        return $settings["loan_number_prefix"];
    }
}
if (!function_exists('expensePrefix')) {
    function expensePrefix()
    {
        $settings = settings();
        return $settings["expense_number_prefix"];
    }
}

if (!function_exists('timeCalculation')) {
    function timeCalculation($startDate, $startTime, $endDate, $endTime)
    {
        $startdate = $startDate . ' ' . $startTime;
        $enddate = $endDate . ' ' . $endTime;

        $startDateTime = new DateTime($startdate);
        $endDateTime = new DateTime($enddate);

        $interval = $startDateTime->diff($endDateTime);
        $totalHours = $interval->h + $interval->i / 60;

        return number_format($totalHours, 2);
    }
}

if (!function_exists('setup')) {
    function setup()
    {
        $setupPath = storage_path() . "/installed";
        return $setupPath;
    }
}

if (!function_exists('userLoggedHistory')) {
    function userLoggedHistory()
    {
        $serverip = $_SERVER['REMOTE_ADDR'];
        $data = @unserialize(file_get_contents('http://ip-api.com/php/' . $serverip));
        if (isset($data['status']) && $data['status'] == 'success') {
            $browser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
            if ($browser->device->type == 'bot') {
                return redirect()->intended(RouteServiceProvider::HOME);
            }
            $referrerData = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : null;
            $data['browser'] = $browser->browser->name ?? null;
            $data['os'] = $browser->os->name ?? null;
            $data['language'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
            $data['device'] = User::getDevice($_SERVER['HTTP_USER_AGENT']);
            $data['referrer_host'] = !empty($referrerData['host']);
            $data['referrer_path'] = !empty($referrerData['path']);
            $result = json_encode($data);
            $details = new LoggedHistory();
            $details->type = Auth::user()->type;
            $details->user_id = Auth::user()->id;
            $details->date = date('Y-m-d H:i:s');
            $details->Details = $result;
            $details->ip = $serverip;
            $details->parent_id = parentId();
            $details->save();
        }
    }
}

if (!function_exists('defaultClientCreate')) {
    function defaultClientCreate($id)
    {
        // Default Client role
        $clientRoleData = [
            'name' => 'client',
            'parent_id' => $id,
        ];
        $systemClientRole = Role::create($clientRoleData);
        // Default Client permissions
        $systemClientPermissions = [
            ['name' => 'manage contact'],
            ['name' => 'create contact'],
            ['name' => 'edit contact'],
            ['name' => 'delete contact'],
            ['name' => 'manage note'],
            ['name' => 'create note'],
            ['name' => 'edit note'],
            ['name' => 'delete note'],
            ['name' => 'manage ticket'],
            ['name' => 'create ticket'],
            ['name' => 'edit ticket'],
            ['name' => 'delete ticket'],
            ['name' => 'reply ticket'],
        ];
        $systemClientRole->givePermissionTo($systemClientPermissions);
        return $systemClientRole;
    }
}

if (!function_exists('defaultTemplate')) {
    function defaultTemplate($id)
    {
        $templateData = [
            'user_create' =>
            [
                'module' => 'user_create',
                'name' => 'New User',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{new_user_name}', '{app_link}', '{username}', '{password}'],
                'subject' => 'Welcome to {company_name}!',
                'templete' => '<p><strong>Dear {new_user_name}</strong>,</p><p>&nbsp;</p><blockquote><p>Welcome to {company_name}! We are excited to have you on board and look forward to providing you with an exceptional experience.</p><p>We hope you enjoy your experience with us. If you have any feedback, feel free to share it with us.</p><p>&nbsp;</p><p>Your account details are as follows:</p><p><strong>App Link:</strong> <a href="{app_link}">{app_link}</a></p><p><strong>Username:</strong> {username}</p><p><strong>Password:</strong> {password}</p><p>&nbsp;</p><p>Thank you for choosing {company_name}!</p></blockquote><p>Best regards,</p><p>{company_name}</p><p>{company_email}</p>
                    ',
                'sms_templete' => 'Hello {new_user_name}, Welcome to {company_name}! Your account is now active. Username: {username} Password: {password} Login here: {app_link} Contact: {company_phone_number} | {company_email}
                    ',
            ],
            'customer_create' =>
            [
                'module' => 'customer_create',
                'name' => 'New Customer',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{new_customer_name}', '{branch_name}', '{branch_email}', '{branch_location}',],
                'subject' => 'Welcome to {company_name}!',
                'templete' => '
                <p><strong>Dear {new_customer_name},</strong></p><p>&nbsp;</p><blockquote><p>Welcome to {company_name}!</p><p>We are excited to have you onboard as our newest customer and look forward to providing you with the highest level of service and support. Whether you are new to our services or have prior experience with us, we are here to ensure your journey with us is smooth and successful.</p><p>Below are the important details regarding your account:</p><p>&nbsp;</p><p><strong>Account Information:</strong></p><ul><li><strong>Company Name:</strong> {company_name}</li><li><strong>Currency:</strong> {company_currency}</li><li>&nbsp;</li></ul><p><strong>Branch Information:</strong></p><ul><li><strong>Branch Name:</strong> {branch_name}</li><li><strong>Branch Email:</strong> {branch_email}</li><li><strong>Branch Location:</strong> {branch_location}</li><li>&nbsp;</li></ul><p>We are pleased to assist you through the following resources:</p><ul><li><strong>Support Email:</strong> {company_email}</li><li><strong>Phone Number:</strong> {company_phone_number}</li><li><strong>Address:</strong> {company_address}</li><li>&nbsp;</li></ul><p>Thank you for choosing {company_name} as your trusted financial partner. If you have any questions or need any support, feel free to reach out to us. We are here to assist you in every way possible!</p><p>&nbsp;</p><p><strong>Best regards,</strong></p><p>{company_name}</p><p>{company_email}</p></blockquote>
                ',
                'sms_templete' => 'Welcome to {company_name}, {new_customer_name}!Your account at {branch_name} is now active.Contact: {branch_email}, {branch_location}
                ',
            ],
            'loan_create' =>
            [
                'module' => 'loan_create',
                'name' => 'New Loan',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{customer_name}', '{branch_name}', '{loan_type}', '{loan_start_date}', '{loan_due_date}', '{loan_amount}', '{purpose_of_loan}', '{loan_terms}', '{loan_term_period}', '{loan_status}',],
                'subject' => 'New Loan Assigned: {loan_type} for {customer_name}',
                'templete' => '
                <p><strong>Loan Assignment Notification</strong></p><p>We are pleased to inform you that a new loan has been assigned to {customer_name}. Below are the details for your reference:</p><p>&nbsp;</p><p><strong>Loan Information:</strong></p><ul><li><strong>Loan Type:</strong> {loan_type}</li><li><strong>Loan Amount:</strong> {company_currency} {loan_amount}</li><li><strong>Purpose of Loan:</strong> {purpose_of_loan}</li><li><strong>Loan Start Date:</strong> {loan_start_date}</li><li><strong>Loan Due Date:</strong> {loan_due_date}</li><li><strong>Loan Terms:</strong> {loan_terms}</li><li><strong>Loan Term Period:</strong> {loan_term_period} months</li><li><strong>Loan Status:</strong> {loan_status}</li><li>&nbsp;</li></ul><p><strong>Branch Information:</strong></p><ul><li><strong>Branch Name:</strong> {branch_name}</li><li><strong>Branch Location:</strong> {branch_location}</li><li>&nbsp;</li></ul><p>If you have any questions or need further assistance, please contact the branch directly for more details.</p><p>&nbsp;</p><p><strong>Best regards,</strong></p><p>{company_name}</p><p>{company_email}</p>
                ',
                'sms_templete' => 'Hi {customer_name}, your {loan_type} of {company_currency} {loan_amount} at {company_name} starts on {loan_start_date}, due on {loan_due_date}. Status: {loan_status}.
                ',
            ],
            'loan_status_update' =>
            [
                'module' => 'loan_status_update',
                'name' => 'Loan Status Update',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{customer_name}', '{branch_name}', '{loan_type}', '{loan_start_date}', '{loan_due_date}', '{loan_amount}', '{purpose_of_loan}', '{loan_terms}', '{loan_term_period}', '{loan_status}',],
                'subject' => 'Loan Status Update: {loan_type} for {customer_name}',
                'templete' => '
                <p>We hope this message finds you well.</p><p>&nbsp;</p><p>We would like to inform you about the current status of your loan. Please find below the updated details of your loan:</p><p>&nbsp;</p><p><strong>Loan Information:</strong></p><ul><li><strong>Loan Type:</strong> {loan_type}</li><li><strong>Loan Amount:</strong> {company_currency} {loan_amount}</li><li><strong>Purpose of Loan:</strong> {purpose_of_loan}</li><li><strong>Loan Start Date:</strong> {loan_start_date}</li><li><strong>Loan Due Date:</strong> {loan_due_date}</li><li><strong>Loan Terms:</strong> {loan_terms}</li><li><strong>Loan Term Period:</strong> {loan_term_period} months</li><li><strong>Loan Status:</strong> {loan_status}</li><li>&nbsp;</li></ul><p><strong>Branch Information:</strong></p><ul><li><strong>Branch Name:</strong> {branch_name}</li><li><strong>Branch Location:</strong> {branch_location}</li><li>&nbsp;</li></ul><p>If you have any questions or need further assistance, please contact the branch directly for more details.</p><p>&nbsp;</p><p><strong>Best regards,</strong></p><p>{company_name}</p><p>{company_email}</p>
                ',
                'sms_templete' => 'Hi {customer_name}, your {loan_type} of {company_currency} {loan_amount} from {company_name} ({branch_name}) starts {loan_start_date}, due {loan_due_date}. Status: {loan_status}.
                ',
            ],
            'repayment_create' =>
            [
                'module' => 'repayment_create',
                'name' => 'New Repayment',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{customer_name}', '{branch_name}', '{payment_date}', '{principal_amount}', '{interest}', '{penality}', '{total_amount}'],
                'subject' => 'New Repayment for {customer_name} - {company_name}',
                'templete' => '
                <p><strong>Repayment Notification</strong></p><p>We would like to inform you that a new repayment has been made by {customer_name}. Below are the details for your reference:</p><p>&nbsp;</p><p><strong>Repayment Information:</strong></p><ul><li><strong>Payment Date:</strong> {payment_date}</li><li><strong>Principal Amount:</strong> {company_currency} {principal_amount}</li><li><strong>Interest:</strong> {company_currency} {interest}</li><li><strong>Penalty:</strong> {company_currency} {penality}</li><li><strong>Total Repayment Amount:</strong> {company_currency} {total_amount}</li><li>&nbsp;</li></ul><p><strong>Branch Information:</strong></p><ul><li><strong>Branch Name:</strong> {branch_name}</li><li>&nbsp;</li></ul><p>If you have any questions regarding this repayment, please feel free to contact the branch for further assistance.</p><p>&nbsp;</p><p><strong>Best regards,</strong></p><p>{company_name}</p><p>{company_email}</p>
                ',
                'sms_templete' => 'Hi {customer_name}, your payment on {payment_date} at {company_name} is received. Total: {company_currency} {total_amount} (Principal: {principal_amount}, Interest: {interest}, Penalty: {penality}).
                ',
            ],
            'account_create' =>
            [
                'module' => 'account_create',
                'name' => 'New Account',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{customer_name}', '{account_type}', '{account_status}', '{balance}', '{interest_rate}', '{interest_duration}', '{minimum_maintain_amount}', '{maintenance_charges}', '{charges_deduct_month}'],
                'subject' => 'Your New Account is Ready!',
                'templete' => '
                <p><strong>Dear {customer_name},</strong></p><p>&nbsp;</p><blockquote><p>Welcome to {company_name}! We are excited to have you on board and look forward to providing you with an exceptional experience.</p><p>We hope you enjoy your experience with us. If you have any feedback or need assistance, feel free to reach out.</p><p>&nbsp;</p><p><strong>Your account details are as follows:</strong></p><p><strong>Account Type:</strong> {account_type}</p><p><strong>Account Status:</strong> {account_status}</p><p><strong>Balance:</strong> {balance} {company_currency}</p><p><strong>Interest Rate:</strong> {interest_rate}%</p><p><strong>Interest Duration:</strong> {interest_duration} months</p><p><strong>Minimum Maintain Amount:</strong> {minimum_maintain_amount} {company_currency}</p><p><strong>Maintenance Charges:</strong> {maintenance_charges} {company_currency} per month</p><p><strong>Charges Deducted Monthly:</strong> {charges_deduct_month} {company_currency}</p><p>&nbsp;</p><p>Thank you for choosing {company_name}!</p></blockquote><p>Best regards,</p><p>{company_name}</p><p>{company_email}</p><p>{company_phone_number}</p><p>{company_address}</p>
                ',
                'sms_templete' => 'Hi {customer_name}, your {account_type} account is {account_status}. Balance: {company_currency} {balance}, Interest: {interest_rate}%/{interest_duration}. Min Bal: {minimum_maintain_amount}.
                ',
            ],
            'transaction_create' =>
            [
                'module' => 'transaction_create',
                'name' => 'New Transaction',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{customer_name}', '{transaction_type}', '{transaction_status}', '{transaction_date_time}', '{transaction_amount}'],
                'subject' => 'New Transaction Confirmation from {company_name}',
                'templete' => '
                <p><strong>Transaction Confirmation</strong></p><p>&nbsp;</p><p>Dear {customer_name},</p><p>&nbsp;</p><p>We are writing to confirm that a new transaction has been successfully processed on your account with {company_name}. Below are the details of the transaction:</p><p>&nbsp;</p><p><strong>Transaction Details:</strong></p><ul><li><strong>Transaction Type:</strong> {transaction_type}</li><li><strong>Transaction Amount:</strong> {company_currency} {transaction_amount}</li><li><strong>Transaction Status:</strong> {transaction_status}</li><li><strong>Transaction Date &amp; Time:</strong> {transaction_date_time}</li><li>&nbsp;</li></ul><p>If you have any questions or need further information, feel free to contact us at {company_email} or call {company_phone_number}.</p><p>&nbsp;</p><p>Thank you for choosing {company_name}!</p><p>&nbsp;</p><p><strong>Best regards,</strong></p><p>{company_name}</p><p>{company_email}</p>
                ',
                'sms_templete' => 'Hi {customer_name}, your {transaction_type} of {company_currency} {transaction_amount} on {transaction_date_time} is {transaction_status}. - {company_name}
                ',
            ],
            'payment_reminder' =>
            [
                'module' => 'payment_reminder',
                'name' => 'Payment Reminder',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{customer_name}', '{branch_name}', '{due_date}', '{interest}', '{penality}', '{total_amount}', '{payment_status}', '{installment_amount}'],
                'subject' => 'Payment Reminder for {customer_name} - {company_name}',
                'templete' => '
                <p><strong>Repayment Reminder</strong></p><p>&nbsp;</p><p>Dear {customer_name},</p><p>&nbsp;</p><p>This is a reminder for an upcoming repayment. Below are the details:</p><p>&nbsp;</p><p><strong>Repayment Information:</strong></p><ul><li><strong>Branch Name:</strong> {branch_name}</li><li><strong>Payment Date:</strong> {due_date}</li><li><strong>Interest:</strong> {company_currency} {interest}</li><li><strong>Penalty:</strong> {company_currency} {penality}</li><li><strong>Installment Amount:</strong> {company_currency} {installment_amount}</li><li><strong>Total Repayment Amount:</strong> {company_currency} {total_amount}</li><li><strong>Payment Status:</strong> {payment_status}</li><li>&nbsp;</li></ul><p>If you have any questions or need further assistance, please feel free to contact {branch_name}.</p><p>&nbsp;</p><p><strong>Best regards,</strong></p><p>{company_name}</p><p>{company_email}</p>
                ',
                'sms_templete' => 'Hi {customer_name}, your installment of {company_currency} {installment_amount} at {company_name} ({branch_name}) is {payment_status}. Due: {due_date}. Total: {total_amount}.
                ',
            ],

        ];

        // Store all created templates if needed
        $createdTemplates = [];

        foreach ($templateData as $key => $value) {
            $template = new Notification();
            $template->module = $value['module'];
            $template->name = $value['name'];
            $template->subject = $value['subject'];
            $template->message = $value['templete'];
            $template->sms_message = $value['sms_templete'];
            $template->short_code = json_encode($value['short_code']);
            $template->enabled_email = 0;
            $template->enabled_sms = 0;
            $template->parent_id = $id;
            $template->save();

            $createdTemplates[] = $template;
        }

        // Return all created templates if needed
        return $createdTemplates;
    }
}

if (!function_exists('MessageReplace')) {
    function MessageReplace($notification, $id = 0)
    {

        $return['subject'] = $notification->subject;
        $return['message'] = $notification->message;
        if (!empty($notification->password)) {
            $notification['password'] = $notification->password;
        }
        $settings = settings();
        if (!empty($notification)) {
            $search = [];
            $replace = [];
            if ($notification->module == 'user_create') {
                $user = User::find($id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{new_user_name}', '{app_link}', '{username}', '{password}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user->name, env('APP_URL'), $user->email, $notification['password']];
            }
            if ($notification->module == 'customer_create') {
                $customer = Customer::find($id);
                $user = User::find($customer->user_id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{new_customer_name}', '{branch_name}', '{branch_email}', '{branch_location}',];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user->name, $customer->branch->name, $customer->branch->email, $customer->branch->location];
            }
            if ($notification->module == 'loan_create') {
                $loan = Loan::find($id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{customer_name}', '{branch_name}', '{loan_type}', '{loan_start_date}', '{loan_due_date}', '{loan_amount}', '{purpose_of_loan}', '{loan_terms}', '{loan_term_period}', '{loan_status}',];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $loan->Customers->name, $loan->branch->name, $loan->loanType->type, dateFormat($loan->loan_start_date), dateFormat($loan->loan_due_date), $loan->amount, $loan->purpose_of_loan, $loan->loan_terms, $loan->loan_term_period, $loan->status];
            }
            if ($notification->module == 'loan_status_update') {
                $loan = Loan::find($id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{customer_name}', '{branch_name}', '{loan_type}', '{loan_start_date}', '{loan_due_date}', '{loan_amount}', '{purpose_of_loan}', '{loan_terms}', '{loan_term_period}', '{loan_status}',];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $loan->Customers->name, $loan->branch->name, $loan->loanType->type, dateFormat($loan->loan_start_date), dateFormat($loan->loan_due_date), $loan->amount, $loan->purpose_of_loan, $loan->loan_terms, $loan->loan_term_period, $loan->status];
            }
            if ($notification->module == 'repayment_create') {
                $repayment = Repayment::find($id);
                $customer = Customer::where('user_id', $repayment->Loans->customer)->first();
                $customerName = User::find($repayment->Loans->customer)->name;
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{customer_name}', '{branch_name}', '{payment_date}', '{principal_amount}', '{interest}', '{penality}', '{total_amount}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $customerName, $customer->branch->name, dateFormat($repayment->payment_date), $repayment->principal_amount, $repayment->interest, $repayment->penality, $repayment->total_amount];
            }
            if ($notification->module == 'account_create') {
                $account = Account::find($id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{customer_name}', '{account_type}', '{account_status}', '{balance}', '{interest_rate}', '{interest_duration}', '{minimum_maintain_amount}', '{maintenance_charges}', '{charges_deduct_month}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $account->Customers->name, $account->accountType->title, $account->status, $account->balance, $account->accountType->interest_rate, $account->accountType->interest_duration, $account->accountType->min_maintain_amount, $account->accountType->maintenance_charges, $account->accountType->charges_deduct_month];
            }
            if ($notification->module == 'transaction_create') {
                $transaction = Transaction::find($id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{customer_name}', '{transaction_type}', '{transaction_status}', '{transaction_date_time}', '{transaction_amount}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $transaction->Customers->name, $transaction->type, $transaction->status, dateFormat($transaction->date_time) . ' ' . timeFormat($transaction->date_time), $transaction->amount];
            }



            $return['subject'] = str_replace($search, $replace, $notification->subject);
            $return['message'] = str_replace($search, $replace, $notification->message);
            $return['sms_message'] = str_replace($search, $replace, $notification->sms_message);
        }

        return $return;
    }
}


if (!function_exists('sendEmail')) {
    function sendEmail($to, $datas)
    {
        $datas['settings'] = settings();
        try {
            emailSettings(parentId());
            Mail::to($to)->send(new TestMail($datas));
            return [
                'status' => 'success',
                'message' => __('Email successfully sent'),
            ];
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return [
                'status' => 'error',
                'message' => __('We noticed that the email settings have not been configured for this system. As a result, email-related functionalities may not work as expected. please add valide email smtp details first.')
            ];
        }
    }
}


if (!function_exists('commonEmailSend')) {
    function commonEmailSend($to, $datas)
    {
        $datas['settings'] = settings();
        try {
            if (Auth::check()) {
                if ($datas['module'] == 'owner_create') {
                    emailSettings(1);
                } else {
                    emailSettings(parentId());
                }
            } else {
                emailSettings($datas['parent_id']);
            }
            Mail::to($to)->send(new Common($datas));
            return [
                'status' => 'success',
                'message' => __('Email successfully sent'),
            ];
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return [
                'status' => 'error',
                'message' => __('We noticed that the email settings have not been configured for this system. As a result, email-related functionalities may not work as expected. please add valide email smtp details first.')
            ];
        }
    }
}


if (!function_exists('emailSettings')) {
    function emailSettings($id)
    {
        $settingData = DB::table('settings')
            ->where('type', 'smtp')
            ->where('parent_id', $id)
            ->get();

        $result = [
            'FROM_EMAIL' => "",
            'FROM_NAME' => "",
            'SERVER_DRIVER' => "",
            'SERVER_HOST' => "",
            'SERVER_PORT' => "",
            'SERVER_USERNAME' => "",
            'SERVER_PASSWORD' => "",
            'SERVER_ENCRYPTION' => "",
        ];

        foreach ($settingData as $setting) {
            $result[$setting->name] = $setting->value;
        }

        // Apply settings dynamically
        config([
            'mail.default' => $result['SERVER_DRIVER'] ?? '',
            'mail.mailers.smtp.host' => $result['SERVER_HOST'] ?? '',
            'mail.mailers.smtp.port' => $result['SERVER_PORT'] ?? '',
            'mail.mailers.smtp.encryption' => $result['SERVER_ENCRYPTION'] ?? '',
            'mail.mailers.smtp.username' => $result['SERVER_USERNAME'] ?? '',
            'mail.mailers.smtp.password' => $result['SERVER_PASSWORD'] ?? '',
            'mail.from.name' => $result['FROM_NAME'] ?? '',
            'mail.from.address' => $result['FROM_EMAIL'] ?? '',
        ]);
        return $result;
    }
}


if (!function_exists('sendEmailVerification')) {
    function sendEmailVerification($to, $data)
    {
        $data['settings'] = emailSettings(1);
        try {
            Mail::to($to)->send(new EmailVerification($data));

            return [
                'status' => 'success',
                'message' => __('Email successfully sent'),
            ];
        } catch (\Exception $e) {
            Log::error('Email Sending Failed: ' . $e->getMessage());

            return [
                'status' => 'error',
                'message' => __('We noticed that the email settings have not been configured for this system. As a result, email-related functionalities may not work as expected. please contact the administrator to resolve this issue.')
            ];
            return redirect()->back()->with('error', __(''));
        }
    }
}



if (!function_exists('RoleName')) {
    function RoleName($permission_id = '0')
    {
        $retuen = '';
        $role_id_array = DB::table('role_has_permissions')->where('permission_id', $permission_id)->pluck('role_id');
        if (!empty($role_id_array)) {
            $role_id_array = DB::table('roles')->whereIn('id', $role_id_array)->pluck('name')->toArray();
            $retuen = implode(', ', $role_id_array);
        }

        return $retuen;
    }
}

if (!function_exists('HomePageSection')) {
    function HomePageSection()
    {
        $retuen = [
            [
                'title' => 'Header Menu',
                'section' => 'Section 0',
                'content' => '',
                'content_value' => '{"name":"Header Menu","menu_pages":["1","2"]}',
            ],
            [
                'title' => 'Banner',
                'section' => 'Section 1',
                'content_value' => '{"name":"Banner","section_enabled":"active","title":"FinanceLend SaaS - Loan Management Solution","sub_title":"The Loan Management System is a robust, scalable, and user-friendly software solution designed to streamline the management of loans for financial institutions, credit unions, and lending organizations. This system provides an end-to-end solution to automate the loan lifecycle, from application to approval, disbursement, and repayment, ensuring efficiency, accuracy, and compliance.","btn_name":"Get Started","btn_link":"#","section_footer_text":"Manage your business efficiently with our all-in-one solution designed for performance, security, and scalability.","section_main_image":{},"section_footer_image_path":"upload\/homepage\/banner_2.png","section_main_image_path":"upload\/homepage\/banner_1.png","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'OverView',
                'section' => 'Section 2',
                'content_value' => '{"name":"OverView","section_enabled":"active","Box1_title":"Customers","Box1_number":"500+","Box2_title":"Subscription Plan","Box2_number":"4+","Box3_title":"Language","Box3_number":"11+","box1_number_image":{},"box2_number_image":{},"box3_number_image":{},"section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"upload\/homepage\/OverView_1.svg","box_image_2_path":"upload\/homepage\/OverView_2.svg","box_image_3_path":"upload\/homepage\/OverView_3.svg","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}'

            ],
            [
                'title' => 'AboutUs',
                'section' => 'Section 3',
                'content_value' => '{"name":"AboutUs","section_enabled":"active","Box1_title":"Empower Your Business to Thrive with Us","Box1_info":"Unlock growth, streamline operations, and achieve success with our innovative solutions.","Box1_list":["Simplify and automate your business processes for maximum efficiency.","Receive tailored strategies to meet business needs and unlock potential.","Grow confidently with flexible solutions that adapt to your business needs.","Make smarter decisions with real-time analytics and performance tracking.","Rely on 24\/7 expert assistance to keep your business running smoothly."],"Box2_title":"Eliminate Paperwork, Elevate Productivity","Box2_info":"Simplify your operations with seamless digital solutions and focus on what truly matters.","Box2_list":["Replace manual paperwork with automated workflows.","Secure cloud storage lets you manage documents on the go.","Streamlined processes save time and reduce errors.","Keep your information safe with encrypted storage.","Reduce printing, storage, and administrative expenses.","Go green by minimizing paper use and waste."],"section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"upload\/homepage\/img-customize-1.svg","Box2_image_path":"upload\/homepage\/img-customize-2.svg","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'Offer',
                'section' => 'Section 4',
                'content_value' => '{"name":"Offer","section_enabled":"active","Sec4_title":"What Our Software Offers","Sec4_info":"Our software provides powerful, scalable solutions designed to streamline your business operations.","Sec4_box1_title":"User-Friendly Interface","Sec4_box1_enabled":"active","Sec4_box1_info":"Simplify operations with an intuitive and easy-to-use platform.","Sec4_box2_title":"End-to-End Automation","Sec4_box2_enabled":"active","Sec4_box2_info":"Automate repetitive tasks to save time and increase efficiency.","Sec4_box3_title":"Customizable Solutions","Sec4_box3_enabled":"active","Sec4_box3_info":"Tailor features to fit your unique business needs and workflows.","Sec4_box4_title":"Scalable Features","Sec4_box4_enabled":"active","Sec4_box4_info":"Grow your business with flexible solutions that scale with you.","Sec4_box5_title":"Enhanced Security","Sec4_box5_enabled":"active","Sec4_box5_info":"Protect your data with advanced encryption and security protocols.","Sec4_box6_title":"Real-Time Analytics","Sec4_box6_enabled":"active","Sec4_box6_info":"Gain actionable insights with live data tracking and reporting.","section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"upload\/homepage\/offers_1.svg","Sec4_box2_image_path":"upload\/homepage\/offers_2.svg","Sec4_box3_image_path":"upload\/homepage\/offers_3.svg","Sec4_box4_image_path":"upload\/homepage\/offers_4.svg","Sec4_box5_image_path":"upload\/homepage\/offers_5.svg","Sec4_box6_image_path":"upload\/homepage\/offers_6.svg","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'Pricing',
                'section' => 'Section 5',
                'content_value' => '{"name":"Pricing","section_enabled":"active","Sec5_title":"Flexible Pricing","Sec5_info":"Get started for free, upgrade later in our application.","section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'Core Features',
                'section' => 'Section 6',
                'content_value' => '{"name":"Core Features","section_enabled":"active","Sec6_title":"Core Features","Sec6_info":"Core Modules For Your Business","Sec6_Box_title":["Dashboard","Customer","Loan","Repayment Schedule","Loan Detail"],"Sec6_Box_subtitle":["The Loan Management System is a robust, scalable, and user-friendly software solution designed to streamline the management of loans for financial institutions, credit unions, and lending organizations.","The Loan Management System is a robust, scalable, and user-friendly software solution designed to streamline the management of loans for financial institutions, credit unions, and lending organizations.","The Loan Management System is a robust, scalable, and user-friendly software solution designed to streamline the management of loans for financial institutions, credit unions, and lending organizations.","The Loan Management System is a robust, scalable, and user-friendly software solution designed to streamline the management of loans for financial institutions, credit unions, and lending organizations.","The Loan Management System is a robust, scalable, and user-friendly software solution designed to streamline the management of loans for financial institutions, credit unions, and lending organizations."],"Sec6_box_image":[{},{},{},{},{}],"section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec6_box0_image_path":"upload\/homepage\/1.png","Sec6_box1_image_path":"upload\/homepage\/2.png","Sec6_box2_image_path":"upload\/homepage\/3.png","Sec6_box3_image_path":"upload\/homepage\/4.png","Sec6_box4_image_path":"upload\/homepage\/5.png","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'Testimonials',
                'section' => 'Section 7',
                'content_value' => '{"name":"Testimonials","section_enabled":"active","Sec7_title":"What Our Customers Say About Us","Sec7_info":"We\u2019re proud of the impact our software has had on businesses just like yours. Hear directly from our customers about how our solutions have made a difference in their day-to-day operations","Sec7_box1_name":"Lenore Becker","Sec7_box1_tag":null,"Sec7_box1_Enabled":"active","Sec7_box1_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","Sec7_box2_name":"Damian Morales","Sec7_box2_tag":"New","Sec7_box2_Enabled":"active","Sec7_box2_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum.","Sec7_box3_name":"Oleg Lucas","Sec7_box3_tag":null,"Sec7_box3_Enabled":"active","Sec7_box3_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","Sec7_box4_name":"Jerome Mccoy","Sec7_box4_tag":null,"Sec7_box4_Enabled":"active","Sec7_box4_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","Sec7_box5_name":"Rafael Carver","Sec7_box5_tag":null,"Sec7_box5_Enabled":"active","Sec7_box5_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend.","Sec7_box6_name":"Edan Rodriguez","Sec7_box6_tag":null,"Sec7_box6_Enabled":"active","Sec7_box6_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","Sec7_box7_name":"Kalia Middleton","Sec7_box7_tag":null,"Sec7_box7_Enabled":"active","Sec7_box7_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum.","Sec7_box8_name":"Zenaida Chandler","Sec7_box8_tag":null,"Sec7_box8_Enabled":"active","Sec7_box8_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"upload\/homepage\/review_1.png","Sec7_box2_image_path":"upload\/homepage\/review_2.png","Sec7_box3_image_path":"upload\/homepage\/review_3.png","Sec7_box4_image_path":"upload\/homepage\/review_4.png","Sec7_box5_image_path":"upload\/homepage\/review_5.png","Sec7_box6_image_path":"upload\/homepage\/review_6.png","Sec7_box7_image_path":"upload\/homepage\/review_7.png","Sec7_box8_image_path":"upload\/homepage\/review_8.png"}',
            ],
            [
                'title' => 'Choose US',
                'section' => 'Section 8',
                'content_value' => '{"name":"Choose US","section_enabled":"active","Sec8_title":"Reason to Choose US","Sec8_box1_info":"Proven Expertise","Sec8_box2_info":"Customizable Solutions","Sec8_box3_info":"Seamless Integration","Sec8_box4_info":"Exceptional Support","Sec8_box5_info":"Scalable and Future-Proof","Sec8_box6_info":"Security You Can Trust","Sec8_box7_info":"User-Friendly Interface","Sec8_box8_info":"Innovation at Its Core","section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'FAQ',
                'section' => 'Section 9',
                'content_value' => '{"name":"FAQ","section_enabled":"active","Sec9_title":"Frequently Asked Questions (FAQ)","Sec9_info":"Please refer the Frequently ask question for your quick help","section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'AboutUS - Footer',
                'section' => 'Section 10',
                'content_value' => '{"name":"AboutUS - Footer","section_enabled":"active","Sec10_title":"About FinanceLend SaaS","Sec10_info":"The Loan Management System is a robust, scalable, and user-friendly software solution designed to streamline the management of loans for financial institutions, credit unions, and lending organizations. This system provides an end-to-end solution to automate the loan lifecycle, from application to approval, disbursement, and repayment, ensuring efficiency, accuracy, and compliance.","section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
        ];

        foreach ($retuen as $key => $value) {
            $HomePage = new HomePage();
            $HomePage->title = $value['title'];
            $HomePage->section = $value['section'];
            if (!empty($value['content_value'])) {
                $HomePage->content_value = $value['content_value'];
            }
            $HomePage->enabled = 1;
            $HomePage->parent_id = 1;
            $HomePage->save();
        }
        return '';
    }
}

if (!function_exists('CustomPage')) {
    function CustomPage()
    {
        $retuen = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy_policy',
                'content' => "<h3><strong>1. Information We Collect</strong></h3><p>We may collect the following types of information from you:</p><h4><strong>a. Personal Information</strong></h4><ul><li>Name, email address, phone number, and other contact details.</li><li>Payment information (if applicable).</li></ul><h4><strong>b. Non-Personal Information</strong></h4><ul><li>Browser type, operating system, and device information.</li><li>Usage data, including pages visited, time spent, and other analytical data.</li></ul><h4><strong>c. Information You Provide</strong></h4><ul><li>Information you voluntarily provide when contacting us, signing up, or completing forms.</li></ul><h4><strong>d. Cookies and Tracking Technologies</strong></h4><ul><li>We use cookies, web beacons, and other tracking tools to enhance your experience and analyze usage patterns.</li></ul><h3><strong>2. How We Use Your Information</strong></h3><p>We use the information collected for the following purposes:</p><ul><li>To provide, maintain, and improve our Services.</li><li>To process transactions and send you confirmations.</li><li>To communicate with you, including responding to inquiries or providing updates.</li><li>To personalize your experience and deliver tailored content.</li><li>To comply with legal obligations and protect against fraud or misuse.</li></ul><h3><strong>3. How We Share Your Information</strong></h3><p>We do not sell your personal information. However, we may share your information with:</p><ul><li><strong>Service Providers:</strong> Third-party vendors who assist in providing our Services.</li><li><strong>Legal Authorities:</strong> When required to comply with legal obligations or protect our rights.</li><li><strong>Business Transfers:</strong> In the event of a merger, acquisition, or sale of assets, your information may be transferred.</li></ul><h3><strong>4. Data Security</strong></h3><p>We implement appropriate technical and organizational measures to protect your data against unauthorized access, disclosure, alteration, or destruction. However, no method of transmission or storage is 100% secure, and we cannot guarantee absolute security.</p><h3><strong>5. Your Rights</strong></h3><p>You have the right to:</p><ul><li>Access, correct, or delete your personal data.</li><li>Opt-out of certain data processing activities, including marketing communications.</li><li>Withdraw consent where processing is based on consent.</li></ul><p>To exercise your rights, please contact us at [contact email].</p><h3><strong>6. Third-Party Links</strong></h3><p>Our Services may contain links to third-party websites. We are not responsible for the privacy practices or content of these websites. Please review their privacy policies before engaging with them.</p><h3><strong>7. Children's Privacy</strong></h3><p>Our Services are not intended for children under the age of [13/16], and we do not knowingly collect personal information from them. If we become aware that a child has provided us with personal data, we will take steps to delete it.</p><h3><strong>8. Changes to This Privacy Policy</strong></h3><p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with a revised 'Last Updated' date. Your continued use of the Services after such changes constitutes your acceptance of the new terms.</p><h3>&nbsp;</h3>"
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms_conditions',
                'content' => "<h3><strong>1. Acceptance of Terms</strong></h3><p>By using our Services, you confirm that you are at least [18 years old or the legal age in your jurisdiction] and capable of entering into a binding agreement. If you are using our Services on behalf of an organization, you represent that you have the authority to bind that organization to these Terms.</p><h3><strong>2. Use of Services</strong></h3><p>You agree to use our Services only for lawful purposes and in accordance with these Terms. You must not:</p><ul><li>Violate any applicable laws or regulations.</li><li>Use our Services in a manner that could harm, disable, overburden, or impair them.</li><li>Attempt to gain unauthorized access to our systems or networks.</li><li>Transmit any harmful code, viruses, or malicious software.</li></ul><h3><strong>3. User Accounts</strong></h3><p>If you create an account with us, you are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You agree to notify us immediately of any unauthorized use of your account or breach of security.</p><h3><strong>4. Intellectual Property</strong></h3><p>All content, trademarks, logos, and intellectual property associated with our Services are owned by [Your Company Name] or our licensors. You are granted a limited, non-exclusive, non-transferable license to access and use the Services for personal or authorized business purposes. Any unauthorized use, reproduction, or distribution is prohibited.</p><h3><strong>5. Payment and Billing</strong> (if applicable)</h3><p>If our Services involve payments:</p><ul><li>All fees are due at the time of purchase unless otherwise agreed.</li><li>We reserve the right to change pricing or introduce new fees with prior notice.</li><li>Refunds, if applicable, will be handled according to our [Refund Policy].</li></ul><h3><strong>6. Termination of Services</strong></h3><p>We reserve the right to suspend or terminate your access to our Services at our discretion, without prior notice, if:</p><ul><li>You breach these Terms.</li><li>We are required to do so by law.</li><li>Our Services are discontinued or altered.</li></ul><h3><strong>7. Limitation of Liability</strong></h3><p>To the fullest extent permitted by law:</p><ul><li>[Your Company Name] and its affiliates shall not be liable for any direct, indirect, incidental, or consequential damages resulting from your use of our Services.</li><li>Our liability is limited to the amount you paid, if any, for accessing our Services.</li></ul><h3><strong>8. Indemnification</strong></h3><p>You agree to indemnify and hold [Your Company Name], its affiliates, employees, and partners harmless from any claims, liabilities, damages, losses, or expenses arising from your use of the Services or violation of these Terms.</p><h3><strong>9. Modifications to Terms</strong></h3><p>We may update these Terms from time to time. Any changes will be effective immediately upon posting, and your continued use of the Services constitutes your acceptance of the revised Terms.</p>"
            ],
        ];
        foreach ($retuen as $key => $value) {
            $Page = new Page();
            $Page->title = $value['title'];
            $Page->slug = $value['slug'];
            $Page->content = $value['content'];
            $Page->enabled = 1;
            $Page->parent_id = 1;
            $Page->save();
        }


        $FAQ_retuen = [
            [
                'question' => 'What features does your software offer?',
                'description' => 'Our software provides a range of features including automation tools, real-time analytics, cloud-based access, secure data storage, seamless integrations, and customizable solutions tailored to your business needs.',
            ],
            [
                'question' => 'Is your software easy to use?',
                'description' => 'Yes! Our platform is designed to be user-friendly and intuitive, so your team can get started quickly without a steep learning curve.',
            ],
            [
                'question' => 'Can I integrate your software with my existing systems?',
                'description' => 'Absolutely! Our software is built to easily integrate with your current tools and systems, making the transition seamless and efficient.',
            ],
            [
                'question' => 'Is customer support available?',
                'description' => 'Yes! We offer 24/7 customer support. Our dedicated team is ready to assist you with any questions or issues you may have.',
            ],
            [
                'question' => 'Is my data secure with your software?',
                'description' => 'Yes. We use advanced encryption and data protection protocols to ensure your data is secure and private at all times.',
            ],
            [
                'question' => 'Can I customize the software to fit my business needs?',
                'description' => 'Yes! Our software is highly customizable to adapt to your unique workflows and requirements.',
            ],
            [
                'question' => 'What types of businesses can benefit from your software?',
                'description' => 'Our solutions are suitable for a wide range of industries, including retail, healthcare, finance, marketing, and more. We tailor our offerings to meet the specific needs of each business.',
            ],

            [
                'question' => 'Is there a free trial available?',
                'description' => 'Yes! We offer a free trial so you can explore the features and capabilities of our software before committing.',
            ],

            [
                'question' => 'Do I need technical expertise to use the software?',
                'description' => 'Not at all. Our software is designed for users of all skill levels. Plus, our support team is available to guide you through any setup or usage questions.',
            ],

            [
                'question' => 'How often is the software updated?',
                'description' => 'We regularly release updates to improve features, security, and overall performance, ensuring that you always have access to the latest technology.',
            ],
        ];
        foreach ($FAQ_retuen as $key => $FAQ_value) {
            $FAQs = new FAQ();
            $FAQs->question = $FAQ_value['question'];
            $FAQs->description = $FAQ_value['description'];
            $FAQs->enabled = 1;
            $FAQs->parent_id = 1;
            $FAQs->save();
        }
        return '';
    }
}
if (!function_exists('DefaultCustomPage')) {
    function DefaultCustomPage()
    {
        $return = Page::where('enabled', 1)->whereIn('id', [1, 2])->get();
        return $return;
    }
}

if (!function_exists('DefaultBankTransferPayment')) {
    function DefaultBankTransferPayment()
    {
        $bankArray = [
            'bank_transfer_payment' => 'on',
            'bank_name' => 'Bank of America',
            'bank_holder_name' => 'SmartWeb Infotech',
            'bank_account_number' => '4242 4242 4242 4242',
            'bank_ifsc_code' => 'BOA45678',
            'bank_other_details' => '',
        ];

        foreach ($bankArray as $key => $val) {
            \DB::insert(
                'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $val,
                    $key,
                    'payment',
                    1,
                ]
            );
        }

        return '';
    }
}

if (!function_exists('accountTransaction')) {
    function accountTransaction($data)
    {
        $account = \App\Models\Account::find($data['account']);
        if ($data['type'] == 'Deposit') {
            $account->balance = $account->balance + $data['amount'];
        } else {
            $account->balance = $account->balance - $data['amount'];
        }
        $account->save();
    }
}

if (!function_exists('QrCode2FA')) {
    function QrCode2FA()
    {
        $user = Auth::user();

        $google2fa = new Google2FA();

        // generate a secret
        $secret = $google2fa->generateSecretKey();

        // generate the QR code, indicating the address
        // of the web application and the user name
        // or email in this case
        $company = env('APP_NAME');
        if ($user->type != 'super admin') {
            $company = isset(settings()['company_name']) && !empty(settings()['company_name']) ? settings()['company_name'] : $company;
        }

        $qr_code = $google2fa->getQRCodeInline(
            $company,
            $user->email,
            $secret
        );

        // store the current secret in the session
        // will be used when we enable 2FA (see below)
        session(["2fa_secret" => $secret]);

        return $qr_code;
    }
}

if (!function_exists('authPage')) {
    function authPage($id)
    {

        $templateData = [
            'title' => [
                "Secure Access, Seamless Experience.",
                "Your Trusted Gateway to Digital Security.",
                "Fast, Safe & Effortless Login."
            ],
            'description' => [
                "Securely access your account with ease. Whether you're logging in, signing up, or resetting your password, we ensure a seamless and protected experience. Your data, your security, our priority.",
                "Fast, secure, and hassle-free authentication. Sign in with confidence and experience a seamless way to access your account—because your security matters.",
                "A seamless and secure way to access your account. Whether you're logging in, signing up, or recovering your password, we ensure your data stays protected at every step."
            ],
        ];

        $authPage = new AuthPage();
        $authPage->title = json_encode($templateData['title']);
        $authPage->description = json_encode($templateData['description']);
        $authPage->section = 1;
        $authPage->image = 'upload/images/1738239707.svg';
        $authPage->parent_id = $id;
        $authPage->save();

        $createdTemplates[] = $authPage;

        return $createdTemplates;
    }
}

if (!function_exists('RepaymentSchedules')) {
    function RepaymentSchedules($loan)
    {
        $principle_amount = $loan->amount;
        $loan_type = $loan->LoanType->interest_type;
        $annual_interest_rate = $loan->LoanType->interest_rate / 100;
        $today = date('Y-m-d');
        $due_date = $loan->loan_due_date;
        
        // Handle penalty calculation based on type
        $penalty_type = $loan->LoanType->penalty_type ?? 'percentage';
        $penalty_value = $loan->LoanType->penalties ?? 0;
        $penalty_rate = ($penalty_type === 'percentage') ? ($penalty_value / 100) : 0;
        $fixed_penalty = ($penalty_type === 'fixed') ? $penalty_value : 0;
        
        $startDate = Carbon::parse($loan->loan_start_date);
        $endDate = Carbon::parse($loan->loan_due_date);
        
        // Calculate time period in years
        $loan_duration_years = $loan->loan_terms;
        if ($loan->loan_term_period == 'months') {
            $loan_duration_years = $loan->loan_terms / 12;
        } elseif ($loan->loan_term_period == 'weeks') {
            $loan_duration_years = $loan->loan_terms / 52;
        } elseif ($loan->loan_term_period == 'days') {
            $loan_duration_years = $loan->loan_terms / 365;
        }
        
        // Calculate total interest for the entire loan duration (simple interest)
        $total_interest_payment = ($principle_amount * $annual_interest_rate * $loan_duration_years);
        $dates = [];

        if ($loan_type && $loan_type != 'onetime_payment') {
            // Use loan type's payment schedule configuration
            $loanTypeModel = $loan->LoanType;
            
            if ($loanTypeModel && $loanTypeModel->payment_frequency) {
                // Use loan type payment schedule configuration
                $currentDate = $startDate->copy();
                $installmentCount = 0;
                
                while ($installmentCount < $loan->loan_terms && $currentDate->lessThanOrEqualTo($endDate)) {
                    if ($loanTypeModel->payment_frequency == 'monthly') {
                        // Set to specific day of month
                        $currentDate->day($loanTypeModel->payment_day);
                        if ($currentDate->greaterThan($startDate)) {
                            $dates[] = $currentDate->copy()->format('Y-m-d');
                            $installmentCount++;
                        }
                        $currentDate->addMonth();
                    } elseif ($loanTypeModel->payment_frequency == 'weekly') {
                        // Set to specific day of week (1=Monday, 7=Sunday)
                        $currentDate->next($loanTypeModel->payment_day);
                        if ($currentDate->greaterThan($startDate) && $currentDate->lessThanOrEqualTo($endDate)) {
                            $dates[] = $currentDate->copy()->format('Y-m-d');
                            $installmentCount++;
                        }
                    } elseif ($loanTypeModel->payment_frequency == 'daily') {
                        $currentDate->addDays($loanTypeModel->payment_day);
                        if ($currentDate->greaterThan($startDate) && $currentDate->lessThanOrEqualTo($endDate)) {
                            $dates[] = $currentDate->copy()->format('Y-m-d');
                            $installmentCount++;
                        }
                    } elseif ($loanTypeModel->payment_frequency == 'yearly') {
                        $currentDate->addYear();
                        if ($currentDate->greaterThan($startDate) && $currentDate->lessThanOrEqualTo($endDate)) {
                            $dates[] = $currentDate->copy()->format('Y-m-d');
                            $installmentCount++;
                        }
                    }
                }
            } else {
                // Fallback to original logic if no payment schedule is configured
                while (true) {
                    if ($loan->loan_term_period == 'days') {
                        $startDate->addDay();
                    } elseif ($loan->loan_term_period == 'months') {
                        $startDate->addMonthNoOverflow();
                    } elseif ($loan->loan_term_period == 'weeks') {
                        $startDate->addWeek();
                    } elseif ($loan->loan_term_period == 'years') {
                        $startDate->addYear();
                    }

                    if ($startDate->greaterThan($endDate)) break;

                    $dates[] = $startDate->copy()->format('Y-m-d');
                }
            }
        }

        $numberOfInstallments = count($dates);


        if ($loan_type && $loan_type == 'onetime_payment') {
            $totalPayment = $principle_amount + $total_interest_payment;
            if ($due_date < $today) {
                $lateFee = ($penalty_type === 'percentage') ? ($totalPayment * $penalty_rate) : $fixed_penalty;
                $totalPayment = $totalPayment + $lateFee;
            }

            $installments[] = [
                'loan_id' => $loan->id,
                'due_date' => $due_date,
                'installment_amount' => $principle_amount,
                'interest' => $total_interest_payment,
                'penality' => isset($lateFee) ? $lateFee : 0,
                'total_amount' => $totalPayment,
                'status' => 'Pending',
                'parent_id' => parentId(),
            ];
        }

        if ($loan_type && $loan_type == 'fixed_rate') {

            $principalPerInstallment = $principle_amount / $numberOfInstallments;
            $interestPerInstallment = $total_interest_payment / $numberOfInstallments;
            $principalRemaining = $principle_amount;
            $totalPayment = $principalPerInstallment + $interestPerInstallment;
            $installments = [];
            foreach ($dates as $key => $value) {

                if ($due_date < $today) {
                    $lateFee = ($penalty_type === 'percentage') ? ($principle_amount * $penalty_rate) : $fixed_penalty;
                    $totalPayment = $principalPerInstallment + $interestPerInstallment + $lateFee;
                }
                $principalRemaining -= $principalPerInstallment;


                $installments[] = [
                    'loan_id' => $loan->id,
                    'due_date' => $value,
                    'installment_amount' => $principalPerInstallment,
                    'interest' => $interestPerInstallment,
                    'total_amount' => $totalPayment,
                    'penality' => isset($lateFee) ? $lateFee : 0,
                    'status' => 'Pending',
                    'parent_id' => parentId()
                ];
            }
        }
        if ($loan_type && $loan_type == 'mortgage_amortization') {
            $monthlyInterestRate = $annual_interest_rate / 12;
            $totalPayment = $principle_amount * ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $numberOfInstallments)) / (pow(1 + $monthlyInterestRate, $numberOfInstallments) - 1);
            foreach ($dates as $key => $value) {
                $interest = $principle_amount * $monthlyInterestRate;
                $installment_amount = $totalPayment - $interest;
                if ($due_date < $today) {
                    $lateFee = ($penalty_type === 'percentage') ? ($installment_amount * $penalty_rate) : $fixed_penalty;
                    $totalPayment = $totalPayment + $lateFee;
                }

                $principle_amount -= $installment_amount;

                $installments[] = [
                    'loan_id' => $loan->id,
                    'due_date' => $value,
                    'installment_amount' => $installment_amount,
                    'interest' => $interest,
                    'total_amount' => $totalPayment,
                    'penality' => isset($lateFee) ? $lateFee : 0,
                    'status' => 'Pending',
                    'parent_id' => parentId()
                ];
            }
        }
        if ($loan_type && $loan_type == 'reducing_amount') {

            $futureDate = strtotime($today);
            if ($loan->loan_terms > 0) {
                $futureDate = strtotime('+' . $loan->loan_terms . $loan->loan_term_period, $futureDate);
            }
            $principalRemaining = $principle_amount;
            $monthlyInterestRate = $interest / 12;

            $principalPerInstallment = $principle_amount / $numberOfInstallments;
            $interest = $principle_amount * $monthlyInterestRate;
            foreach ($dates as $key => $value) {
                $interest = $principle_amount * $monthlyInterestRate;
                $totalPayment = $principalPerInstallment + $interest;
                if ($due_date < $today) {
                    $lateFee = ($penalty_type === 'percentage') ? ($totalPayment * $penalty_rate) : $fixed_penalty;
                    $totalPayment = $totalPayment + $lateFee;
                }

                $principle_amount -= $principalPerInstallment;

                $installments[] = [
                    'loan_id' => $loan->id,
                    'due_date' => $value,
                    'installment_amount' => $principalPerInstallment,
                    'interest' => $interest,
                    'total_amount' => $totalPayment,
                    'penality' => isset($lateFee) ? $lateFee : 0,
                    'status' => 'Pending',
                    'parent_id' => parentId()
                ];
            }
        }
        if ($loan_type && $loan_type == 'flat_rate') {


            $principalPerInstallment = $principle_amount / $numberOfInstallments;
            $interestPerInstallment = $interest_payment / $numberOfInstallments;
            $principalRemaining = $principle_amount;
            $installments = [];
            $totalPayment = $principalPerInstallment + $interestPerInstallment;
            foreach ($dates as $key => $value) {

                if ($due_date < $today) {
                    $lateFee = ($penalty_type === 'percentage') ? ($totalPayment * $penalty_rate) : $fixed_penalty;
                    $totalPayment = $totalPayment + $lateFee;
                }
                $principalRemaining -= $principalPerInstallment;

                $installments[] = [
                    'loan_id' => $loan->id,
                    'due_date' => $value,
                    'installment_amount' => $principalPerInstallment,
                    'interest' => $interestPerInstallment,
                    'total_amount' => $totalPayment,
                    'penality' => isset($lateFee) ? $lateFee : 0,
                    'status' => 'Pending',
                    'parent_id' => parentId()
                ];
            }
        }


        return $installments;
    }
}

if (!function_exists('defaultCustomerCreate')) {
    function defaultCustomerCreate($id)
    {
        $CustomerRoleData = [
            'name' => 'customer',
            'parent_id' => $id,
        ];
        $systemCustomerRole = Role::create($CustomerRoleData);

        $systemCustomerPermissions = [
            ['name' => 'manage contact'],
            ['name' => 'create contact'],
            ['name' => 'edit contact'],
            ['name' => 'delete contact'],
            ['name' => 'manage note'],
            ['name' => 'create note'],
            ['name' => 'edit note'],
            ['name' => 'delete note'],
        ];
        $systemCustomerRole->givePermissionTo($systemCustomerPermissions);
        return $systemCustomerRole;
    }
}




if (!function_exists('FilesExtension')) {
    function FilesExtension()
    {
        return [
            "jpeg" => "jpeg",
            "jpg" => "jpg",
            "png" => "png",
            "doc" => "doc",
            "csv" => "csv",
            "docx" => "docx",
            "mp4" => "mp4",
            "mp3" => "mp3",
            "xls" => "xls",
            "pdf" => "pdf",
            "zip" => "zip",
            "json" => "json",
            "txt" => "txt",
            "svg" => "svg",
            "ppt" => "ppt",
            "3dmf" => "3dmf",
            "3dm" => "3dm",
            "gtar" => "gtar",
            "flv" => "flv",
            "fh4" => "fh4",
            "fh5" => "fh5",
            "fhc" => "fhc",
            "help" => "help",
            "hlp" => "hlp",
            "avi" => "avi",
            "ai" => "ai",
            "bin" => "bin",
            "bmp" => "bmp",
            "cab" => "cab",
            "c" => "c",
            "c++" => "c++",
            "class" => "class",
            "css" => "css",
            "cdr" => "cdr",
            "dot" => "dot",
            "dwg" => "dwg",
            "eps" => "eps",
            "exe" => "exe",
            "gif" => "gif",
            "gz" => "gz",
            "js" => "js",
            "java" => "java",
            "latex" => "latex",
            "log" => "log",
            "m3u" => "m3u",
            "midi" => "midi",
            "mid" => "mid",
            "mov" => "mov",
            "ppz" => "ppz",
            "pot" => "pot",
            "ps" => "ps",
            "qt" => "qt",
            "qd3d" => "qd3d",
            "qd3" => "qd3",
            "qxd" => "qxd",
            "rar" => "rar",
            "sgml" => "sgml",
            "sgm" => "sgm",
            "tar" => "tar",
            "tiff" => "tiff",
            "tif" => "tif",
            "tgz" => "tgz",
            "tex" => "tex",
            "html" => "html",
            "htm" => "htm",
            "ico" => "ico",
            "vob" => "vob",
            "wav" => "wav",
            "wrl" => "wrl",
            "xla" => "xla",
            "xlc" => "xlc",
            "xml" => "xml",
            "imap" => "imap",
            "inf" => "inf",
            "jpe" => "jpe",
            "mpeg" => "mpeg",
            "mpg" => "mpg",
            "mp2" => "mp2",
            "ogg" => "ogg",
            "phtml" => "phtml",
            "php" => "php",
            "pgp" => "pgp",
            "pps" => "pps",
            "ra" => "ra",
            "ram" => "ram",
            "rm" => "rm",
            "rtf" => "rtf",
            "spr" => "spr",
            "sprite" => "sprite",
            "stream" => "stream",
            "swf" => "swf",
        ];
    }
}




if (!function_exists('fetch_file')) {
    function fetch_file($filename = '', $path = '')
    {
        $settings = settingsById(1);

        try {
            if ($settings['storage_type'] == 'wasabi') {
                config(
                    [
                        'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                        'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                        'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                        'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                        'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com',
                    ]
                );
            } elseif ($settings['storage_type'] == 's3') {
                config(
                    [
                        'filesystems.disks.s3.key' => $settings['aws_s3_key'],
                        'filesystems.disks.s3.secret' => $settings['aws_s3_secret'],
                        'filesystems.disks.s3.region' => $settings['aws_s3_region'],
                        'filesystems.disks.s3.bucket' => $settings['aws_s3_bucket'],
                        'filesystems.disks.s3.use_path_style_endpoint' => false,
                    ]
                );
            }

            return \Storage::disk($settings['storage_type'])->url($path . $filename);
        } catch (\Throwable $th) {
            return '';
        }
    }
}


if (!function_exists('handleFileUpload')) {
    function handleFileUpload($file, $uploadPath, $customValidation = [])
    {

        try {
            $settings = settingsById(1);


            if (empty($settings['storage_type'])) {
                throw new \Exception(__('Please set proper configuration for storage.'));
            }

            // Setup filename
            $originalName = $file->getClientOriginalName();
            $fileNameOnly = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileName = $fileNameOnly . '_' . time() . '.' . $extension;

            // Determine disk and MIME types
            switch ($settings['storage_type']) {
                case 'wasabi':
                    config([
                        'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                        'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                        'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                        'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                        'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com',
                    ]);
                    $disk = 'wasabi';
                    $mimes = $settings['wasabi_file_type'] ?? '';
                    break;

                case 's3':
                    config([
                        'filesystems.disks.s3.key' => $settings['aws_s3_key'],
                        'filesystems.disks.s3.secret' => $settings['aws_s3_secret'],
                        'filesystems.disks.s3.region' => $settings['aws_s3_region'],
                        'filesystems.disks.s3.bucket' => $settings['aws_s3_bucket'],
                    ]);
                    $disk = 's3';
                    $mimes = $settings['aws_s3_file_type'] ?? '';
                    break;

                default:
                    $disk = 'local';
                    $mimes = $settings['local_file_type'] ?? '';
                    break;
            }

            // Validate file
            $validation = count($customValidation) > 0 ? $customValidation : ['mimes:' . $mimes];
            $validator = \Validator::make(
                ['upload_file' => $file],
                ['upload_file' => $validation]
            );

            if ($validator->fails()) {
                return [
                    'flag' => 0,
                    'msg' => $validator->messages()->first()
                ];
            }

            // Upload logic
            if ($disk === 'local') {
                $destination = storage_path($uploadPath);
                if (!file_exists($destination)) {
                    mkdir($destination, 0777, true);
                }
                $file->move($destination, $fileName);
            } else {
                \Storage::disk($disk)->putFileAs($uploadPath, $file, $fileName);
            }

            return [
                'flag' => 1,
                'msg' => 'Upload successful',
                'path' => $uploadPath . '/' . $fileName,
                'filename' => $fileName,
                'disk' => $disk,
            ];
        } catch (\Exception $e) {
            return [
                'flag' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
}


if (!function_exists('uploadLogoFile')) {
    function uploadLogoFile($file, $fieldName, $parentId, $userType)
    {
        try {
            $settings = settingsById(1);

            if (empty($settings['storage_type'])) {
                throw new \Exception(__('Please set proper configuration for storage.'));
            }
            // Determine disk and config
            switch ($settings['storage_type']) {
                case 'wasabi':
                    config([
                        'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                        'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                        'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                        'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                        'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com',
                    ]);
                    $disk = 'wasabi';
                    break;

                case 's3':
                    config([
                        'filesystems.disks.s3.key' => $settings['aws_s3_key'],
                        'filesystems.disks.s3.secret' => $settings['aws_s3_secret'],
                        'filesystems.disks.s3.region' => $settings['aws_s3_region'],
                        'filesystems.disks.s3.bucket' => $settings['aws_s3_bucket'],
                    ]);
                    $disk = 's3';
                    break;

                default:
                    $disk = 'local';
                    break;
            }

            // Validate PNG file
            $validator = \Validator::make(
                ['upload_file' => $file],
                ['upload_file' => 'required|mimes:png']
            );

            if ($validator->fails()) {
                return [
                    'flag' => 0,
                    'msg' => $validator->messages()->first()
                ];
            }

            $uploadPath = 'upload/logo/';
            $filename = ($userType === 'super admin')
                ? "{$fieldName}.png"
                : "{$parentId}_{$fieldName}.png";

            if ($disk === 'local') {
                $destination = public_path($uploadPath);
                if (!file_exists($destination)) {
                    if (!mkdir($destination, 0777, true) && !is_dir($destination)) {
                        throw new \Exception("Unable to create directory: $destination");
                    }
                }
                $file->storeAs($uploadPath, $filename);
            } else {
                \Storage::disk($disk)->putFileAs($uploadPath, $file, $filename);
            }

            return [
                'flag' => 1,
                'msg' => 'Upload successful',
                'filename' => $filename,
                'path' => $uploadPath . '/' . $filename,
                'disk' => $disk
            ];
        } catch (\Exception $e) {
            \Log::error('Logo upload error: ' . $e->getMessage());
            return [
                'flag' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
}


if (!function_exists('deleteOldFile')) {
    function deleteOldFile($imgName, $path)
    {
        try {
            $settings = settingsById(1);
            $disk = $settings['storage_type'] ?? 'local';

            if ($disk === 'wasabi') {
                config([
                    'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                    'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                    'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                    'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                    'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com',
                ]);
            } elseif ($disk === 's3') {
                config([
                    'filesystems.disks.s3.key' => $settings['aws_s3_key'],
                    'filesystems.disks.s3.secret' => $settings['aws_s3_secret'],
                    'filesystems.disks.s3.region' => $settings['aws_s3_region'],
                    'filesystems.disks.s3.bucket' => $settings['aws_s3_bucket'],
                ]);
            }

            \Storage::disk($disk)->delete($path . $imgName);
        } catch (\Exception $e) {
            \Log::error("Failed to delete file: " . $e->getMessage());
        }
    }
}


if (!function_exists('send_twilio_msg')) {
    function send_twilio_msg($to, $msg)
    {
         if (!empty($msg) && !empty($to)) {
            $settings = settings();


            $sid         = $settings['twilio_sid'];
            $token       = $settings['twilio_token'];
            $from_number = $settings['twilio_from_number'];

            try {
                $client = new Client($sid, $token);
                $client->messages->create($to, [
                    'from' => $from_number,
                    'body' => $msg,
                ]);
            } catch (\Exception $e) {
                 \Log::error('Twilio SMS send failed: ' . $e->getMessage());
            }
        }
    }
}


if (!function_exists('NewPermission')) {
    function NewPermission()
    {

        $permissions = [
            ['name' => 'manage storage settings', 'guard_name' => 'web', 'roles' => ['super admin']],
            ['name' => 'repayment schedule payment', 'guard_name' => 'web', 'roles' => ['owner', 'customer']],
            ['name' => 'manage payment settings', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage twilio settings', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage loan', 'guard_name' => 'web', 'roles' => ['customer']],
            ['name' => 'create loan', 'guard_name' => 'web', 'roles' => ['customer']],
            ['name' => 'show loan', 'guard_name' => 'web', 'roles' => ['customer']],
            ['name' => 'manage account settings', 'guard_name' => 'web', 'roles' => ['customer']],
            ['name' => 'manage account', 'guard_name' => 'web', 'roles' => ['customer']],
            ['name' => 'show account', 'guard_name' => 'web', 'roles' => ['customer']],
            ['name' => 'manage transaction', 'guard_name' => 'web', 'roles' => ['customer']],
            ['name' => 'manage repayment', 'guard_name' => 'web', 'roles' => ['customer']],
            ['name' => 'repayment schedule payment', 'guard_name' => 'web', 'roles' => ['customer']],
            ['name' => 'manage password settings', 'guard_name' => 'web', 'roles' => ['customer']],
            ['name' => 'manage 2FA settings', 'guard_name' => 'web', 'roles' => ['customer']],
        ];

        if (!empty($permissions)) {
            foreach ($permissions as $permData) {
                Permission::firstOrCreate([
                    'name' => $permData['name'],
                    'guard_name' => $permData['guard_name']
                ]);
            }

            $permissionsByRole = [];

            foreach ($permissions as $permData) {
                foreach ($permData['roles'] as $roleName) {
                    $permissionsByRole[$roleName][] = $permData['name'];
                }
            }

            foreach ($permissionsByRole as $roleName => $permNames) {
                $roles = Role::where('name', $roleName)->get();

                foreach ($roles as $role) {
                    $role->givePermissionTo($permNames);
                }
            }
        }

        $removePermissions = [
            ['name' => 'create note', 'guard_name' => 'web', 'roles' => ['manager', 'customer']],
            ['name' => 'edit note', 'guard_name' => 'web', 'roles' => ['manager', 'customer']],
            ['name' => 'delete note', 'guard_name' => 'web', 'roles' => ['manager', 'customer']],
            ['name' => 'show note', 'guard_name' => 'web', 'roles' => ['manager', 'customer']],
        ];

        foreach ($removePermissions as $permData) {
            $permission = Permission::where('name', $permData['name'])
                ->where('guard_name', $permData['guard_name'])
                ->first();

            if ($permission) {
                foreach ($permData['roles'] as $roleName) {
                    $roles = Role::where('name', $roleName)->get();
                    foreach ($roles as $role) {
                        $role->revokePermissionTo($permission);
                    }
                }
            }
        }
        defaultSMSTemplate();
        return true;
    }
}

if (!function_exists('defaultSMSTemplate')) {
    function defaultSMSTemplate()
    {
        $templateData = [
            'user_create' =>
            [
                'module' => 'user_create',
                'sms_message' => 'Hello {new_user_name}, Welcome to {company_name}! Your account is now active. Username: {username} Password: {password} Login here: {app_link} Contact: {company_phone_number} | {company_email}
                    ',
            ],
            'customer_create' =>
            [
                'module' => 'customer_create',
                'sms_message' => 'Welcome to {company_name}, {new_customer_name}!Your account at {branch_name} is now active.Contact: {branch_email}, {branch_location}
                ',
            ],
            'loan_create' =>
            [
                'module' => 'loan_create',
                'sms_message' => 'Hi {customer_name}, your {loan_type} of {company_currency} {loan_amount} at {company_name} starts on {loan_start_date}, due on {loan_due_date}. Status: {loan_status}.
                ',
            ],
            'loan_status_update' =>
            [
                'module' => 'loan_status_update',
                'sms_message' => 'Hi {customer_name}, your {loan_type} of {company_currency} {loan_amount} from {company_name} ({branch_name}) starts {loan_start_date}, due {loan_due_date}. Status: {loan_status}.
                ',
            ],
            'repayment_create' =>
            [
                'module' => 'repayment_create',
                'sms_message' => 'Hi {customer_name}, your payment on {payment_date} at {company_name} is received. Total: {company_currency} {total_amount} (Principal: {principal_amount}, Interest: {interest}, Penalty: {penality}).
                ',
            ],
            'account_create' =>
            [
                'module' => 'account_create',
                'sms_message' => 'Hi {customer_name}, your {account_type} account is {account_status}. Balance: {company_currency} {balance}, Interest: {interest_rate}%/{interest_duration}. Min Bal: {minimum_maintain_amount}.
                ',
            ],
            'transaction_create' =>
            [
                'module' => 'transaction_create',
                'sms_message' => 'Hi {customer_name}, your {transaction_type} of {company_currency} {transaction_amount} on {transaction_date_time} is {transaction_status}. - {company_name}
                ',
            ],
            'payment_reminder' =>
            [
                'module' => 'payment_reminder',
                'sms_message' => 'Hi {customer_name}, your installment of {company_currency} {installment_amount} at {company_name} ({branch_name}) is {payment_status}. Due: {due_date}. Total: {total_amount}.
                ',
            ],

        ];

        $createdTemplates = [];

        foreach ($templateData as $key => $value) {
            $template = Notification::where('module', $value['module'])->whereNull('sms_message')->update(['sms_message' => $value['sms_message'], 'enabled_sms' => 0]);
            $createdTemplates[] = $template;
        }

        return $createdTemplates;
    }
}
