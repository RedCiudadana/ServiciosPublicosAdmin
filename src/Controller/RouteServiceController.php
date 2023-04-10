<?php

namespace App\Controller;

use App\Entity\PublicService;
use App\Entity\RouteService;
use App\Entity\RouteServiceItem;
use App\Form\RouteService\RouteServiceBaseType;
use App\Form\RouteService\SelectDependencyType;
use App\Form\RouteService\SelectItemType;
use App\Handler\NodeHandler;
use App\Handler\PublicService as HandlerPublicService;
use App\Repository\RouteServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use LogicException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/routes")
 * @IsGranted("ROLE_ADMIN")
 */
class RouteServiceController extends AbstractController
{
    /**
     * @Route("/", name="app_route_service_index", methods={"GET"})
     */
    public function index(RouteServiceRepository $routeServiceRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $routeServiceRepository->createQueryBuilder('u');

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('route_service/index.html.twig', [
            'routes_service' => $pagination
        ]);
    }

    /**
     * @Route("/new", name="app_route_service_new", methods={"GET", "POST"})
     */
    public function new(
        Request $request,
        RouteServiceRepository $routeServiceRepository,
        NodeHandler $nodeHandler): Response
    {
        $routeService = new RouteService();

        $form = $this->createForm(RouteServiceBaseType::class, $routeService);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $routeServiceRepository->add($routeService);

            $nodeHandler->addNode($routeService->getId(), NodeHandler::TYPE_ROUTE);

            return $this->redirectToRoute('app_route_service_items', ['id' => $routeService->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('route_service/new.html.twig', [
            'route_service' => $routeService,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_route_service_show", methods={"GET"})
     */
    public function show(RouteService $routeService): Response
    {
        return $this->render('route_service/show.html.twig', [
            'route_service' => $routeService,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_route_service_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, RouteService $routeService, RouteServiceRepository $routeServiceRepository): Response
    {
        $form = $this->createForm(RouteServiceBaseType::class, $routeService);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $routeServiceRepository->add($routeService);
            return $this->redirectToRoute('app_route_service_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('route_service/edit.html.twig', [
            'route_service' => $routeService,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_route_service_delete", methods={"POST"})
     */
    public function delete(Request $request, RouteService $routeService, RouteServiceRepository $routeServiceRepository): Response
    {
        throw new LogicException('No se puede eliminar usuarios');

        if ($this->isCsrfTokenValid('delete'.$routeService->getId(), $request->request->get('_token'))) {
            $routeServiceRepository->remove($routeService);
        }

        return $this->redirectToRoute('app_route_service_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/items", name="app_route_service_items", methods={"GET", "POST"})
     */
    public function routeServiceItems(
        Request $request,
        RouteService $routeService,
        RouteServiceRepository $routeServiceRepository,
        NodeHandler $nodeHandler,
        EntityManagerInterface $entityManager
    ): Response
    {
        $item = new RouteServiceItem();
        $item->setRouteService($routeService);

        $form = $this->createForm(SelectItemType::class,
        $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->beginTransaction();

                $routeService->addRouteServiceItem($form->getData());
                $routeServiceRepository->add($routeService);

                $publicService = $form->getData()->getPublicService();

                if (!$nodeHandler->getNode(
                        $publicService,
                        NodeHandler::TYPE_PUBLIC_SERVICE
                    )
                ) {
                    $nodeHandler->addNode(
                        $publicService,
                        NodeHandler::TYPE_PUBLIC_SERVICE
                    );
                }

                $dependency = $nodeHandler->getDependency(
                    $routeService->getId(),
                    NodeHandler::TYPE_ROUTE,
                    $publicService->getId(),
                    NodeHandler::TYPE_PUBLIC_SERVICE
                );

                if (!$dependency) {
                    $nodeHandler->addDependency(
                        $routeService->getId(),
                        NodeHandler::TYPE_ROUTE,
                        $publicService->getId(),
                        NodeHandler::TYPE_PUBLIC_SERVICE
                    );
                }

                $entityManager->commit();
            } catch (\Throwable $th) {
                $entityManager->rollback();
                throw $th;
            }

            return $this->redirectToRoute('app_route_service_items', [ 'id' => $routeService->getId() ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('route_service/items.html.twig', [
            'route_service' => $routeService,
            'nodes' => $nodeHandler->getNodesBy($routeService, NodeHandler::TYPE_ROUTE),
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/items/{routeServiceItem}", name="app_route_service_items_delete", methods={"POST"})
     */
    public function routeServiceItemsDelete(Request $request, RouteService $routeService, RouteServiceRepository $routeServiceRepository, RouteServiceItem $routeServiceItem): Response
    {
        $routeService->removeRouteServiceItem($routeServiceItem);

        $routeServiceRepository->add($routeService);

        $this->addFlash('success', 'Tramite de ruta eliminado');

        return $this->redirectToRoute('app_route_service_items', ['id' => $routeService->getId()], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/items/{publicService}/public_service", name="app_route_service_items_public_service", methods={"GET", "POST"})
     */
    public function routeServiceItemsAddDependency(
        Request $request,
        RouteService $routeService,
        PublicService $publicService,
        HandlerPublicService $publicServiceHandler
    ): Response
    {
        $form = $this->createForm(SelectDependencyType::class, [], ['data_class' => null]);
        $form->handleRequest($request);

        // get dependencies with dataprovider
        if ($form->isSubmitted() && $form->isValid()) {

            if (!$publicServiceHandler->getNode($form->getData()['publicService'])) {
                $publicServiceHandler->addNode($form->getData()['publicService']);
            }

            $publicServiceHandler->addDependency($publicService, $form->getData()['publicService']);

            $this->addFlash('success', 'Se agrego la dependencia exisitosamente');
            return $this->redirectToRoute('app_route_service_items', ['id' => $routeService->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('route_service/items_public_service.html.twig', [
            'route_service' => $routeService,
            'publicService' => $publicService,
            'dependencies' => [],
            'form' => $form,
        ]);
    }
}
