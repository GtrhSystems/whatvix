<?php

namespace App\Models;

use App\Models\Platform;
use App\Models\Template;
use App\Traits\HasFilter;
use App\Models\QuickReply;
use App\Traits\HasWorkspace;
use App\Traits\HasActivityLog;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Modules\QAReply\App\Models\AutoResponse;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, HasFilter, HasWorkspace, HasActivityLog;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role',
        'name',
        'avatar',
        'email',
        'password',
        'authKey',
        'access_token',
        'current_workspace_id',
        'plan_id',
        'plan_data',
        'credits',
        'email_verified_at',
        'status',
        'provider',
        'provider_id',
        'will_expire'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'meta' => 'array',
        'plan_data' => 'array',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function getPermissionGroups()
    {
        $permission_groups = DB::table('permissions')
            ->select('group_name as name')
            ->groupBy('group_name')
            ->get();
        return $permission_groups;
    }

    public static function getPermissionGroup()
    {
        return DB::table('permissions')
            ->select('group_name')
            ->groupBy('group_name')
            ->get();
    }
    public static function getPermissionsByGroupName($group_name)
    {
        $permissions = DB::table('permissions')
            ->select('name', 'id')
            ->where('group_name', $group_name)
            ->get();
        return $permissions;
    }

    public static function roleHasPermissions($role, $permissions)
    {
        $hasPermission = true;
        foreach ($permissions as $permission) {
            if (!$role->hasPermissionTo($permission->name)) {
                $hasPermission = false;
                return $hasPermission;
            }
        }
        return $hasPermission;
    }

    public function getAvatarAttribute($value)
    {
        return $value ? asset($value) : "https://ui-avatars.com/api/?name={$this->name}";
    }

    public function plan()
    {
        return $this->belongsTo('App\Models\Plan', 'plan_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // sender messages
    public function platforms(): HasMany
    {
        return $this->hasMany(Platform::class, 'owner_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class, 'owner_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'owner_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'owner_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'owner_id');
    }
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'owner_id');
    }

    public function autoReplies(): HasMany
    {
        return $this->hasMany(AutoReply::class, 'owner_id');
    }

    public function quickReplies(): HasMany
    {
        return $this->hasMany(QuickReply::class, 'owner_id');
    }


  

    public function aiTemplates(): BelongsToMany
    {
        return $this->belongsToMany(AiTemplate::class);
    }
    public function aiGeneratedContents(): HasMany
    {
        return $this->hasMany(AiGenerate::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function aiCredentials()
    {
        return $this->hasMany(AiTrainingCredential::class);
    }

    public function aiTrainings()
    {
        return $this->hasMany(AiTraining::class);
    }



    public function autoResponses(): HasMany
    {
        return $this->hasMany(AutoResponse::class, 'owner_id');
    }


    public function getDashboardRoute(): string // route name
    {
        return match ($this->role) {
            'admin' => 'admin.dashboard',
            'user' => 'user.dashboard',
            'team_member' => 'user.dashboard',
            default => 'user.dashboard',
        };
    }

    public function getCurrentWorkspaceOwnerId()
    {
        return activeWorkspaceOwnerId();
    }

    public function isSuperAdmin(): bool
    {
        return $this->id === 1 && $this->role === 'admin';
    }

    public function getReferUser(array $fields = ['*']): ?self
    {
        return self::where('email', data_get($this->meta, 'invited_by_email'))->first($fields);
    }
}
