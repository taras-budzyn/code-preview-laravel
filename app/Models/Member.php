<?php

declare(strict_types = 1);

namespace App\Models;

use App\User;
use Backpack\CRUD\app\Http\Controllers\Operations\InlineCreateOperation;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * App\Models\Member
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $surname
 * @property string|null $birthday
 * @property string $sex
 * @property string|null $phone_number
 * @property string|null $email
 * @property string|null $belt
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $medical_file
 * @property-read Collection|array<Media> $media
 * @property-read int|null $media_count
 * @property-read User $user
 * @method static Builder|Member newModelQuery()
 * @method static Builder|Member newQuery()
 * @method static Builder|Member query()
 * @method static Builder|Member whereBelt($value)
 * @method static Builder|Member whereBirthday($value)
 * @method static Builder|Member whereCreatedAt($value)
 * @method static Builder|Member whereEmail($value)
 * @method static Builder|Member whereId($value)
 * @method static Builder|Member whereName($value)
 * @method static Builder|Member whereNote($value)
 * @method static Builder|Member wherePhoneNumber($value)
 * @method static Builder|Member whereSex($value)
 * @method static Builder|Member whereSurname($value)
 * @method static Builder|Member whereUpdatedAt($value)
 * @method static Builder|Member whereUserId($value)
 * @mixin Eloquent
 * @property int $personal_code
 * @property string $residence
 * @property string $status
 * @property-read Collection $belts
 * @property-read int|null $belts_count
 * @property-read string $belt_list
 * @property-read Collection $users
 * @property-read int|null $users_count
 * @method static Builder|Member wherePersonalCode($value)
 * @method static Builder|Member whereResidence($value)
 * @method static Builder|Member whereStatus($value)
 * @property Carbon|null $deleted_at
 * @method static \Illuminate\Database\Query\Builder|Member onlyTrashed()
 * @method static Builder|Member whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Member withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Member withoutTrashed()
 * @property-read string $full_name
 * @property int|null $old_id
 * @property int|null $first_workout_place_id
 * @method static Builder|Member whereOldId($value)
 * @property string|null $gender
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Venturecraft\Revisionable\Revision> $revisionHistory
 * @property-read int|null $revision_history_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereGender($value)
 * @property Pivot $pivot
 * @property-read string $title
 */
class Member extends Model implements HasMedia
{
    use InlineCreateOperation;
    use CrudTrait;
    use HasMediaTrait;
    use SoftDeletes;
    use RevisionableTrait;

    public const GENDER_MALE = 'male';

    public const GENDER_FEMALE = 'female';

    public const STATUS = ['active', 'not_active', 'waiting_approval', 'not_approved', 'on_line'];

    /** @var string */
    protected $table = 'members';

    /** @var array<string> */
    protected $guarded = ['id'];

    protected $appends = ['full_name'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_member')->withPivot(['is_representative_himself']);
    }

    public function belts(): HasMany
    {
        return $this->hasMany(Belt::class);
    }

    public function getBeltListAttribute(): string
    {
        $belts = $this
            ->belts()
            ->get()
            ->map(static function (Belt $belt): array {
                return [
                    'name' => $belt->name,
                    'date' => $belt->date,
                    'belt_id' => $belt->id,
                ];
            })->all();

        return json_encode($belts);
    }

    public function getFullNameAttribute(): string
    {
        return trim(sprintf('%s %s', $this->name, $this->surname));
    }

    public function getTitleAttribute(): string
    {
        $title = $this->getFullNameAttribute();

        if ($this->pivot->getAttribute('is_representative_himself')) {
            $title .= sprintf(' (%s)', __('atstovauja save'));
        }

        return $title;
    }

    public function __toString(): string
    {
        return $this->name." ".$this->surname;
    }
}
