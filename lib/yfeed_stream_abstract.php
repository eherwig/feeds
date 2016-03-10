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

abstract class rex_yfeed_stream_abstract
{
    protected
        // Type parameter
        $typeParams = [],

        $streamId,
        $etag,
        $lastModified,
        $countAdded = 0,
        $countUpdated = 0;

    public function setTypeParams(array $params)
    {
        $this->typeParams = $params;
    }

    public function setStreamId($value)
    {
        $this->streamId = $value;
    }

    public function setEtag($value)
    {
        $this->etag = $value;
    }

    public function setLastModified($value)
    {
        $this->lastModified = $value;
    }

    public function getAddedCount()
    {
        return $this->countAdded;
    }

    public function getUpdateCount()
    {
        return $this->countUpdated;
    }


    abstract public function getTypeName();

    abstract public function getTypeParams();

    abstract public function fetch();
}
