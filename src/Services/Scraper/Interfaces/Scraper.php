<?php


namespace App\Services\Scraper\Interfaces;


interface Scraper
{
    public function run(string $url): array;
}