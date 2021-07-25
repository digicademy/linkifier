<?php
declare(strict_types = 1);
namespace Digicademy\Linkifier\LinkHandling;

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

use TYPO3\CMS\Core\LinkHandling\RecordLinkHandler as TYPO3RecordLinkHandler;

class RecordLinkHandler extends TYPO3RecordLinkHandler
{

    /**
     * Returns all valid parameters for linking to a TYPO3 page as a string
     *
     * @param array $parameters
     * @return string
     * @throws \InvalidArgumentException
     */
    public function asString(array $parameters): string
    {
        $urn = $this->baseUrn;

        if ($parameters['uid'] && empty($parameters['value'])) {

            $urn .= sprintf('?identifier=%s&uid=%s', $parameters['identifier'], $parameters['uid']);

        } elseif (empty($parameters['uid']) && $parameters['value'] ) {

            $urn .= sprintf('?identifier=%s&value=%s', $parameters['identifier'], $parameters['value']);

        } else {

            throw new \InvalidArgumentException('The RecordLinkHandler expects either uid or value as $parameter', 1550307443);

        }

        if (!empty($parameters['fragment'])) {
            $urn .= sprintf('#%s', $parameters['fragment']);
        }

        return $urn;
    }

    /**
     * Returns all relevant information built in the link to a page (see asString())
     *
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException
     */
    public function resolveHandlerData(array $data): array
    {

        if (empty($data['identifier'])) {
            throw new \InvalidArgumentException('The RecordLinkHandler expects identifier as $data configuration', 1550307431);
        }

        return $data;
    }
}
