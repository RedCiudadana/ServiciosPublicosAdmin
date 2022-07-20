<?php

namespace App\Handler;

use App\Repository\InstitutionRepository;
use App\Repository\PublicServiceRepository;
use Doctrine\ORM\Internal\Hydration;

class Dashboard
{
    /**
     * {@inheritdoc}
     */
    private $institutionRepo;

    /**
     * {@inheritdoc}
     */
    private $publicServiceRepo;

    public function __construct(InstitutionRepository $institutionRepo, PublicServiceRepository $publicServiceRepo) {
        $this->institutionRepo = $institutionRepo;
        $this->publicServiceRepo = $publicServiceRepo;
    }

    public function getDashboardStats(): array
    {
        $institutionQb = $this->institutionRepo->createQueryBuilder('i');
        $countInsitutions = $institutionQb
            ->select('COUNT(i.id) as count_i')
            ->getQuery()
            ->getResult();

        $publicServiceQb = $this->publicServiceRepo->createQueryBuilder('ps');
        $countPublicServices = (clone $publicServiceQb)
            ->select('COUNT(ps.id) AS count_ps')
            ->getQuery()
            ->getResult();

        $countPublicServicesByInstitution = (clone $publicServiceQb)
            ->innerJoin('ps.institution', 'i')
            ->select('COUNT(ps.id) AS count_ps, i.id AS institution_id, i.name AS institution_name')
            // ->addSelect('i')
            ->groupBy('i')
            ->getQuery()
            ->getResult();

        $countPublicServicesByCategory = (clone $publicServiceQb)
            ->innerJoin('ps.subcategory', 'sc')
            ->innerJoin('sc.category', 'c')
            ->select('COUNT(ps.id) AS count_ps, c.id AS category_id, c.name AS category_name')
            // ->addSelect('c')
            ->groupBy('c')
            ->getQuery()
            ->getResult();

        return [
            'countInsitutions' => $countInsitutions,
            'countPublicServices' => $countPublicServices,
            'countPublicServicesByInstitution' => $countPublicServicesByInstitution,
            'countPublicServicesByCategory' => $countPublicServicesByCategory
        ];
    }
}
