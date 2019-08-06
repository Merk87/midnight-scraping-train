<?php


namespace App\Services\Scraper;


use App\Services\Scraper\Interfaces\Scraper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractScraper implements Scraper
{
    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var \DateTimeImmutable
     */
    protected $now;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $em)
    {
        $this->httpClient = $httpClient;
        // Added Entity Manager here, but I would rather create another suit of helpers, but not enough time.
        $this->em = $em;
        $this->now = $this->createNewInmutableDate();
    }

    public function run(string $url): array
    {
    }

    /**
     * @return \DateTimeInterface
     * @throws \Exception
     */
    protected function createNewInmutableDate(): \DateTimeInterface
    {
        return new \DateTimeImmutable();
    }

    /**
     * Helper function to wrap HttpClient calls.
     *
     * @param string $url
     *
     * @return object
     * @throws TransportExceptionInterface
     */
    protected function executeRequest(string $url)
    {
        return $this->httpClient->request('GET', $url);
    }

    /**
     * @param $htmlContent
     *
     * @return Crawler
     */
    protected function createCrawler($htmlContent)
    {
        return new Crawler($htmlContent);
    }
}