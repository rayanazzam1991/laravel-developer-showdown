<?php

namespace App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Model;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Features\SyncUserAttributes\Application\Events\UserAttributesChanged;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string $time_zone
 * @property string $userName
 *
 * @method static Builder|User whereEmail($value)
 * @method static UserFactory factory($count = null, $state = [])
 *
 * @property Carbon|null $email_verified_at
 * @property mixed $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read string $user_name
 *
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereTimeZone($value)
 * @method static Builder|User whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
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
            'first_name' => 'string',
            'last_name' => 'string',
            'time_zone' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * @return Attribute<string,string>
     */
    public function userName(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, mixed $attributes): string {
                if (is_array($attributes)) {
                    return ($attributes['first_name'] ?? '')
                        .' '.
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
