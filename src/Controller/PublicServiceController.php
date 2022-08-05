<?php

namespace App\Controller;

use App\Config\Roles;
use App\Entity\Category;
use App\Entity\Institution;
use App\Entity\PublicService;
use App\Entity\SubCategory;
use App\Event\ResourceEvent;
use App\Form\PublicService\BaseType as PublicServiceType;
use App\Form\PublicService\UploadCollectionType;
use App\Repository\PublicServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/public/service")
 */
class PublicServiceController extends BaseController
{
    /**
     * @Route("/", name="app_public_service_index", methods={"GET"})
     */
    public function index(PublicServiceRepository $publicServiceRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $publicServices = null;

        if ($this->isGranted(Roles::ADMIN)) {
            $query = $publicServiceRepository
                ->createQueryBuilder('ps')
                ->innerJoin('ps.institution', 'institution');
        } else {
            $query =
                $publicServiceRepository->findByUser($this->getUser(), 30);
        }

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );


        return $this->render('public_service/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/upload_csv", name="app_public_service_upload_csv", methods={"GET" ,"POST"})
     */
    public function uploadPublicServicesWithCSV(Request $request, EntityManagerInterface $em, EventDispatcherInterface $eventDispatcher, ValidatorInterface $validator, ManagerRegistry $mr)
    {
        $form = $this->createForm(UploadCollectionType::class);
        $form->handleRequest($request);

        $updatedResources = 0;
        $createdResources = 0;

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var UploadedFile
             */
            $file = $form->getData()['file'];

            $fileContents = (file_get_contents($file->getPathname()));

            $csvEncoder = new CsvEncoder();
            $data = $csvEncoder->decode($fileContents, 'csv', [
                'csv_delimiter' => $form->getData()['csv_delimiter'] ?? ','
            ]);

            $dataChunk = array_chunk($data, 10);

            $institutionsNotFound = [];
            $subcategoryNotFound = [];

            foreach ($dataChunk as $data) {
                foreach ($data as $row) {
                    $trimAndEncodeFunction = function ($str) {
                        return \ForceUTF8\Encoding::toUTF8(trim($str));
                    };

                    $row = array_combine(
                        array_map($trimAndEncodeFunction, array_keys($row)),
                        array_map($trimAndEncodeFunction, array_values($row))
                    );

                    $institution = $em->getRepository(Institution::class)->findOneBy([
                        'name' => $row['institucion']
                    ]);

                    $row = array_map(function ($value) {
                        return \ForceUTF8\Encoding::toUTF8(trim($value));
                    }, $row);

                    if (!$institution) {
                        if (!in_array($row['institucion'], $institutionsNotFound)) {
                            $institutionsNotFound[] = $row['institucion'];
                            $this->addFlash(
                                'warning',
                                sprintf('La institución %s no existe', $row['institucion'])
                            );
                        }

                        continue;
                    }

                    $publicService = $em->getRepository(PublicService::class)->findOneBy([
                        'name' => $row['nombre'],
                        'institution' => $institution
                    ]);

                    if (!$publicService) {
                        $publicService = new PublicService();
                    }

                    $category = $em->getRepository(Category::class)->findOneBy([
                        'name' => $row['categoria']
                    ]);

                    $subcategory = $em->getRepository(SubCategory::class)->findOneBy([
                        'name' => $row['subcategoria']
                    ]);

                    if (!$subcategory) {
                        if (!in_array($row['subcategoria'], $subcategoryNotFound)) {
                            $subcategoryNotFound[] = $row['subcategoria'];
                            $this->addFlash(
                                'warning',
                                sprintf('La sub-categoría %s no existe', $row['subcategoria'])
                            );
                        }

                        continue;
                    }


                    $publicService->setInstitution($institution);
                    $publicService->setSubcategory($subcategory);

                    $publicService->setName($row['nombre']);
                    $publicService->setDescription(substr($row['descripcion'], 0, 250));
                    $publicService->setInstructions($row['instrucciones']);
                    $publicService->setRequirements($row['requisitos']);
                    $publicService->setCost(floatval($row['costo']));
                    $publicService->setTimeResponse(substr($row['tiempo_de_respuesta'], 0, 250));
                    $publicService->setTypeOfDocumentObtainable(substr($row['documento_obtenible'], 0, 250));
                    $publicService->setUrl(substr($row['enlace'], 0, 250));
                    $publicService->setNormative(substr($row['respaldo_legal'], 0, 250));

                    $errors = $validator->validate($publicService);

                    if (count($errors) > 0) {
                        $this->addFlash('danger', sprintf('Error al procesar tramite %s', $row['nombre']));

                        continue;
                    }

                    $formService = $this->createForm(PublicServiceType::class, $publicService, [
                        'csrf_protection' => false
                    ]);

                    // TODO: Use validator component
                    $formService->setData($publicService);
                    $formService->submit([], false);

                    if (!$formService->isValid()) {
                        $this->addFlash('warning', sprintf('El servicio %s no es válido', $row['nombre']));

                        if ($updatedResources > 0) {
                            $this->addFlash('success', sprintf('Se actualizaron: %s', $updatedResources));
                        }

                        if ($createdResources > 0) {
                            $this->addFlash('success', sprintf('Se agregaron: %s', $createdResources));
                        }

                        return $this->renderForm('public_service/upload_collection.html.twig', [
                            'form' => $form
                        ]);
                    }

                    if ($publicService->getId()) {
                        $updatedResources += 1;
                    } else {
                        $createdResources += 1;
                    }

                    $em->persist($publicService);

                    $event = new ResourceEvent($publicService);
                    $eventDispatcher->dispatch($event, ResourceEvent::name);
                }

                try {
                    $em->flush();
                } catch (\Throwable $th) {
                    $this->addFlash('warning', sprintf('Error al persistir cambios del batch %s', array_search($data, array_keys($dataChunk))));
                    $form->addError(new FormError(sprintf('Error al persistir cambios del batch %s', array_search($data, array_keys($dataChunk)))));

                    $mr->resetManager();
                }

                $em->clear();
            }
        }

        if ($updatedResources > 0) {
            $this->addFlash('success', sprintf('Se actualizaron: %s', $updatedResources));
        }

        if ($createdResources > 0) {
            $this->addFlash('success', sprintf('Se agregaron: %s', $createdResources));
        }

        return $this->renderForm('public_service/upload_collection.html.twig', [
            'form' => $form
        ]);
    }

