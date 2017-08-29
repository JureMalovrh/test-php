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

    /**
     * @param SymfonyStyle $io Output of the table
     * @param array $views Data from the DB
     * @return void
     */
    protected function printTable($io, $views)
    {
        /* 
            The data returned is going to be in a array, where the first person will be alphabetically first one, we just fill
            all the values we need (we put them in an array). If the name is different than the previous name, we put the line
            inside of our table data. 
        */
        $name = NULL;
        $line = NULL;
        $dataForTable = array();
        foreach ($views as $data) {
            // we got new name, so we should write line into table data
            if($data["name"] != $name) {
                $name = $data["name"];
                // just to see if the $line is not the first one
                if(isset($line)) {
                    array_push($dataForTable, $line);
                }
                // rewrite it and update name
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
    /**
     * @param string $order String for order of the query
     * @param number $year Number for year [optional]
     * @return void
     */
    protected function getDBQuery($order, $year=NULL) 
    {
        if(is_null($year)) {
            return 
                "SELECT v.profile_id, pr.profile_name AS name, MONTH(v.date) as month, SUM(v.views) as views
                FROM views as v 
                LEFT JOIN `profiles` as pr ON v.profile_id = pr.profile_id
                GROUP BY MONTH(v.date), v.profile_id, pr.profile_name
                ORDER BY pr.profile_name {$order}, MONTH(v.date) ASC";
        } else {
            return 
                "SELECT v.profile_id, pr.profile_name AS name, MONTH(v.date) as month, SUM(v.views) as views
                FROM views as v 
                LEFT JOIN `profiles` as pr ON v.profile_id = pr.profile_id
                WHERE YEAR(v.date) = {$year}
                GROUP BY MONTH(v.date), v.profile_id, pr.profile_name
                ORDER BY pr.profile_name {$order}, MONTH(v.date) ASC";
        }
       
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $db Connection */
        $io = new SymfonyStyle($input,$output);
        $db = $this->getContainer()->get('database_connection');

        $year = $io->ask('Which year would you like to see (press enter for all)', -1, function ($year) use($io) {
            if (!is_numeric($year)) {
                $io->text("Year is not a number!");
                exit();
            }

            return $year;
        });

        $order = $io->ask('Do you want name ASC or DESC', 'ASC', function ($order) use($io){
            if ($order != 'ASC' && $order != 'DESC') {
                $io->text("Wrong ordering");
                exit();
            }

            return $order;
        });
        
        

        // find min and max year so we know where to start and end
        $minMaxYear = $db->query('SELECT MIN(YEAR(date)) as min, MAX(YEAR(date)) as max from views')->fetchAll();
        $minYear = $minMaxYear[0]['min'];
        $maxYear = $minMaxYear[0]['max'];
        // for output purposes
        $minYearOut = $minYear;
        $maxYearOut = $maxYear;

        // if anything is null (should not happen for max), then there is no data present
        if($minYear == NULL || $maxYear == NULL) {
            $io->text("There is no data in views table.");
            exit();
        }
        // if year is not -1, just set variables to this year so we can only print specific year
        if($year != -1) {
            $minYear = $year;
            $maxYear = $year;
        }
        /* 
            go through all the years -> not really efficient, but somewhere needs to be done some loop, just because we have year + month not just one of them
        */
        for ($year=$minYear; $year <= $maxYear ; $year++) {
            $io->title("YEAR: {$year}");
            $views = $db->query($this->getDBQuery($order, $year))->fetchAll();

            if(empty($views)) {
                $io->text("There is no data for this year");
                continue;
            }

            $this->printTable($io, $views);
        }

        // get all data for all years
        $viewsAllYearsAndMonths = $db->query($this->getDBQuery($order))->fetchAll();
        $io->title("All present years together (from {$minYearOut} to {$maxYearOut})");
        $this->printTable($io, $viewsAllYearsAndMonths);
        
    }
}
