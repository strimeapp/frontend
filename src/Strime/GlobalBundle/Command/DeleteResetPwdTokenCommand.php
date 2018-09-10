<?php

namespace Strime\GlobalBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use Strime\GlobalBundle\Entity\ResetPwdToken;

class DeleteResetPwdTokenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:reset-pwd-token:delete')
            ->setDescription('Delete the tokens to reset passwords after 24h')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln( "[".date("Y-m-d H:i:s")."] Beginning of the script." );

        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Get the tokens
        $tokens = $em->getRepository('StrimeGlobalBundle:ResetPwdToken')->findAll();

        // If there are results
        if(is_array($tokens)) {

            $output->writeln( "[".date("Y-m-d H:i:s")."] Nb tokens found: " . count($tokens) );

            // For each token
            foreach ($tokens as $token) {

                // Check if the token has more than 24h
                $now = new \DateTime();

                // Get the interval of days before the end of the confirmation period
                $token_creation_date = $token->getCreatedAt();
                $interval = $token_creation_date->diff($now);

                $output->writeln( "[".date("Y-m-d H:i:s")."] Age of the token: " . $interval->d . "d " . $interval->h . "h" );

                // If the token has more than 24h, remove it.
                if($interval->d >= 1) {

                    $token_id = $token->getId();
                    $em->remove( $token );
                    $em->flush();
                    $output->writeln( "[".date("Y-m-d H:i:s")."] Token #" . $token_id . ": OK." );
                }
            }
        }

        // If there are no results
        else {
            $output->writeln( "[".date("Y-m-d H:i:s")."] Nb tokens found: 0" );
        }

        $output->writeln( "[".date("Y-m-d H:i:s")."] End of the script" );
    }
}
