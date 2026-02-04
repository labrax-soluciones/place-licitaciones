<?php

namespace App\Controller;

use App\Repository\LicitacionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class DashboardController extends AbstractController
{
    public function __construct(
        private LicitacionRepository $licitacionRepository
    ) {
    }

    #[Route('/dashboard/estadisticas', name: 'api_dashboard_estadisticas', methods: ['GET'])]
    public function estadisticas(): JsonResponse
    {
        $stats = $this->licitacionRepository->getEstadisticas();
        return $this->json($stats);
    }

    #[Route('/dashboard/recientes', name: 'api_dashboard_recientes', methods: ['GET'])]
    public function recientes(): JsonResponse
    {
        $licitaciones = $this->licitacionRepository->findRecientes(10);

        $data = array_map(fn($l) => [
            'id' => $l->getId(),
            'expediente' => $l->getExpediente(),
            'titulo' => $l->getTitulo(),
            'estado' => $l->getEstado(),
            'tipoContrato' => $l->getTipoContratoDescripcion(),
            'importeSinIva' => $l->getImporteSinIva(),
            'provincia' => $l->getProvincia(),
            'fechaPublicacion' => $l->getFechaPublicacion()?->format('Y-m-d'),
            'fechaLimite' => $l->getFechaLimitePresentacion()?->format('Y-m-d H:i'),
            'organo' => $l->getOrganoContratante()?->getNombre(),
        ], $licitaciones);

        return $this->json($data);
    }

    #[Route('/dashboard/abiertas', name: 'api_dashboard_abiertas', methods: ['GET'])]
    public function abiertas(): JsonResponse
    {
        $licitaciones = $this->licitacionRepository->findAbiertas();

        $data = array_map(fn($l) => [
            'id' => $l->getId(),
            'expediente' => $l->getExpediente(),
            'titulo' => $l->getTitulo(),
            'tipoContrato' => $l->getTipoContratoDescripcion(),
            'importeSinIva' => $l->getImporteSinIva(),
            'provincia' => $l->getProvincia(),
            'fechaLimite' => $l->getFechaLimitePresentacion()?->format('Y-m-d H:i'),
            'diasRestantes' => $l->getFechaLimitePresentacion()
                ? (new \DateTime())->diff($l->getFechaLimitePresentacion())->days
                : null,
            'organo' => $l->getOrganoContratante()?->getNombre(),
            'urlLicitacion' => $l->getUrlLicitacion(),
        ], $licitaciones);

        return $this->json($data);
    }
}
