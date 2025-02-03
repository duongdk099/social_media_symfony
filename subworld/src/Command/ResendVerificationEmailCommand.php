<?php

namespace App\Command;

use App\Entity\User;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:resend-verification-email',
    description: 'Resend verification email after 24 hours if not verified.',
)]
class ResendVerificationEmailCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private EmailService $emailService;

    public function __construct(EntityManagerInterface $entityManager, EmailService $emailService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTime();
        $users = $this->entityManager->getRepository(User::class)->findUnverifiedAfter24Hours();

        foreach ($users as $user) {
            $newToken = bin2hex(random_bytes(32));
            $user->setVerificationToken($newToken);
            $user->setVerificationTokenExpiresAt((new \DateTime())->modify('+24 hours'));

            $this->entityManager->flush();

            $verificationUrl = "http://localhost:8000/api/auth/verify-email/$newToken";
            $this->emailService->sendVerificationEmail($user->getEmail(), $verificationUrl);

            $output->writeln("Verification email resent to: " . $user->getEmail());
        }

        return Command::SUCCESS;
    }
}
