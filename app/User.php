<?php

declare(strict_types=1);

namespace App;

use App\Models\Contract;
use App\Models\Member;
use App\Notifications\ResetPasswordNotification;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Backpack\CRUD\app\Notifications\ResetPasswordNotification as BackpackResetPasswordNotification;
use Eloquent;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection|array<DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|array<Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection|array<Role> $roles
 * @property-read int|null $roles_count
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User permission($permissions)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Collection|array<\App\Models\Member> $members
 * @property-read int|null $members_count
 * @property string $surname
 * @property int|null $personal_code
 * @property string|null $residence
 * @property string|null $phone_number
 * @property string $status
 * @method static Builder|User wherePersonalCode($value)
 * @method static Builder|User wherePhoneNumber($value)
 * @method static Builder|User whereResidence($value)
 * @method static Builder|User whereStatus($value)
 * @method static Builder|User whereSurname($value)
 * @property Carbon|null $deleted_at
 * @method static QueryBuilder|User onlyTrashed()
 * @method static Builder|User whereDeletedAt($value)
 * @method static QueryBuilder|User withTrashed()
 * @method static QueryBuilder|User withoutTrashed()
 * @property-read string $full_name
 * @property int|null $old_id
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Venturecraft\Revisionable\Revision> $revisionHistory
 * @property-read int|null $revision_history_count
 * @property Pivot $pivot
 * @method static Builder|User whereOldId($value)
 * @property-read string $title
 * @property string|null $birthday
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereBirthday($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\Contract> $contracts
 * @property-read int|null $contracts_count
 * @property int $e_invoice
 * @property string|null $e_invoice_value
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEInvoiceValue($value)
 */
class User extends Authenticatable
{
    use CrudTrait;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;
    use Notifiable;
    use RevisionableTrait;

    public const STATUS = ['active', 'not_active', 'on_line',];
    public const STATUS_NOT_ACTIVE = 'not_active';

    /** @var array<string> */
    protected $hidden = ['password', 'remember_token',];

    /** @var array<string> */
    protected $fillable = [
        'name', 'surname', 'personal_code', 'residence',
        'phone_number', 'status', 'email', 'password', 'e_invoice', 'e_invoice_value',
        'birthday',
    ];

    /** @var array<string> */
    protected $appends = ['full_name'];

    /** @var array<string> */
    protected $revisionFormattedFields = [
        'password' => 'string:**********',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'user_member')->withPivot(['is_representative_himself']);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(sprintf('%s %s', $this->name, $this->surname));
    }

    public function notify(Object $instance): void
    {
        if ($instance instanceof BackpackResetPasswordNotification) {
            $instance = new ResetPasswordNotification($instance->token);
        }

        app(Dispatcher::class)->send($this, $instance);
    }

    public function getTitleAttribute(): string
    {
        $title = $this->getFullNameAttribute();

        if ($this->pivot->getAttribute('is_representative_himself')) {
            $title .= sprintf(' (%s)', __('save'));
        }

        return $title;
    }

    /**
     * Checks if the current logged-in user is able to view the videos.
     *
     * @return boolean
     */
    public function canViewVideos(): bool
    {
        $video_allowed_groups = ['Karatė', 'karate nuotolinės', 'Kata'];

        $contracts = $this->contracts()->select('contracts.id', 'contracts.status')->whereIn('contracts.status', ['signed', 'approved'])->get();

        $contracts = $contracts->filter(function ($contract) use ($video_allowed_groups) {

            $contract_groups = $contract->group->map(function ($group) {
                return $group->groupSetting->name;
            })->toArray();

            foreach ($contract_groups as $cg) {
                if (in_array($cg, $video_allowed_groups)) {
                    return true;
                }
            }
        });

        if (!$contracts->isEmpty() || $this->can('view videos')) {
            return true;
        }
        return false;
    }

    public function __toString(): string
    {
        return $this->name . " " . $this->surname;
    }

    /**
     * Check if user has contracts signed, approved or sent for sign
     *
     * @return boolean
     */
    public function hasWebAccess(): bool
    {
        $user_contracts = $this->contracts->whereIn('status', [Contract::STATUS_SIGNED, Contract::STATUS_APPROVED, Contract::STATUS_SUBMITTED]);
        if ($user_contracts) {
            return true;
        }
        return false;
    }
}
