<?php

declare(strict_types=1);

namespace App\Models;

use App\User;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Support\Carbon;

/**
 * App\Models\Contract
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $representative_id
 * @property int $user_id
 * @property int $member_subscription_id
 * @property int $group_id
 * @property string|null $date_from
 * @property string|null $date_until
 * @property int|null $nvs_discount
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $serie
 * @property int $contract_template_id
 * @property int|null $member_id
 * @property string $status
 * @property int|null $agreement_photo
 * @property int|null $agreement_personal_data
 * @property int|null $agreement_events
 * @property int|null $agreement_personal_data_third_company
 * @property-read \App\Models\ContractTemplate $contractTemplate
 * @property-read \App\Models\Group $group
 * @property-read \App\Models\Member|null $member
 * @property-read \App\Models\MemberSubscription $memberSubscription
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\App\Models\PauseContract> $pauseContracts
 * @property-read int|null $pause_contracts_count
 * @property-read \App\User $representative
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Venturecraft\Revisionable\Revision> $revisionHistory
 * @property-read int|null $revision_history_count
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Contract onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract valid()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereAgreementEvents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereAgreementPersonalData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereAgreementPersonalDataThirdCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereAgreementPhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereContractTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereDateFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereDateUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereMemberSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereNvsDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereRepresentativeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereSerie($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Contract withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Contract withoutTrashed()
 * @mixin \Eloquent
 * @property string|null $number
 * @property int $specify_allow
 * @property string|null $specify_date
 * @property int|null $specify_user
 * @property int|null $old_id
 * @property int|null $member_class
 * @property int $e_invoice
 * @property string|null $e_invoice_value
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereEInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereEInvoiceValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereMemberClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereOldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereSpecifyAllow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereSpecifyDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contract whereSpecifyUser($value)
 */
class Contract extends Model
{
    use CrudTrait;
    use SoftDeletes;
    use RevisionableTrait;

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SUBMITTED,
        self::STATUS_SIGNED,
        self::STATUS_NOT_APPROVED,
        self::STATUS_APPROVED,
        self::STATUS_TERMINATED,
    ];
    public const CLASSES = [
        1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12,
    ];
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_SIGNED = 'signed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_TERMINATED = 'terminated';
    public const STATUS_NOT_APPROVED = 'not approved';

    /** @var array<string> */
    protected $guarded = ['id'];

    /** @var array<string> */
    protected $fillable = [
        'representative_id',
        'group_id',
        'number',
        'user_id',
        'member_subscription_id',
        'contract_template_id',
        'date_from',
        'date_until',
        'nvs_discount',
        'status',
        'agreement_photo',
        'agreement_personal_data',
        'agreement_events',
        'agreement_personal_data_third_company',
        'specify_allow',
        'specify_date',
        'specify_user',
        'member_class',
        'e_invoice',
        'e_invoice_value',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'id_with_name', 'current_group_in_history'
    ];

    public function representative(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    //TODO: refactor - rename
    public function group(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'contract_group_history')
            ->where(function ($q) {
                $q->whereNull('left_date')->orWhere('left_date', '>', Carbon::today());
            });
    }

    public function memberSubscription(): BelongsTo
    {
        return $this->belongsTo(MemberSubscription::class);
    }

    public function availableDiscounts(): BelongsTo
    {
        return $this->BelongsTo(Discount::class);
    }

    public function discounts(): hasMany
    {
        return $this->hasMany(ContractDiscountHistory::class);
    }

    public function contractTemplate(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class);
    }

    public function pauseContracts(): HasMany
    {
        return $this->hasMany(PauseContract::class);
    }

    public function compensations(): HasMany
    {
        return $this->hasMany(ContractCompensate::class);
    }

    public function cancelations()
    {
        return $this->hasMany('App\Models\CancelContract');
    }

    public function pauses()
    {
        return $this->hasMany('App\Models\ContractPause');
    }

    public function nvsContract()
    {
        return $this->hasOne('App\Models\Nvm', 'member_contract_id')
            ->where('nvms.status', 'approved_budora')
            ->whereNull('nvm_valid_until_date');
    }

    public function groupHistory()
    {
        return $this->hasMany('App\Models\ContractGroupHistory');
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\Invoice');
    }

    public function getPauseContractsByType(int $type): Collection
    {
        return $this->pauseContracts->filter(
            static function ($value, $key) use ($type) {
                return $value->type === $type;
            }
        )->sortByDesc(static function ($value, $key) {
            return $value->id;
        });
    }

    public function getPauseContractsFromMultipleTypes(array $types): Collection
    {
        return $this->pauseContracts->filter(function ($value, $key) use ($types) {
            return in_array($value->type, $types);
        })->sortByDesc(function ($value, $key) {
            return $value->id;
        });
    }

    public function canToPause(): bool
    {
        //todo: implement full functionality
        return null === $this->pauseContracts()->get()->all();
    }

    public function canSendSubmit(): bool
    {
        return 'draft' === $this->status;
    }

    public function canSigned(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_SIGNED], true);
    }



    public function isPaused()
    {
        if (!is_null($this->pauses)) {
            if (!is_null($this->pauses->where('pause_start', '<=', \Carbon\Carbon::now())
                ->where('pause_end', '>=', \Carbon\Carbon::now())
                ->where('status', 'approved')->first())) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function setGroupIdAttribute($value)
    {
        if (!empty($value)) {
            $this->groupHistory()->save(new ContractGroupHistory([
                'group_id' => $value,
                'left_date' => null
            ]));
        }
    }

    public function getContractGroupJoinedDateAttribute()
    {
        return $this->groupHistory ?? $this->groupHistory->whereNull('left_date')->first()->created_at;
    }

    public function getCurrentGroupInHistoryAttribute()
    {
        return !empty($this->groupHistory) ? $this->groupHistory->whereNull('left_date')->first() : null;
    }
}
