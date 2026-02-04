<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\AlertaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AlertaRepository::class)]
#[ORM\Table(name: 'alerta')]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['alerta:read', 'alerta:detail']]),
        new GetCollection(normalizationContext: ['groups' => ['alerta:read']]),
        new Post(denormalizationContext: ['groups' => ['alerta:write']]),
        new Patch(denormalizationContext: ['groups' => ['alerta:write']]),
        new Delete()
    ],
    paginationItemsPerPage: 25
)]
#[ApiResource(
    uriTemplate: '/usuarios/{usuarioId}/alertas',
    operations: [new GetCollection()],
    uriVariables: [
        'usuarioId' => new Link(toProperty: 'usuario', fromClass: Usuario::class)
    ],
    normalizationContext: ['groups' => ['alerta:read']]
)]
class Alerta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['alerta:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['alerta:read', 'alerta:write'])]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['alerta:read', 'alerta:write'])]
    private ?array $codigosCpv = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['alerta:read', 'alerta:write'])]
    private ?array $tiposContrato = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['alerta:read', 'alerta:write'])]
    private ?array $provincias = [];

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 2, nullable: true)]
    #[Groups(['alerta:read', 'alerta:write'])]
    private ?string $importeMinimo = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 2, nullable: true)]
    #[Groups(['alerta:read', 'alerta:write'])]
    private ?string $importeMaximo = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['alerta:read', 'alerta:write'])]
    private ?string $palabrasClave = null;

    #[ORM\Column]
    #[Groups(['alerta:read', 'alerta:write'])]
    private bool $activa = true;

    #[ORM\Column]
    #[Groups(['alerta:read', 'alerta:write'])]
    private bool $notificarEmail = true;

    #[ORM\ManyToOne(inversedBy: 'alertas')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['alerta:read', 'alerta:write'])]
    private ?Usuario $usuario = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['alerta:read'])]
    private ?\DateTimeImmutable $ultimaNotificacion = null;

    #[ORM\Column]
    #[Groups(['alerta:read'])]
    private int $totalNotificaciones = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->codigosCpv = [];
        $this->tiposContrato = [];
        $this->provincias = [];
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getCodigosCpv(): ?array
    {
        return $this->codigosCpv;
    }

    public function setCodigosCpv(?array $codigosCpv): static
    {
        $this->codigosCpv = $codigosCpv;
        return $this;
    }

    public function getTiposContrato(): ?array
    {
        return $this->tiposContrato;
    }

    public function setTiposContrato(?array $tiposContrato): static
    {
        $this->tiposContrato = $tiposContrato;
        return $this;
    }

    public function getProvincias(): ?array
    {
        return $this->provincias;
    }

    public function setProvincias(?array $provincias): static
    {
        $this->provincias = $provincias;
        return $this;
    }

    public function getImporteMinimo(): ?string
    {
        return $this->importeMinimo;
    }

    public function setImporteMinimo(?string $importeMinimo): static
    {
        $this->importeMinimo = $importeMinimo;
        return $this;
    }

    public function getImporteMaximo(): ?string
    {
        return $this->importeMaximo;
    }

    public function setImporteMaximo(?string $importeMaximo): static
    {
        $this->importeMaximo = $importeMaximo;
        return $this;
    }

    public function getPalabrasClave(): ?string
    {
        return $this->palabrasClave;
    }

    public function setPalabrasClave(?string $palabrasClave): static
    {
        $this->palabrasClave = $palabrasClave;
        return $this;
    }

    public function isActiva(): bool
    {
        return $this->activa;
    }

    public function setActiva(bool $activa): static
    {
        $this->activa = $activa;
        return $this;
    }

    public function isNotificarEmail(): bool
    {
        return $this->notificarEmail;
    }

    public function setNotificarEmail(bool $notificarEmail): static
    {
        $this->notificarEmail = $notificarEmail;
        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getUltimaNotificacion(): ?\DateTimeImmutable
    {
        return $this->ultimaNotificacion;
    }

    public function setUltimaNotificacion(?\DateTimeImmutable $ultimaNotificacion): static
    {
        $this->ultimaNotificacion = $ultimaNotificacion;
        return $this;
    }

    public function getTotalNotificaciones(): int
    {
        return $this->totalNotificaciones;
    }

    public function setTotalNotificaciones(int $totalNotificaciones): static
    {
        $this->totalNotificaciones = $totalNotificaciones;
        return $this;
    }

    public function incrementNotificaciones(): static
    {
        $this->totalNotificaciones++;
        $this->ultimaNotificacion = new \DateTimeImmutable();
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Comprueba si una licitación coincide con los criterios de la alerta
     */
    public function matchLicitacion(Licitacion $licitacion): bool
    {
        // Filtro por CPV
        if (!empty($this->codigosCpv)) {
            $match = false;
            foreach ($this->codigosCpv as $cpv) {
                foreach ($licitacion->getCodigosCpv() ?? [] as $cpvLicitacion) {
                    if (str_starts_with($cpvLicitacion, $cpv)) {
                        $match = true;
                        break 2;
                    }
                }
            }
            if (!$match) {
                return false;
            }
        }

        // Filtro por tipo de contrato
        if (!empty($this->tiposContrato) && !in_array($licitacion->getTipoContrato(), $this->tiposContrato)) {
            return false;
        }

        // Filtro por provincia
        if (!empty($this->provincias) && !in_array($licitacion->getProvincia(), $this->provincias)) {
            return false;
        }

        // Filtro por importe mínimo
        if ($this->importeMinimo !== null && $licitacion->getImporteSinIva() !== null) {
            if ((float)$licitacion->getImporteSinIva() < (float)$this->importeMinimo) {
                return false;
            }
        }

        // Filtro por importe máximo
        if ($this->importeMaximo !== null && $licitacion->getImporteSinIva() !== null) {
            if ((float)$licitacion->getImporteSinIva() > (float)$this->importeMaximo) {
                return false;
            }
        }

        // Filtro por palabras clave
        if (!empty($this->palabrasClave)) {
            $keywords = array_map('trim', explode(',', strtolower($this->palabrasClave)));
            $titulo = strtolower($licitacion->getTitulo() ?? '');
            $descripcion = strtolower($licitacion->getDescripcion() ?? '');

            $match = false;
            foreach ($keywords as $keyword) {
                if (str_contains($titulo, $keyword) || str_contains($descripcion, $keyword)) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                return false;
            }
        }

        return true;
    }
}
