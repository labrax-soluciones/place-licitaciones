<?php

namespace App\Repository;

use App\Entity\Alerta;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Alerta>
 */
class AlertaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alerta::class);
    }

    public function findActiveByUsuario(Usuario $usuario): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.usuario = :usuario')
            ->andWhere('a.activa = true')
            ->setParameter('usuario', $usuario)
            ->getQuery()
            ->getResult();
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.usuario', 'u')
            ->addSelect('u')
            ->andWhere('a.activa = true')
            ->andWhere('u.activo = true')
            ->andWhere('u.notificacionesEmail = true')
            ->getQuery()
            ->getResult();
    }
}
