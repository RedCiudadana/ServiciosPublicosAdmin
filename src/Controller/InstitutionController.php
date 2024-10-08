<?php

namespace App\Controller;

use App\Entity\Institution;
use App\Form\Institution\BaseType as InstitutionType;
use App\Form\PublicService\UploadCollectionType;
use App\Handler\Institution as HandlerInstitution;
use App\Repository\InstitutionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

/**
 * @Route("/institution")
 * @IsGranted("ROLE_ADMIN")
 */
class InstitutionController extends AbstractController
{
    /**
     * @Route("/", name="app_institution_index", methods={"GET"})
     */
    public function index(InstitutionRepository $institutionRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $institutionRepository->createQueryBuilder('i');

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('institution/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/upload_csv", name="app_institution_upload_csv", methods={"GET" ,"POST"})
     */
    public function uploadInstitutionsWithCSV(
        Request $request,
        HandlerInstitution $handlerInstitution
    )
    {
        $form = $this->createForm(UploadCollectionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var UploadedFile
             */
            $file = $form->getData()['file'];

            $fileContents = file_get_contents($file->getPathname());

            $csvEncoder = new CsvEncoder();

            $data = $csvEncoder->decode($fileContents, 'csv', [
                'csv_delimiter' => $form->getData()['csv_delimiter'] ?? ','
            ]);

            try {
                $resourcesProcessed = $handlerInstitution->processRowsAndCreate($data);
            } catch (\LogicException $th) {
                $this->addFlash('error', 'Error al procesar archivo');

                return $this->renderForm('public_service/upload_collection.html.twig', [
                    'form' => $form
                ]);

                return $this->redirectToRoute('app_institution_index');
            }

            if (count($resourcesProcessed) > 0) {
                $this->addFlash('success', sprintf('Se procesaron %s registros', count($resourcesProcessed)));
            }

            return $this->redirectToRoute('app_institution_index');
        }

        return $this->renderForm('public_service/upload_collection.html.twig', [
            'form' => $form
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

    /**
     * @Route("/{id}/history", name="app_institution_history", methods={"GET"})
     */
    public function history(EntityManagerInterface $em, Institution $institution)
    {
        /**
         * @var LogEntryRepository
         */
        $logRepo = $em->getRepository(LogEntry::class);
        $logs = $logRepo->getLogEntries($institution);

        return $this->render('institution/history.html.twig', [
            'institution' => $institution,
            'logs' => $logs
        ]);
    }

    /**
     * @Route("/{id}/history/apply/{version}", name="app_institution_apply_version", methods={"GET"})
     */
    public function applyVersion(Institution $institution, string $version, EntityManagerInterface $em)
    {
        /**
         * @var LogEntryRepository
         */
        $logRepo = $em->getRepository(LogEntry::class);
        $logRepo->revert($institution, $version);

        $em->persist($institution);
        $em->flush();

        return $this->redirectToRoute('app_institution_edit', [
            'id' => $institution->getId()
        ]);
    }
}
