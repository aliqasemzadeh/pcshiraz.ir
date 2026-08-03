<?php

namespace App\Enums;

enum OrganizationUserRoleEnum: string
{
    case Approver = 'approver';

    public function label(): string
    {
        return __('enums.organization_user_role.'.$this->value);
    }
}
