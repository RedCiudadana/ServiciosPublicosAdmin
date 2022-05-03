<?php

namespace App\Controller;

use App\Entity\Institution;
use App\Form\Institution\BaseType as InstitutionType;
use App\Repository\InstitutionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/institution")
 * @IsGranted("ROLE_ADMIN")
 */
class InstitutionController extends AbstractController
{
    /**
     * @Route("/", name="app_institution_index", methods={"GET"})
     */
    public function index(InstitutionRepository $institutionRepository): Response
    {
        return $this->render('institution/index.html.twig', [
            'institutions' => $institutionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_institution_new", methods={"GET", "POST"})
     */
    public function new(Request $request, InstitutionRepository $institutionRepository): Response
    {
        $institution = new Institution();
        $form = $this->createForm(InstitutionType::class, $institution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $institutionRepository->add($institution);
            return $this->redirectToRoute('app_institution_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('institution/new.html.twig', [
            'institution' => $institution,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_institution_show", methods={"GET"})
     */
    public function show(Institution $institution): Response
    {
        return $this->render('institution/show.html.twig', [
            'institution' => $institution,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_institution_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Institution $institution, InstitutionRepository $institutionRepository): Response
    {
        $form = $this->createForm(InstitutionType::class, $institution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $institutionRepository->add($institution);
            return $this->redirectToRoute('app_institution_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('institution/edit.html.twig', [
            'institution' => $institution,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_institution_delete", methods={"POST"})
     */
    public function delete(Request $request, Institution $institution, InstitutionRepository $institutionRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$institution->getId(), $request->request->get('_token'))) {
            $institutionRepository->remove($institution);
        }

        return $this->redirectToRoute('app_institution_index', [], Response::HTTP_SEE_OTHER);
    }
}
