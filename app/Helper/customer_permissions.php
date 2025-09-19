<?php

namespace App\Helper;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class CustomerPermissions
{
    /**
     * Ensure the customer has all required permissions
     * 
     * @param \App\Models\User $customer
     * @return void
     */
    public static function ensurePermissions(User $customer)
    {
        // List of required permissions for customers
        $requiredPermissions = [
            'manage loan', 
            'create loan', 
            'edit loan', 
            'delete loan', 
            'show loan',
            'manage loan type',
            'show loan type',
            'manage contact',
            'create contact',
            'edit contact',
            'delete contact',
            'manage note',
            'create note',
            'edit note',
            'delete note',
            'manage account',
            'show account',
            'manage transaction',
            'manage repayment',
            'manage account settings',
            'manage password settings',
            'manage 2FA settings'
        ];

        // Assign permissions directly to the customer
        foreach ($requiredPermissions as $permission) {
            $permObj = Permission::where('name', $permission)->first();
            if ($permObj && !$customer->hasPermissionTo($permission)) {
                $customer->givePermissionTo($permObj);
            }
        }
    }
}
