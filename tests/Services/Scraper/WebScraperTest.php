<?php

namespace App\Tests\Services\Scraper;

use App\Entity\Article;
use App\Entity\Tag;
use App\Services\Scraper\WebScraper;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @property MockObject httpClient
 */
class WebScraperTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $em;

    private $httpClient;
    /**
     * @var array
     */
    private $articleArrayData;
    /**
     * @var MockObject
     */
    private $mockedArticle;

    protected function setUp()
    {
        parent::setUp();
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->articleArrayData = [
            'title'           => "Article in test",
            'canonical'       => "/article/in/test/",
            'internalId'      => "99999999",
            'bodyContent'     => "This is the content of the test article.",
            'featuredImage'   => "/path/to/image/jpg",
            'tags'            => ['url_tag_1' => 'Tag1', 'url_tag_2' => 'Tag2',],
            'stockMarket'     => ['XXX', 'YYY', 'ZZZ'],
            'stockSymbol'     => ['AAA', 'BBB', 'CCC'],
            'author'          => 'Mr. Robot',
            'publicationDate' => new \DateTime()
        ];

        $this->mockedArticle = $this->createMock(Article::class);
    }

    public function testInstanceObject()
    {
        $webScraper = new WebScraper($this->httpClient, $this->em);
        $this->assertInstanceOf(WebScraper::class, $webScraper);
    }

    public function testCreateArticleObject()
    {
        $article = new Article();
        $article->setHeading($this->articleArrayData['title']);
        $article->setCanonicalUrl($this->articleArrayData['canonical']);
        $article->setRemoteId($this->articleArrayData['internalId']);
        $article->setBody($this->articleArrayData['bodyContent']);
        $article->setFeaturedImgUrl($this->articleArrayData['featuredImage']);
        $article->setAuthor($this->articleArrayData['author']);
        $article->setPublishDate($this->articleArrayData['publicationDate']);

        $this->assertEquals($this->articleArrayData['title'], $article->getHeading());
        $this->assertEquals($this->articleArrayData['canonical'], $article->getCanonicalUrl());
        $this->assertEquals($this->articleArrayData['internalId'], $article->getRemoteId());
        $this->assertEquals($this->articleArrayData['bodyContent'], $article->getBody());
        $this->assertEquals($this->articleArrayData['featuredImage'], $article->getFeaturedImgUrl());
        $this->assertEquals($this->articleArrayData['publicationDate'], $article->getPublishDate());
    }

    public function testProcessArticleTags()
    {
        $article = new Article();
        foreach ($this->articleArrayData['tags'] as $url => $displayText)
        {
            $tag = new Tag();
            $tag->setUrl($url);
            $tag->setDisplayText($displayText);
            $this->assertEquals($displayText, $tag->getDisplayText());
            $this->assertEquals($url, $tag->getUrl());

            $article->addTag($tag);
        }

        $this->assertNotEquals([], $article->getTags()->count());
        $this->assertEquals(count($this->articleArrayData['tags']),$article->getTags()->count());
    }
}
