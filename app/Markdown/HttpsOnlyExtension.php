<?php

namespace App\Markdown;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\ExtensionInterface;

/** Enlaces e imágenes que no son https:// (http:, data:, javascript:, relativos…) quedan como su texto, sin etiqueta. */
class HttpsOnlyExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addEventListener(DocumentParsedEvent::class, function (DocumentParsedEvent $event) {
            $blocked = [];
            $walker = $event->getDocument()->walker();
            while ($step = $walker->next()) {
                $node = $step->getNode();
                if ($step->isEntering() && ($node instanceof Link || $node instanceof Image)
                    && ! str_starts_with(strtolower($node->getUrl()), 'https://')) {
                    $blocked[] = $node;
                }
            }

            foreach ($blocked as $node) {
                foreach ([...$node->children()] as $child) {
                    $node->insertBefore($child);
                }
                $node->detach();
            }
        });
    }
}
