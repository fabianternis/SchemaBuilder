<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\{Fillable, Hidden};
use Illuminate\Database\Eloquent\{Concerns\HasUlids, Factories\HasFactory};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['username', 'email', 'password', 'github_id', 'github_token', 'github_refresh_token', 'hackclub_id', 'hackclub_token'])]
#[Hidden(['password', 'remember_token', 'github_token', 'github_refresh_token', 'hackclub_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUlids, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function projects()
    {
        // return $this->hasMany(Project::class, 'user_id');
        return $this->hasMany(Project::class, 'owner_id');
    }
}
