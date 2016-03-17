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
        $credentials = array(
            'consumer_key' => rex_config::get('yfeed', 'twitter_consumer_key'),
            'consumer_secret' => rex_config::get('yfeed', 'twitter_consumer_secret'),
            'oauth_token' => rex_config::get('yfeed', 'twitter_oauth_token'),
            'oauth_token_secret' => rex_config::get('yfeed', 'twitter_oauth_token_secret'),
        );
        $auth = new ApplicationOnlyAuth($credentials, new ObjectSerializer());
        $items = $auth->get('statuses/user_timeline', $this->typeParams);
        /*
        echo '<pre>'; print_r($this->fields); echo '</pre><hr />';
        echo '<pre>'; print_r($auth->getHeaders()); echo '</pre>';
        echo '<pre>'; print_r($response); echo '</pre><hr />';
        exit();
        */
        foreach ($items as $item) {

            $response = new rex_yfeed_response($this->streamId, $item->id);
            $response->setContentRaw($item->text);
            $response->setContent(strip_tags($item->text));

            if (isset($item->entities->urls) && isset($item->entities->urls->url)) {
                $response->setUrl($item->entities->urls->url);
            }
            $date = new DateTime($item->created_at);
            $response->setDate($date->format('U'));

            $response->setAuthor($item->user->name);
            $response->setLanguage($item->lang);
            $response->setRaw($item);

            if ($response->changedByUser()) {
                $this->countNotUpdatedChangedByUser++;
            } elseif ($response->exists()) {
                $this->countUpdated++;
            } else {
                $this->countAdded++;
            }

            $response->save();
        }
    }
}
