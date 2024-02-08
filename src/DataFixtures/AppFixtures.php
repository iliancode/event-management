<?php

namespace App\DataFixtures;

use App\Constants\UserConstants;
use App\Entity\Event;
use App\Entity\EventParticipation;
use App\Entity\Type;
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

    private function makeUser(string $email, string $password, array $roles, string $username, string $firstname, string $lastname, string $biography): User
    {
        return (new User())
            ->setEmail($email)
            ->setPassword($this->userPasswordHasherInterface->hashPassword(new User(), $password))
            ->setRoles($roles)
            ->setUsername($username)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setBiography($biography);
    }

    private function makeType(string $name, int $max): Type
    {
        return (new Type())
            ->setLabel($name)
            ->setMaxParticipants($max);
    }

    private function makeEvent(User $user, Type $type, string $title, string $description, \DateTime $date, string $city, string $zipcode, string $address, string $location): Event
    {
        return (new Event())
            ->setOrganizer($user)
            ->setType($type)
            ->setTitle($title)
            ->setDescription($description)
            ->setDate($date)
            ->setCity($city)
            ->setZipcode($zipcode)
            ->setAddress($address)
            ->setLocation($location);
    }

    private function makeEventParticipation(User $user, Event $event, int $banned) : EventParticipation
    {
        return (new EventParticipation())
            ->setUser($user)
            ->setEvent($event)
            ->setBanned($banned);
    }

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        // make admin
        $user = $this->makeUser('admin@esgi.fr', 'admin', [UserConstants::ROLE_ADMIN, UserConstants::ROLE_EDITOR, UserConstants::ROLE_USER], 'admin', 'Admin', 'ADIMN', 'admin');
        $manager->persist($user);
        $manager->flush();

        // make 5 editor
        for ($i = 0; $i < 5; $i++) {
            $user = $this->makeUser('editor' . $i . '@esgi.fr', 'editor' . $i, [UserConstants::ROLE_EDITOR, UserConstants::ROLE_USER], 'editor' . $i, 'Editor' . $i, 'EDITOR' . $i, 'editor' . $i);
            $manager->persist($user);
            $manager->flush();
        }

        // make 20 user
        for ($i = 0; $i < 20; $i++) {
            $user = $this->makeUser('user' . $i . '@esgi.fr', 'user' . $i, [UserConstants::ROLE_USER], 'user' . $i, 'User' . $i, 'USER' . $i, 'user' . $i);
            $manager->persist($user);
            $manager->flush();
        }

        $types = [
            ["Football à 11", 11],
            ["Football à 7", 7],
            ["Football à 5", 5],
            ["Futsal", 5],
            ["Tournoi classique", 44],
            ["Tournoi interentreprise", 44]
        ];

        // make each type of array $types
        for ($i = 0; $i < count($types); $i++) {
            $type = $this->makeType($types[$i][0], $types[$i][1]);
            $manager->persist($type);
            $manager->flush();
        }

        $users = $manager->getRepository(User::class)->findAll();
        $types = $manager->getRepository(Type::class)->findAll();

        // make 100 events
        for ($i = 0; $i < 100; $i++) {
            $event = $this->makeEvent($users[random_int(0, count($users) - 1)], $types[random_int(0, count($types) - 1)], 'event' . $i, 'event' . $i, new \DateTime(), 'city' . $i, 'zipcode' . $i, 'address' . $i, 'location' . $i);
            $manager->persist($event);
            $manager->flush();
        }

        $events = $manager->getRepository(Event::class)->findAll();

        // make 150 eventParticipation
        for ($i = 0; $i < 150; $i++) {
            $eventParticipation = $this->makeEventParticipation($users[random_int(0, count($users) - 1)], $events[random_int(0, count($events) - 1)], random_int(0, 1));
            $manager->persist($eventParticipation);
            $manager->flush();
        }
    }
}
