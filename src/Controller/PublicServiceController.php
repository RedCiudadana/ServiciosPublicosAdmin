<?php

namespace App\Controller;

use App\Entity\PublicService;
use App\Form\PublicService\BaseType as PublicServiceType;
use App\Repository\PublicServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/public/service")
 */
class PublicServiceController extends AbstractController
{
    /**
     * @Route("/", name="app_public_service_index", methods={"GET"})
     */
    public function index(PublicServiceRepository $publicServiceRepository): Response
    {
        return $this->render('public_service/index.html.twig', [
            'public_services' => $publicServiceRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_public_service_new", methods={"GET", "POST"})
     */
    public function new(Request $request, PublicServiceRepository $publicServiceRepository): Response
    {
        $publicService = new PublicService();
        $form = $this->createForm(PublicServiceType::class, $publicService);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $publicServiceRepository->add($publicService);
            return $this->redirectToRoute('app_public_service_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('public_service/new.html.twig', [
            'public_service' => $publicService,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_public_service_show", methods={"GET"})
     */
    public function show(PublicService $publicService): Response
    {
        return $this->render('public_service/show.html.twig', [
            'public_service' => $publicService,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_public_service_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, PublicService $publicService, PublicServiceRepository $publicServiceRepository): Response
    {
        $form = $this->createForm(PublicServiceType::class, $publicService);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $publicServiceRepository->add($publicService);
            return $this->redirectToRoute('app_public_service_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('public_service/edit.html.twig', [
            'public_service' => $publicService,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_public_service_delete", methods={"POST"})
     */
    public function delete(Request $request, PublicService $publicService, PublicServiceRepository $publicServiceRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$publicService->getId(), $request->request->get('_token'))) {
            $publicServiceRepository->remove($publicService);
        }

        return $this->redirectToRoute('app_public_service_index', [], Response::HTTP_SEE_OTHER);
    }
}
