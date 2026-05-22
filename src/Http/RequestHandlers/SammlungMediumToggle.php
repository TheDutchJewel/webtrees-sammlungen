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
 * POST /tree/{tree}/archiv/sammlung-medium
 *
 * Fügt ein Medienobjekt (m_id) zu einer manuellen Sammlung hinzu
 * oder entfernt es daraus. Gibt JSON zurück.
 */
final class SammlungMediumToggle implements RequestHandlerInterface
{
    public function __construct(
        private readonly CollectionService       $collectionService,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface  $streamFactory,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $user = Validator::attributes($request)->user();

        if (!Auth::isManager($tree, $user)) {
            return $this->json(['ok' => false, 'fehler' => 'Keine Berechtigung.'], 403);
        }

        $body         = (array) $request->getParsedBody();
        $collectionId = (int) ($body['collection_id'] ?? 0);
        $pfad         = trim((string) ($body['pfad'] ?? ''));
        $mId          = trim((string) ($body['m_id'] ?? '')) ?: null;
        $aktion       = (string) ($body['aktion'] ?? 'hinzufuegen');

        if ($collectionId === 0 || $pfad === '') {
            return $this->json(['ok' => false, 'fehler' => 'Fehlende Parameter (pfad).'], 400);
        }

        // Nur manuelle Sammlungen (kein Top-Level-Ordner) erlaubt
        $sammlung = $this->collectionService->findeNachId($collectionId);
        if ($sammlung === null) {
            return $this->json(['ok' => false, 'fehler' => 'Sammlung nicht gefunden.'], 400);
        }

        if ($aktion === 'entfernen') {
            $this->collectionService->pfadEntfernen($tree, $collectionId, $pfad);
            $istDrin = false;
        } else {
            $this->collectionService->pfadZuordnen($tree, $collectionId, $pfad, $mId ?: null);
            $istDrin = true;
        }

        return $this->json(['ok' => true, 'istDrin' => $istDrin]);
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
