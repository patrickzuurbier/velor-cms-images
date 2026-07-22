<?php

declare(strict_types=1);

namespace Velor\Images\Policies;

use Velor\Images\Models\Image;
use App\Models\User;
use App\Policies\Concerns\UsesRolePermissions;

class ImagePolicy
{
    use UsesRolePermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, Image::class, __FUNCTION__);
    }

    public function view(User $user, Image $image): bool
    {
        return $this->allows($user, Image::class, __FUNCTION__);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, Image::class, __FUNCTION__);
    }

    public function update(User $user, Image $image): bool
    {
        return $this->allows($user, Image::class, __FUNCTION__);
    }

    public function reorder(User $user): bool
    {
        return $this->allows($user, Image::class, __FUNCTION__);
    }

    public function delete(User $user, Image $image): bool
    {
        return $this->allows($user, Image::class, __FUNCTION__);
    }

    public function restore(User $user, Image $image): bool
    {
        return $this->allows($user, Image::class, __FUNCTION__);
    }

    public function forceDelete(User $user, Image $image): bool
    {
        return $this->allows($user, Image::class, __FUNCTION__);
    }
}
