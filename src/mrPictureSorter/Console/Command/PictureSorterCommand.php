<?php
/**
 * Created by martinratinaud.
 * User: martin
 * Date: 17/08/13
 * Time: 11:51
 * To change this template use File | Settings | File Templates.
 */

namespace mrPictureSorter\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;


class PictureSorterCommand extends Command
{
    protected function configure()
    {
        $this->setName("sort")
            ->setDescription("Sorts all pictures within the given folder")
            ->setDefinition(array(
                new InputOption('purge', 'p', InputOption::VALUE_NONE, 'Remove the __DONE folder'),
                new InputOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Format of the folder name', "Ymd"),
                new InputArgument('folder', InputArgument::REQUIRED, 'Folder to sorts the pictures from', null)
            ))
            ->setHelp(<<<EOT
The <info>sort</info> command takes all pictures within a given folder and relocate them in the <format> folder base on their exif information

Run with <info>php bin/console sort <folder></info>

Run with <info>php bin/console sort --purge --format=Ymd <folder></info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //...
        $this->folder       = $input->getArgument("folder");
        $this->folder_done  = $this->folder."/__DONE";
        $this->format       = $input->getOption("format");
        $this->output       = $output;
        $purge              = $input->getOption("purge");
        $this->fs           = new Filesystem();
        $f                  = new Finder();


        if($purge) {
            $this->fs->remove($this->folder_done);
            $this->output->writeln("Removing $this->folder_done folder");
        }

        //Creating Done folder
        $this->fs->mkdir($this->folder_done);

        $f->files()->in($this->folder);
        $i = 0;
        foreach ($f as $file) {
            $i++;
            if($i >10) break;
            $date = $this->getFileDateTime($file->getRealpath());
            $this->moveFile($file, $date);
        }
    }

    protected function getFileDateTime($filepath)
    {
        $infos = @exif_read_data($filepath);

        //TODO find data for mp4 files

        if (isset($infos["FileDateTime"])) {
            return date($this->format, $infos["FileDateTime"]);
        } else {
            return false;
        }
    }

    protected function moveFile($file, $date)
    {
        $oldfilepath = $file->getRealpath();
        $filename = str_replace("/","-",$file->getRelativePathname());
        if($date) {
            $newfilefolder = $this->folder_done."/".$date;
        } else {
            $newfilefolder = $this->folder_done."/__NOTCLASSIFIED";
        }
        if(!$this->fs->exists($newfilefolder)) {
            $this->fs->mkdir($newfilefolder);
        }

        $newfilepath = $newfilefolder."/".$filename;
        $this->output->writeln("moving $oldfilepath to $newfilepath folder");
        $this->fs->copy($oldfilepath, $newfilepath);
    }

}
