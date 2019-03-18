<?php
declare(strict_types=1);

namespace Plan2net\Sierrha\Error;

/*
 * Copyright 2019 plan2net GmbH
 * 
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Controller\ErrorPageController;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * An error handler that shows content from a page expect for web resource requests (eg CSS).
 */
class StatusNotFoundHandler extends BaseHandler
{

    /**
     * @param ServerRequestInterface $request
     * @param string                 $message
     * @param array                  $reasons
     * @return ResponseInterface
     * @throws \Exception
     */
    public function handlePageError(ServerRequestInterface $request, string $message, array $reasons = []): ResponseInterface
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sierrha');

        try {
            if ($this->statusCode !== 404) {
                throw new \InvalidArgumentException('Sierrha-StatusNotFoundHandler only handles status 404.', 1548963650);
            }
            if (empty($this->handlerConfiguration['tx_sierrha_notFoundContentSource'])) {
                throw new \InvalidArgumentException('Sierrha-StatusNotFoundHandler needs to have a content URL set.', 1547651257);
            }

            // don't show pretty error page for web resources
            if (!empty($extensionConfiguration['resourceExtensionRegexp']
                && preg_match('/\.(?:'.$extensionConfiguration['resourceExtensionRegexp'].')$/', $request->getUri()->getPath()))) {
                $content = $this->getLanguageService()->sL('LLL:EXT:sierrha/Resources/Private/Language/locallang.xlf:resourceNotFound');
            } else {
                $resolvedUrl = $this->resolveUrl($request, $this->handlerConfiguration['tx_sierrha_notFoundContentSource']);
                $content = GeneralUtility::getUrl($resolvedUrl);
            }
        } catch (\Exception $e) {
            if ($extensionConfiguration['debugMode']
                || GeneralUtility::cmpIP(GeneralUtility::getIndpEnv('REMOTE_ADDR'), $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'])) {
                // @todo add detailed debug output
                $content = GeneralUtility::makeInstance(ErrorPageController::class)->errorAction(
                    get_class($e),
                    $e->getMessage(),
                    AbstractMessage::ERROR,
                    $e->getCode()
                );
                throw new ImmediateResponseException(new HtmlResponse($content, 500));
            } else {
                throw $e;
            }
        }

        return new HtmlResponse($content, $this->statusCode);
    }
}
