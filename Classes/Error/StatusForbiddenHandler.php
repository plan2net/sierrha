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
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Controller\ErrorPageController;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;

/**
 * An error handler that redirects to a login page.
 */
class StatusForbiddenHandler implements PageErrorHandlerInterface {

	/**
	 * @var int
	 */
	protected $statusCode;

	/**
	 * @var array
	 */
	protected $handlerConfiguration;

	/**
	 * @param int $statusCode
	 * @param array $configuration
	 */
	public function __construct(int $statusCode, array $configuration) {
		$this->statusCode = $statusCode;
		$this->handlerConfiguration = $configuration;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param string $message
	 * @param array $reasons
	 * @return ResponseInterface
	 */
	public function handlePageError(ServerRequestInterface $request, string $message, array $reasons = []): ResponseInterface {
		try {
            if ($this->statusCode !== 403) {
                throw new \InvalidArgumentException('Sierrha-StatusForbiddenHandler only handles HTTP status 403.', 1547651137);
            }
            if (empty($this->handlerConfiguration['tx_sierrha_loginPage'])) {
                throw new \InvalidArgumentException('Sierrha-StatusForbiddenHandler needs to have a login page URL set.', 1547651257);
            }

            // if the user is already logged in, another login with the same account will not resolve the issue
            // NOTE: we're checking also for BE sessions in case a FE user group is simulated
            $context = GeneralUtility::makeInstance(Context::class);
            if ($context->getPropertyFromAspect('frontend.user', 'isLoggedIn')
                || ($context->getPropertyFromAspect('backend.user', 'isLoggedIn')
                    && $context->getPropertyFromAspect('frontend.user', 'groupIds')[1] === -2 // special "any group" (simulated)
                )) {
                return GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction(
                    $request,
                    'The requested page was not accessible with the provided credentials',
                    ['code' => PageAccessFailureReasons::ACCESS_DENIED_GENERAL]
                );
            }

			$resolvedUrl = $this->resolveUrl($request, $this->handlerConfiguration['tx_sierrha_loginPage']);
		} catch (\Exception $e) {
			$extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sierrha');

			if ($extensionConfiguration['debugMode']
				|| GeneralUtility::cmpIP(GeneralUtility::getIndpEnv('REMOTE_ADDR'), $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'])) {
				$content = GeneralUtility::makeInstance(ErrorPageController::class)->errorAction(
					get_class($e),
					$e->getMessage(),
					AbstractMessage::ERROR,
					$e->getCode()
				);

				return new HtmlResponse($content, 500);
			}
			throw $e;
		}

		/* @var $siteLanguage SiteLanguage */
		$siteLanguage = $request->getAttribute('language');
		$resolvedUrl = str_replace(
			['###ISO_639-1###', '###IETF_BCP47'],
			[$siteLanguage->getTwoLetterIsoCode(), $siteLanguage->getHreflang()],
			$resolvedUrl
		);
		$requestUri = (string)$request->getUri();
		$loginParameters = str_replace(
			['###URL###', '###URL_BASE64###'],
			[rawurlencode($requestUri), rawurlencode(base64_encode($requestUri))],
			$this->handlerConfiguration['tx_sierrha_loginUrlParameter']
		);

		return new RedirectResponse($resolvedUrl . (strpos($resolvedUrl, '?') === false ? '?' : '&') . $loginParameters);
	}

	/**
	 * Resolve the URL
	 *
	 * @param ServerRequestInterface $request
	 * @param string $typoLinkUrl
	 * @return string
	 */
	protected function resolveUrl(ServerRequestInterface $request, string $typoLinkUrl): string {
		$linkService = GeneralUtility::makeInstance(LinkService::class);
		$urlParams = $linkService->resolve($typoLinkUrl);
		if ($urlParams['type'] !== 'page' && $urlParams['type'] !== 'url') {
			throw new \InvalidArgumentException('StatusForbiddenHandler can only handle TYPO3 links of type "page" or "url"', 1547651754);
		}
		if ($urlParams['type'] === 'url') {
			return $urlParams['url'];
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
}
