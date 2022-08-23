<?php

namespace App\Controller;

use App\Config\Roles;
use App\Entity\Institution;
use App\Entity\User;
use App\Form\PublicService\UploadCollectionType;
use App\Form\RegistrationFormType;
use App\Form\User\SelectInstitutionType;
use App\Form\UserType;
use App\Handler\User as HandlerUser;
use App\Repository\InstitutionRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

/**
 * @Route("/user")
 * @IsGranted("ROLE_ADMIN")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="app_user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository,PaginatorInterface $paginator, Request $request): Response
    {
        $query = $userRepository->createQueryBuilder('u');

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('user/index.html.twig', [
            'users' => $pagination
        ]);
    }

    /**
     * @Route("/new", name="app_user_new", methods={"GET", "POST"})
     */
    public function new(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $user->setPassword(' ');
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        
        $user = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $userRepository->add($user);
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/upload_csv", name="app_user_upload_csv", methods={"GET" ,"POST"})
     */
    public function uploadUsersWithCSV(
        Request $request,
        HandlerUser $handlerUser
    ) {
        $form = $this->createForm(UploadCollectionType::class);

        $form->handleRequest($request);

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

            try {
                $resourcesProcessed = $handlerUser->processRowsAndCreate($data);
            } catch (\LogicException $th) {
                $this->addFlash('error', $th->getMessage());

                return $this->renderForm('public_service/upload_collection.html.twig', [
                    'form' => $form
                ]);
            }

            if (count($resourcesProcessed) > 0) {
                $this->addFlash('success', sprintf('Se procesaron %s registros', count($resourcesProcessed)));
            }

            return $this->redirectToRoute('app_user_index');
        }

        return $this->renderForm('public_service/upload_collection.html.twig', [
            'form' => $form
        ]);
    }

    /**
     * @Route("/{id}", name="app_user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_user_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $user->isAdministrator = true;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getData()->isAdministrator) {
                $user->addRole(Roles::ADMIN);
            }

            $userRepository->add($user);
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        throw new LogicException('No se puede eliminar usuarios');

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/institutions", name="app_user_institutions", methods={"GET", "POST"})
     */
    public function userInstitutions(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(SelectInstitutionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->addInstitution($form->getData()['institution']);
            $userRepository->add($user);

            return $this->redirectToRoute('app_user_institutions', [ 'id' => $user->getId() ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/institutions.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/institutions/{institution}", name="app_user_institutions_delete", methods={"POST"})
     */
    public function userInstitutionsDelete(Request $request, User $user, UserRepository $userRepository, Institution $institution): Response
    {
        $user->removeInstitution($institution);

        $userRepository->add($user);

        $this->addFlash('success', 'Institution removed from user');

        return $this->redirectToRoute('app_user_institutions', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
    }
}
