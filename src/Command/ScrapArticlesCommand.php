<?php

namespace App\Command;

use App\Services\Scraper\WebScraper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapArticlesCommand extends Command
{
    protected static $defaultName = 'app:scrap-articles';
    /**
     * @var WebScraper
     */
    private $scraper;

    public function __construct(WebScraper $scraper)
    {
        parent::__construct();
        $this->scraper = $scraper;
    }

    protected function configure()
    {
        $this
            ->setDescription('Command to scrap web content, particularly because the nature of the test from https://marketexclusive.com/category/cannabis-stocks-news/')
            ->addArgument('url', InputArgument::REQUIRED, 'Url to scrap')
            ->setHelp("The <info>app:scrap-articles</info> command scraps the given url for articles and return the total of articles scrapped.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Gathering all the necessay information, please wait...");
        $result = $this->scraper->run($input->getArgument('url'));
        count($result) > 0
            ? $output->writeln(sprintf("%d articles has been processed", count($result)))
            : $output->writeln("No new Articles to process")
            ;

    }
}
