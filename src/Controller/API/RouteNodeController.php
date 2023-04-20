<?php

namespace App\Controller\API;

use App\Controller\BaseController;
use App\Entity\RouteService;
use App\Form\RouteService\SelectDependencyType;
use App\Handler\NodeHandler;
use App\Repository\PublicServiceRepository;
use App\Repository\RouteServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/node/api")
 */
class RouteNodeController extends BaseController
{
    /**
     * Devuelve todos los nodos dependientes de una ruta en una estructura de arbol/jerarquia
     *
     * @Route("/routes/{id}/tree", name="api_app_route_service_items_tree", methods={"GET"})
     */
    public function routeServiceItems(
        Request $request,
        RouteService $routeService,
        RouteServiceRepository $routeServiceRepository,
        NodeHandler $nodeHandler,
        EntityManagerInterface $entityManager,
        PublicServiceRepository $publicServiceRepository
    ): Response {
        $nodes = $nodeHandler
            ->getNodesBy($routeService->getId(), NodeHandler::TYPE_ROUTE);

        $treeNodes = [];

        if (!$routeService) {
            throw $this->createNotFoundException();
        }

        foreach ($nodes as $row) {
            # CREATE PARENT ROUTE
            if (!isset($treeNodes[$row['v']->id])) {
                $treeNodes[$row['v']->id] = $row['v'];
            }

            $parent = $treeNodes[$row['v']->id];
            $currentNode = $parent;
            $lastRelId = null;

            foreach ($row['r'] as $rel) {
                if ($rel->start_id === $parent->id && $currentNode) {
                    $currentNode = $parent;
                } else {
                    $currentNode = $currentNode->children[$rel->start_id];
                }

                if (!isset($currentNode->children)) {
                    $currentNode->children = [];
                }

                $lastRelId = $rel->id;
            }

            $publicServicesId[] = $row['v2']->properties->identifier;
            $row['v2']->properties->parentId = $currentNode->id;
            $row['v2']->properties->edgeParentId = $lastRelId;

            $currentNode->children[$row['v2']->id] = $row['v2'];
        }

        return $this->json(
            ['route' => $treeNodes]
        );
    }
}
