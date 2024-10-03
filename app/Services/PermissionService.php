<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionService 
{
    /**
     * sets the user role permissions to a new added users
     * 
     * @param  App\Models\User $user
     * @return bool
     */
    public static function setUserPermissions(User $user) : bool {
        $permissions = DB::table("role_has_permissions")
            ->where("role_id", "=", $user->role_id)
            ->select("permission_id")
            ->get();
        
        $permissionsRecords = $permissions->map(function ($perm) use ($user) {
            return [
                "id" => Str::uuid()->toString(),
                "user_id" => $user->id,
                "permission_id" => $perm->permission_id
            ];
        })->toArray();
        
        try {
            DB::table("user_has_permissions")
                ->insert($permissionsRecords);
            return true;
        } catch (Exception $e) {
            return false;
        } 
    }
}