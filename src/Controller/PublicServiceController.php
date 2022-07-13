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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
            $publicServices = $publicServiceRepository->findBy([], ['id' => 'DESC']);
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
    public function uploadPublicServicesWithCSV(Request $request, EntityManagerInterface $em, EventDispatcherInterface $eventDispatcher, ValidatorInterface $validator)
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

            $updatedResources = 0;
            $createdResources = 0;

            $dataChunk = array_chunk($data, 50);

            foreach ($dataChunk as $data) {
                foreach ($data as $row) {
                    $trimAndEncodeFunction = function ($str) {
                        return trim($str);
                    };

                    $row = array_combine(
                        array_map($trimAndEncodeFunction, array_keys($row)),
                        array_map($trimAndEncodeFunction, array_values($row))
                    );

                    $institution = $em->getRepository(Institution::class)->findOneBy([
                        'name' => $row['institucion']
                    ]);

                    $row = array_map(function ($value) {
                        return trim($value);
                    }, $row);

                    if (!$institution) {
                        $this->addFlash(
                            'warning',
                            sprintf('La institución %s no existe', $row['institucion'])
                        );

                        $form->addError(
                            new FormError(sprintf(
                                'La institución %s no existe',
                                $row['institucion']
                            ))
                        );

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
                        $this->addFlash(
                            'warning',
                            sprintf('La sub-categoría %s no existe', $row['subcategoria'])
                        );

                        $form->addError(
                            new FormError(sprintf(
                                'La sub-categoría %s no existe',
                                $row['subcategoria']
                            ))
                        );

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


                    $publicService->setInstitution($institution);
                    $publicService->setSubcategory($subcategory);

                    $publicService->setName($row['nombre']);
                    $publicService->setDescription($row['descripcion']);
                    $publicService->setInstructions($row['instrucciones']);
                    $publicService->setRequirements($row['requisitos']);
                    $publicService->setCost(floatval($row['costo']));
                    $publicService->setTimeResponse($row['tiempo_de_respuesta']);
                    $publicService->setTypeOfDocumentObtainable($row['documento_obtenible']);
                    $publicService->setUrl($row['enlace']);
                    $publicService->setNormative($row['respaldo_legal']);

                    $errors = $validator->validate($publicService);

                    if (count($errors) > 0) {
                        $this->addFlash('danger', sprintf('Error al procesar tramite %s', $row['name']));

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
                    throw $th;
                    $this->addFlash('warning', 'Error al persistir cambios');
                    $form->addError(new FormError('Error al persistir los cambios'));

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
