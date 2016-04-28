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

class rex_yfeed_stream_facebook_user_feed extends rex_yfeed_stream_facebook
{
    public function getTypeName()
    {
        return rex_i18n::msg('yfeed_facebook_user_feed');
    }

    protected function getEndpoint()
    {
        return sprintf('/me/%s', $this->typeParams['result_type']);
    }
}
