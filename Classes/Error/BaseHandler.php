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

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * A foundation class for error handlers.
 */
abstract class BaseHandler implements PageErrorHandlerInterface
{

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var array
     */
    protected $handlerConfiguration;

    /**
     * @var array
     */
    protected $extensionConfiguration;

	/**
	 * @param int $statusCode
	 * @param array $configuration
	 */
	public function __construct(int $statusCode, array $configuration)
    {
        $this->statusCode = $statusCode;
        $this->handlerConfiguration = $configuration;
        try {
            $this->extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sierrha');
        } catch (\Exception $e) {
            // @todo log configuration error
            $this->extensionConfiguration = [];
        }
	}

    /**
     * Resolve TYPO3 style URL into real world URL, replace language markers for external URL
     *
     * @param ServerRequestInterface $request
     * @param string                 $typoLinkUrl
     * @return string
     * @throws \TYPO3\CMS\Core\Routing\InvalidRouteArgumentsException
     */
    protected function resolveUrl(ServerRequestInterface $request, string $typoLinkUrl): string
    {
        $linkService = GeneralUtility::makeInstance(LinkService::class);
        $urlParams = $linkService->resolve($typoLinkUrl);
        if ($urlParams['type'] !== 'page' && $urlParams['type'] !== 'url') {
            throw new \InvalidArgumentException('The error handler accepts only TYPO3 links of type "page" or "url"', 1547651754);
        }
        if ($urlParams['type'] === 'url') {
            /* @var $siteLanguage SiteLanguage */
            $siteLanguage = $request->getAttribute('language');
            $resolvedUrl = str_replace(
                ['###ISO_639-1###', '###IETF_BCP47'],
                [$siteLanguage->getTwoLetterIsoCode(), $siteLanguage->getHreflang()],
                $urlParams['url']
            );

            return $resolvedUrl;
        }

        /* @var $site Site */
        $site = $request->getAttribute('site', null);
        if (!$site instanceof Site) {
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId((int)$urlParams['pageuid']);
        }

        return (string)$site->getRouter()->generateUri(
            (int)$urlParams['pageuid'],
            ['_language' => $request->getAttribute('language', null)]
        );
    }

    /**
     * @param string $message
     * @param \Throwable $e
     * @return string
     * @throws ImmediateResponseException
     */
    protected function handleInternalFailure(string $message, \Throwable $e): string
    {
        // @todo add logging
        $title = 'Page Not Found';
        $exitImmediately = false;
        if ($this->isDebugModeActive()) {
            $title .= ': ' . $message;
            $message = get_class($e) . ': ' . $e->getMessage();
            if ($e->getCode()) {
                $message .= ' [code: ' . $e->getCode() . ']';
            }
            $exitImmediately = true;
        }
        $content = GeneralUtility::makeInstance(ErrorPageController::class)->errorAction(
            $title,
            $message,
            AbstractMessage::ERROR
        );
        if ($exitImmediately) {
            $content .= $this->debugInformationAsHtml(['exception' => $e]);
            throw new ImmediateResponseException(new HtmlResponse($content, 500));
        }

        return $content;
    }

    /**
     * Create HTML comment containing debug information
     *
     * @param array $data
     * @return string
     */
    protected function debugInformationAsHtml(array $data = []): string
    {
        $content = '';
        if ($this->isDebugModeActive()) {
            $url = isset($data['url']) ? "CONTENT URL: {$data['url']}\n" : '';
            $ext = isset($data['ext']) ? "RESOURCE FILE EXTENSION: {$data['ext']}\n" : '';
            $login = isset($data['login']) ? "LOGIN: yes\n" : '';
            $content = "
<!--
HTTP STATUS: {$this->statusCode}
{$url}{$ext}{$login}
-->";

            $content .= $this->extensionConfiguration;
            $content .= $this->handlerConfiguration;

            if (isset($data['exception'])) {
                $content .= 'EXCEPTION: ' . $data['exception']->getMessage() ."\n"
                    . $data['exception']->getTraceAsString();
            }

            $content .= "\n-->\n";
        }

        return $content;
    }

    /**
     * @return bool
     */
    protected function isDebugModeActive(): bool
    {
        $active = false;
        if ($this->extensionConfiguration['debugMode']) {
            if (GeneralUtility::getApplicationContext()->isProduction()) {
                /** @var Context $context */
                $context = GeneralUtility::makeInstance(Context::class);
                if (GeneralUtility::cmpIP(GeneralUtility::getIndpEnv(
                        'REMOTE_ADDR'),
                        $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']
                    )
                    || ($context->getPropertyFromAspect('backend.user', 'isLoggedIn'))) {
                    $active = true;
                }
            } else {
                $active = true;
            }
        } elseif (GeneralUtility::getApplicationContext()->isDevelopment()
            && GeneralUtility::cmpIP(
                GeneralUtility::getIndpEnv('REMOTE_ADDR'),
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']
            )
        ) {
            $active = true;
        }

        return $active;
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'] ?? GeneralUtility::makeInstance(LanguageService::class);
    }
}
