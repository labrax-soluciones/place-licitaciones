<?php

namespace App\Service;

use App\Entity\Licitacion;
use App\Entity\OrganoContratante;
use App\Repository\LicitacionRepository;
use App\Repository\OrganoContratanteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PlaceAtomParser
{
    /** @var array<string, OrganoContratante> In-memory cache for organos by NIF */
    private array $organosCache = [];

    private const NAMESPACES = [
        'atom' => 'http://www.w3.org/2005/Atom',
        'cbc' => 'urn:dgpe:names:draft:codice:schema:xsd:CommonBasicComponents-2',
        'cac' => 'urn:dgpe:names:draft:codice:schema:xsd:CommonAggregateComponents-2',
        'cbc-place' => 'urn:dgpe:names:draft:codice-place-ext:schema:xsd:CommonBasicComponents-2',
        'cac-place' => 'urn:dgpe:names:draft:codice-place-ext:schema:xsd:CommonAggregateComponents-2',
    ];

    private const TIPOS_CONTRATO = [
        '1' => 'Suministros',
        '2' => 'Servicios',
        '3' => 'Obras',
        '21' => 'Gestión de Servicios Públicos',
        '31' => 'Concesión de Obras Públicas',
        '40' => 'Colaboración público-privada',
        '7' => 'Administrativo especial',
        '8' => 'Privado',
    ];

    private const TIPOS_PROCEDIMIENTO = [
        '1' => 'Abierto',
        '2' => 'Restringido',
        '3' => 'Negociado con publicidad',
        '4' => 'Negociado sin publicidad',
        '5' => 'Diálogo competitivo',
        '6' => 'Asociación para la innovación',
        '100' => 'Basado en Acuerdo Marco',
        '999' => 'Otros',
    ];

    public function __construct(
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $entityManager,
        private LicitacionRepository $licitacionRepository,
        private OrganoContratanteRepository $organoRepository,
        private LoggerInterface $logger,
        private string $atomUrl
    ) {
    }

    public function sync(): array
    {
        $stats = [
            'nuevas' => 0,
            'actualizadas' => 0,
            'errores' => 0,
            'total' => 0
        ];

        try {
            $this->logger->info('Iniciando sincronización con PLACE ATOM feed', ['url' => $this->atomUrl]);

            $response = $this->httpClient->request('GET', $this->atomUrl, [
                'timeout' => 120,
                'headers' => [
                    'Accept' => 'application/atom+xml, application/xml, text/xml',
                    'User-Agent' => 'PLACE-Licitaciones/1.0'
                ]
            ]);

            $content = $response->getContent();
            $stats['total'] = $this->parseAtomFeed($content, $stats);

            $this->logger->info('Sincronización completada', $stats);

        } catch (\Exception $e) {
            $this->logger->error('Error en sincronización PLACE', [
                'error' => $e->getMessage(),
                'previous' => $e->getPrevious()?->getMessage(),
            ]);
            throw $e;
        }

        return $stats;
    }

    private function parseAtomFeed(string $content, array &$stats): int
    {
        // Clear the in-memory cache at the start of each sync
        $this->organosCache = [];

        $xml = new \SimpleXMLElement($content);

        foreach (self::NAMESPACES as $prefix => $uri) {
            $xml->registerXPathNamespace($prefix, $uri);
        }

        $entries = $xml->xpath('//atom:entry');
        $count = 0;

        foreach ($entries as $entry) {
            try {
                $this->processEntry($entry, $stats);
                $count++;

                // Flush cada 50 entradas para evitar problemas de memoria
                if ($count % 50 === 0) {
                    $this->entityManager->flush();
                    $this->logger->info("Procesadas $count licitaciones");
                }
            } catch (\Exception $e) {
                $stats['errores']++;
                $idPlace = (string)$entry->id;
                $this->logger->warning('Error procesando entrada', [
                    'id' => $idPlace,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);

                // Si el EntityManager está cerrado, no podemos continuar
                if (!$this->entityManager->isOpen()) {
                    $this->logger->error('EntityManager cerrado, abortando sincronización', [
                        'last_error' => $e->getMessage()
                    ]);
                    break;
                }
            }
        }

        // Flush final
        if ($this->entityManager->isOpen()) {
            $this->entityManager->flush();
        }

        return $count;
    }

    private function processEntry(\SimpleXMLElement $entry, array &$stats): void
    {
        foreach (self::NAMESPACES as $prefix => $uri) {
            $entry->registerXPathNamespace($prefix, $uri);
        }

        // Obtener ID único de PLACE
        $idPlace = (string)$entry->id;
        if (empty($idPlace)) {
            return;
        }

        // Buscar si ya existe
        $licitacion = $this->licitacionRepository->findByIdPlace($idPlace);
        $isNew = ($licitacion === null);

        if ($isNew) {
            $licitacion = new Licitacion();
            $licitacion->setIdPlace($idPlace);
        }

        // Extraer datos del XML
        $this->extractLicitacionData($entry, $licitacion);

        // Extraer y asociar órgano contratante
        $organo = $this->extractOrganoContratante($entry);
        if ($organo) {
            $this->entityManager->persist($organo);
            $licitacion->setOrganoContratante($organo);
        }

        $licitacion->setUpdatedAt(new \DateTimeImmutable());
        $licitacion->setRawXml($entry->asXML());

        $this->entityManager->persist($licitacion);

        if ($isNew) {
            $stats['nuevas']++;
        } else {
            $stats['actualizadas']++;
        }
    }

    private function extractLicitacionData(\SimpleXMLElement $entry, Licitacion $licitacion): void
    {
        // Título
        $titulo = (string)$entry->title;
        $licitacion->setTitulo($titulo ?: 'Sin título');

        // URL
        $link = $entry->xpath('atom:link[@rel="alternate"]/@href');
        if (!empty($link)) {
            $licitacion->setUrlLicitacion((string)$link[0]);
        }

        // Expediente
        $expediente = $this->xpathValue($entry, './/cbc:ContractFolderID');
        $licitacion->setExpediente($expediente ?: 'N/A');

        // Estado
        $estado = $this->xpathValue($entry, './/cbc-place:ContractFolderStatusCode');
        $licitacion->setEstado($estado ?: 'PUB');
        $licitacion->setEstadoDescripcion($this->getEstadoDescripcion($estado));

        // Tipo de contrato
        $tipo = $this->xpathValue($entry, './/cac:ProcurementProject/cbc:TypeCode');
        $licitacion->setTipoContrato($tipo ?: '2');
        $licitacion->setTipoContratoDescripcion(self::TIPOS_CONTRATO[$tipo] ?? 'Desconocido');

        // Subtipo
        $subtipo = $this->xpathValue($entry, './/cac:ProcurementProject/cbc:SubTypeCode');
        $licitacion->setSubtipo($subtipo);

        // Importes
        $importeSinIva = $this->xpathValue($entry, './/cac:ProcurementProject/cac:BudgetAmount/cbc:TaxExclusiveAmount');
        $importeConIva = $this->xpathValue($entry, './/cac:ProcurementProject/cac:BudgetAmount/cbc:TotalAmount');

        if ($importeSinIva) {
            $licitacion->setImporteSinIva($importeSinIva);
        }
        if ($importeConIva) {
            $licitacion->setImporteConIva($importeConIva);
        }

        // Códigos CPV
        $cpvNodes = $entry->xpath('.//cac:ProcurementProject/cac:RequiredCommodityClassification/cbc:ItemClassificationCode');
        $cpvs = [];
        foreach ($cpvNodes as $cpv) {
            $cpvs[] = (string)$cpv;
        }
        $licitacion->setCodigosCpv($cpvs);

        // Ubicación
        $provincia = $this->xpathValue($entry, './/cac:ProcurementProject/cac:RealizedLocation/cbc:CountrySubentity');
        $nuts = $this->xpathValue($entry, './/cac:ProcurementProject/cac:RealizedLocation/cac:Address/cbc:CountrySubentityCode');
        $licitacion->setProvincia($provincia);
        $licitacion->setCodigoNuts($nuts);

        // Procedimiento
        $procedimiento = $this->xpathValue($entry, './/cac:TenderingProcess/cbc:ProcedureCode');
        $licitacion->setTipoProcedimiento($procedimiento);
        $licitacion->setTipoProcedimientoDescripcion(self::TIPOS_PROCEDIMIENTO[$procedimiento] ?? 'Otro');

        // Fechas
        $fechaPub = $this->xpathValue($entry, './/cac-place:ValidNoticeInfo/cbc:IssueDate');
        if ($fechaPub) {
            $licitacion->setFechaPublicacion(new \DateTime($fechaPub));
        }

        $fechaLimite = $this->xpathValue($entry, './/cac:TenderingProcess/cac:TenderSubmissionDeadlinePeriod/cbc:EndDate');
        $horaLimite = $this->xpathValue($entry, './/cac:TenderingProcess/cac:TenderSubmissionDeadlinePeriod/cbc:EndTime');
        if ($fechaLimite) {
            $datetime = $fechaLimite . ($horaLimite ? ' ' . $horaLimite : ' 23:59:59');
            $licitacion->setFechaLimitePresentacion(new \DateTime($datetime));
        }

        // Duración
        $duracion = $this->xpathValue($entry, './/cac:ProcurementProject/cac:PlannedPeriod/cbc:DurationMeasure');
        $unidad = $this->xpathAttribute($entry, './/cac:ProcurementProject/cac:PlannedPeriod/cbc:DurationMeasure', 'unitCode');
        if ($duracion) {
            $meses = (int)$duracion;
            if ($unidad === 'ANN') {
                $meses *= 12;
            }
            $licitacion->setDuracionMeses($meses);
        }

        // Descripción (usando el nombre del proyecto)
        $nombre = $this->xpathValue($entry, './/cac:ProcurementProject/cbc:Name');
        $licitacion->setDescripcion($nombre);

        // Criterios de adjudicación
        $criterios = $this->extractCriteriosAdjudicacion($entry);
        $licitacion->setCriteriosAdjudicacion($criterios);

        // Documentos
        $documentos = $this->extractDocumentos($entry);
        $licitacion->setDocumentos($documentos);

        // Datos de adjudicación (si está resuelta)
        $this->extractDatosAdjudicacion($entry, $licitacion);
    }

    private function extractOrganoContratante(\SimpleXMLElement $entry): ?OrganoContratante
    {
        $nif = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cac:Party/cac:PartyIdentification/cbc:ID[@schemeName="NIF" or @schemeName="CIF"]');
        $nombre = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cac:Party/cac:PartyName/cbc:Name');

        if (!$nif || !$nombre) {
            // Intentar ruta alternativa
            $nif = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cac:Party/cac:PartyIdentification/cbc:ID');
            $nombre = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cac:Party/cac:PartyName/cbc:Name');
        }

        if (!$nif) {
            return null;
        }

        // Check in-memory cache first (for entities not yet flushed)
        if (isset($this->organosCache[$nif])) {
            return $this->organosCache[$nif];
        }

        $organo = $this->organoRepository->findOrCreate($nif, $nombre ?: 'Desconocido');

        // Store in cache for future lookups within the same batch
        $this->organosCache[$nif] = $organo;

        // Actualizar datos adicionales
        $dir3 = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cac:Party/cac:PartyIdentification/cbc:ID[@schemeName="DIR3"]');
        if ($dir3) {
            $organo->setDir3($dir3);
        }

        $idPlataforma = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cac:Party/cac:PartyIdentification/cbc:ID[@schemeName="ID_PLATAFORMA"]');
        if ($idPlataforma) {
            $organo->setIdPlataforma($idPlataforma);
        }

        $tipoAdmin = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cbc:ContractingPartyTypeCode');
        $organo->setTipoAdministracion($tipoAdmin);

        $actividad = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cbc:ActivityCode');
        $organo->setCodigoActividad($actividad);

        // Dirección
        $direccion = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cac:Party/cac:PostalAddress/cbc:StreetName');
        $cp = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cac:Party/cac:PostalAddress/cbc:PostalZone');
        $municipio = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cac:Party/cac:PostalAddress/cbc:CityName');
        $provincia = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cac:Party/cac:PostalAddress/cbc:CountrySubentity');

        if ($direccion) $organo->setDireccion($direccion);
        if ($cp) $organo->setCodigoPostal($cp);
        if ($municipio) $organo->setMunicipio($municipio);
        if ($provincia) $organo->setProvincia($provincia);

        // Contacto
        $email = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cac:Party/cac:Contact/cbc:ElectronicMail');
        $telefono = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cac:Party/cac:Contact/cbc:Telephone');

        if ($email) $organo->setEmail($email);
        if ($telefono) $organo->setTelefono($telefono);

        // URL perfil
        $urlPerfil = $this->xpathValue($entry, './/cac-place:LocatedContractingParty/cac:Party/cbc:WebsiteURI');
        if ($urlPerfil) $organo->setUrlPerfil($urlPerfil);

        $organo->setUpdatedAt(new \DateTimeImmutable());

        return $organo;
    }

    private function extractCriteriosAdjudicacion(\SimpleXMLElement $entry): array
    {
        $criterios = [];
        $nodes = $entry->xpath('.//cac:TenderingTerms/cac:AwardingTerms/cac:AwardingCriteria');

        foreach ($nodes as $node) {
            foreach (self::NAMESPACES as $prefix => $uri) {
                $node->registerXPathNamespace($prefix, $uri);
            }

            $criterio = [
                'tipo' => $this->xpathValue($node, './/cbc:AwardingCriteriaTypeCode'),
                'descripcion' => $this->xpathValue($node, './/cbc:Description'),
                'peso' => $this->xpathValue($node, './/cbc:WeightNumeric'),
            ];

            if (!empty($criterio['descripcion']) || !empty($criterio['peso'])) {
                $criterios[] = $criterio;
            }
        }

        return $criterios;
    }

    private function extractDocumentos(\SimpleXMLElement $entry): array
    {
        $documentos = [];
        $tipos = [
            'LegalDocumentReference' => 'Pliego administrativo',
            'TechnicalDocumentReference' => 'Pliego técnico',
            'AdditionalDocumentReference' => 'Documento adicional'
        ];

        foreach ($tipos as $tag => $tipoDesc) {
            $nodes = $entry->xpath(".//cac:$tag");
            foreach ($nodes as $node) {
                foreach (self::NAMESPACES as $prefix => $uri) {
                    $node->registerXPathNamespace($prefix, $uri);
                }

                $doc = [
                    'tipo' => $tipoDesc,
                    'nombre' => $this->xpathValue($node, './/cbc:ID'),
                    'url' => $this->xpathValue($node, './/cac:ExternalReference/cbc:URI'),
                    'hash' => $this->xpathValue($node, './/cac:ExternalReference/cbc:DocumentHash'),
                ];

                if (!empty($doc['url'])) {
                    $documentos[] = $doc;
                }
            }
        }

        return $documentos;
    }

    private function extractDatosAdjudicacion(\SimpleXMLElement $entry, Licitacion $licitacion): void
    {
        $fechaAdj = $this->xpathValue($entry, './/cac:TenderResult/cbc:AwardDate');
        if ($fechaAdj) {
            $licitacion->setFechaAdjudicacion(new \DateTime($fechaAdj));
        }

        $numOfertas = $this->xpathValue($entry, './/cac:TenderResult/cbc:ReceivedTenderQuantity');
        if ($numOfertas) {
            $licitacion->setNumOfertas((int)$numOfertas);
        }

        $adjNombre = $this->xpathValue($entry, './/cac:TenderResult/cac:WinningParty/cac:PartyName/cbc:Name');
        if ($adjNombre) {
            $licitacion->setAdjudicatarioNombre($adjNombre);
        }

        $adjNif = $this->xpathValue($entry, './/cac:TenderResult/cac:WinningParty/cac:PartyIdentification/cbc:ID');
        if ($adjNif) {
            $licitacion->setAdjudicatarioNif($adjNif);
        }

        $importeAdj = $this->xpathValue($entry, './/cac:TenderResult/cac:AwardedTenderedProject/cac:LegalMonetaryTotal/cbc:PayableAmount');
        if ($importeAdj) {
            $licitacion->setImporteAdjudicacion($importeAdj);
        }
    }

    private function xpathValue(\SimpleXMLElement $xml, string $xpath): ?string
    {
        $result = $xml->xpath($xpath);
        if (!empty($result)) {
            return trim((string)$result[0]);
        }
        return null;
    }

    private function xpathAttribute(\SimpleXMLElement $xml, string $xpath, string $attribute): ?string
    {
        $result = $xml->xpath($xpath);
        if (!empty($result)) {
            $attrs = $result[0]->attributes();
            return isset($attrs[$attribute]) ? (string)$attrs[$attribute] : null;
        }
        return null;
    }

    private function getEstadoDescripcion(?string $estado): string
    {
        return match ($estado) {
            'PUB' => 'Publicada',
            'EV' => 'En evaluación',
            'ADJ' => 'Adjudicada provisionalmente',
            'RES' => 'Resuelta',
            'ANU' => 'Anulada',
            'PRE' => 'Previa',
            default => 'Desconocido'
        };
    }
}
