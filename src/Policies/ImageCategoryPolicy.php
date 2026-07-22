<?php

declare(strict_types=1);

namespace Velor\Images\Policies;

use Velor\Images\Models\ImageCategory;
use App\Models\User;
use App\Policies\Concerns\UsesRolePermissions;

class ImageCategoryPolicy
{
    use UsesRolePermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, ImageCategory::class, __FUNCTION__);
    }

    public function view(User $user, ImageCategory $imageCategory): bool
    {
        return $this->allows($user, ImageCategory::class, __FUNCTION__);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, ImageCategory::class, __FUNCTION__);
    }

    public function update(User $user, ImageCategory $imageCategory): bool
    {
        return $this->allows($user, ImageCategory::class, __FUNCTION__);
    }

    public function delete(User $user, ImageCategory $imageCategory): bool
    {
        return $this->allows($user, ImageCategory::class, __FUNCTION__);
    }

    public function restore(User $user, ImageCategory $imageCategory): bool
    {
        return $this->allows($user, ImageCategory::class, __FUNCTION__);
    }

    public function forceDelete(User $user, ImageCategory $imageCategory): bool
    {
        return $this->allows($user, ImageCategory::class, __FUNCTION__);
    }
}
