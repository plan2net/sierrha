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
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;

/**
 * An error handler that redirects to a login page.
 *
 * Class StatusForbiddenHandler
 * @package Plan2net\Sierrha\Error
 */
class StatusForbiddenHandler extends BaseHandler
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
        if ($this->isPageGroupAccessDenial($reasons)) {
            return $this->handlePageGroupAccessDenial($request, $message);
        } else {
            // trigger "page not found"
            $response = GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction(
                $request,
                $message,
                $reasons
            );
            // stop further processing to make sure TYPO3 returns 403 and not 404
            throw new ImmediateResponseException($response);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param string                 $message
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function handlePageGroupAccessDenial(ServerRequestInterface $request, string $message): ResponseInterface
    {
        try {
            if ($this->statusCode !== 403) {
                throw new \InvalidArgumentException('Sierrha-StatusForbiddenHandler only handles HTTP status 403.', 1547651137);
            }
            if (empty($this->handlerConfiguration['tx_sierrha_loginPage'])) {
                throw new \InvalidArgumentException('Sierrha-StatusForbiddenHandler needs to have a login page URL set.', 1547651257);
            }

            /** @var Context $context */
            $context = GeneralUtility::makeInstance(Context::class);
            // if the user is already logged in, another login with the same account will not resolve the issue
            if ($this->isLoggedIn($context)) {
                if (empty($this->handlerConfiguration['tx_sierrha_noPermissionsContentSource'])) {
                    // trigger "page not found"
                    $response = GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction(
                        $request,
                        'The requested page was not accessible with the provided credentials',
                        ['code' => PageAccessFailureReasons::ACCESS_DENIED_GENERAL]
                    );
                    // stop further processing to make sure TYPO3 returns 403 and not 404
                    throw new ImmediateResponseException($response);
                }
                $resolvedUrl = $this->resolveUrl($request, $this->handlerConfiguration['tx_sierrha_noPermissionsContentSource']);
                $response = new HtmlResponse($this->fetchUrl($resolvedUrl, 'noPermissionsTitle', 'noPermissionsDetails'));
            } else {
                $resolvedUrl = $this->resolveUrl($request, $this->handlerConfiguration['tx_sierrha_loginPage']);
                $requestUri = (string)$request->getUri();
                $loginParameters = str_replace(
                    ['###URL###', '###URL_BASE64###'],
                    [rawurlencode($requestUri), rawurlencode(base64_encode($requestUri))],
                    $this->handlerConfiguration['tx_sierrha_loginUrlParameter']
                );
                $response = new RedirectResponse($resolvedUrl . (strpos($resolvedUrl, '?') === false ? '?' : '&') . $loginParameters);
            }
        } catch (ImmediateResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            $response = new HtmlResponse($this->handleInternalFailure($message, $e));
        }

        return $response;
    }

    /**
     * @param array $reasons
     * @return bool
     */
    protected function isPageGroupAccessDenial(array $reasons): bool
    {
        if (!isset($reasons['code'])) {
            return false;
        }
        if ($reasons['code'] === PageAccessFailureReasons::ACCESS_DENIED_PAGE_NOT_RESOLVED
            || $reasons['code'] === PageAccessFailureReasons::ACCESS_DENIED_SUBSECTION_NOT_RESOLVED) {
            unset($reasons['code']);
            if (count($reasons) === 1 && isset($reasons['fe_group'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Context $context
     * @return bool
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    protected function isLoggedIn(Context $context): bool
    {
        // we're checking also for BE sessions in case a FE user group is simulated
        if ($context->getPropertyFromAspect('frontend.user', 'isLoggedIn')
            || ($context->getPropertyFromAspect('backend.user', 'isLoggedIn')
                && $context->getPropertyFromAspect('frontend.user', 'groupIds')[1] === -2 // special "any group" (simulated)
            )) {
            return true;
        }

        return false;
    }
}
