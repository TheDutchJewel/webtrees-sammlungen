<?php

declare(strict_types=1);

namespace Sammlungen\Http\RequestHandlers;

use Sammlungen\ViewModel\SammlungenViewModel;
use Fisharebest\Webtrees\Tree;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * GET /archiv/sammlungen[?kategorie=slug]
 * GET /archiv/sammlungen?kategorie=__unlinked__[&typ=photo]
 *
 * Vereint drei Quellen:
 *   (a) Automatisch aus media_file.source_media_type
 *   (b) Manuell gepflegte Sammlungen aus sammlungen_collection
 *   (c) Nicht-eingebundene Medien (kein OBJE-Link, Sonder-Slug __unlinked__)
 *
 * Datenaufbereitung liegt im SammlungenViewModel; dieser Handler ist nur Glue-Code.
 */
class SammlungenPage extends AbstractArchivHandler
{
    public function __construct(
        private readonly SammlungenViewModel $viewModel,
    ) {}

    protected function respond(
        ServerRequestInterface $request,
        ?Tree $tree
    ): ResponseInterface {
        return $this->viewResponse(
            $this->viewName('sammlungen'),
            $this->viewModel->aufbauen($tree, $request->getQueryParams())
        );
    }
}
