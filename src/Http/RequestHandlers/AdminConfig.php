<?php

declare(strict_types=1);

namespace Sammlungen\Http\RequestHandlers;

use Sammlungen\Cache\ApcuCacheService;
use Sammlungen\SammlungenModule;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Http\Exceptions\HttpAccessDeniedException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Services\TreeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AdminConfig implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private ApcuCacheService $cache;

    public function __construct(
        private readonly SammlungenModule $module,
        private readonly TreeService          $treeService,
    ) {
        $this->cache = new ApcuCacheService($module->cacheTtl());
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!Auth::isAdmin()) {
            throw new HttpAccessDeniedException(
                I18N::translate('Sie haben keine Berechtigung für diese Seite.')
            );
        }

        if ($request->getMethod() === 'POST') {
            return $this->save($request);
        }

        return $this->showForm();
    }

    private function showForm(): ResponseInterface
    {
        // Alle vorhandenen Bäume – für den "Sammlungen verwalten"-Link in der View.
        $trees = $this->treeService->all();

        return $this->viewResponse(
            '_sammlungen_::admin-config',
            [
                'title'         => I18N::translate('Sammlungen – Einstellungen'),
                'module'        => $this->module,
                'cacheTtl'      => $this->module->cacheTtl(),
                'perPage'       => $this->module->perPage(),
                'showFooter'    => false,
                'tree'          => $trees->first(),   // erster Baum als Default, kann null sein
                'trees'         => $trees,            // alle Bäume für Selektor
                'apcuAvailable' => $this->cache->isApcuAvailable(),
            ]
        );
    }

    private function save(ServerRequestInterface $request): ResponseInterface
    {
        $params = (array) $request->getParsedBody();

        $cacheTtl = max(60, min(86400, (int) ($params[SammlungenModule::SETTING_CACHE_TTL] ?? 900)));
        $perPage  = max(10, min(200,   (int) ($params[SammlungenModule::SETTING_PER_PAGE]  ?? 50)));

        $this->module->setPreference(SammlungenModule::SETTING_CACHE_TTL, (string) $cacheTtl);
        $this->module->setPreference(SammlungenModule::SETTING_PER_PAGE,  (string) $perPage);

        $this->cache->flush();

        return redirect(route('sammlungen.admin.config'));
    }
}
