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

class rex_yfeed_stream_facebook_page_feed extends rex_yfeed_stream_facebook
{
    public function getTypeName()
    {
        return rex_i18n::msg('yfeed_facebook_page_feed');
    }

    public function getTypeParams()
    {
        return array_merge([
            [
                'label' => rex_i18n::msg('yfeed_facebook_page_id'),
                'name' => 'page_id',
                'type' => 'string',
            ],
        ], parent::getTypeParams());
    }

    protected function getEndpoint()
    {
        return sprintf('/%s/%s', $this->typeParams['page_id'], $this->typeParams['result_type']);
    }
}
