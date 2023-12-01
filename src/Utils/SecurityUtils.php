<?php

namespace App\Utils;

use App\Constants\UserConstants;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityUtils
{
    public function __construct
    (
        private readonly Security $security,
        private readonly UserPasswordHasherInterface $userPasswordHasherInterface
    )
    {
    }

    /**
     * Method to check if the user is already authenticated
     *
     * @return bool
     */
    public function isUserAuthenticated(): bool
    {
        return $this->security->isGranted('IS_AUTHENTICATED_FULLY');
    }

    /**
     * Method to check if the user is editor
     *
     * @return bool
     */
    public function isUserEditor(): bool
    {
        return $this->security->isGranted(UserConstants::ROLE_EDITOR);
    }

    /**
     * Method to check if the user is admin
     *
     * @return bool
     */
    public function isUserAdmin(): bool
    {
        return $this->security->isGranted(UserConstants::ROLE_ADMIN);
    }

    /**
     * Method to encode the password
     *
     */

    public function encodePassword($user, $password): string
    {
        return $this->userPasswordHasherInterface->hashPassword($user, $password);
    }
}