<?php

/**
 * This file is part of the Feeds package.
 *
 * @author FriendsOfREDAXO
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class rex_feeds_stream_google_places extends rex_feeds_stream_abstract
{
    public function getTypeName()
    {
        return rex_i18n::msg('feeds_places_name');
    }

    public function getTypeParams()
    {
        return [
            [
                'label' => rex_i18n::msg('feeds_places_api_key'),
                'name' => 'api_key',
                'type' => 'string',
                'notice' => rex_i18n::msg('feeds_places_api_key_notice'),
            ],
            [
                'label' => rex_i18n::msg('feeds_places_id'),
                'name' => 'id',
                'type' => 'string',
                'notice' => rex_i18n::msg('feeds_places_id_notice'),
            ],
            [
                'label' => rex_i18n::msg('feeds_places_photos'),
                'name' => 'photos',
                'type' => 'select',
                'options' => [0 => "Nein", 1 => "Ja"],
                'default' => 1,
            ],
            [
                'label' => rex_i18n::msg('feeds_places_reviews'),
                'name' => 'reviews',
                'type' => 'select',
                'options' => [0 => "Nein", 1 => "Ja"],
                'default' => 1,
            ],
        ];
    }
    public function fetch()
    {
        $credentials = [
            'api_key' => $this->typeParams['api_key'],
            'places_id' => $this->typeParams['id'],
        ];
        $place_details = [];
        try {
            $socket = rex_socket::factory("maps.googleapis.com", 443, true);
            $socket->setPath("/maps/api/place/details/json?language=de&place_id=".$credentials['places_id']."&key=".$credentials['api_key']);
            $response = $socket->doGet();
            if ($response->isOk()) {
                $result = json_decode($response->getBody());
                if ($result->status != "OK") {
                    echo rex_view::error($result->error_message);
                } else {
                    $place_details = $result->result;

                    $item = new rex_feeds_item($this->streamId, $credentials['places_id']);

                    $item->setTitle($place_details->name);

                    $item->setContentRaw($place_details->formatted_address);
                    $item->setContent(strip_tags($place_details->adr_address));
            
                    $item->setUrl($place_details->url);
                    $item->setDate(new DateTime());
            
                    $item->setAuthor("Google Places");
                    $item->setLanguage("de");
                    $item->setRaw(serialize($place_details));
            
                    $this->updateCount($item);
                    $item->save();

                    // Google Places / My Business Photos
                    $images = [];
                    $item = null;
                    if ($this->typeParams['photos'] == 1 && is_array($place_details->photos)) {
                        foreach ($place_details->photos as $photo) {
                            $url = "https://maps.googleapis.com/maps/api/place/photo?photoreference=".$photo->photo_reference."&maxwidth=1200&place_id=".$credentials['places_id']."&key=".$credentials['api_key'];

                            $item = new rex_feeds_item($this->streamId, $photo->photo_reference);
                            $item->setTitle(strip_tags(implode(",", $photo->html_attributions)));
                            $item->setUrl($url);
                            $item->setAuthor(implode(",", $photo->html_attributions));
                            $item->setRaw(serialize($photo));
                            $item->setMedia($url);
                            $this->updateCount($item);
                            $item->save();
                        }
                    }
                    // Google Places / My Business Reviews
                    $reviews = [];
                    $item = null;
                    if ($this->typeParams['reviews'] == 1 && is_array($place_details->reviews)) {
                        foreach ($place_details->reviews as $review) {
                            $item = new rex_feeds_item($this->streamId, md5($review->author_url));
                            $item->setTitle($review->author_name);
                            $item->setUrl($url);
                            $item->setAuthor($review->author_name);
                            $item->setUrl($review->author_url);
                            $item->setMedia($review->profile_photo_url);
                            $item->setContent($review->text);
                            $item->setContentRaw($review->rating);
                            $item->setDate(new DateTime(date('Y-m-d H:i:s', $review->time)));
                            $this->updateCount($item);
                            $item->save();
                        }
                    }
                }
            }
        } catch (rex_socket_exception $e) {
            echo rex_view::error($e->getMessage());
        }
    }
}
