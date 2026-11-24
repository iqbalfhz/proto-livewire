<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'email_verified_at', 'workos_id', 'avatar'])]
#[Hidden(['workos_id', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Keeps is_admin in memory for new users, since strict mode throws on missing attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_admin' => false,
    ];

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Authentication lives in WorkOS, so there is no password column to read.
     */
    public function getAuthPassword(): ?string
    {
        return null;
    }

    /**
     * Sign-in is closed by default: only admins and listed emails get through.
     */
    public function isAllowedToSignIn(): bool
    {
        $allowedEmails = Str::of((string) config('auth.allowed_emails'))
            ->explode(',')
            ->map(fn (string $email) => Str::lower(trim($email)))
            ->filter();

        return $this->isAdmin() || $allowedEmails->contains(Str::lower($this->email));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'email_verified_at' => 'datetime',
        ];
    }
}
