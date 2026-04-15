<?php

namespace App\Command;

use App\Entity\Security\Factory\MemberFactory;
use App\Repository\MemberRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:first-admin',
    description: 'The command that creates the first admin user when there are no users in the table',
)]
class FirstAdminCommand extends Command
{
    public function __construct(
        private readonly MemberFactory $memberFactory,
        private readonly MemberRepository $memberRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email of the user')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the user')
            ->addArgument('password', InputArgument::REQUIRED, 'Password of the user')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var string $email */
        $email = $input->getArgument('email');
        /** @var string $name */
        $name = $input->getArgument('name');
        /** @var string $password */
        $password = $input->getArgument('password');

        $members = $this->memberRepository->findAll();

        if (count($members) > 0) {
            $io->error('Users already exists in the database. Please connect with an admin to update your password.');
            return Command::FAILURE;
        }

        $member = $this->memberFactory->createAdmin($name, $email, $password);

        $this->memberRepository->save($member, true);

        $io->success('The admin user has been created.');

        return Command::SUCCESS;
    }
}
