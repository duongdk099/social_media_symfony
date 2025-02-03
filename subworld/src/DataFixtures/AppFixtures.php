<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Role;
use App\Entity\Subworld;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Vote;
use App\Entity\Media;
use App\Entity\Notification;
use App\Entity\Message;
use App\Entity\Report;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $roleAdmin = (new Role())->setName('ROLE_ADMIN');
        $roleUser = (new Role())->setName('ROLE_USER');
        $manager->persist($roleAdmin);
        $manager->persist($roleUser);
        $manager->flush();

        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $user = (new User())
                ->setEmail("user$i@example.com")
                ->setUsername("user$i");

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);
            $user->addRoleEntity($i === 1 ? $roleAdmin : $roleUser);

            $manager->persist($user);
            $users[] = $user;
        }

        $subworlds = [];
        for ($i = 1; $i <= 5; $i++) {
            $subworld = (new Subworld())
                ->setName($faker->word)
                ->setDescription($faker->sentence)
                ->setCreatedAt($faker->dateTime)
                ->setOwner($faker->randomElement($users));

            foreach ($faker->randomElements($users, rand(3, 8)) as $member) {
                $subworld->addMember($member);
            }

            $manager->persist($subworld);
            $subworlds[] = $subworld;
        }

        $posts = [];
        foreach ($subworlds as $subworld) {
            for ($i = 1; $i <= rand(5, 10); $i++) {
                $post = (new Post())
                    ->setTitle($faker->sentence)
                    ->setContent($faker->paragraph)
                    ->setCreatedAt($faker->dateTime)
                    ->setUpdatedAt($faker->dateTime)
                    ->setUser($faker->randomElement($users))
                    ->setSubworld($subworld);

                $manager->persist($post);
                $posts[] = $post;
            }
        }

        $comments = [];
        foreach ($posts as $post) {
            for ($i = 1; $i <= rand(3, 6); $i++) {
                $comment = (new Comment())
                    ->setContent($faker->paragraph)
                    ->setCreatedAt($faker->dateTime)
                    ->setUser($faker->randomElement($users))
                    ->setPost($post);

                $manager->persist($comment);
                $comments[] = $comment;
            }
        }

        foreach (array_merge($posts, $comments) as $votable) {
            for ($i = 1; $i <= rand(5, 15); $i++) {
                $vote = (new Vote())
                    ->setValue($faker->randomElement([-1, 1]))
                    ->setUser($faker->randomElement($users))
                    ->setPost($votable instanceof Post ? $votable : null)
                    ->setComment($votable instanceof Comment ? $votable : null);

                $manager->persist($vote);
            }
        }

        foreach ($posts as $post) {
            for ($i = 1; $i <= rand(1, 3); $i++) {
                $media = (new Media())
                    ->setUrl($faker->imageUrl)
                    ->setType($faker->randomElement(['image', 'video']))
                    ->setPost($post);

                $manager->persist($media);
            }
        }

        foreach ($users as $user) {
            for ($i = 1; $i <= rand(2, 5); $i++) {
                $notification = (new Notification())
                    ->setMessage($faker->sentence)
                    ->setCreatedAt($faker->dateTime)
                    ->setIsRead($faker->boolean)
                    ->setUser($user);

                $manager->persist($notification);
            }
        }

        for ($i = 1; $i <= 20; $i++) {
            $sender = $faker->randomElement($users);
            do {
                $receiver = $faker->randomElement($users);
            } while ($receiver === $sender);

            $message = (new Message())
                ->setContent($faker->sentence)
                ->setCreatedAt($faker->dateTime)
                ->setSender($sender)
                ->setReceiver($receiver);

            $manager->persist($message);
        }

        foreach (array_merge($posts, $comments) as $reportable) {
            for ($i = 1; $i <= rand(1, 3); $i++) {
                $report = (new Report())
                    ->setReason($faker->sentence)
                    ->setCreatedAt($faker->dateTime)
                    ->setUser($faker->randomElement($users))
                    ->setPost($reportable instanceof Post ? $reportable : null)
                    ->setComment($reportable instanceof Comment ? $reportable : null);

                $manager->persist($report);
            }
        }

        $manager->flush();
    }
}
