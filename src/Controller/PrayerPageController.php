<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PrayerPageController extends AbstractController
{
    #[Route('/', name: 'app_prayer_page')]
    public function index(): Response
    {
        return $this->render('prayer_page/index.html.twig', []);
    }
}
