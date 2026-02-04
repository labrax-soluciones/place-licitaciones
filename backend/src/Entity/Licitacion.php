<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\LicitacionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: LicitacionRepository::class)]
#[ORM\Table(name: 'licitacion')]
#[ORM\Index(columns: ['expediente'], name: 'idx_expediente')]
#[ORM\Index(columns: ['estado'], name: 'idx_estado')]
#[ORM\Index(columns: ['tipo_contrato'], name: 'idx_tipo_contrato')]
#[ORM\Index(columns: ['fecha_publicacion'], name: 'idx_fecha_publicacion')]
#[ORM\Index(columns: ['fecha_limite_presentacion'], name: 'idx_fecha_limite')]
#[ORM\Index(columns: ['importe_sin_iva'], name: 'idx_importe')]
#[ORM\Index(columns: ['provincia'], name: 'idx_provincia')]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['licitacion:read', 'licitacion:detail']]),
        new GetCollection(normalizationContext: ['groups' => ['licitacion:read']])
    ],
    order: ['fechaPublicacion' => 'DESC'],
    paginationItemsPerPage: 25
)]
#[ApiFilter(SearchFilter::class, properties: [
    'expediente' => 'partial',
    'titulo' => 'partial',
    'estado' => 'exact',
    'tipoContrato' => 'exact',
    'provincia' => 'exact',
    'codigoNuts' => 'start',
    'organoContratante.nombre' => 'partial',
    'organoContratante.nif' => 'exact',
    'organoContratante.provincia' => 'exact'
])]
#[ApiFilter(RangeFilter::class, properties: ['importeSinIva', 'importeConIva'])]
#[ApiFilter(DateFilter::class, properties: ['fechaPublicacion', 'fechaLimitePresentacion', 'fechaAdjudicacion'])]
#[ApiFilter(OrderFilter::class, properties: ['fechaPublicacion', 'importeSinIva', 'fechaLimitePresentacion', 'titulo'])]
class Licitacion
{
    public const ESTADO_PUBLICADA = 'PUB';
    public const ESTADO_EVALUACION = 'EV';
    public const ESTADO_ADJUDICADA = 'ADJ';
    public const ESTADO_RESUELTA = 'RES';
    public const ESTADO_ANULADA = 'ANU';

