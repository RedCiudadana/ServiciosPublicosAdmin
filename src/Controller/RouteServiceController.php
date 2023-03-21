<?php

namespace App\Controller;

use App\Entity\RouteService;
use App\Entity\RouteServiceItem;
use App\Form\RouteService\RouteServiceBaseType;
use App\Form\RouteService\SelectItemType;
use App\Repository\RouteServiceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use LogicException;
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
    public function new(Request $request, RouteServiceRepository $routeServiceRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $routeService = new RouteService();

        $form = $this->createForm(RouteServiceBaseType::class, $routeService);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $routeServiceRepository->add($routeService);

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
        $form = $this->createForm(UserType::class, $routeService);
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
    public function userItems(Request $request, RouteService $routeService, RouteServiceRepository $routeServiceRepository): Response
    {
        $form = $this->createForm(SelectItemType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $routeService->addRouteServiceItem($form->getData()['route_service_item']);
            $routeServiceRepository->add($routeService);

            return $this->redirectToRoute('app_route_service_items', [ 'id' => $routeService->getId() ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('route_service/items.html.twig', [
            'route_service' => $routeService,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/items/{routeServiceItem}", name="app_route_service_items_delete", methods={"POST"})
     */
    public function userItemsDelete(Request $request, RouteService $routeService, RouteServiceRepository $routeServiceRepository, RouteServiceItem $routeServiceItem): Response
    {
        $routeService->removeRouteServiceItem($routeServiceItem);

        $routeServiceRepository->add($routeService);

        $this->addFlash('success', 'RouteServiceItem removed from user');

        return $this->redirectToRoute('app_route_service_items', ['id' => $routeService->getId()], Response::HTTP_SEE_OTHER);
    }
}
