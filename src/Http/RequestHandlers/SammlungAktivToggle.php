<?php

declare(strict_types=1);

namespace Sammlungen\Http\RequestHandlers;

use Sammlungen\Service\CollectionService;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * POST /tree/{tree}/archiv/admin/sammlungen/toggle-aktiv
 *
 * Schaltet das aktiv-Flag einer Sammlung um. Gibt JSON zurück.
 */
final class SammlungAktivToggle implements RequestHandlerInterface
{
    public function __construct(
        private readonly CollectionService        $collectionService,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface   $streamFactory,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $user = Validator::attributes($request)->user();

        if (!Auth::isManager($tree, $user)) {
            return $this->json(['ok' => false, 'fehler' => 'Keine Berechtigung.'], 403);
        }

        $body  = (array) $request->getParsedBody();
        $id    = (int) ($body['id'] ?? 0);
        $aktiv = (bool) ($body['aktiv'] ?? false);

        if ($id === 0) {
            return $this->json(['ok' => false, 'fehler' => 'Fehlende ID.'], 400);
        }

        $sammlung = $this->collectionService->findeNachId($id);
        if ($sammlung === null || (int) $sammlung->gedcom_id !== $tree->id()) {
            return $this->json(['ok' => false, 'fehler' => 'Sammlung nicht gefunden.'], 404);
        }

        $this->collectionService->setAktiv($id, $aktiv);

        return $this->json(['ok' => true, 'aktiv' => $aktiv]);
    }

    private function json(array $data, int $status = 200): ResponseInterface
    {
        $body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        return $this->responseFactory
            ->createResponse($status)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withBody($this->streamFactory->createStream($body));
    }
}
