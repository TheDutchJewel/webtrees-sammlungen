<?php

declare(strict_types=1);

namespace Sammlungen\Http\RequestHandlers;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Webtrees;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function basename;
use function filesize;
use function is_file;
use function pathinfo;
use function realpath;
use function str_starts_with;
use function strtolower;

use const PATHINFO_EXTENSION;

/**
 * GET /tree/{tree}/archiv/media-datei?pfad=Kirchenb%C3%BCcher-.../datei.pdf
 *
 * Liefert eine Mediendatei aus dem Baum-Medienverzeichnis direkt aus,
 * auch wenn sie noch nicht als GEDCOM-Medienobjekt importiert wurde.
 */
final class MediaDateiServe implements RequestHandlerInterface
{
    private const MIME_TYPES = [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'mp3'  => 'audio/mpeg',
        'mp4'  => 'video/mp4',
        'avi'  => 'video/x-msvideo',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'txt'  => 'text/plain',
    ];

    public function __construct(
        private readonly ResponseFactoryInterface $response_factory,
        private readonly StreamFactoryInterface   $stream_factory,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $user = Validator::attributes($request)->user();

        // Nur Mitglieder des Baums dürfen Mediendateien abrufen
        if (!Auth::isMember($tree, $user)) {
            return $this->response_factory->createResponse(StatusCodeInterface::STATUS_FORBIDDEN);
        }

        $pfad = Validator::queryParams($request)->string('pfad', '');

        // Sicherheit: Pfad normalisieren, keine Traversal-Angriffe
        $pfad = str_replace(['\\', '//'], ['/', '/'], $pfad);
        $pfad = preg_replace('/\.\.+/', '', $pfad) ?? '';
        $pfad = ltrim($pfad, '/');

        $mediaBase = Webtrees::DATA_DIR . $tree->getPreference('MEDIA_DIRECTORY', 'media/');
        $fullPath  = $mediaBase . $pfad;

        $realBase = realpath($mediaBase);
        $realFile = realpath($fullPath);

        if (
            $realBase === false
            || $realFile === false
            || !str_starts_with($realFile, $realBase)
            || !is_file($realFile)
        ) {
            return $this->response_factory->createResponse(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $extension = strtolower(pathinfo($realFile, PATHINFO_EXTENSION));
        $mime      = self::MIME_TYPES[$extension] ?? 'application/octet-stream';
        $stream    = $this->stream_factory->createStreamFromFile($realFile, 'rb');

        return $this->response_factory
            ->createResponse(StatusCodeInterface::STATUS_OK)
            ->withHeader('Content-Type', $mime)
            ->withHeader('Content-Length', (string) filesize($realFile))
            ->withHeader('Content-Disposition', 'inline; filename="' . basename($realFile) . '"')
            ->withHeader('Cache-Control', 'private, max-age=3600')
            ->withBody($stream);
    }
}
