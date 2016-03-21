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
        $credentials = array(
            'app_id' => rex_config::get('yfeed', 'facebook_app_id'),
            'app_secret' => rex_config::get('yfeed', 'facebook_app_secret'),
            'default_graph_version' => 'v2.5',
        );
        $auth = new Facebook\Facebook($credentials);
        $auth->setDefaultAccessToken('user-access-token');
        //$helper = $auth->getPageTabHelper();
        //$token = $helper->getAccessToken();
        //echo '>>' . $token;
        try {
            //$response = $auth->get('/me', $token);
            $response = $auth->request('GET', '/me/feed');
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo rex_view::error('Graph returned an error: ' . $e->getMessage());
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo rex_view::error('Facebook SDK returned an error: ' . $e->getMessage());
            exit;
        }

        //$graphNode = $response->getGraphNode();
        /*
        echo '<pre>'; print_r($this->fields); echo '</pre><hr />';
        echo '<pre>'; print_r($auth->getHeaders()); echo '</pre>';
        */
        echo '<pre>'; print_r($response); echo '</pre><hr />';
        exit();
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
