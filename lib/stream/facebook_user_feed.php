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

class rex_yfeed_stream_facebook_user_feed extends rex_yfeed_stream_abstract
{
    public function getTypeName()
    {
        return rex_i18n::msg('yfeed_facebook_user_feed');
    }

    public function getTypeParams()
    {
        return [
            [
                'label' => rex_i18n::msg('yfeed_facebook_user_id'),
                'name' => 'user_id',
                'type' => 'string',
            ],
        ];
    }

    public function fetch()
    {
        $credentials = [
            'app_id' => rex_config::get('yfeed', 'facebook_app_id'),
            'app_secret' => rex_config::get('yfeed', 'facebook_app_secret'),
            'default_graph_version' => 'v2.5',
        ];
        $auth = new Facebook\Facebook($credentials);
        $auth->setDefaultAccessToken('user-access-token');
        //$helper = $auth->getPageTabHelper();
        //$token = $helper->getAccessToken();
        //echo '>>' . $token;
        try {
            //$item = $auth->get('/me', $token);
            $response = $auth->request('GET', '/me/feed');
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo rex_view::error('Graph returned an error: ' . $e->getMessage());
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo rex_view::error('Facebook SDK returned an error: ' . $e->getMessage());
            exit;
        }

        //$graphNode = $item->getGraphNode();
        /*
        echo '<pre>'; print_r($this->fields); echo '</pre><hr />';
        echo '<pre>'; print_r($auth->getHeaders()); echo '</pre>';
        */
        echo '<pre>';
        print_r($item);
        echo '</pre><hr />';
        exit();
        foreach ($items as $facebookItem) {
            $item = new rex_yfeed_item($this->streamId, $facebookItem->id);
            $item->setContentRaw($facebookItem->text);
            $item->setContent(strip_tags($facebookItem->text));

            if (isset($facebookItem->entities->urls) && isset($facebookItem->entities->urls->url)) {
                $item->setUrl($facebookItem->entities->urls->url);
            }
            $date = new DateTime($facebookItem->created_at);
            $item->setDate($date->format('U'));

            $item->setAuthor($facebookItem->user->name);
            $item->setLanguage($facebookItem->lang);
            $item->setRaw($facebookItem);

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
