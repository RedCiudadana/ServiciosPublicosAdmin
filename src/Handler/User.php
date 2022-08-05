<?php

namespace App\Handler;

use App\Entity\Institution;
use App\Entity\User as EntityUser;
use App\Form\User\UserCreateType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use LogicException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class User
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    /**
     * @var UserPasswordHasherInterface
     */
    private $userPasswordHasher;

    public function __construct(EntityManagerInterface $em, FormFactoryInterface $formFactory, FlashBagInterface $flashBag, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->flashBag = $flashBag;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function processRowsAndCreate($data)
    {
        // In the case they copy from the public services csv.
        $data = array_unique($data, SORT_REGULAR);

        $resources = [];

        // Validate data has columns
        $columns = [
            'correo' => 'email',
            'contraseña' => 'password',
            'nombre' => 'name',
            'cargo' => 'position',
            'institucion' => 'institution'
        ];

        $propertyColumns = array_flip($columns);

        $chunks = array_chunk($data, 30);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $row) {
                // CLEAN DATA
                $trimAndEncodeFunction = function ($str) {
                    return \ForceUTF8\Encoding::toUTF8(trim($str));
                };

                $row = array_combine(
                    array_map($trimAndEncodeFunction, array_keys($row)),
                    array_map($trimAndEncodeFunction, array_values($row))
                );

                foreach (array_keys($columns) as $column) {
                    if (!isset($row[$column])) {
                        throw new LogicException(sprintf('El archivo no cuenta con la columna "%s".', $column));
                    }
                }

                /* Process Category */

                $resourceData = $this->transformDataToProperties($row, $columns);

                $resource = null;

                // Check if already exists in cache
                if (isset($resources[$resourceData['email']])) {
                    $this->flashBag->add(
                        'warning',
                        sprintf('El correo ya fue utilizado por otro usuario "%s".', $resourceData['email'])
                    );

                    continue;
                }

                // Search if exist already in the database
                if (!$resource) {
                    $resource = $this->em->getRepository(EntityUser::class)->findOneBy([
                        'email' => $resourceData['email']
                    ]);
                }

                // For now we are no updating users, so if already exist we skip. 
                // Probably we need to do jobs for this if are large files.
                if ($resource) {
                    continue;
                }

                if (!$resource) {
                    $resource = new EntityUser();
                }

                if ($resourceData['institution']) {
                    $institution = $this->em->getRepository(Institution::class)->findOneBy([
                        'name' => $resourceData['institution']
                    ]);

                    if (!$institution) {
                        throw new LogicException(
                            sprintf('La institución %s no existe', $resourceData['institution'])
                        );
                    }

                    $resource->addInstitution($institution);
                }

                unset($resourceData['institution']);

                // Set data in Form
                $form = $this->formFactory->create(UserCreateType::class, $resource, [
                    'csrf_protection' => false
                ]);

                $form->submit($resourceData, false);

                if ($form->isValid()) {

                    if ($resourceData['password']) {
                        $user = $form->getData();
                        $user->setPassword(
                            $this->userPasswordHasher->hashPassword(
                                $user,
                                $user->getPassword()
                            )
                        );
                    }

                    $this->em->persist($user);

                    $resources[$resourceData['email']] = $form->getData();
                } else {
                    /**
                     * @var FormError
                     */
                    $errors = $form->getErrors(true);

                    foreach ($errors as $error) {
                        $message = $error->getMessage();
                        $property = $error->getOrigin()->getName();
                        $propertyName = $propertyColumns[$property] ?? $property;

                        $this->flashBag->add('warning', sprintf('Error en %s: %s', $propertyName, $message));
                    }

                    throw new LogicException(sprintf('Error al procesar registro %s', $resourceData['name']));
                }
            }

            $this->em->flush();
        }

        return array_merge($resources);
    }

    private function transformDataToProperties(array $data, array $columns): array
    {
        $propierties = [];

        foreach ($columns as $key => $value) {
            $propierties[$value] = $data[$key];
        }

        return $propierties;
    }
}