    public const TIPO_SERVICIOS = '2';
    public const TIPO_SUMINISTROS = '1';
    public const TIPO_OBRAS = '3';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['licitacion:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Groups(['licitacion:read'])]
    private ?string $idPlace = null;

    #[ORM\Column(length: 100)]
    #[Groups(['licitacion:read'])]
    private ?string $expediente = null;

    #[ORM\Column(length: 1000)]
    #[Groups(['licitacion:read'])]
    private ?string $titulo = null;

    #[ORM\Column(length: 20)]
    #[Groups(['licitacion:read'])]
    private ?string $estado = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['licitacion:read'])]
    private ?string $estadoDescripcion = null;

    #[ORM\Column(length: 10)]
    #[Groups(['licitacion:read'])]
    private ?string $tipoContrato = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['licitacion:read'])]
    private ?string $tipoContratoDescripcion = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['licitacion:detail'])]
    private ?string $subtipo = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 2, nullable: true)]
    #[Groups(['licitacion:read'])]
    private ?string $importeSinIva = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 2, nullable: true)]
    #[Groups(['licitacion:read'])]
    private ?string $importeConIva = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['licitacion:read'])]
    private ?array $codigosCpv = [];

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['licitacion:read'])]
    private ?string $provincia = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['licitacion:detail'])]
    private ?string $codigoNuts = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['licitacion:detail'])]
    private ?string $tipoProcedimiento = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Groups(['licitacion:detail'])]
    private ?string $tipoProcedimientoDescripcion = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['licitacion:read'])]
    private ?\DateTimeInterface $fechaPublicacion = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['licitacion:read'])]
    private ?\DateTimeInterface $fechaLimitePresentacion = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['licitacion:detail'])]
    private ?\DateTimeInterface $fechaAdjudicacion = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['licitacion:detail'])]
    private ?int $duracionMeses = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['licitacion:read'])]
    private ?string $urlLicitacion = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['licitacion:detail'])]
    private ?string $descripcion = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['licitacion:detail'])]
    private ?array $documentos = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['licitacion:detail'])]
    private ?array $criteriosAdjudicacion = [];

    #[ORM\Column(nullable: true)]
    #[Groups(['licitacion:detail'])]
    private ?int $numOfertas = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['licitacion:detail'])]
    private ?string $adjudicatarioNombre = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['licitacion:detail'])]
    private ?string $adjudicatarioNif = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 2, nullable: true)]
    #[Groups(['licitacion:detail'])]
    private ?string $importeAdjudicacion = null;

    #[ORM\ManyToOne(inversedBy: 'licitaciones')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['licitacion:read'])]
    private ?OrganoContratante $organoContratante = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['licitacion:detail'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rawXml = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->codigosCpv = [];
        $this->documentos = [];
        $this->criteriosAdjudicacion = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdPlace(): ?string
    {
        return $this->idPlace;
    }

    public function setIdPlace(string $idPlace): static
    {
        $this->idPlace = $idPlace;
        return $this;
    }

    public function getExpediente(): ?string
    {
        return $this->expediente;
    }

    public function setExpediente(string $expediente): static
    {
        $this->expediente = $expediente;
        return $this;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;
        return $this;
    }

    public function getEstadoDescripcion(): ?string
    {
        return $this->estadoDescripcion;
    }

    public function setEstadoDescripcion(?string $estadoDescripcion): static
    {
        $this->estadoDescripcion = $estadoDescripcion;
        return $this;
    }

    public function getTipoContrato(): ?string
    {
        return $this->tipoContrato;
    }

    public function setTipoContrato(string $tipoContrato): static
    {
        $this->tipoContrato = $tipoContrato;
        return $this;
    }

    public function getTipoContratoDescripcion(): ?string
    {
        return $this->tipoContratoDescripcion;
    }

    public function setTipoContratoDescripcion(?string $tipoContratoDescripcion): static
    {
        $this->tipoContratoDescripcion = $tipoContratoDescripcion;
        return $this;
    }

    public function getSubtipo(): ?string
    {
        return $this->subtipo;
    }

    public function setSubtipo(?string $subtipo): static
    {
        $this->subtipo = $subtipo;
        return $this;
    }

    public function getImporteSinIva(): ?string
    {
        return $this->importeSinIva;
    }

    public function setImporteSinIva(?string $importeSinIva): static
    {
        $this->importeSinIva = $importeSinIva;
        return $this;
    }

    public function getImporteConIva(): ?string
    {
        return $this->importeConIva;
    }

    public function setImporteConIva(?string $importeConIva): static
    {
        $this->importeConIva = $importeConIva;
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

    public function addCodigoCpv(string $cpv): static
    {
        if (!in_array($cpv, $this->codigosCpv ?? [])) {
            $this->codigosCpv[] = $cpv;
        }
        return $this;
    }

    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    public function setProvincia(?string $provincia): static
    {
        $this->provincia = $provincia;
        return $this;
    }

    public function getCodigoNuts(): ?string
    {
        return $this->codigoNuts;
    }

    public function setCodigoNuts(?string $codigoNuts): static
    {
        $this->codigoNuts = $codigoNuts;
        return $this;
    }

    public function getTipoProcedimiento(): ?string
    {
        return $this->tipoProcedimiento;
    }

    public function setTipoProcedimiento(?string $tipoProcedimiento): static
    {
        $this->tipoProcedimiento = $tipoProcedimiento;
        return $this;
    }

    public function getTipoProcedimientoDescripcion(): ?string
    {
        return $this->tipoProcedimientoDescripcion;
    }

    public function setTipoProcedimientoDescripcion(?string $tipoProcedimientoDescripcion): static
    {
        $this->tipoProcedimientoDescripcion = $tipoProcedimientoDescripcion;
        return $this;
    }

    public function getFechaPublicacion(): ?\DateTimeInterface
    {
        return $this->fechaPublicacion;
    }

    public function setFechaPublicacion(?\DateTimeInterface $fechaPublicacion): static
    {
        $this->fechaPublicacion = $fechaPublicacion;
        return $this;
    }

    public function getFechaLimitePresentacion(): ?\DateTimeInterface
    {
        return $this->fechaLimitePresentacion;
    }

    public function setFechaLimitePresentacion(?\DateTimeInterface $fechaLimitePresentacion): static
    {
        $this->fechaLimitePresentacion = $fechaLimitePresentacion;
        return $this;
    }

    public function getFechaAdjudicacion(): ?\DateTimeInterface
    {
        return $this->fechaAdjudicacion;
    }

    public function setFechaAdjudicacion(?\DateTimeInterface $fechaAdjudicacion): static
    {
        $this->fechaAdjudicacion = $fechaAdjudicacion;
        return $this;
    }

    public function getDuracionMeses(): ?int
    {
        return $this->duracionMeses;
    }

    public function setDuracionMeses(?int $duracionMeses): static
    {
        $this->duracionMeses = $duracionMeses;
        return $this;
    }

    public function getUrlLicitacion(): ?string
    {
        return $this->urlLicitacion;
    }

    public function setUrlLicitacion(?string $urlLicitacion): static
    {
        $this->urlLicitacion = $urlLicitacion;
        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function getDocumentos(): ?array
    {
        return $this->documentos;
    }

    public function setDocumentos(?array $documentos): static
    {
        $this->documentos = $documentos;
        return $this;
    }

    public function getCriteriosAdjudicacion(): ?array
    {
        return $this->criteriosAdjudicacion;
    }

    public function setCriteriosAdjudicacion(?array $criteriosAdjudicacion): static
    {
        $this->criteriosAdjudicacion = $criteriosAdjudicacion;
        return $this;
    }

    public function getNumOfertas(): ?int
    {
        return $this->numOfertas;
    }

    public function setNumOfertas(?int $numOfertas): static
    {
        $this->numOfertas = $numOfertas;
        return $this;
    }

    public function getAdjudicatarioNombre(): ?string
    {
        return $this->adjudicatarioNombre;
    }

    public function setAdjudicatarioNombre(?string $adjudicatarioNombre): static
    {
        $this->adjudicatarioNombre = $adjudicatarioNombre;
        return $this;
    }

    public function getAdjudicatarioNif(): ?string
    {
        return $this->adjudicatarioNif;
    }

    public function setAdjudicatarioNif(?string $adjudicatarioNif): static
    {
        $this->adjudicatarioNif = $adjudicatarioNif;
        return $this;
    }

    public function getImporteAdjudicacion(): ?string
    {
        return $this->importeAdjudicacion;
    }

    public function setImporteAdjudicacion(?string $importeAdjudicacion): static
    {
        $this->importeAdjudicacion = $importeAdjudicacion;
        return $this;
    }

    public function getOrganoContratante(): ?OrganoContratante
    {
        return $this->organoContratante;
    }

    public function setOrganoContratante(?OrganoContratante $organoContratante): static
    {
        $this->organoContratante = $organoContratante;
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

    public function getRawXml(): ?string
    {
        return $this->rawXml;
    }

    public function setRawXml(?string $rawXml): static
    {
        $this->rawXml = $rawXml;
        return $this;
    }

    public function isAbierta(): bool
    {
        if ($this->fechaLimitePresentacion === null) {
            return false;
        }
        return $this->fechaLimitePresentacion > new \DateTime();
    }
}
