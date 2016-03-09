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

$subpage = rex_be_controller::getCurrentPagePart(2);

echo rex_view::title(rex_i18n::msg('yfeed'));

include rex_be_controller::getCurrentPageObject()->getSubPath();
