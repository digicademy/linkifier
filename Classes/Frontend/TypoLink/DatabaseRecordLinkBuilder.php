<?php
declare(strict_types = 1);
namespace Digicademy\Linkifier\Frontend\Typolink;

/***************************************************************
 *  Copyright notice
 *
 *  Torsten Schrade <Torsten.Schrade@adwmainz.de>, Academy of Sciences and Literature | Mainz
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;
use TYPO3\CMS\Frontend\Typolink\DatabaseRecordLinkBuilder as TYPO3DatabaseRecordLinkBuilder;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

class DatabaseRecordLinkBuilder extends TYPO3DatabaseRecordLinkBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array &$linkDetails, string $linkText, string $target, array $conf): array
    {
        $tsfe = $this->getTypoScriptFrontendController();
        $pageTsConfig = $tsfe->getPagesTSconfig();
        $configurationKey = $linkDetails['identifier'] . '.';
        $configuration = $tsfe->tmpl->setup['config.']['recordLinks.'];
        $linkHandlerConfiguration = $pageTsConfig['TCEMAIN.']['linkHandler.'];

        if (!isset($configuration[$configurationKey], $linkHandlerConfiguration[$configurationKey])) {
            throw new UnableToLinkException(
                'Configuration how to link "' . $linkDetails['typoLinkParameter'] . '" was not found, so "' . $linkText . '" was not linked.',
                1490989149,
                null,
                $linkText
            );
        }
        $typoScriptConfiguration = $configuration[$configurationKey]['typolink.'];
        $linkHandlerConfiguration = $linkHandlerConfiguration[$configurationKey]['configuration.'];

        // implements the possibility to get/link records not only by table/uid but by table/field/value
        if (empty($linkDetails['uid']) && $linkDetails['value']) {
            $dbResult = $tsfe->sys_page->getRecordsByField(
                $linkHandlerConfiguration['table'],
                $linkHandlerConfiguration['field'],
                $linkDetails['value'],
                '',
                '',
                '',
                '0,1'
            );
            $dbResult[0] ? $record = $dbResult[0] : $record = 0;
        } elseif ($configuration[$configurationKey]['forceLink']) {
            $record = $tsfe->sys_page->getRawRecord($linkHandlerConfiguration['table'], $linkDetails['uid']);
        } else {
            $record = $tsfe->sys_page->checkRecord($linkHandlerConfiguration['table'], $linkDetails['uid']);
        }

        if ($record === 0) {
            throw new UnableToLinkException(
                'Record not found for "' . $linkDetails['typoLinkParameter'] . '" was not found, so "' . $linkText . '" was not linked.',
                1490989659,
                null,
                $linkText
            );
        }

        // add link details to record for TypoScript access
        $linkDetails['value'] ? $recordFieldValue = '&amp;value='. $linkDetails['value'] : $recordFieldValue = '&amp;uid='. $linkDetails['uid'];
        $record['linkifier_furtherLinkParams'] = str_replace(
            't3://record?identifier='. $linkDetails['identifier'] . $recordFieldValue .'',
            '', $linkDetails['typoLinkParameter']
        );
        $record['linkifier_linkText'] = $linkText;
        $record['linkifier_linkHandlerKeyword'] = $linkDetails['identifier'];
        $linkDetails['value'] ? $record['linkifier_linkHandlerValue'] = $linkDetails['value'] : $record['linkifier_linkHandlerValue'] = $linkDetails['uid'];

        // Unset the parameter part of the given TypoScript configuration while keeping
        // config that has been set in addition.
        unset($conf['parameter.']);

        $typoLinkCodecService = GeneralUtility::makeInstance(TypoLinkCodecService::class);
        $parameterFromDb = $typoLinkCodecService->decode($conf['parameter']);
        unset($parameterFromDb['url']);
        $parameterFromTypoScript = $typoLinkCodecService->decode($typoScriptConfiguration['parameter']);
        $parameter = array_replace_recursive($parameterFromTypoScript, array_filter($parameterFromDb));
        $typoScriptConfiguration['parameter'] = $typoLinkCodecService->encode($parameter);

        $typoScriptConfiguration = array_replace_recursive($conf, $typoScriptConfiguration);

        if (!empty($linkDetails['fragment'])) {
            $typoScriptConfiguration['section'] = $linkDetails['fragment'];
        }

        // Build the full link to the record
        $localContentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $localContentObjectRenderer->start($record, $linkHandlerConfiguration['table']);
        $localContentObjectRenderer->parameters = $this->contentObjectRenderer->parameters;
        $link = $localContentObjectRenderer->typoLink($linkText, $typoScriptConfiguration);

        $this->contentObjectRenderer->lastTypoLinkLD = $localContentObjectRenderer->lastTypoLinkLD;
        $this->contentObjectRenderer->lastTypoLinkUrl = $localContentObjectRenderer->lastTypoLinkUrl;
        $this->contentObjectRenderer->lastTypoLinkTarget = $localContentObjectRenderer->lastTypoLinkTarget;

        // hacky workaround to prevent TYPO3 putting a link together, link is already built
        throw new UnableToLinkException(
            '',
            1491130170,
            null,
            $link
        );
    }
}
