<?php declare(strict_types=1);
/**
 * Created 2021-11-25
 * Author Dmitry Kushneriov
 */

namespace App\Command;


use App\Command\Validators\EmailValidator;
use App\Command\Validators\NameValidator;
use App\Command\Validators\PasswordValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\AdminUserManager;

class CreateAdminCommand extends AbstractCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-admin';

    public function __construct(
        private AdminUserManager $userManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName(self::$defaultName)
            ->setDescription('Create a new admin user')
            ->setHelp('This command allows you to create an admin')
            ->addArgument('email', InputArgument::OPTIONAL, 'The admin email (username)')
            ->addArgument('password', InputArgument::OPTIONAL, 'The admin password')
            ->addArgument('firstName', InputArgument::OPTIONAL, 'The admin first name')
            ->addArgument('lastName', InputArgument::OPTIONAL, 'The admin last name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $this->getOrAskArgument($input, $output, 'email', 'Email: ', new EmailValidator());
        if ($email === null) {
            return Command::FAILURE;
        }
        if (!$this->userManager->emailIsUnique($email)) {
            $this->outputFormatted($output, 'This email is already used', 'error');
            return Command::FAILURE;
        }

        $password = $this->getOrAskArgument($input, $output, 'password', 'Password: ', new PasswordValidator());
        if ($password === null) {
            return Command::FAILURE;
        }

        $firstNameValidator = new NameValidator();
        $firstName = $this->getOrAskArgument($input, $output, 'firstName', 'First name: ', $firstNameValidator);
        if (!empty($firstNameValidator->getErrors())) {
            return Command::FAILURE;
        }

        $lastNameValidator = new NameValidator();
        $lastName = $this->getOrAskArgument($input, $output, 'lastName', 'Last name: ', $lastNameValidator);
        if (!empty($lastNameValidator->getErrors())) {
            return Command::FAILURE;
        }

        $this->userManager->createAdmin($email, $password, $firstName, $lastName);
        $this->outputFormatted($output, "Admin {$email} created\n");

        return Command::SUCCESS;
    }
}