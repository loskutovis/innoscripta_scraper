<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Parser implements ParserInterface
{
    private string $url;

    private array $words = [];

    private array $images = [];

    private array $links = [];

    private HttpClientInterface $client;

    /**
     * @param HttpClientInterface $client
     */
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $url
     * @return Parser
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return array
     */
    public function getWords(): array
    {
        return $this->words;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @return array
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function parse(): void
    {
        $content = $this->getHtmlContent();
        $crawler = new Crawler($content);

        $this->links = $this->parseLinks($crawler->filter('body a'));
        $this->images = $this->parseImages($crawler->filter('body img'));
        $this->words = $this->parseWords($crawler->filter('body'));
    }

    /**
     * @return string
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    private function getHtmlContent(): string
    {
        $response = $this->client->request(
            'GET',
            $this->url
        );

        return $response->getContent();
    }

    /**
     * @param Crawler $crawler
     * @return array
     */
    private function parseLinks(Crawler $crawler): array
    {
        $links = [];

        foreach ($crawler as $node) {
            $link = $node->getAttribute('href');

            if (!empty($link) && str_starts_with($link, 'http')) {
                $this->updateEntityCounter($links, $link);
            }
        }

        arsort($links);

        return $links;
    }

    /**
     * @param Crawler $crawler
     * @return array
     */
    private function parseWords(Crawler $crawler): array
    {
        $words = [];
        $duplicatedWords = explode(' ', $crawler->text());

        foreach ($duplicatedWords as $word) {
            $word = strtolower($word);
            $word = preg_replace("/[^A-Za-z0-9]/", '', $word);

            if (!empty($word) && !is_numeric($word)) {
                $this->updateEntityCounter($words, $word);
            }
        }

        arsort($words);

        return $words;
    }

    /**
     * @param Crawler $crawler
     * @return array
     */
    private function parseImages(Crawler $crawler): array
    {
        $images = [];
        foreach ($crawler as $node) {
            $src = $node->getAttribute('src');

            if (!empty($src)) {
                $this->updateEntityCounter($images, $src);
            }
        }

        arsort($images);

        return $images;
    }

    /**
     * @param array $array
     * @param string $key
     */
    private function updateEntityCounter(array &$array, string $key): void
    {
        if (empty($array[$key])) {
            $array[$key] = 0;
        }

        $array[$key]++;
    }
}
