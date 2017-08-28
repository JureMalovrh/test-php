<?php
namespace BOF\Command;

use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;




class ReportYearlyCommand extends ContainerAwareCommand
{
    private $headline = array("Profile \ Month", "January", "February", "March", "April", "May", "June", "July", "August","September","October","November","December");

    private $emptyLine = array("%name_placeholder%", "n/a", "n/a", "n/a", "n/a", "n/a", "n/a", "n/a", "n/a", "n/a", "n/a", "n/a", "n/a");
    
    protected function configure()
    {
        $this
            ->setName('report:profiles:yearly')
            ->setDescription('Page views report')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $db Connection */
        $io = new SymfonyStyle($input,$output);
        $db = $this->getContainer()->get('database_connection');
        
        /* TODO: add prompt for a year */
        /* TODO: add prompt for name asc-desc */

        $minMaxYear = $db->query('SELECT MIN(YEAR(date)) as min, MAX(YEAR(date)) as max from views')->fetchAll();
        $minYear = $minMaxYear[0]['min'];
        $maxYear = $minMaxYear[0]['max'];

        if($minYear == NULL || $maxYear == NULL) {
            $io->text("There is no data in views table.");
            exit();
        } 
        
        for ($year=$minYear; $year <= $maxYear ; $year++) {
            $io->title($year);
            
            $views = $db->query("
                SELECT v.profile_id, pr.profile_name AS name, MONTH(v.date) as month, YEAR(v.date), SUM(v.views) as views
                FROM views as v 
                LEFT JOIN `profiles` as pr ON v.profile_id = pr.profile_id
                WHERE YEAR(v.date) = {$year}
                GROUP BY YEAR(v.date), MONTH(v.date), v.profile_id, pr.profile_name
                ORDER BY pr.profile_name ASC, MONTH(v.date) ASC"
            )->fetchAll();

            $name = NULL;
            $line = NULL;
            $dataForTable = array();
            foreach ($views as $data) {
                if($data["name"] != $name) {
                    $name = $data["name"];
                    
                    if(isset($line)) {
                        array_push($dataForTable, $line);
                    }

                    $line = $this->emptyLine;
                    $line[0] = $name;
                    $line[$data["month"]] = $data["views"];
                    
                } else {
                    $line[$data["month"]] = $data["views"];
                }
            }
            array_push($dataForTable, $line);
            $io->table($this->headline, $dataForTable);
        }     
    }
}
