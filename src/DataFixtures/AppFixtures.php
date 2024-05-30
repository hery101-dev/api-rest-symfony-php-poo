<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        $roles = ['ROLE_CANDIDATE', 'ROLE_RECRUITER'];
        $types = ['candidate', 'recruiter'];

        for ($i = 0; $i < 2; $i++) {
            $user = new User();
            $user->setEmail($faker->email);

            $randomRole = $roles[array_rand($roles)];
            $randomType = $types[array_rand($types)];

            $user->setRoles([$randomRole]);
            $user->setUserType($randomType);
            $user->setIsVerified(true);

            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                   '1234'
                )
            );

            $manager->persist($user);
        }

        $manager->flush();
    }
}
