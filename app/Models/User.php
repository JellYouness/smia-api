<?php

namespace App\Models;

use App\Enums\ROLE as ROLE_ENUM;
use App\Notifications\ResetPasswordNotification;
use DB;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rules\Enum;
use Laravel\Sanctum\HasApiTokens;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class User extends BaseModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable;
    use Authorizable;
    use CanResetPassword;
    use HasApiTokens;
    use HasRelationships;
    use MustVerifyEmail;
    use Notifiable;

    public static $cacheKey = 'users';

    protected $fillable = [
        'email',
        'username',
        'password',
        'first_name',
        'last_name',
        'phone_number',
        'date_registered',
        'last_login',
        'status',
        'user_type',
        'two_factor_enabled',
        'accepted_terms',
        'email_verified',
        'email_verified_at',
        'two_factor_secret',
        'google_id',
        'facebook_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'roles',
        'permissions',
        'password',
        'remember_token',
    ];

    protected $appends = [
        'rolesNames',
        'permissionsNames',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    protected static function booted()
    {
        parent::booted();
        static::created(
            function ($user) {
                $user->givePermission('users.' . $user->id . '.read');
                $user->givePermission('users.' . $user->id . '.update');
                $user->givePermission('users.' . $user->id . '.delete');
            }
        );
        static::deleted(
            function ($user) {
                $permissions = Permission::where('name', 'like', 'users.' . $user->id . '.%')->get();
                DB::table('users_permissions')->whereIn('permission_id', $permissions->pluck('id'))->delete();
                Permission::destroy($permissions->pluck('id'));
            }
        );
    }

    public function getAuthPasswordName()
    {
        return 'password';
    }

    public function hasPermission($entityName, $action, $entityId = null)
    {
        $permissionName = $entityName . ".$action";
        if ($this->hasPermissionName($permissionName)) {
            return true;
        }
        $permissionName = $entityName . '.*';
        if ($this->hasPermissionName($permissionName)) {
            return true;
        }
        if ($entityId !== null) {
            $permissionName = $entityName . ".$entityId.$action";
            if ($this->hasPermissionName($permissionName)) {
                return true;
            }
        }

        return false;
    }

    public function givePermission($permissionName)
    {
        $permission = Permission::where('name', $permissionName)->first();
        if (! $permission) {
            $permission = Permission::create(['name' => $permissionName]);
        }
        $this->permissions()->save($permission);
    }

    public function removeAllPermissions($entityName, $entityId)
    {
        $ids = $this->permissions()->where('name', 'like', $entityName . '.' . $entityId . '.%')->pluck('permissions.id');
        $this->permissions()->detach($ids);
    }

    public function syncPermissions($permissions)
    {
        $this->permissions()->sync($permissions);
    }

    public function getRolesNamesAttribute()
    {
        $rolesNames = $this->roles->pluck('name')->all();
        sort($rolesNames);

        return $rolesNames;
    }

    public function getPermissionsNamesAttribute()
    {
        return $this->allPermissions()->pluck('name')->all();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'users_permissions');
    }

    public function rolesTableReadPermissions(string $table)
    {
        return $this->hasManyDeepFromRelations($this->roles(), (new Role)->permissions())->where('permissions.name', 'like', $table . '%read');
    }

    public function allTableReadPermissions(string $table)
    {
        return $this->permissions()->select('permissions.id', 'permissions.name')->where('permissions.name', 'like', $table . '%read')->union($this->rolesTableReadPermissions($table)->select('permissions.id', 'permissions.name', 'permissions.id as pivot_permission_id', 'users_roles.user_id as pivot_user_id'));
    }

    public function hasRole(ROLE_ENUM $role): bool
    {
        return $this->roles->contains('name', $role->value);
    }

    public function assignRole(ROLE_ENUM $role)
    {
        $roleModel = Role::where('name', $role->value)->firstOrFail();
        $this->roles()->save($roleModel);
    }

    public function syncRoles(array $roles)
    {
        $roleIds = Role::whereIn(
            'name',
            array_map(
                function (ROLE_ENUM $role) {
                    return $role->value;
                },
                $roles
            )
        )->get()->pluck('id');
        $this->roles()->sync($roleIds);
    }

    public function allPermissions()
    {
        $permissions = $this->permissions;
        foreach ($this->roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }

        return $permissions;
    }

    public function hasPermissionName($permissionName)
    {
        return $this->allPermissions()->contains('name', $permissionName);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function creator()
    {
        return $this->hasOne(Creator::class);
    }

    public function ambassador()
    {
        return $this->hasOne(Ambassador::class);
    }

    public function systemAdministrator()
    {
        return $this->hasOne(SystemAdministrator::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function rules($id = null)
    {
        $id = $id ?? request()->route('id');
        $rules = [
            'role' => [
                'required',
                new Enum(ROLE_ENUM::class),
                'exists:roles,name',
            ],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string',
        ];
        if ($id !== null) {
            $rules['email'] .= ',' . $id;
            $rules['password'] = 'nullable|string';
        }

        return $rules;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\EmailVerificationNotification);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->unread();
    }

    public function readNotifications()
    {
        return $this->notifications()->read();
    }

    public function markAllNotificationsAsRead()
    {
        return $this->unreadNotifications()->update(['read_at' => now()]);
    }

    public function getUnreadNotificationsCount()
    {
        return $this->unreadNotifications()->count();
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['role', 'joined_at', 'last_read_at'])
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function getUnreadConversationsCount(): int
    {
        return $this->conversations()
            ->whereHas('messages', function ($query) {
                $query->where('created_at', '>', \DB::raw('conversation_participants.last_read_at'));
            })
            ->count();
    }

    // Accessor methods for firstName and lastName
    public function getFirstNameAttribute()
    {
        return $this->attributes['first_name'] ?? '';
    }

    public function getLastNameAttribute()
    {
        return $this->attributes['last_name'] ?? '';
    }
}
