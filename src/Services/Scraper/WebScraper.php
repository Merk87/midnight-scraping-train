<?php


namespace App\Services\Scraper;

use App\Entity\Article;
use App\Entity\CompanyStock;
use App\Entity\StockMarket;
use App\Entity\Tag;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class WebScraper extends AbstractScraper
{

    /**
     * Constant to store how long in the past we want to crawl links
     */
    const MAX_DAYS_IN_THE_PAST = -25;

    /**
     * @param string $url
     *
     * @return array
     * @throws \Exception
     * @throws TransportExceptionInterface
     */
    public function run(string $url): array
    {
        return $this->saveArticles($this->extractArticleInformation($this->collectArticleLinks($this->executeRequest($url))));
    }

    /**
     * @param $content
     *
     * @return array
     */
    private function collectArticleLinks($content): array
    {
        return $this->extractArticleLinks($this->createCrawler($content->getContent()));
    }

    /**
     * @param Crawler $crawler
     *
     * @return array
     */
    private function extractArticleLinks(Crawler $crawler): array
    {
        // Had to repeat the call to processAnchorElementsToGetArticlesUrl to don't get the links in the footer as half
        // of them are finance related but not cannabis related, and also duplicates.
        return array_merge(
            $this->processAnchorElementsToGetArticlesUrl($crawler->filter('.td-ss-main-content .td-module-thumb > a')),
            $this->processAnchorElementsToGetArticlesUrl($crawler->filter('.td_block_big_grid_1 .td-module-thumb > a'))
        );
    }

    /**
     *
     * @param Crawler $anchorElements
     *
     * @return array
     */
    private function processAnchorElementsToGetArticlesUrl(Crawler $anchorElements): array
    {
        return array_unique($anchorElements->each(function (Crawler $element) {
            $uri = $element->link()->getUri();
            echo $uri." -> following link...\n";
            return $uri;
        }));
    }

    /**
     * @param array $articleLinks
     *
     * @return array
     * @throws TransportExceptionInterface
     */
    private function extractArticleInformation(array $articleLinks): array
    {
        $articles = [];
        foreach ($articleLinks as $link) {
            $articles[] = $this->getArticleInformation($link);
        }
        return array_filter($articles);
    }

    /**
     * @param $url
     *
     * @return array
     * @throws TransportExceptionInterface
     */
    private function getArticleInformation($url): ?array
    {
        $articleContent = $this->createCrawler($this->executeRequest($url)->getContent());

        $publicationDate = $this->getPublicationDate($articleContent);
        $remoteId = $this->getRemoteId($articleContent);

        if ($this->checkDateForScrapping($publicationDate) === false
            || $this->checkIfArticleIsAlreadyScrapped($remoteId) === false
        ) {
            return null;
        }

        return [
            'title'           => $this->getArticleTitle($articleContent),
            'canonical'       => $this->getCanonicalLink($articleContent),
            'internalId'      => $remoteId,
            'bodyContent'     => $this->getArticleHTML($articleContent),
            'featuredImage'   => $this->getFeaturedImage($articleContent),
            'tags'            => $this->getTags($articleContent),
            'stockMarket'     => $this->getStockMarket($articleContent),
            'stockSymbol'     => $this->getStockSymbol($articleContent),
            'author'          => $this->getAuthor($articleContent),
            'publicationDate' => $publicationDate
        ];
    }

    /**
     * @param Crawler $articleContent
     *
     * @return string
     */
    private function getArticleTitle(Crawler $articleContent): string
    {
        return $articleContent->filter('header > h1')->text();
    }

    /**
     * @param Crawler $articleContent
     *
     * @return string
     */
    private function getCanonicalLink(Crawler $articleContent): string
    {
        return $this->getAttrValue($articleContent, 'link[rel="canonical"]', 'href');
    }

    /**
     * @param Crawler $articleContent
     *
     * @return mixed
     */
    private function getRemoteId(Crawler $articleContent): int
    {
        preg_match('/p=\K\d+/', $articleContent->filter('link[rel="shortlink"]')->attr('href'), $matches);
        return (int)$matches[0];
    }

    /**
     * @param Crawler $articleContent
     *
     * @return string
     */
    private function getArticleHTML(Crawler $articleContent): string
    {
        return implode($articleContent->filter('.td-post-content > p')->each(function (Crawler $item) {
            return sprintf("<p>%s</p>", $item->html());
        }));

    }

    /**
     * @param Crawler $articleContent
     *
     * @return string
     */
    private function getFeaturedImage(Crawler $articleContent): string
    {
        return $articleContent->filter('img.entry-thumb')->attr('src');
    }

    /**
     * @param Crawler $articleContent
     *
     * @return string[]
     */
    private function getTags(Crawler $articleContent): array
    {
        return call_user_func_array('array_merge',
            $articleContent->filter('.td-post-source-tags li > a')->each(function (Crawler $tag) {
                return [$tag->attr('href') => $tag->text()];
            }));
    }

    /**
     * @param Crawler $articleContent
     *
     * @return string[]
     */
    private function getStockMarket(Crawler $articleContent): array
    {
        $results = array_filter($articleContent->filter('.td-post-content > p > strong')->each(function (
            Crawler $boldText
        ) {
            preg_match_all('/(?!\()(\S+)((?=:))/i', $boldText->text(), $stockMarkets);

            if (empty(array_filter($stockMarkets[1])) !== true) {
                return $stockMarkets[1];
            }
        }));

        return !empty($results) ? array_unique(call_user_func_array('array_merge', $results)) : [];

    }

    /**
     * @param Crawler $articleContent
     *
     * @return string[]
     */
    private function getStockSymbol(Crawler $articleContent): array
    {
        $results = array_filter($articleContent->filter('.td-post-content > p > strong')->each(function (
            Crawler $boldText
        ) {
            preg_match_all('/(?!\()(\S+)((?=\)))/i', $boldText->text(), $stockSymbols);

            if (empty(array_filter($stockSymbols[1])) !== true) {
                return $stockSymbols[1];
            }
        }));

        return !empty($results) ? array_unique(call_user_func_array('array_merge', $results)) : [];
    }

    /**
     * @param Crawler $articleContent
     *
     * @return string
     */
    private function getAuthor(Crawler $articleContent)
    {
        return $this->getTextValue($articleContent, '.td-post-author-name > a');
    }

    /**
     * @param Crawler $articleContent
     *
     * @return \DateTimeInterface
     * @throws \Exception
     */
    private function getPublicationDate(Crawler $articleContent): \DateTimeInterface
    {
        return new \DateTimeImmutable($this->getTextValue($articleContent, 'time'));
    }

    /**
     * @param Crawler $articleContent
     * @param string  $selector
     * @param string  $attrName
     *
     * @return string
     */
    private function getAttrValue(Crawler $articleContent, string $selector, string $attrName): string
    {
        return $articleContent->filter($selector)->attr($attrName);
    }

    /**
     * @param Crawler $articleContent
     * @param string  $selector
     *
     * @return string
     */
    private function getTextValue(Crawler $articleContent, string $selector): string
    {
        return $articleContent->filter($selector)->text();
    }

    /**
     * @param \DateTimeInterface $publicationDate
     *
     * @return bool
     */
    private function checkDateForScrapping(\DateTimeInterface $publicationDate): bool
    {
        return $this->getArticleAgeInNegativeNumber($publicationDate) > self::MAX_DAYS_IN_THE_PAST;
    }

    /**
     * @param \DateTimeInterface $publicationDate
     *
     * @return int
     */
    private function getArticleAgeInNegativeNumber(\DateTimeInterface $publicationDate): int
    {
        return $this->now->diff($publicationDate)->format('%R%a');
    }

    /**
     * @param int $remoteId
     *
     * @return bool
     */
    private function checkIfArticleIsAlreadyScrapped(int $remoteId): bool
    {
        $article = $this->em->getRepository(Article::class)->findBy(['remoteId' => $remoteId]);
        return !$article ? true : false;
    }

    /**
     * @param array $extractedArticleInformation
     *
     * @return array
     */
    private function saveArticles(array $extractedArticleInformation): array
    {
        $articleObjects = [];
        foreach ($extractedArticleInformation as $articleArray) {
            $articleObject = $this->createArticleObject($articleArray);
            $this->em->persist($articleObject);
            $this->em->flush();
            $articleObjects[] = $articleObject;
        }
        return $articleObjects;
    }

    /**
     * @param $articleArray
     *
     * @return Article
     */
    private function createArticleObject($articleArray): Article
    {
        $article = new Article();
            $article->setHeading($articleArray['title']);
            $article->setCanonicalUrl($articleArray['canonical']);
            $article->setRemoteId($articleArray['internalId']);
            $article->setBody($articleArray['bodyContent']);
            $article->setFeaturedImgUrl($articleArray['featuredImage']);
            $article->setAuthor($articleArray['author']);
            $article->setPublishDate($articleArray['publicationDate']);

        $article = $this->processArticleTags($article, $articleArray['tags']);
        $article = $this->processArticleStockMarkets($article, $articleArray['stockMarket']);
        $article = $this->processArticleStockSymbols($article, $articleArray['stockSymbol']);

        return $article;
    }

    /**
     * This violates the SRP, I'm aware
     * @param Article $article
     * @param         $tags
     *
     * @return Article
     */
    private function processArticleTags(Article $article, $tags): Article
    {
        foreach ($tags as $url => $text) {
            $tag = $this->em->getRepository(Tag::class)->findOneBy(['displayText' => $text]);
            if (!$tag) {
                $tag = new Tag();
                $tag->setUrl($url);
                $tag->setDisplayText($text);
            }

            $article->addTag($tag);
        }
        return $article;
    }

    /**
     * This violates the SRP, I'm aware
     * @param Article $article
     * @param         $stockMarkets
     *
     * @return Article
     */
    private function processArticleStockMarkets(Article $article, $stockMarkets): Article
    {
        foreach ($stockMarkets as $market) {
            $stockMarket = $this->em->getRepository(StockMarket::class)->findOneBy(['symbol' => $market]);
            if (!$stockMarket) {
                $stockMarket = new StockMarket();
                $stockMarket->setSymbol($market);
            }

            $article->addMarket($stockMarket);
        }

        return $article;
    }

    /**
     * This violates the SRP, I'm aware
     * @param Article $article
     * @param         $stockSymbols
     *
     * @return Article
     */
    private function processArticleStockSymbols(Article $article, $stockSymbols): Article
    {
        foreach ($stockSymbols as $stock) {
            $companyStock = $this->em->getRepository(CompanyStock::class)->findOneBy(['symbol' => $stock]);
            if(!$companyStock){
                $companyStock = new CompanyStock();
                $companyStock->setSymbol($stock);
            }
            $article->addCompanyStock($companyStock);
        }

        return $article;
    }

}