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



class rex_yfeed_item
{
    private
        $streamId,
        $uid,
        $title,
        $content,
        $contentRaw,
        $url,
        $date,
        $author,
        $language,
        $media,
        $raw,

        $primaryId,
        $debug = false,
        $changedByUser,
        $exists;


    public function __construct($streamId, $uid)
    {
        $this->primaryId = 0;
        $this->streamId = (int)$streamId;
        $this->uid = $uid;
        $this->exists = false;
        $this->changedByUser = false;

        $sql = rex_sql::factory();
        $sql->setQuery('
            SELECT      `id`,
                        `changed_by_user`
            FROM        ' . self::table() . '
            WHERE       `stream_id` = :stream_id
                AND     `uid` = :uid
            LIMIT       1',
            [
                'stream_id' => $this->streamId,
                'uid' => $this->uid
            ]
        );

        if ($sql->getRows()) {
            if ($sql->getValue('changed_by_user') == '1') {
                $this->changedByUser = true;
            } else {
                $this->primaryId = $sql->getValue('id');
                $this->exists = true;
            }
        }
    }

    public static function table()
    {
        return rex::getTable('yfeed_item');
    }


    public function setTitle($value)
    {
        $this->title = $value;
    }
    public function setContentRaw($value)
    {
        $this->contentRaw = $value;
    }

    public function setContent($value)
    {
        $this->content = $value;
    }

    public function setUrl($value)
    {
        $this->url = $value;
    }
    public function setDate($value)
    {
        if (!rex_yfeed_helper::isValidTimeStamp($value)) {
            throw new rex_exception('Unexpected date format. The date must be a valid timestamp.');
        }
        $this->date = $value;
    }
    public function setAuthor($value)
    {
        $this->author = $value;
    }
    public function setLanguage($value)
    {
        $this->language = $value;
    }
    public function setMedia($path)
    {
        $dataObject = DataURI\Data::buildFromUrl($path);
        $this->media = DataURI\Dumper::dump($dataObject);
    }

    public function setRaw($value)
    {
        $this->raw = json_encode( (array)$value );
    }


    public function exists()
    {
        return $this->exists;
    }


    public function changedByUser()
    {
        return $this->changedByUser;
    }

    public function save()
    {
        if ($this->changedByUser) {
            return;
        }

        $sql = rex_sql::factory();
        $sql->setDebug($this->debug);
        $sql->setTable(self::table());

        if ($this->title) {
            $sql->setValue('title', $this->title);
        }
        if ($this->content) {
            $sql->setValue('content', $this->content);
        }
        if ($this->contentRaw) {
            $sql->setValue('content_raw', $this->contentRaw);
        }
        if ($this->url) {
            $sql->setValue('url', $this->url);
        }
        if ($this->date) {
            $sql->setValue('date', $this->date);
        }
        if ($this->author) {
            $sql->setValue('author', $this->author);
        }
        if ($this->language) {
            $sql->setValue('language', $this->language);
        }
        if ($this->media) {
            $sql->setValue('media', $this->media);
        }
        if ($this->raw) {
            $sql->setValue('raw', $this->raw);
        }

        if ($this->exists) {
            $where = '`id` = :id AND `uid` = :uid';
            $params = ['id' => $this->primaryId, 'uid' => $this->uid];
            $sql->setWhere($where, $params);
            $sql->addGlobalUpdateFields();
            $sql->update();
        } else {
            $sql->setValue('uid', $this->uid);
            $sql->setValue('stream_id', $this->streamId);
            $sql->addGlobalCreateFields();
            $sql->addGlobalUpdateFields();
            $sql->insert();
        }
    }

}
