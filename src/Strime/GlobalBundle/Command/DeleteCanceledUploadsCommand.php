<?php

namespace Strime\GlobalBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

class DeleteCanceledUploadsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:uploads:delete-canceled')
            ->setDescription('Delete the files in the upload folder that are older than 2 days')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Read the content of the upload folder
        $files = scandir("web/uploads/assets");

        // Foreach file, determine its age and delete it if needed
        foreach ($files as $file) {

            $full_path = "web/uploads/assets/" . $file;

            // Determine the age of the file
            $file_modification_date = filemtime( $full_path );
            $file_age = time() - $file_modification_date;
            $output->writeln( "[".date("Y-m-d H:i:s")."] ".$file );
            $output->writeln( "[".date("Y-m-d H:i:s")."] Modification date : " . $file_modification_date );
            $output->writeln( "[".date("Y-m-d H:i:s")."] File age : " . $file_age );

            // If the file is not a system file and is older than 2 days, we delete it
            if((strcmp($file[0], ".") != 0) && ($file_age > (60*60*24*2))) {
                $output->writeln("[".date("Y-m-d H:i:s")."] >>> Is older than 2 days.");

                $file_deletion = unlink( $full_path );

                if($file_deletion)
                    $output->writeln("[".date("Y-m-d H:i:s")."] OK: File deleted");
                else
                    $output->writeln("[".date("Y-m-d H:i:s")."] NOK: An error occured while deleting the file.");
            }

            $output->writeln("");
        }

        $response = "[".date("Y-m-d H:i:s")."] OK: The upload folder has been cleaned.";
        $output->writeln( $response );
    }
}
