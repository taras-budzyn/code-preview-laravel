<?php

declare(strict_types = 1);

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * App\Models\ContractTemplate
 *
 * @property int $id
 * @property string $name
 * @property int $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ContractTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ContractTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ContractTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ContractTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ContractTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ContractTemplate whereStatus($value)
 * @mixin \Eloquent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ContractTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ContractTemplate whereUpdatedAt($value)
 * @property string|null $public_name
 * @property string|null $type
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Venturecraft\Revisionable\Revision> $revisionHistory
 * @property-read int|null $revision_history_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ContractTemplate wherePublicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ContractTemplate whereType($value)
 */
class ContractTemplate extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    public const STATUS = ['active', 'not_active'];

    public const TYPE = ['monthly', 'season', 'individual'];
    public const TYPE_MONTHLY = 'monthly';
    public const TYPE_SEASONAL = 'season';
    public const TYPE_INDIVIDUAL = 'individual';

    /** @var array<string> */
    protected $guarded = ['id'];

    public function __toString(): string
    {
        return $this->name;
    }
}
