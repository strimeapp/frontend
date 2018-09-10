<?php

namespace Strime\GlobalBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use Strime\GlobalBundle\Entity\NewFeature;
use Strime\GlobalBundle\Entity\HasReadNewFeature;

class DeleteHasReadNewFeatureCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:has-read-new-feature:delete')
            ->setDescription('Delete the outdated lines in the has read new feature table')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln( "[".date("Y-m-d H:i:s")."] Beginning of the script." );

        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Get the last new feature
        $last_new_feature = $em->getRepository('StrimeGlobalBundle:NewFeature')->findOneBy(array(), array('id' => 'DESC'));
        $output->writeln( "[".date("Y-m-d H:i:s")."] Last feature ID: " . $last_new_feature->getId() );

        // Get all the lines in the Has Read New Feature table which are not linked to this feature.
        $query = $em->createQueryBuilder();
        $query->select( 'app_has_read' );
        $query->from( 'StrimeGlobalBundle:HasReadNewFeature','app_has_read' );
        $query->where('app_has_read.new_feature != :new_feature_id');
        $query->setParameter('new_feature_id', $last_new_feature->getId());
        $lines_to_delete = $query->getQuery()->getResult();
        $output->writeln( "[".date("Y-m-d H:i:s")."] Nb of lines to delete: " . count($lines_to_delete) );

        // Delete the lines found
        foreach ($lines_to_delete as $line) {
            
            $em->remove( $line );
            $em->flush();
            $output->writeln( "[".date("Y-m-d H:i:s")."] Line #" . $line->getId() . ": OK." );
        }


        $output->writeln( "[".date("Y-m-d H:i:s")."] OK: The table has been cleaned." );
    }
}