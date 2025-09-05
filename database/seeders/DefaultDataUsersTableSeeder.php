<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DefaultDataUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Default All Permission
        $currentRouteName = Route::currentRouteName();
        if ($currentRouteName != 'LaravelUpdater::database') {
            $allPermission = [
                [
                    'name' => 'manage user',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create user',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit user',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete user',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'show user',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage role',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create role',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit role',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete role',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage contact',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create contact',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit contact',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete contact',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage note',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create note',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit note',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete note',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage logged history',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete logged history',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage pricing packages',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create pricing packages',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit pricing packages',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete pricing packages',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'buy pricing packages',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage pricing transation',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage coupon',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create coupon',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit coupon',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete coupon',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage coupon history',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete coupon history',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage account settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage password settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage general settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage company settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage email settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage payment settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage seo settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage google recaptcha settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage notification',
                    'gaurd_name' => 'web',
                ],
                [
                    'name' => 'edit notification',
                    'gaurd_name' => 'web',
                ],
                [
                    'name' => 'manage FAQ',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create FAQ',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit FAQ',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete FAQ',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage Page',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create Page',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit Page',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete Page',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'show Page',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage home page',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit home page',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage footer',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit footer',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage 2FA settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage auth page',
                    'guard_name' => 'web',
                ],

                [
                    'name' => 'manage branch',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'create branch',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'delete branch',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'edit branch',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'manage loan type',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'create loan type',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'edit loan type',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'delete loan type',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'show loan type',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'manage document type',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'create document type',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'edit document type',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'delete document type',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'manage customer',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'create customer',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'edit customer',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'delete customer',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'show customer',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'create customer document',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'delete customer document',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'manage loan',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'create loan',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'edit loan',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'delete loan',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'show loan',
                    'guard_name' => 'web'
                ],

                [
                    'name' => 'manage account type',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'create account type',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'edit account type',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'delete account type',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'manage account',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'create account',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'edit account',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'delete account',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'show account',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'manage transaction',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'create transaction',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'edit transaction',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'delete transaction',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'manage expense',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'create expense',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'edit expense',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'delete expense',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'manage repayment',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'create repayment',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'edit repayment',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'delete repayment',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'delete repayment schedule',
                    'guard_name' => 'web'
                ],
                [
                    'name' => 'payment reminder',
                    'gaurd_name' => 'web',
                ],
            ];
            Permission::insert($allPermission);

            // Default Super Admin Role
            $superAdminRoleData =  [
                'name' => 'super admin',
                'parent_id' => 0,
            ];
            $systemSuperAdminRole = Role::create($superAdminRoleData);
            $systemSuperAdminPermission = [
                ['name' => 'manage user'],
                ['name' => 'create user'],
                ['name' => 'edit user'],
                ['name' => 'delete user'],
                ['name' => 'show user'],
                ['name' => 'manage contact'],
                ['name' => 'create contact'],
                ['name' => 'edit contact'],
                ['name' => 'delete contact'],
                ['name' => 'manage note'],
                ['name' => 'create note'],
                ['name' => 'edit note'],
                ['name' => 'delete note'],
                ['name' => 'manage pricing packages'],
                ['name' => 'create pricing packages'],
                ['name' => 'edit pricing packages'],
                ['name' => 'delete pricing packages'],
                ['name' => 'manage pricing transation'],
                ['name' => 'manage coupon'],
                ['name' => 'create coupon'],
                ['name' => 'edit coupon'],
                ['name' => 'delete coupon'],
                ['name' => 'manage coupon history'],
                ['name' => 'delete coupon history'],
                ['name' => 'manage account settings'],
                ['name' => 'manage password settings'],
                ['name' => 'manage general settings'],
                ['name' => 'manage email settings'],
                ['name' => 'manage payment settings'],
                ['name' => 'manage seo settings'],
                ['name' => 'manage google recaptcha settings'],
                ['name' => 'manage FAQ'],
                ['name' => 'create FAQ'],
                ['name' => 'edit FAQ'],
                ['name' => 'delete FAQ'],
                ['name' => 'manage Page'],
                ['name' => 'create Page'],
                ['name' => 'edit Page'],
                ['name' => 'delete Page'],
                ['name' => 'show Page'],
                ['name' => 'manage home page'],
                ['name' => 'edit home page'],
                ['name' => 'manage footer'],
                ['name' => 'edit footer'],
                ['name' => 'manage 2FA settings'],
                ['name' => 'manage auth page'],


            ];
            $systemSuperAdminRole->givePermissionTo($systemSuperAdminPermission);
            // Default Super Admin
            $superAdminData =     [
                'name' => 'Super Admin',
                'email' => 'superadmin@gmail.com',
                'password' => Hash::make('123456'),
                'type' => 'super admin',
                'lang' => 'english',
                'email_verified_at' => now(),
                'profile' => 'avatar.png',
            ];
            $systemSuperAdmin = User::create($superAdminData);
            $systemSuperAdmin->assignRole($systemSuperAdminRole);
            HomePageSection();
            CustomPage();
            authPage($systemSuperAdmin->id);
            DefaultBankTransferPayment();

            // Default Owner Role
            $ownerRoleData = [
                'name' => 'owner',
                'parent_id' => $systemSuperAdmin->id,
            ];
            $systemOwnerRole = Role::create($ownerRoleData);

            // Default Owner All Permissions
            $systemOwnerPermission = [
                ['name' => 'manage user'],
                ['name' => 'create user'],
                ['name' => 'edit user'],
                ['name' => 'delete user'],
                ['name' => 'manage role'],
                ['name' => 'create role'],
                ['name' => 'edit role'],
                ['name' => 'delete role'],
                ['name' => 'manage contact'],
                ['name' => 'create contact'],
                ['name' => 'edit contact'],
                ['name' => 'delete contact'],
                ['name' => 'manage note'],
                ['name' => 'create note'],
                ['name' => 'edit note'],
                ['name' => 'delete note'],
                ['name' => 'manage logged history'],
                ['name' => 'delete logged history'],
                ['name' => 'manage pricing packages'],
                ['name' => 'buy pricing packages'],
                ['name' => 'manage pricing transation'],
                ['name' => 'manage account settings'],
                ['name' => 'manage password settings'],
                ['name' => 'manage general settings'],
                ['name' => 'manage company settings'],
                ['name' => 'manage email settings'],
                ['name' => 'manage 2FA settings'],
                ['name' => 'manage branch'],
                ['name' => 'create branch'],
                ['name' => 'delete branch'],
                ['name' => 'edit branch'],
                ['name' => 'manage loan type'],
                ['name' => 'create loan type'],
                ['name' => 'edit loan type'],
                ['name' => 'delete loan type'],
                ['name' => 'show loan type'],
                ['name' => 'manage document type'],
                ['name' => 'create document type'],
                ['name' => 'edit document type'],
                ['name' => 'delete document type'],
                ['name' => 'manage customer'],
                ['name' => 'create customer'],
                ['name' => 'edit customer'],
                ['name' => 'delete customer'],
                ['name' => 'show customer'],
                ['name' => 'create customer document'],
                ['name' => 'delete customer document'],
                ['name' => 'manage loan'],
                ['name' => 'create loan'],
                ['name' => 'edit loan'],
                ['name' => 'delete loan'],
                ['name' => 'show loan',],
                ['name' => 'manage account type'],
                ['name' => 'create account type'],
                ['name' => 'edit account type'],
                ['name' => 'delete account type'],
                ['name' => 'manage account'],
                ['name' => 'create account'],
                ['name' => 'edit account'],
                ['name' => 'delete account'],
                ['name' => 'show account'],
                ['name' => 'manage transaction'],
                ['name' => 'create transaction'],
                ['name' => 'edit transaction'],
                ['name' => 'delete transaction'],
                ['name' => 'manage expense'],
                ['name' => 'create expense'],
                ['name' => 'edit expense'],
                ['name' => 'delete expense'],
                ['name' => 'manage repayment'],
                ['name' => 'create repayment'],
                ['name' => 'edit repayment'],
                ['name' => 'delete repayment'],
                ['name' => 'delete repayment schedule'],
                ['name' => 'payment reminder'],
                ['name' => 'manage notification'],
                ['name' => 'edit notification'],
            ];
            $systemOwnerRole->givePermissionTo($systemOwnerPermission);

            // Default Owner Create
            $ownerData =    [
                'name' => 'Owner',
                'email' => 'owner@gmail.com',
                'password' => Hash::make('123456'),
                'type' => 'owner',
                'lang' => 'english',
                'email_verified_at' => now(),
                'profile' => 'avatar.png',
                'subscription' => 1,
                'parent_id' => $systemSuperAdmin->id,
                'subscription_expire_date' => Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD'),
            ];
            $systemOwner = User::create($ownerData);
            // Default Template Assign
            defaultTemplate($systemOwner->id);
            defaultCustomerCreate($systemOwner->id);
            // Default Owner Role Assign
            $systemOwner->assignRole($systemOwnerRole);



            // Default Owner Role
            $managerRoleData =  [
                'name' => 'manager',
                'parent_id' => $systemOwner->id,
            ];
            $systemManagerRole = Role::create($managerRoleData);
            // Default Manager All Permissions
            $systemManagerPermission = [
                ['name' => 'manage user'],
                ['name' => 'create user'],
                ['name' => 'edit user'],
                ['name' => 'delete user'],
                ['name' => 'manage contact'],
                ['name' => 'create contact'],
                ['name' => 'edit contact'],
                ['name' => 'delete contact'],
                ['name' => 'manage note'],
                ['name' => 'create note'],
                ['name' => 'edit note'],
                ['name' => 'delete note'],
                ['name' => 'manage 2FA settings'],
                ['name' => 'manage branch'],
                ['name' => 'create branch'],
                ['name' => 'delete branch'],
                ['name' => 'edit branch'],
                ['name' => 'manage loan type'],
                ['name' => 'create loan type'],
                ['name' => 'edit loan type'],
                ['name' => 'delete loan type'],
                ['name' => 'show loan type'],
                ['name' => 'manage document type'],
                ['name' => 'create document type'],
                ['name' => 'edit document type'],
                ['name' => 'delete document type'],
                ['name' => 'manage customer'],
                ['name' => 'create customer'],
                ['name' => 'edit customer'],
                ['name' => 'delete customer'],
                ['name' => 'show customer'],
                ['name' => 'create customer document'],
                ['name' => 'delete customer document'],
                ['name' => 'manage loan'],
                ['name' => 'create loan'],
                ['name' => 'edit loan'],
                ['name' => 'delete loan'],
                ['name' => 'show loan',],
                ['name' => 'manage account type'],
                ['name' => 'create account type'],
                ['name' => 'edit account type'],
                ['name' => 'delete account type'],
                ['name' => 'manage account'],
                ['name' => 'create account'],
                ['name' => 'edit account'],
                ['name' => 'delete account'],
                ['name' => 'show account'],
                ['name' => 'manage transaction'],
                ['name' => 'create transaction'],
                ['name' => 'edit transaction'],
                ['name' => 'delete transaction'],
                ['name' => 'manage expense'],
                ['name' => 'create expense'],
                ['name' => 'edit expense'],
                ['name' => 'delete expense'],
                ['name' => 'manage repayment'],
                ['name' => 'create repayment'],
                ['name' => 'edit repayment'],
                ['name' => 'delete repayment'],
            ];
            $systemManagerRole->givePermissionTo($systemManagerPermission);

            // Default Manager Create
            $managerData =   [
                'name' => 'Manager',
                'email' => 'manager@gmail.com',
                'password' => Hash::make('123456'),
                'type' => 'manager',
                'lang' => 'english',
                'email_verified_at' => now(),
                'profile' => 'avatar.png',
                'subscription' => 0,
                'parent_id' => $systemOwner->id,
            ];
            $systemManager = User::create($managerData);
            // Default Manager Role Assign
            $systemManager->assignRole($systemManagerRole);
             

            // Subscription default data
            $subscriptionData = [
                'title' => 'Basic',
                'package_amount' => 0,
                'interval' => 'Monthly',
                'user_limit' => 10,
                'customer_limit' => 10,
                'enabled_logged_history' => 1,
            ];
            \App\Models\Subscription::create($subscriptionData);
            NewPermission();
        } else {
            NewPermission();
        }
    }
}
