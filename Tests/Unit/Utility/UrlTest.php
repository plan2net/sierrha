<?php

namespace Plan2net\Sierrha\Tests\Error;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use Plan2net\Sierrha\Utility\Url;
use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Controller\ErrorPageController;

class UrlTest extends UnitTestCase
{
    protected const ERROR_PAGE_CONTROLLER_CONTENT = 'FALLBACK ERROR TEXT';

    /**
     * System Under Test
     */
    protected Url $sut;

    protected LanguageService $languageServiceStub;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->sut = new Url();

        $this->languageServiceStub = $this->createMock(LanguageService::class);
        $this->languageServiceStub->method('sL')->willReturn('lorem ipsum');

        parent::setUp();
    }

    protected function setupErrorPageControllerStub(): void
    {
        $errorPageControllerStub = $this->getMockBuilder(ErrorPageController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $errorPageControllerStub->method('errorAction')
            ->willReturn(self::ERROR_PAGE_CONTROLLER_CONTENT);
        GeneralUtility::addInstance(ErrorPageController::class, $errorPageControllerStub);
    }

    protected function setupRequestFactoryStub($response): void
    {
        // Create a stub for GuzzleClientFactory
        $guzzleClientStub = $this->getMockBuilder(\GuzzleHttp\Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $guzzleClientStub->method('request')
            ->willReturn($response);

        $guzzleFactoryStub = $this->getMockBuilder(GuzzleClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $guzzleFactoryStub->method('getClient')
            ->willReturn($guzzleClientStub);

        // Now create the RequestFactory stub with the required dependency
        $requestFactoryStub = $this->getMockBuilder(RequestFactory::class)
            ->setConstructorArgs([$guzzleFactoryStub])
            ->getMock();
        $requestFactoryStub->method('request')
            ->willReturn($response);

        GeneralUtility::addInstance(RequestFactory::class, $requestFactoryStub);
    }

    protected function buildResponseBody(string $body)
    {
        $stream = @fopen('php://memory', 'r+');
        fputs($stream, $body);
        rewind($stream);

        return $stream;
    }

    #[Test]
    public function httpErrorOnFetchingUrlIsDetected()
    {
        $this->setupErrorPageControllerStub();

        // Anything but 200
        $this->setupRequestFactoryStub(
            new Response($this->buildResponseBody('SERVER ERROR TEXT'), 500)
        );

        $result = $this->sut->fetchWithFallback('http://foo.bar/', $this->languageServiceStub, '');
        $this->assertEquals(self::ERROR_PAGE_CONTROLLER_CONTENT, $result);
    }

    #[Test]
    public function emptyContentOfFetchedUrlIsDetected()
    {
        $this->setupErrorPageControllerStub();

        // Will return an empty string
        $this->setupRequestFactoryStub(new Response());

        $result = $this->sut->fetchWithFallback('http://foo.bar/', $this->languageServiceStub, '');
        $this->assertEquals(self::ERROR_PAGE_CONTROLLER_CONTENT, $result);
    }

    #[Test]
    public function unusableContentOfFetchedUrlIsDetected()
    {
        $this->setupErrorPageControllerStub();

        $this->setupRequestFactoryStub(new Response($this->buildResponseBody(' <h1> </h1> ')));

        $result = $this->sut->fetchWithFallback('http://foo.bar/', $this->languageServiceStub, '');
        $this->assertEquals(self::ERROR_PAGE_CONTROLLER_CONTENT, $result);
    }

    #[Test]
    public function usableContentOfFetchedUrlIsReturned()
    {
        $errorPageContent = 'CUSTOM ERROR PAGE TEXT';
        $this->setupRequestFactoryStub(new Response($this->buildResponseBody($errorPageContent)));

        $result = $this->sut->fetchWithFallback('http://foo.bar/', $this->languageServiceStub, '');
        $this->assertEquals($errorPageContent, $result);
    }
}
