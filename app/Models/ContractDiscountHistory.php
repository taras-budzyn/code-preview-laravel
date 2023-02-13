<?php

declare(strict_types = 1);

namespace App\models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\ContractDiscountHistory
 *
 * @property int $id
 *
 * @property int $contract_id
 * @property int $discount_id
 * @property Carbon start_at
 * @property Carbon|null end_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 
 *
 */
class ContractDiscountHistory extends Model
{
    use CrudTrait;

    protected $guarded = ['id'];

    protected $table = 'contract_discount_history';

    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
