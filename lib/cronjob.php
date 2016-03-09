<?php

class rex_cronjob_yfeed extends rex_cronjob
{
    public function execute()
    {
        $feeds = rex_yfeed::getAll();
        $success = 0;
        $error = 0;
        foreach ($feeds as $feed) {
            if ($feed->fetch()) {
                $success++;
            } else {
                $error++;
            }
        }
        $this->setMessage($success . ' feeds succeeded, ' . $error . ' failed');
        return !$error;
    }

    public function getTypeName()
    {
        return rex_addon::get('yfeed')->i18n('yfeed_cronjob');
    }
}
