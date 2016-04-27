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

use TwitterOAuth\Auth\ApplicationOnlyAuth;
use TwitterOAuth\Serializer\ObjectSerializer;

class rex_yfeed_stream_twitter_user_timeline extends rex_yfeed_stream_abstract
{
    public function getTypeName()
    {
        return rex_i18n::msg('yfeed_twitter_user_timeline');
    }

    public function getTypeParams()
    {
        return [
            [
                'label' => rex_i18n::msg('yfeed_twitter_screen_name'),
                'name' => 'screen_name',
                'type' => 'string',
            ],
            [
                'label' => rex_i18n::msg('yfeed_twitter_count'),
                'name' => 'count',
                'type' => 'select',
                'options' => [5, 10, 15, 20, 30, 50, 75, 100],
                'default' => 10,
            ],
            [
                'label' => rex_i18n::msg('yfeed_twitter_exclude_replies'),
                'name' => 'exclude_replies',
                'type' => 'select',
                'options' => ['1' => rex_i18n::msg('yes'), '0' => rex_i18n::msg('no')],
                'default' => 1,
            ],
        ];
    }

    public function fetch()
    {
        $credentials = [
            'consumer_key' => rex_config::get('yfeed', 'twitter_consumer_key'),
            'consumer_secret' => rex_config::get('yfeed', 'twitter_consumer_secret'),
            'oauth_token' => rex_config::get('yfeed', 'twitter_oauth_token'),
            'oauth_token_secret' => rex_config::get('yfeed', 'twitter_oauth_token_secret'),
        ];
        $auth = new ApplicationOnlyAuth($credentials, new ObjectSerializer());
        $items = $auth->get('statuses/user_timeline', $this->typeParams);
        /*
        echo '<pre>'; print_r($this->fields); echo '</pre><hr />';
        echo '<pre>'; print_r($auth->getHeaders()); echo '</pre>';
        echo '<pre>'; print_r($item); echo '</pre><hr />';
        exit();
        */
        foreach ($items as $twitterItem) {
            $item = new rex_yfeed_item($this->streamId, $twitterItem->id);
            $item->setContentRaw($twitterItem->text);
            $item->setContent(strip_tags($twitterItem->text));

            if (isset($twitterItem->entities->urls) && isset($twitterItem->entities->urls->url)) {
                $item->setUrl($twitterItem->entities->urls->url);
            }
            $date = new DateTime($twitterItem->created_at);
            $item->setDate($date->format('U'));

            $item->setAuthor($twitterItem->user->name);
            $item->setLanguage($twitterItem->lang);
            $item->setRaw($twitterItem);

            if ($item->changedByUser()) {
                ++$this->countNotUpdatedChangedByUser;
            } elseif ($item->exists()) {
                ++$this->countUpdated;
            } else {
                ++$this->countAdded;
            }

            $item->save();
        }
    }
}
