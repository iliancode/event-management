<?php

namespace App\DataFixtures;

use App\Constants\UserConstants;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct
    (
        private readonly UserPasswordHasherInterface $userPasswordHasherInterface
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = (new User())
            ->setEmail('admin@esievent.fr')
            ->setPassword($this->userPasswordHasherInterface->hashPassword(new User(), 'admin'))
            ->setRoles([UserConstants::ROLE_ADMIN])
            ->setUsername('admin')
            ->setFirstname('Admin')
            ->setLastname('ADIMN')
            ->setBiography('admin');
        $manager->persist($user);
        $manager->flush();
    }
}
