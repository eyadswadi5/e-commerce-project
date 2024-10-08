<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class Role extends BaseModel
{
    protected $fillable = ['role', 'desc'];

    public function permissions() {
        $permissions = Permission::join("role_has_permissions", "permissions.id", "=", "role_has_permissions.permission_id")
            ->where("role_has_permissions.role_id", "=", $this->id)
            ->select("permissions.*")
            ->get(); 

        return $permissions;
    }

    public static function allWithPermissions() {
        try {
            $roles = Role::all();
            $x = $roles->map(function ($role) {
                return [
                    "id" => $role->id,
                    "role" => $role->role,
                    "desc" => $role->desc,
                    "permissions" => $role->permissions(),
                ];
            });
            return $x;
        } catch (QueryException $e) {
            throw new Exception("database error occurres");
        } 
    }
}
