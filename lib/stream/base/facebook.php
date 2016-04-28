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

abstract class rex_yfeed_stream_facebook extends rex_yfeed_stream_abstract
{

    public function getTypeParams()
    {
        return [
            [
                'label' => rex_i18n::msg('yfeed_facebook_token'),
                'name' => 'token',
                'type' => 'string',
            ],
            [
                'label' => rex_i18n::msg('yfeed_facebook_result_type'),
                'name' => 'result_type',
                'type' => 'select',
                'options' => [
                    'feed' => rex_i18n::msg('yfeed_facebook_result_type_feed'),
                    'posts' => rex_i18n::msg('yfeed_facebook_result_type_posts'),
                    'tagged' => rex_i18n::msg('yfeed_facebook_result_type_tagged'),
                ],
                'default' => 'feed',
            ],
            [
                'label' => rex_i18n::msg('yfeed_facebook_count'),
                'name' => 'count',
                'type' => 'select',
                'options' => [5 => 5, 10 => 10, 15 => 15, 20 => 20, 30 => 30, 50 => 50, 75 => 75, 100 => 100],
                'default' => 10,
            ],
        ];
    }

    public function fetch()
    {
        $fb = $this->getFacebook();

        $fields = 'id,from,story,message,link,created_time,attachments';
        $url = $this->getEndpoint().sprintf('?locale=de&fields=%s&limit=%d', $fields, $this->typeParams['count']);
        $items = $fb->get($url)->getGraphEdge();

        /** @var Facebook\GraphNodes\GraphNode $facebookItem */
        foreach ($items as $facebookItem) {
            $item = new rex_yfeed_item($this->streamId, $facebookItem->getField('id'));
            $item->setTitle($facebookItem->getField('story'));
            $item->setContentRaw($facebookItem->getField('message'));
            $item->setContent(strip_tags($facebookItem->getField('message')));
            $item->setUrl($facebookItem->getField('link'));
            $item->setDate($facebookItem->getField('created_time'));

            $from = $facebookItem->getField('from');
            if ($from && $name = $from->getField('name')) {
                $item->setAuthor($name);
            }

            $attachments = $facebookItem->getField('attachments');
            if ($attachments) {
                /** @var Facebook\GraphNodes\GraphNode $attachment */
                foreach ($attachments as $attachment) {
                    if ('photo' !== $attachment->getField('type') || !$media = $attachment->getField('media')) {
                        continue;
                    }
                    /** @var Facebook\GraphNodes\GraphNode $image */
                    $image = $media->getField('image');
                    if ($image) {
                        $item->setMedia($image->getField('src'));
                        break;
                    }
                }
            }

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

    abstract protected function getEndpoint();

    /**
     * @return \Facebook\Facebook
     */
    protected function getFacebook()
    {
        static $facebook;

        if (!$facebook) {
            $credentials = [
                'app_id' => rex_config::get('yfeed', 'facebook_app_id'),
                'app_secret' => rex_config::get('yfeed', 'facebook_app_secret'),
                'default_graph_version' => 'v2.6',
            ];
            $facebook = new Facebook\Facebook($credentials);
            $this->checkAccessToken();
            $facebook->setDefaultAccessToken($this->typeParams['token']);
        }

        return $facebook;
    }

    private function checkAccessToken()
    {
        $oauth = $this->getFacebook()->getOAuth2Client();
        $metaData = $oauth->debugToken($this->typeParams['token']);

        if ($metaData->getExpiresAt()->getTimestamp() > time() + 60 * 60 * 24 * 50) {
            return;
        }

        try {
            $code = $oauth->getCodeFromLongLivedAccessToken($this->typeParams['token']);
            $newToken = $oauth->getAccessTokenFromCode($code);
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            if (false === strpos($e->getMessage(), 'long-lived')) {
                throw $e;
            }

            $newToken = $oauth->getLongLivedAccessToken($this->typeParams['token']);
        }

        if (!$newToken) {
            return;
        }

        $this->typeParams['token'] = (string) $newToken;
        rex_sql::factory()
            ->setTable(rex_yfeed_stream::table())
            ->setWhere('id = :id', ['id' => $this->streamId])
            ->setArrayValue('type_params', $this->typeParams)
            ->update();
    }
}
