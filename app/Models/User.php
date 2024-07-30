<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'added_by',
        'phone',
		'user_status', 
		'status'
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
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
	    'role' => 'string',
    ];
	protected $appends = ['profile_image_url'];
	
	
	public function addedByAdmin()
    {
        return $this->belongsTo(Agent::class, 'added_by_admin_id');
    }
	
	

    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image ? Storage::url($this->profile_image) : null;
    }
	
	public static function getNameByAgentId($agentId)
    {
        return self::where('id', $agentId)->value('name') ?? 'User not found';
    }
	
	public static function getAgentCountByAdmin($adminId)
    {
        return self::where('role', 'agent')->where('added_by')->count();
    }
}
