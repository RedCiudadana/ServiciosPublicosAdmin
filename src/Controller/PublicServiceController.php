<?php

namespace App\Controller;

use App\Config\Roles;
use App\Entity\PublicService;
use App\Form\PublicService\BaseType as PublicServiceType;
use App\Form\PublicService\UploadCollectionType;
use App\Repository\PublicServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

/**
 * @Route("/public/service")
 */
class PublicServiceController extends BaseController
{
    /**
     * @Route("/", name="app_public_service_index", methods={"GET"})
     */
    public function index(PublicServiceRepository $publicServiceRepository): Response
    {
        $publicServices = null;

        if ($this->isGranted(Roles::ADMIN)) {
            $publicServices = $publicServiceRepository->findAll();
        } else {
            $publicServices =
                $publicServiceRepository->findByUser($this->getUser());
        }

        return $this->render('public_service/index.html.twig', [
            'public_services' => $publicServices
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/upload_csv", name="app_public_service_upload_csv", methods={"GET" ,"POST"})
     */
    public function uploadPublicServicesWithCSV(Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(UploadCollectionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var UploadedFile
             */
            $file = $form->getData()['file'];

            $fileContents = utf8_encode(file_get_contents($file->getPathname()));

            $csvEncoder = new CsvEncoder();
            $data = $csvEncoder->decode($fileContents, 'csv');

            foreach ($data as $row) {
                $publicService = new PublicService();

                $row['Institución'];
                $row['Categoría'];
                $row['Subcategoría'];

                $publicService->setInstitutionDepartment($row['Dirección / Unidad Ejecutora / Departamento']);
                $publicService->setName($row['Trámite']);
                $publicService->setDescription($row['Descripción']);
                $publicService->setInstructions($row['Pasos']);
                $publicService->setRequirements($row['Requisitos']);
                $publicService->setCost($row['Costo']);
                $publicService->setTimeResponse($row['Tiempo de respuesta']);
            }
        }

        return $this->renderForm('public_service/upload_collection.html.twig', [
            'form' => $form
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
        if (!$this->validateAccessToResource($publicService)) {
            return new AccessDeniedException('You cannot access this page');
        };

        return $this->render('public_service/show.html.twig', [
            'public_service' => $publicService,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_public_service_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, PublicService $publicService, PublicServiceRepository $publicServiceRepository): Response
    {
        if (!$this->validateAccessToResource($publicService)) {
            return new AccessDeniedException('You cannot access this page');
        };

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
        if ($this->isCsrfTokenValid('delete' . $publicService->getId(), $request->request->get('_token'))) {
            $publicServiceRepository->remove($publicService);
        }

        return $this->redirectToRoute('app_public_service_index', [], Response::HTTP_SEE_OTHER);
    }

    private function validateAccessToResource(PublicService $publicService)
    {
        if ($this->isGranted(Roles::ADMIN)) {
            return true;
        }

        return in_array($publicService->getInstitution(), $this->getUser()->getInstitutions()->toArray());
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}/history", name="app_public_service_history", methods={"GET"})
     */
    public function history(EntityManagerInterface $em, PublicService $publicService)
    {
        /**
         * @var LogEntryRepository
         */
        $logRepo = $em->getRepository(LogEntry::class);
        $logs = $logRepo->getLogEntries($publicService);

        return $this->render('public_service/history.html.twig', [
            'public_service' => $publicService,
            'logs' => $logs
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}/history/apply/{version}", name="app_public_service_apply_version", methods={"GET"})
     */
    public function applyVersion(PublicService $publicService, string $version, EntityManagerInterface $em)
    {
        /**
         * @var LogEntryRepository
         */
        $logRepo = $em->getRepository(LogEntry::class);
        $logRepo->revert($publicService, $version);

        $em->persist($publicService);
        $em->flush();

        return $this->redirectToRoute('app_public_service_edit', [
            'id' => $publicService->getId()
        ]);
    }
}
