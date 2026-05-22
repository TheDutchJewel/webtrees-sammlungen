<?php

declare(strict_types=1);

namespace Sammlungen\Http\RequestHandlers;

use Sammlungen\Cache\ApcuCacheService;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Http\Exceptions\HttpAccessDeniedException;
use Fisharebest\Webtrees\I18N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * POST /archiv/admin/cache-clear
 *
 * Leert den gesamten APCu-Cache des Moduls manuell.
 * Redirect zurück zur Admin-Konfigurationsseite.
 */
class CacheClear implements RequestHandlerInterface
{
    public function __construct(
        private readonly ApcuCacheService $cache
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!Auth::isAdmin()) {
            throw new HttpAccessDeniedException(
                I18N::translate('Sie haben keine Berechtigung für diese Aktion.')
            );
        }

        $this->cache->flush();

        // PHP OPcache zurücksetzen damit geänderte Moduldateien sofort wirken.
        // Auf Synology: sudo synopkg restart WebStation (falls opcache_reset nicht reicht)
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        FlashMessages::addMessage(
            I18N::translate('Der Sammlungen-Cache wurde erfolgreich geleert.'),
            'success'
        );

        return redirect(route('sammlungen.admin.config'));
    }
}
