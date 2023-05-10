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
use App\Repository\PublicServiceRepository;
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
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
    public function edit(
        Request $request,
        RouteService $routeService,
        RouteServiceRepository $routeServiceRepository,
        NodeHandler $nodeHandler,
        PublicServiceRepository $publicServiceRepository
    ): Response
    {
        $form = $this->createForm(RouteServiceBaseType::class, $routeService);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $routeServiceRepository->add($routeService);
            return $this->redirectToRoute('app_route_service_index', [], Response::HTTP_SEE_OTHER);
        }

        $nodes = $nodeHandler
            ->getNodesBy($routeService->getId(), NodeHandler::TYPE_ROUTE);

        $treeNodes = [];
        $publicServicesId = [];

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

        $publicServices = [];
        if (count($publicServicesId) > 0) {
            $publicServices = $publicServiceRepository->findBy([
                'id' => $publicServicesId
            ]);
        }

        $publicServicesHash = [];

        foreach ($publicServices as $pb) {
            $publicServicesHash[$pb->getId()] = $pb;
        }

        $encoders = [new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(['datetime_format' => 'd-m-Y']), new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getName();
            },
        ];

        $publicServiceData = $serializer->serialize($publicServicesHash, 'json', $defaultContext);
        // Maybe there is a way to ask the serializer to return a array
        $publicServiceData = json_decode($publicServiceData, true);

        $routeData = $serializer->serialize($routeService, 'json', $defaultContext);
        // Maybe there is a way to ask the serializer to return a array
        $routeData = json_decode($routeData, true);

        return $this->renderForm('route_service/edit.html.twig', [
            'route_service' => $routeService,
            'route_service_data' => $routeData,
            'nodesList' => $nodes,
            'public_service_data' => $publicServiceData,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_route_service_delete", methods={"POST"})
     */
    public function delete(Request $request, RouteService $routeService, RouteServiceRepository $routeServiceRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$routeService->getId(), $request->request->get('_token'))) {
            $routeServiceRepository->remove($routeService);
        }

        return $this->redirectToRoute('app_route_service_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Agrega una dependencia a una ruta. Agregar un edge entre vertex:Route -> vertex:PublicService
     *
     * @Route("/{id}/items", name="app_route_service_items", methods={"GET", "POST"})
     */
    public function routeServiceItems(
        Request $request,
        RouteService $routeService,
        RouteServiceRepository $routeServiceRepository,
        NodeHandler $nodeHandler,
        EntityManagerInterface $entityManager,
        PublicServiceRepository $publicServiceRepository
    ): Response
    {
        $form = $this->createForm(SelectDependencyType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $dependency = $form->getData()['publicService'];

            if (!$nodeHandler->getNode($dependency->getId(), NodeHandler::TYPE_PUBLIC_SERVICE)) {
                $nodeHandler->addNode($dependency->getId(), NodeHandler::TYPE_PUBLIC_SERVICE);
            }

            $nodeHandler->addDependency(
                $routeService->getId(),
                NodeHandler::TYPE_ROUTE,
                $dependency->getId(),
                NodeHandler::TYPE_PUBLIC_SERVICE,
                $routeService->getId()
            );

            $this->addFlash('success', 'Se agrego la dependencia exisitosamente');
            return $this->redirectToRoute('app_route_service_edit', ['id' => $routeService->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('route_service/items.html.twig', [
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
        NodeHandler $nodeHandler
    ): Response
    {
        $form = $this->createForm(SelectDependencyType::class, [], ['data_class' => null]);
        $form->handleRequest($request);

        // get dependencies with dataprovider
        if ($form->isSubmitted() && $form->isValid()) {

            $dependency = $form->getData()['publicService'];

            if (!$nodeHandler->getNode($dependency->getId(), NodeHandler::TYPE_PUBLIC_SERVICE)) {
                $nodeHandler->addNode($dependency->getId(), NodeHandler::TYPE_PUBLIC_SERVICE);
            }

            $nodeHandler->addDependency(
                $publicService->getId(),
                NodeHandler::TYPE_PUBLIC_SERVICE,
                $dependency->getId(),
                NodeHandler::TYPE_PUBLIC_SERVICE,
                $routeService->getId()
            );

            $this->addFlash('success', 'Se agrego la dependencia exisitosamente');
            return $this->redirectToRoute('app_route_service_items', ['id' => $routeService->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('route_service/items_public_service.html.twig', [
            'route_service' => $routeService,
            'publicService' => $publicService,
            'dependencies' => [], /* $nodeHandler
                ->getNodesBy($routeService->getId(), NodeHandler::TYPE_ROUTE), */
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/items/{publicService}/public_service/delete/{edgeId}", name="app_route_service_items_public_service_delete", methods={"POST"})
     */
    public function routeServiceItemsDeleteDependency(
        Request $request,
        RouteService $routeService,
        PublicService $publicService,
        NodeHandler $nodeHandler
    ): Response
    {
        $edgeId = $request->attributes->get('edgeId');

        $nodeHandler->removeDependencyById($edgeId);

        $this->addFlash('success', 'Se agrego la dependencia exisitosamente');

        return $this->redirectToRoute('app_route_service_items', ['id' => $routeService->getId()], Response::HTTP_SEE_OTHER);
    }
}
