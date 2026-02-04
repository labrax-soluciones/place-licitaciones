<?php

namespace App\Repository;

use App\Entity\Licitacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Licitacion>
 */
class LicitacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Licitacion::class);
    }

    public function findByIdPlace(string $idPlace): ?Licitacion
    {
        return $this->findOneBy(['idPlace' => $idPlace]);
    }

    public function findAbiertas(): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.fechaLimitePresentacion > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('l.fechaLimitePresentacion', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.organoContratante', 'o')
            ->addSelect('o');

        if (!empty($filters['cpv'])) {
            $qb->andWhere('l.codigosCpv LIKE :cpv')
               ->setParameter('cpv', '%' . $filters['cpv'] . '%');
        }

        if (!empty($filters['tipoContrato'])) {
            $qb->andWhere('l.tipoContrato = :tipo')
               ->setParameter('tipo', $filters['tipoContrato']);
        }

        if (!empty($filters['provincia'])) {
            $qb->andWhere('l.provincia = :provincia')
               ->setParameter('provincia', $filters['provincia']);
        }

        if (!empty($filters['importeMin'])) {
            $qb->andWhere('l.importeSinIva >= :importeMin')
               ->setParameter('importeMin', $filters['importeMin']);
        }

        if (!empty($filters['importeMax'])) {
            $qb->andWhere('l.importeSinIva <= :importeMax')
               ->setParameter('importeMax', $filters['importeMax']);
        }

        if (!empty($filters['soloAbiertas'])) {
            $qb->andWhere('l.fechaLimitePresentacion > :now')
               ->setParameter('now', new \DateTime());
        }

        if (!empty($filters['texto'])) {
            $qb->andWhere('l.titulo LIKE :texto OR l.descripcion LIKE :texto')
               ->setParameter('texto', '%' . $filters['texto'] . '%');
        }

        $qb->orderBy('l.fechaPublicacion', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function getEstadisticas(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // Total por tipo de contrato
        $porTipo = $conn->executeQuery("
            SELECT tipo_contrato, COUNT(*) as total, SUM(CAST(importe_sin_iva AS NUMERIC)) as importe_total
            FROM licitacion
            GROUP BY tipo_contrato
        ")->fetchAllAssociative();

        // Total por provincia (top 10)
        $porProvincia = $conn->executeQuery("
            SELECT provincia, COUNT(*) as total
            FROM licitacion
            WHERE provincia IS NOT NULL
            GROUP BY provincia
            ORDER BY total DESC
            LIMIT 10
        ")->fetchAllAssociative();

        // Evolución mensual (últimos 12 meses)
        $evolucion = $conn->executeQuery("
            SELECT
                TO_CHAR(fecha_publicacion, 'YYYY-MM') as mes,
                COUNT(*) as total,
                SUM(CAST(importe_sin_iva AS NUMERIC)) as importe_total
            FROM licitacion
            WHERE fecha_publicacion >= NOW() - INTERVAL '12 months'
            GROUP BY TO_CHAR(fecha_publicacion, 'YYYY-MM')
            ORDER BY mes
        ")->fetchAllAssociative();

        // Totales generales
        $totales = $conn->executeQuery("
            SELECT
                COUNT(*) as total_licitaciones,
                SUM(CAST(importe_sin_iva AS NUMERIC)) as importe_total,
                COUNT(CASE WHEN fecha_limite_presentacion > NOW() THEN 1 END) as abiertas
            FROM licitacion
        ")->fetchAssociative();

        return [
            'porTipo' => $porTipo,
            'porProvincia' => $porProvincia,
            'evolucion' => $evolucion,
            'totales' => $totales
        ];
    }

    public function findRecientes(int $limit = 10): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.organoContratante', 'o')
            ->addSelect('o')
            ->orderBy('l.fechaPublicacion', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca licitaciones por categoría predefinida
     */
    public function findByCategoria(string $categoria, bool $soloAbiertas = false): array
    {
        $keywords = $this->getKeywordsByCategoria($categoria);

        if (empty($keywords)) {
            return [];
        }

        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.organoContratante', 'o')
            ->addSelect('o');

        // Construir condición OR para todas las palabras clave
        $orConditions = [];
        foreach ($keywords as $i => $keyword) {
            $orConditions[] = "LOWER(l.titulo) LIKE LOWER(:kw{$i})";
            $orConditions[] = "LOWER(l.descripcion) LIKE LOWER(:kw{$i})";
            $qb->setParameter("kw{$i}", '%' . $keyword . '%');
        }

        $qb->andWhere('(' . implode(' OR ', $orConditions) . ')');

        if ($soloAbiertas) {
            $qb->andWhere('l.fechaLimitePresentacion > :now')
               ->setParameter('now', new \DateTime());
        }

        $qb->orderBy('l.fechaPublicacion', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Devuelve las palabras clave para cada categoría
     */
    private function getKeywordsByCategoria(string $categoria): array
    {
        $categorias = [
            'vehiculos' => [
                'vehículo', 'vehiculo', 'vehículos', 'vehiculos',
                'coche ', 'coches',
                'automóvil', 'automovil', 'automóviles', 'automoviles',
                'motocicleta', 'motocicletas', 'moto ', 'motos',
                'furgoneta', 'furgonetas',
                'furgón', 'furgon', 'furgones',
                'flota de vehículos', 'flota de vehiculos',
                'alquiler de vehículos', 'alquiler de vehiculos',
                'parque móvil', 'parque movil',
                'renting',
            ],
            'informatica' => [
                'informático', 'informatico', 'informática', 'informatica',
                'software', 'hardware',
                'ordenador', 'ordenadores',
                'servidor', 'servidores',
                'cloud', 'nube',
                'desarrollo web', 'aplicación', 'aplicacion',
            ],
            'limpieza' => [
                'limpieza', 'higiene', 'desinfección', 'desinfeccion',
                'mantenimiento limpieza', 'servicio limpieza',
            ],
            'seguridad' => [
                'seguridad', 'vigilancia', 'vigilante',
                'alarma', 'cctv', 'videovigilancia',
            ],
        ];

        return $categorias[$categoria] ?? [];
    }

    /**
     * Devuelve las categorías disponibles
     */
    public function getCategoriasDisponibles(): array
    {
        return [
            ['id' => 'vehiculos', 'nombre' => 'Vehículos (coches, motos, renting)'],
            ['id' => 'informatica', 'nombre' => 'Informática y tecnología'],
            ['id' => 'limpieza', 'nombre' => 'Limpieza e higiene'],
            ['id' => 'seguridad', 'nombre' => 'Seguridad y vigilancia'],
        ];
    }
}
