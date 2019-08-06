<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 */
class Article
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $canonicalUrl;

    /**
     * @ORM\Column(type="integer")
     */
    private $remoteId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $heading;

    /**
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @ORM\Column(type="date")
     */
    private $publishDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $featuredImgUrl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $author;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", cascade={"persist"})
     */
    private $tags;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\StockMarket", cascade={"persist"})
     */
    private $markets;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\CompanyStock", cascade={"persist"})
     */
    private $companyStocks;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->markets = new ArrayCollection();
        $this->companyStocks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    public function setCanonicalUrl(string $canonicalUrl): self
    {
        $this->canonicalUrl = $canonicalUrl;

        return $this;
    }

    public function getRemoteId(): ?int
    {
        return $this->remoteId;
    }

    public function setRemoteId(int $remoteId): self
    {
        $this->remoteId = $remoteId;

        return $this;
    }

    public function getHeading(): ?string
    {
        return $this->heading;
    }

    public function setHeading(string $heading): self
    {
        $this->heading = $heading;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getPublishDate(): ?\DateTimeInterface
    {
        return $this->publishDate;
    }

    public function setPublishDate(\DateTimeInterface $publishDate): self
    {
        $this->publishDate = $publishDate;

        return $this;
    }

    public function getFeaturedImgUrl(): ?string
    {
        return $this->featuredImgUrl;
    }

    public function setFeaturedImgUrl(string $featuredImgUrl): self
    {
        $this->featuredImgUrl = $featuredImgUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author): void
    {
        $this->author = $author;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

        return $this;
    }

    /**
     * @return Collection|StockMarket[]
     */
    public function getMarkets(): Collection
    {
        return $this->markets;
    }

    public function addMarket(StockMarket $market): self
    {
        if (!$this->markets->contains($market)) {
            $this->markets[] = $market;
        }

        return $this;
    }

    public function removeMarket(StockMarket $market): self
    {
        if ($this->markets->contains($market)) {
            $this->markets->removeElement($market);
        }

        return $this;
    }

    /**
     * @return Collection|StockMarket[]
     */
    public function getCompanyStocks(): Collection
    {
        return $this->companyStocks;
    }

    public function addCompanyStock(CompanyStock $companyStock): self
    {
        if (!$this->companyStocks->contains($companyStock)) {
            $this->companyStocks[] = $companyStock;
        }

        return $this;
    }

    public function removeCompanyStock(CompanyStock $companyStock): self
    {
        if ($this->companyStocks->contains($companyStock)) {
            $this->companyStocks->removeElement($companyStock);
        }

        return $this;
    }

}
