<?php

declare(strict_types=1);

namespace Velor\Images\Policies;

use App\Models\Image;
use App\Models\User;
use App\Policies\Concerns\UsesRolePermissions;
use App\Services\Authorization\Contracts\PermissionServiceInterface;
use Velor\Images\Repositories\Contracts\ImageManagementRepositoryInterface;

class ImagePolicy
{
    use UsesRolePermissions;

    public function __construct(
        PermissionServiceInterface $permissions,
        protected ImageManagementRepositoryInterface $imageManagementRepository,
    ) {
        $this->permissions = $permissions;
    }

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

    public function delete(User $user, Image $image): bool
    {
        return $this->imageManagementRepository->isUnused($image)
            && $this->allows($user, Image::class, __FUNCTION__);
    }

    public function restore(User $user, Image $image): bool
    {
        return $this->allows($user, Image::class, __FUNCTION__);
    }

    public function forceDelete(User $user, Image $image): bool
    {
        return $this->imageManagementRepository->isUnused($image)
            && $this->allows($user, Image::class, __FUNCTION__);
    }
}
