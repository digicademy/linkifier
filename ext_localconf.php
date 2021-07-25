<?php
if (!defined('TYPO3_MODE')) die('Access denied!');

// register handler files
$GLOBALS['TYPO3_CONF_VARS']['SYS']['linkHandler']['record'] = \Digicademy\Linkifier\LinkHandling\RecordLinkHandler::class;
$GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder']['record'] = \Digicademy\Linkifier\Frontend\Typolink\DatabaseRecordLinkBuilder::class;
