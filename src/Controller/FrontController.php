<?php

namespace App\Controller;

use App\Service\ParserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FrontController
 */
class FrontController extends AbstractController
{
    public const URL = 'https://www.innoscripta.de/';

    private ParserInterface $parser;

    /**
     * @param ParserInterface $parser
     */
    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @Route("/", name="parse_url")
     *
     * @return Response
     */
    public function parseUrl(): Response
    {
        $this->parser
            ->setUrl(self::URL)
            ->parse();

        return $this->render('parser/index.html.twig', [
            'links' => $this->parser->getLinks(),
            'words' => $this->parser->getWords(),
            'images' => $this->parser->getImages()
        ]);
    }
}
