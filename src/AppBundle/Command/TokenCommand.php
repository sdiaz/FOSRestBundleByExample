<?php

/**
 * This file is part of the FOSRestByExample package.
 *
 * (c) Santiago Diaz <santiago.diaz@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\User as User;

class TokenCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('app:token:create')
            ->setDescription('Create a token')
            ->addArgument('username', InputArgument::REQUIRED, 'The Username')
            ->addArgument('password', InputArgument::REQUIRED, 'The Password')
            ->setHelp(
                <<<EOT
                The <info>app:token:create</info> command generates a valid X-WSSE token for a user.

  <info>php bin/console app:token:create user password</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $userManager = $this->getContainer()->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($input->getArgument('username'));

        if (!$user instanceof User) {
            $output->writeln("User not found.");
        } else {

            $factory = $this->getContainer()->get('security.encoder_factory');

            $encoder = $factory->getEncoder($user);
            $password = $encoder->encodePassword($input->getArgument('password'), $user->getSalt());

            $created = date('c');
            $nonce = substr(md5(uniqid('nonce_', true)), 0, 16);
            $nonceSixtyFour = base64_encode($nonce);
            $passwordDigest = base64_encode(sha1($nonce . $created . $password, true));

            $token = sprintf(
                'X-WSSE : UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
                $input->getArgument('username'),
                $passwordDigest,
                $nonceSixtyFour,
                $created
            );

            $output->writeln($token);
        }
    }

}
