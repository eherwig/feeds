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

class rex_yfeed_stream_instagram_tag extends rex_yfeed_stream_abstract
{
    public function getTypeName()
    {
        return rex_i18n::msg('yfeed_instagram_tag');
    }

    public function getTypeParams()
    {
        return [
            [
                'label' => rex_i18n::msg('yfeed_instagram_tag_name'),
                'name' => 'tag',
                'type' => 'string',
                'notice' => rex_i18n::msg('yfeed_instagram_tag_without_prefix'),
            ],
            [
                'label' => rex_i18n::msg('yfeed_instagram_count'),
                'name' => 'count',
                'type' => 'select',
                'options' => [5 => 5, 10 => 10, 15 => 15, 20 => 20, 30 => 30, 50 => 50, 75 => 75, 100 => 100],
                'default' => 10,
            ],
            // [
            //     'label' => rex_i18n::msg('yfeed_twitter_result_type'),
            //     'name' => 'result_type',
            //     'type' => 'select',
            //     'options' => [
            //         'mixed' => rex_i18n::msg('yfeed_twitter_result_type_mixed'),
            //         'recent' => rex_i18n::msg('yfeed_twitter_result_type_recent'),
            //         'popular' => rex_i18n::msg('yfeed_twitter_result_type_popular'), ],
            //     'default' => 'mixed',
            // ],
        ];
    }

    public function fetch()
    {
        $instagram = new Instagram(rex_config::get('yfeed', 'instagram_access_token'));

        $data = $instagram->getTagMedia($this->typeParams['tag'], ['count' => $this->typeParams['count']]);

        /** @var Media $instagramItem */
        foreach ($data as $instagramItem) {
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
}
