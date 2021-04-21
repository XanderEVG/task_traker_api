<?php

namespace App\DataFixtures;

use App\Common\Auth\UserRoles;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $encoder;

    /**
     * AppFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $admin = new User();
        $admin->setUsername("admin");
        $admin->setPassword($this->encoder->encodePassword($admin, '1'));
        $admin->addRole(UserRoles::ROLE_ADMIN);
        $manager->persist($admin);
        $manager->flush();

        // По хорошему надо разбить этот метод на разные классы с передачей зависимостей(созданного юзера)
        $task = new Task();
        $task->setCaption("Задача 1");
        $task->setDescription("Прикрутить эластик");
        $task->setDate(new \DateTime());
        $task->setPerformer($admin);
        $manager->persist($task);
        $manager->flush();
    }
}
