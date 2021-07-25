.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

Reference
---------
::

    ###################
    General Information
    ###################

    Both URNs describe the same resource/record:

    t3://record?identifier=HANDLER_KEYWORD&amp;value=FIELD_VALUE
    t3://record?identifier=HANDLER_KEYWORD&amp;uid=UID_VALUE

    Both can/should therefore be handled by the same link handler.

    The first URN is system independent because it uses a persistent identifier that already exists outside of TYPO3.

    The second URN depends on TYPO3 because it uses a uid. It is valid as soon as a record exists in the TYPO3 system.

    Relevant classes:
    TYPO3\CMS\Core\LinkHandling\RecordLinkHandler => Parses/checks the record link structure in database
    TYPO3\CMS\Recordlist\LinkHandler\RecordLinkHandler => Manages the RTE record link integration in backend
    TYPO3\CMS\Frontend\Typolink\DatabaseRecordLinkBuilder => Parses/applies TypoScript configuration of the record link

    Relevant files:
    typo3/sysext/core/Configuration/DefaultConfiguration.php

    #########################################################
    Upgrades from TYPO3 7.6 to 8.7 and link handler migration
    #########################################################

    During the upgrade process from TYPO3 7.6 to 8.6 the RteLinkSyntaxUpdater will upgrade all <link> tags
    to the new <a href="t3://"> link syntax. If the linkifier extension was used before and if custom link
    handlers like <link linkifier_keyword:value> exist in DB this migration will not be successful. It is
    therefore necessary to temporarily insert the following switch the following file BEFORE the upgrade
    wizard is run:

    TYPO3\CMS\Install\Updates\RowUpdater\RteLinkSyntaxUpdater (after line 181; outcomment line 182)

    switch ($linkParts['type']) {

        case '###YOUR_HANDLER_KEYWORD###':
            $anchorTagAttributes['href'] = 't3://record?identifier=###YOUR_HANDLER_KEYWORD###&value=' . $linkParts['value'];
            break;

        default:
            $anchorTagAttributes['href'] = $linkService->asString($linkParts);
            break;
    }

    #####################
    Configuration example
    #####################

    @see:

    - https://docs.typo3.org/typo3cms/extensions/core/Changelog/8.6/Feature-79626-IntegrateRecordLinkHandler.html
    - https://docs.typo3.org/typo3cms/TyposcriptReference/8.7/Functions/Typolink/Index.html#handler-syntax
    - https://docs.typo3.org/typo3cms/extensions/core/Changelog/7.6/Feature-66369-AddedLinkBrowserAPIs.html
    - https://stackoverflow.com/questions/44378613/linkhandler-hook-on-typo3-8
    - https://www.clickstorm.de/blog/linkhandler-typo3/
    - https://usetypo3.com/linkhandler.html

    TCEMAIN.linkHandler {

        my_handler {
            handler = TYPO3\CMS\Recordlist\LinkHandler\RecordLinkHandler
            label = My Linkhandler
            configuration {
                table = tx_my_table
                field = my_field
            }
            scanBefore = page
        }

    }

    config.recordLinks.my_handler {

        typolink {
            useCacheHash = 1

            parameter.cObject = CASE
            parameter.cObject {

                key.field = some_field

                2 = TEXT
                2.value = my target page uid

                3 = TEXT
                3.value = my target page uid

                default = TEXT
                default.value = my default target page
            }

            # workaround for https://forge.typo3.org/issues/81620 (classes by ATagParams are ignored)
            parameter.noTrimWrap = || _top lemma|
            # ATagParams = class="lemma"

            section = main

            title.field = linkifier_linkText

            additionalParams.dataWrap = &tx_my_extension[uid]={field : uid}&tx_my_extension[action]=show&tx_my_extension[controller]=MyController

        }

    }
