<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ChromeDevToolsController extends AbstractController
{
    #[Route('/.well-known/appspecific/com.chrome.devtools.json', name: 'chrome_devtools_config', methods: ['GET'])]
    public function config(): JsonResponse
    {
        return new JsonResponse([]);
    }
}
