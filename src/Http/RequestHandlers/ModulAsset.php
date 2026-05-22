<?php

declare(strict_types=1);

namespace Sammlungen\Http\RequestHandlers;

use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * GET /archiv/asset/{datei}
 *
 * Liefert statische Asset-Dateien des Moduls aus (JS, CSS). Nur Whitelist-Dateien
 * unter resources/js/ und resources/css/. Liefert mit langem Cache-Header,
 * Cache-Busting via ?v=… in der einbindenden Seite.
 */
final class ModulAsset implements RequestHandlerInterface
{
    /** Erlaubte Endungen + Content-Type */
    private const ERLAUBTE_TYPEN = [
        'js'  => 'application/javascript; charset=UTF-8',
        'css' => 'text/css; charset=UTF-8',
    ];

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface   $streamFactory,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $datei = Validator::queryParams($request)->string('datei', '');

        // Sicherheits-Check: keine Pfad-Trenner, nur erlaubte Endungen
        if ($datei === '' || preg_match('/[\/\\\\]/', $datei) || str_starts_with($datei, '.')) {
            return $this->responseFactory->createResponse(404);
        }

        $endung = strtolower((string) (pathinfo($datei, PATHINFO_EXTENSION)));
        if (!isset(self::ERLAUBTE_TYPEN[$endung])) {
            return $this->responseFactory->createResponse(404);
        }

        $unterordner = $endung;  // 'js' oder 'css'
        $voll        = __DIR__ . '/../../../resources/' . $unterordner . '/' . $datei;

        if (!is_file($voll) || !is_readable($voll)) {
            return $this->responseFactory->createResponse(404);
        }

        return $this->responseFactory
            ->createResponse(200)
            ->withHeader('Content-Type', self::ERLAUBTE_TYPEN[$endung])
            ->withHeader('Cache-Control', 'public, max-age=86400')
            ->withBody($this->streamFactory->createStreamFromFile($voll));
    }
}
