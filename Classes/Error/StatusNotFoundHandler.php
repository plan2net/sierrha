<?php

declare(strict_types=1);

namespace Plan2net\Sierrha\Error;

/*
 * Copyright 2019-2024 plan2net GmbH
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Plan2net\Sierrha\Utility\Url;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * An error handler that shows content from a page expect for web resource requests (e.g. CSS).
 */
class StatusNotFoundHandler extends BaseHandler
{
    protected const KEY_PREFIX = 'pageNotFound';

    /**
     * @throws \Exception
     */
    public function handlePageError(
        ServerRequestInterface $request,
        string $message,
        array $reasons = []
    ): ResponseInterface {
        try {
            if ($this->statusCode !== 404) {
                throw new \InvalidArgumentException(
                    'Sierrha-StatusNotFoundHandler only handles status 404.', 1548963650
                );
            }
            if (empty($this->handlerConfiguration['tx_sierrha_notFoundContentSource'])) {
                throw new \InvalidArgumentException(
                    'Sierrha-StatusNotFoundHandler needs to have a content URL set.',
                    1547651257
                );
            }
            if ($request->getHeader('x-sierrha')) {
                throw new \InvalidArgumentException(
                    'Sierrha-StatusNotFoundHandler called itself in a loop.', 1620737618
                );
            }

            // Don't show pretty error page for web resources
            if (!empty($this->extensionConfiguration['resourceExtensionRegexp'])
                && preg_match(
                    '/\.(?:' . $this->extensionConfiguration['resourceExtensionRegexp'] . ')$/',
                    $request->getUri()->getPath()
                )) {
                $content = $this->getLanguageService()->sL(
                    'LLL:EXT:sierrha/Resources/Private/Language/locallang.xlf:resourceNotFound'
                );
            } else {
                /** @var Url $urlUtility */
                $urlUtility = GeneralUtility::makeInstance(Url::class);
                [
                    'url' => $resolvedUrl,
                    'typo3Language' => $this->typo3Language,
                    'pageUid' => $pageUid
                ] = $urlUtility->resolve($request, $this->handlerConfiguration['tx_sierrha_notFoundContentSource']);
                $content = $this->fetchUrl($resolvedUrl, $pageUid);
            }
        } catch (\Exception $e) {
            $content = $this->handleInternalFailure($message, $e);
        }

        return new HtmlResponse($content, $this->statusCode);
    }
}
