<?php

namespace App\Features\SyncUserAttributes\Repository\Eloquent\Model;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Features\SyncUserAttributes\Events\UserAttributesChanged;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


/**
 * @property int $id
 * @property string $email
 * @property string|mixed $first_name
 * @property string|mixed $last_name
 * @property string $time_zone
 * @property string|mixed $userName
 * @method static Builder|User whereEmail($value)
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'time_zone',
        'email',
        'password',
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

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function userName(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, mixed $attributes) {
                if (is_array($attributes)) {
                    return ($attributes['first_name'] ?? '')
                        . ' ' .
                        ($attributes['last_name'] ?? '');
                }

                return '';
            }
        );
    }

    protected static function boot(): void
    {
        parent::boot();

        static::updating(function (User $user) {
            // fire the change event just if the user fields changed
            if ($user->isDirty()) {
                UserAttributesChanged::dispatch($user);
            }
        });
    }
}
