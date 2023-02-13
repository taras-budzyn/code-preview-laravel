<?php

declare(strict_types = 1);

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;


/**
 * App\Models\ContractGroupHistory
 *
 * @property int $id

 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $left_date
 *
 */
class ContractGroupHistory extends Model
{
    use CrudTrait;

    protected $guarded = ['id'];

    protected $table = 'contract_group_history';

    protected $dates = [
        'left_date',
        'created_at',
        'updated_at',
    ];

    protected static function boot() {
        parent::boot();

        static::addGlobalScope('withGroup', function ($query) {
            return $query->with('group');
        });
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function scopeCurrent($query) {
        return $query->whereNull('contract_group_history.left_date');
    }

    public function scopeForContract($query, $contract) {
        return $query->where('contract_group_history.contract_id', $contract->id);
    }

    public function scopeContractsOnDate($query, $date) {
        return $query->where(function($q) use ($date) {
             $q->whereNull('contract_group_history.left_date')->where('contract_group_history.created_at', '<=', $date)->orWhere(function($query) use ($date) {
                 $query->where('contract_group_history.left_date', '>', $date)->where('contract_group_history.created_at', '<=', $date);
            });
        });
    }
}
