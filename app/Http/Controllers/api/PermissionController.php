<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function PHPSTORM_META\map;

class PermissionController extends BaseController
{
    public function index()
    {
        try {
            $permissions = Permission::all();
            $roles = Role::allWithPermissions();

            return $this->rst(true, 200, null, null, [
                "permissions" => $permissions,
                "roles" => $roles,
            ]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to get permissions", [["message" => "database error occurres"]]);
        } catch (Exception $e) {
            return $this->rst(false, 500, "Failed to get permissions", [["message" => "unknown error occurres"]]);
        }
    }

    public function role_has_permissions(string $role_id)
    {
        $validator = Validator::make(["role_id" => $role_id], [
            "role_id" => "required|uuid|exists:roles,id"
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "Failed to get role permissions", $validator->errors());

        try {
            $role = Role::findOrFail($role_id);
            $permissions = $role->permissions();
            $result = [
                "role" => [
                    "id" => $role->id,
                    "role" => $role->role,
                    "desc" => $role->desc,
                    "permissions" => $permissions
                ]
            ];
            return $this->rst(true, 200, null, null, $result);
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "Failed to get role permissions", [["message" => "role not found"]]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to get role permissions", [["message" => "database error occurres"]]);
        } catch (Exception $e) {
            return $this->rst(false, 500, "Failed to get role permissions", [["message" => "unknown error occurres"]]);
        }
    }

    public function user_has_permissions(string $user_id)
    {
        $validator = Validator::make(["user_id" => $user_id], [
            "user_id" => "required|uuid|exists:users,id"
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "Failed to get user permissions", $validator->errors());

        try {
            $user = User::findOrFail($user_id);
            $permissions = $user->permissions();
            $result = [
                "user" => [
                    "id" => $user->id,
                    "name" => $user->name,
                    "email" => $user->email,
                    "phone_number" => $user->phone_number,
                    "role" => $user->role()->role,
                    "permissions" => $permissions
                ]
            ];
            return $this->rst(true, 200, null, null, $result);
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "Failed to get user permissions", [["message" => "user not found"]]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to get user permissions", [["message" => "database error occurres"]]);
        } catch (Exception $e) {
            return $this->rst(false, 500, "Failed to get user permissions", [["message" => "unknown error occurres"]]);
        }
    }

    public function add_role_permissions(string $role_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            "permissions.*" => "required|uuid|exists:permissions,id"
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "Failed to add role permissions");

        try {
            $role = Role::findOrFail($role_id);
            $permissions = collect($request->permissions)
                ->map(function ($p) use ($role) {
                    return [
                        "id" => Str::uuid()->toString(),
                        "role_id" => $role->id,
                        "permission_id" => $p,
                        "created_at" => now(),
                        "updated_at" => now(),
                    ];
                })->toArray();

            try {
                DB::table("role_has_permissions")
                    ->upsert($permissions, ["role_id", "permission_id"], []);
            } catch (QueryException $e) {
                throw new Exception("Some permissions have been set before");
            }

            return $this->rst(true, 201, "role permissions added successfully");
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "Failed to add role permissions", [["message" => "role not found"]]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to add role permissions", [["message" => "database error occurres"]]);
        } catch (Exception $e) {
            return $this->rst(false, 422, "Failed to add role permissions", [["message" => $e->getMessage()]]);
        }
    }

    public function add_user_permissions(string $user_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            "permissions.*" => "required|uuid|exists:permissions,id"
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "Failed to add role permissions");

        try {
            $user = User::findOrFail($user_id);
            $permissions = collect($request->permissions)
                ->map(function ($p) use ($user) {
                    return [
                        "id" => Str::uuid()->toString(),
                        "user_id" => $user->id,
                        "permission_id" => $p,
                        "created_at" => now(),
                        "updated_at" => now(),
                    ];
                })->toArray();

            try {
                DB::table("user_has_permissions")
                    ->upsert($permissions, ["user_id", "permission_id"], []);
            } catch (QueryException $e) {
                throw new Exception("Some permissions have been set before");
            }

            return $this->rst(true, 201, "user permissions added successfully");
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "Failed to add user permissions", [["message" => "user not found"]]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to add user permissions", [["message" => "database error occurres"]]);
        } catch (Exception $e) {
            return $this->rst(false, 422, "Failed to add user permissions", [["message" => $e->getMessage()]]);
        }
    }

    public function remove_role_permissions(string $role_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            "permissions.*" => "required|uuid|exists:permissions,id"
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "Failed to delete role permissions", [["message" => "error validating the data"]]);

        try {
            $role = Role::findOrFail($role_id);
            DB::table("role_has_permissions")
                ->where("role_id", "=", $role->id)
                ->whereIn("permission_id", $request->permissions)
                ->delete();
            return $this->rst(true, 200, "role permissions deleted successfully");
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "Failed to delete role permissions", [["message" => "role not found"]]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to delete role permissions", [["message" => "database error occurres"]]);
        }
    }

    public function remove_user_permissions(string $user_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            "permissions.*" => "required|uuid|exists:permissions,id"
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "Failed to delete role permissions", [["message" => "error validating the data"]]);

        try {
            $user = User::findOrFail($user_id);
            DB::table("user_has_permissions")
                ->where("user_id", "=", $user->id)
                ->whereIn("permission_id", $request->permissions)
                ->delete();
            return $this->rst(true, 200, "user permissions deleted successfully");
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "Failed to delete user permissions", [["message" => "user not found"]]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to delete user permissions", [["message" => "database error occurres"]]);
        }
    }
}
