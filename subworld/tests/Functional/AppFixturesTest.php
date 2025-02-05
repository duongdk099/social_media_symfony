<?php

namespace App\Tests\Functional;

use App\DataFixtures\AppFixtures;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Subworld;
use App\Entity\Vote;
use App\Entity\Media;
use App\Entity\Notification;
use App\Entity\Message;
use App\Entity\Report;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixturesTest extends KernelTestCase
{
    private $entityManager;

    /**
     * Loads the AppFixtures into the test database before each test.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        // Retrieve the EntityManager from the container
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        // Build the fixture loader
        $loader = new Loader();
        
        // IMPORTANT: Pass the UserPasswordHasherInterface from the container
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $loader->addFixture(new AppFixtures($passwordHasher));

        // Use ORMExecutor and ORMPurger to load the fixtures into a fresh test database
        $executor = new ORMExecutor($this->entityManager, new ORMPurger());
        $executor->execute($loader->getFixtures());
    }

    public function testFixtureLoadsUsers(): void
    {
        // We expect 10 users as per the fixture
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $this->assertCount(10, $users, 'There should be exactly 10 users in the database.');

        // Check that exactly one user has ROLE_ADMIN
        $adminUsers = array_filter($users, fn(User $u) => in_array('ROLE_ADMIN', $u->getRoles()));
        $this->assertCount(1, $adminUsers, 'Exactly 1 user should have ROLE_ADMIN.');
    }

    public function testFixtureLoadsSubworlds(): void
    {
        // We expect 5 subworlds as per the fixture
        $subworlds = $this->entityManager->getRepository(Subworld::class)->findAll();
        $this->assertCount(5, $subworlds, 'There should be exactly 5 subworlds in the database.');

        // Optional: check that each subworld has an owner
        foreach ($subworlds as $subworld) {
            $this->assertNotNull($subworld->getOwner(), 'Each subworld should have an owner.');
        }
    }

    public function testFixtureLoadsPosts(): void
    {
        // Check that we have some posts (5 to 10 per subworld = 25 to 50 total)
        $posts = $this->entityManager->getRepository(Post::class)->findAll();
        $this->assertGreaterThanOrEqual(25, count($posts), 'There should be at least 25 posts (5 subworlds * 5 posts each).');
    }

    public function testFixtureLoadsComments(): void
    {
        // We have 3 to 6 comments per post
        $comments = $this->entityManager->getRepository(Comment::class)->findAll();
        $this->assertNotEmpty($comments, 'There should be some comments in the database.');
    }

    public function testFixtureLoadsVotes(): void
    {
        // Votes are assigned to both posts and comments
        $votes = $this->entityManager->getRepository(Vote::class)->findAll();
        $this->assertNotEmpty($votes, 'There should be some votes in the database.');
    }

    public function testFixtureLoadsMedia(): void
    {
        // 1 to 3 media items per post
        $media = $this->entityManager->getRepository(Media::class)->findAll();
        $this->assertNotEmpty($media, 'There should be media items in the database.');
    }

    public function testFixtureLoadsNotifications(): void
    {
        // 2 to 5 notifications per user
        $notifications = $this->entityManager->getRepository(Notification::class)->findAll();
        $this->assertNotEmpty($notifications, 'There should be notifications in the database.');
    }

    public function testFixtureLoadsMessages(): void
    {
        // 20 messages total
        $messages = $this->entityManager->getRepository(Message::class)->findAll();
        $this->assertCount(20, $messages, 'There should be exactly 20 messages in the database.');
    }

    public function testFixtureLoadsReports(): void
    {
        // 1 to 3 reports for each post or comment
        $reports = $this->entityManager->getRepository(Report::class)->findAll();
        $this->assertNotEmpty($reports, 'There should be some reports in the database.');
    }

    /**
     * After each test, optionally purge the database
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // If needed, close the EntityManager or purge the DB
        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }
}
