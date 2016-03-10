<?php

/**
 * This file is part of the YFeed package.
 *
 * @author (c) Yakamara Media GmbH & Co. KG
 * @author thomas.blum@redaxo.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PicoFeed\Parser\Item;
use PicoFeed\PicoFeedException;
use PicoFeed\Reader\Reader;


class rex_yfeed_stream_rss extends rex_yfeed_stream_abstract
{
    public function getTypeName()
    {
        return rex_i18n::msg('yfeed_rss_feed');
    }

    public function getTypeParams()
    {
        return [
            [
                'label' => rex_i18n::msg('yfeed_rss_url'),
                'name' => 'url',
                'type' => 'string',
            ],
        ];

    }

    public function fetch()
    {
        $reader = new Reader();
        $resource = $reader->download($this->typeParams['url'], $this->lastModified, $this->etag);
        if (!$resource->isModified()) {
            return;
        }
        $parser = $reader->getParser(
            $resource->getUrl(),
            $resource->getContent(),
            $resource->getEncoding()
        );
        $parser->disableContentFiltering();
        $feed = $parser->execute();

        /** @type Item $item */
        foreach ($feed->getItems() as $item) {

            $response = new rex_yfeed_response($this->streamId, $item->getId());
            $response->setTitle($item->getTitle());
            $response->setContentRaw($item->getContent());

            $parser->filterItemContent($feed, $item);
            $response->setContent(strip_tags($item->getContent()));

            $response->setUrl($item->getUrl());
            $response->setDate($item->getDate()->format('U'));
            $response->setAuthor($item->getAuthor());
            $response->setLanguage($item->getLanguage());
            if ($item->getEnclosureUrl()) {
                $response->setMedia($item->getEnclosureUrl());
            }
            $response->setRaw($item);

            if ($response->exists()) {
                $this->countUpdated++;
            } else {
                $this->countAdded++;
            }

            $response->save();
        }
    }
}
