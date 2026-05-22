<?php

declare(strict_types=1);

namespace Sammlungen\Http\RequestHandlers;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Gemeinsame Basis für alle Sammlungen-Request-Handler.
 *
 * Stellt $tree und $module aus dem Request bereit und
 * delegiert die eigentliche Logik an handle() der Subklasse.
 */
abstract class AbstractArchivHandler implements RequestHandlerInterface
{
    use ViewResponseTrait;

    /**
     * Modul-Name (wird für View-Namespace-Auflösung verwendet).
     * Muss mit SammlungenModule::name() übereinstimmen.
     */
    protected const MODULE_NAME = '_sammlungen_';

    // ---------------------------------------------------------------
    // PSR-15 Entry Point
    // ---------------------------------------------------------------

    final public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Tree|null $tree */
        try { $tree = Validator::attributes($request)->tree(); } catch (\Throwable $e) { $tree = null; }

        // Nicht eingeloggt oder kein Mitglied → zur Login-Seite umleiten
        if ($tree === null || !Auth::isMember($tree)) {
            $params = ['url' => (string) $request->getUri()];
            if ($tree !== null) {
                $params['tree'] = $tree->name();
            } else {
                // Tree-Name aus URL-Attributen lesen (auch ohne Auth-Prüfung)
                try {
                    $params['tree'] = Validator::attributes($request)->string('tree', '');
                } catch (\Throwable) {}
            }
            return redirect(route(\Fisharebest\Webtrees\Http\RequestHandlers\LoginPage::class, $params));
        }

        return $this->respond($request, $tree);
    }

    // ---------------------------------------------------------------
    // Template-Methode – muss von jeder Archiv-Seite implementiert werden
    // ---------------------------------------------------------------

    abstract protected function respond(
        ServerRequestInterface $request,
        ?Tree $tree
    ): ResponseInterface;

    // ---------------------------------------------------------------
    // Hilfsmethoden
    // ---------------------------------------------------------------

    /**
     * Gibt den voll qualifizierten View-Namen (Namespace::template) zurück.
     *
     * Beispiel: viewName('archiv')  →  'sammlungen::archiv'
     */
    protected function viewName(string $template): string
    {
        return self::MODULE_NAME . '::' . $template;
    }
}
