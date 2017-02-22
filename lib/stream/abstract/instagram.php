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

use Instagram\Instagram;
use Instagram\Media;

abstract class rex_yfeed_stream_instagram_abstract extends rex_yfeed_stream_abstract
{
    public function fetch()
    {
        $accessToken = rex_config::get('yfeed', 'instagram_access_token');

        if ($accessToken) {
            $this->fetchOfficialApi($accessToken);

            return;
        }

        $this->fetchFrontendApi();
    }

    /**
     * @param Instagram $instagram
     *
     * @return Media[]
     */
    abstract protected function fetchItemsFromOfficialApi(Instagram $instagram);

    /**
     * @return mixed
     */
    abstract protected function fetchItemsFromFrontendApi();

    private function fetchOfficialApi($accessToken)
    {
        $instagram = new Instagram($accessToken);
        $instagramItems = $this->fetchItemsFromOfficialApi($instagram);

        foreach ($instagramItems as $instagramItem) {
            $item = new rex_yfeed_item($this->streamId, $instagramItem->getId());
            $item->setTitle($instagramItem->getCaption());

            $item->setUrl($instagramItem->getLink());
            $item->setDate(new DateTime($instagramItem->getCreatedTime('Y-m-d H:i:s')));

            $item->setMedia($instagramItem->getStandardResImage()->url);

            $item->setAuthor($instagramItem->getUser()->getFullName());
            $item->setRaw($instagramItem);

            $this->updateCount($item);
            $item->save();
        }
    }

    private function fetchFrontendApi()
    {
        $instagramItems = $this->fetchItemsFromFrontendApi();

        foreach ($instagramItems as $instagramItem) {
            $item = new rex_yfeed_item($this->streamId, $instagramItem->id);
            $item->setTitle(isset($instagramItem->caption) ? $instagramItem->caption : null);

            $item->setUrl('https://www.instagram.com/p/'.$instagramItem->code.'/');
            $item->setDate(new DateTime('@'.$instagramItem->date));

            if (!$instagramItem->is_video) {
                $item->setMedia($instagramItem->display_src);
            }

            $item->setAuthor($instagramItem->owner->full_name);
            $item->setRaw($instagramItem);

            $this->updateCount($item);
            $item->save();
        }
    }
}
