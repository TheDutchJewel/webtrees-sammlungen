# Changelog

Alle nennenswerten Änderungen an diesem Modul werden hier dokumentiert.

Das Format orientiert sich an [Keep a Changelog](https://keepachangelog.com/de/1.1.0/),
und das Projekt nutzt [Semantic Versioning](https://semver.org/lang/de/).

## [1.0.0] – 2026-05-22

### Erstes eigenständiges Release

Das Modul wurde aus dem früheren kombinierten `Familienarchiv`-Modul herausgelöst und
fokussiert sich auf Foto-/Dokumenten-Sammlungen. Orte-Funktionalität wurde in ein
separates Modul (`ortsregister`) ausgelagert, Quellen-Funktionalität ersatzlos gestrichen
(webtrees-Core deckt dies ab).

### Hinzugefügt
- Galerie-Ansicht für ordner-basierte und manuelle Sammlungen
- Lightbox mit Sidebar-Editor, Thumbnail-Streifen, Tastatur-Navigation
- EXIF-/XMP-Lesen und -Schreiben (Imagick) mit automatischem Tages-Backup
- Abgleich-Sektion EXIF ↔ webtrees (Beschreibung, Personen)
- Datei-Umbenennen aus der Lightbox heraus
- Manuelle Sammlungen (CRUD): Name, Slug, Icon, Farbe, Ansicht (foto/raster/gemischt/dokument)
- Pfad-basierte Sammlungszugehörigkeit (`familienarchiv_collection_pfad`)
- „Nicht eingebundene Medien"-Übersicht mit Typ-Aufschlüsselung
- Foto-Picker im Admin für manuelle Sammlungen
- Klickbarer Aktiv-Status-Toggle in der Sammlungs-Verwaltung
- APCu-Cache mit Array-Fallback und konfigurierbarem TTL
- Deutsche Übersetzung (`de.po` / `de.mo`)
- Architektur: ViewModel-Schicht für Daten-Aufbereitung, externes JS-Asset (`sammlung-galerie.js`)
- Test-Suite (PHPUnit 11) mit Unit- und Integration-Tests (SQLite In-Memory)

### Datenmodell
Drei DB-Tabellen werden automatisch angelegt:
- `familienarchiv_collection` (Sammlungs-Definitionen)
- `familienarchiv_collection_medium` (M:N mit webtrees-Medien)
- `familienarchiv_collection_pfad` (M:N mit Dateipfaden)
