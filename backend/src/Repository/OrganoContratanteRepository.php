<?php

namespace App\Repository;

use App\Entity\OrganoContratante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrganoContratante>
 */
class OrganoContratanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganoContratante::class);
    }

    public function findByNif(string $nif): ?OrganoContratante
    {
        return $this->findOneBy(['nif' => $nif]);
    }

    public function findOrCreate(string $nif, string $nombre): OrganoContratante
    {
        $organo = $this->findByNif($nif);

        if ($organo === null) {
            $organo = new OrganoContratante();
            $organo->setNif($nif);
            $organo->setNombre($nombre);
        }

        return $organo;
    }
}
