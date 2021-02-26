<?php

namespace App\Service;

/**
 * Interface ParserInterface
 * @package App\Service
 */
interface ParserInterface
{
    public function setUrl(string $url): self;

    public function parse(): void;
}
