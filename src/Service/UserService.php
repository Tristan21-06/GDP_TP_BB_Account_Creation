<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    )
    {
    }

    public function isPasswordSecured(string $password): bool
    {
        return preg_match("/^(?=.*\d)(?=.*[A-Z])(?=.*[\W_]).{8,24}$/", $password);
    }

    public function hashPassword(User $user): User
    {
        $plaintextPassword = $user->getPassword();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);

        return $user;
    }
}