    /**
     * Last 30 changes applied to public services
     * @IsGranted("ROLE_ADMIN")
     * @Route("/history", name="app_public_service_history_index", methods={"GET"})
     * {@inheritdoc}
     */
    public function indexHistory(Request $request, EntityManagerInterface $em)
    {
        /**
         * @var LogEntryRepository
         */
        $logRepo = $em->getRepository(LogEntry::class);
        $logs = $logRepo->findBy(
            [
                'objectClass' => PublicService::class
            ],
            [
                'loggedAt' => 'DESC'
            ],
            30
        );

        return $this->render('public_service/index_history.html.twig', [
            'logs' => $logs
        ]);
    }

    /**
     * @Route("/new", name="app_public_service_new", methods={"GET", "POST"})
     */
    public function new(Request $request, PublicServiceRepository $publicServiceRepository, EventDispatcherInterface $eventDispatcher): Response
    {
        $publicService = new PublicService();
        $form = $this->createForm(PublicServiceType::class, $publicService);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $publicServiceRepository->add($publicService);

            $event = new ResourceEvent($publicService);
            $eventDispatcher->dispatch($event, ResourceEvent::name);

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
    public function edit(Request $request, PublicService $publicService, PublicServiceRepository $publicServiceRepository, EventDispatcherInterface $eventDispatcher): Response
    {
        if (!$this->validateAccessToResource($publicService)) {
            return new AccessDeniedException('You cannot access this page');
        };

        $form = $this->createForm(PublicServiceType::class, $publicService);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $publicServiceRepository->add($publicService);

            $event = new ResourceEvent($publicService);
            $eventDispatcher->dispatch($event, ResourceEvent::name);

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
    public function delete(Request $request, PublicService $publicService, PublicServiceRepository $publicServiceRepository, EventDispatcherInterface $eventDispatcher): Response
    {
        if ($this->isCsrfTokenValid('delete' . $publicService->getId(), $request->request->get('_token'))) {
            $publicServiceRepository->remove($publicService);

            $event = new ResourceEvent($publicService);
            $eventDispatcher->dispatch($event, ResourceEvent::name);
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
