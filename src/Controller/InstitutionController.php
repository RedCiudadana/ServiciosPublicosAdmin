<?php

namespace App\Controller;

use App\Entity\Institution;
use App\Event\ResourceEvent;
use App\Form\Institution\BaseType as InstitutionType;
use App\Form\PublicService\UploadCollectionType;
use App\Repository\InstitutionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @IsGranted("ROLE_ADMIN")
     * @Route("/upload_csv", name="app_institution_upload_csv", methods={"GET" ,"POST"})
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

            foreach ($data as $row) {
                $institution = $em->getRepository(Institution::class)->findOneBy([
                    'name' => $row['nombre']
                ]);

                $row = array_map(function ($value) {
                    return trim($value);
                }, $row);


                foreach ($row as $key => $value) {
                    $row[trim($key)] = $value;
                }

                $institution = $em->getRepository(Institution::class)->findOneBy([
                    'name' => $row['nombre']
                ]);

                if (!$institution) {
                    $institution = new Institution();
                }

                $institution->setName($row['nombre']);
                $institution->setDescription($row['descripcion']);
                $institution->setAddress($row['direccion']);
                $institution->setSchedule($row['horario']);
                $institution->setWebpage($row['pagina_web']);
                $institution->setEmail($row['correo_electronico']);
                $institution->setFacebookURL($row['facebook']);
                $institution->setTwitterURL($row['twitter']);

                $errors = $validator->validate($institution);

                if (count($errors) > 0) {
                    $this->addFlash('danger', sprintf('Error al procesar tramite %s', $row['name']));

                    return $this->renderForm('public_service/upload_collection.html.twig', [
                        'form' => $form
                    ]);
                }

                $formInstitution = $this->createForm(InstitutionType::class, $institution, [
                    'csrf_protection' => false
                ]);

                // TODO: Use validator component
                $formInstitution->setData($institution);
                $formInstitution->submit([], false);

                if (!$formInstitution->isValid()) {
                    $this->addFlash('warning', sprintf('La institución %s no es válida', $row['nombre']));

                    return $this->renderForm('public_service/upload_collection.html.twig', [
                        'form' => $form
                    ]);
                }

                if ($institution->getId()) {
                    $updatedResources += 1;
                } else {
                    $createdResources += 1;
                }

                $em->persist($institution);

                $event = new ResourceEvent($institution);
                $eventDispatcher->dispatch($event, ResourceEvent::name);
            }

            try {
                $em->flush();
            } catch (\Throwable $th) {
                throw $th;
                $this->addFlash('warning', 'Error al persistir cambios');
                $form->addError(new FormError('Error al persistir los cambios'));

                return $this->renderForm('public_service/upload_collection.html.twig', [
                    'form' => $form
                ]);
            }

            if ($updatedResources > 0) {
                $this->addFlash('success', sprintf('Se actualizaron: %s', $updatedResources));
            }

            if ($createdResources > 0) {
                $this->addFlash('success', sprintf('Se agregaron: %s', $createdResources));
            }
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
