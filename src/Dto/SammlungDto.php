<?php

declare(strict_types=1);

namespace Sammlungen\Dto;

/**
 * Unveränderliches Value-Object für eine Sammlung
 * (thematisch gruppierte Medienobjekte nach `source_media_type`).
 */
final class SammlungDto
{
    /**
     * Alle gültigen GEDCOM-Medientypen gemäß GEDCOM 5.5.1 Standard.
     */
    public const TYPEN = [
        'audio'        => 'Audio',
        'book'         => 'Bücher',
        'card'         => 'Karten / Karteikarten',
        'document'     => 'Dokumente',
        'electronic'   => 'Elektronische Dokumente',
        'fiche'        => 'Mikrofiche',
        'film'         => 'Film / Mikrofilm',
        'magazine'     => 'Zeitschriften',
        'manuscript'   => 'Manuskripte',
        'map'          => 'Landkarten',
        'newspaper'    => 'Zeitungen',
        'photo'        => 'Fotos',
        'tombstone'    => 'Grabsteine',
        'video'        => 'Video',
        'other'        => 'Sonstiges',
        ''             => 'Ohne Typ',
    ];

    /** Zuordnung Medientyp → FontAwesome-Icon-Klasse */
    public const ICONS = [
        'audio'      => 'fa-headphones',
        'book'       => 'fa-book',
        'card'       => 'fa-id-card',
        'document'   => 'fa-file-alt',
        'electronic' => 'fa-file-alt',
        'fiche'      => 'fa-th',
        'film'       => 'fa-film',
        'magazine'   => 'fa-newspaper',
        'manuscript' => 'fa-scroll',
        'map'        => 'fa-map',
        'newspaper'  => 'fa-newspaper',
        'photo'      => 'fa-image',
        'tombstone'  => 'fa-monument',
        'video'      => 'fa-video',
        'other'      => 'fa-folder-open',
        ''           => 'fa-folder',
    ];

    public function __construct(
        /**
         * GEDCOM source_media_type-Schlüssel (z. B. „photo", „book").
         * Leerer String = Medien ohne Typenangabe.
         */
        public readonly string $typ,

        /** Anzeigename der Sammlung */
        public readonly string $name,

        /** Anzahl Medienobjekte in dieser Sammlung */
        public readonly int $anzahl,

        /**
         * Xrefs der neuesten Medienobjekte (maximal 3) für Vorschau.
         * @var list<string>
         */
        public readonly array $vorschauXrefs = [],
    ) {}

    /**
     * Gibt die FontAwesome-Icon-Klasse für diesen Typ zurück.
     */
    public function icon(): string
    {
        return self::ICONS[strtolower($this->typ)] ?? 'fa-folder-open';
    }

    /**
     * Gibt den URL-sicheren Kategorieschlüssel zurück.
     */
    public function slug(): string
    {
        return $this->typ === '' ? '_ohne_typ' : $this->typ;
    }
}
