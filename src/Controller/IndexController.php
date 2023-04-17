<?php

namespace App\Controller;

use App\Handler\Dashboard;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="app_index")
     */
    public function index(Dashboard $dashboardHandler, ManagerRegistry $doctrine): Response
    {
        /**
         * @var Connection
         */
        $connection = $doctrine->getConnection('graph');

        $connection->executeQuery('LOAD \'age\';');
        $connection->executeQuery('SET search_path = ag_catalog, "$user", public;');

        $stm = $connection->executeStatement("SELECT * FROM cypher('graph_public_services', $$ CREATE  p = (:Tramite {name: 'DPI', institution: 'SAT'})-[:NEED_OF]->(:Tramite {name: 'NACIMIENTO', institution: 'SAT'})<-[:NEED_OF]-(:Tramite {name: 'NIT', institution: 'SAT'}) $$ ) as (p agtype);");

        $result = $connection->fetchAllAssociative('SELECT * FROM cypher(\'graph_public_services\', $$ MATCH (v) RETURN v $$) as (v agtype);');
        $string = str_replace('::vertex', '', $result[0]['v']);
        $json = json_decode($string);

        return $this->render('index/index.html.twig', [
            'stats' => $dashboardHandler->getDashboardStats()
        ]);
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
