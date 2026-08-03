<?php

namespace App\Models;

use App\Enums\OrganizationUserRoleEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'code',
    'internal_note',
    'is_active',
])]
class Organization extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function generateCode(): string
    {
        do {
            $code = Str::upper(Str::random(16));
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'is_active'])
            ->withTimestamps();
    }

    public function approvers(): BelongsToMany
    {
        return $this->users()
            ->wherePivot('role', OrganizationUserRoleEnum::Approver->value)
            ->wherePivot('is_active', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function installmentPlans(): HasMany
    {
        return $this->hasMany(InstallmentPlan::class);
    }

    public function assignedInstallmentPlans(): BelongsToMany
    {
        return $this->belongsToMany(InstallmentPlan::class, 'organization_installment_plan')
            ->withPivot(['is_default', 'is_active', 'priority'])
            ->withTimestamps();
    }

    public function isApprover(User $user): bool
    {
        return $this->approvers()->where('users.id', $user->id)->exists();
    }
}
