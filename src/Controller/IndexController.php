<?php

namespace App\Controller;

use App\Handler\Dashboard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="app_index")
     */
    public function index(): Response
    {
        return $this->render('index/index.html.twig', []);
    }

    /**
     * @Route("/dashboard", name="app_dashboard")
     */
    public function home_dashboard(Dashboard $dashboardHandler): Response
    {
        return $this->render('index/home_dashboard.html.twig', [
            'stats' => $dashboardHandler->getDashboardStats()
        ]);
    }
}
