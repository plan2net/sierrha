<?php

namespace Plan2net\Sierrha\Tests\Error;

use Plan2net\Sierrha\Utility\Url;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Controller\ErrorPageController;

/**
 * @backupGlobals enabled
 */
class UrlTest extends UnitTestCase
{

    protected const ERROR_PAGE_CONTROLLER_CONTENT = 'FALLBACK ERROR TEXT';

    /**
     * System Under Test
     *
     * @var Url
     */
    protected $sut;

    protected function setUp()
    {
        $this->sut = new Url();

        $languageServiceStub = $this->createMock(LanguageService::class);
        $languageServiceStub->method('sL')->willReturn('lorem ipsum');
        $GLOBALS['LANG'] = $languageServiceStub;
    }

    protected function setupErrorPageControllerStub()
    {
        $errorPageControllerStub = $this->getMockBuilder(ErrorPageController::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $errorPageControllerStub->method('errorAction')
                                ->willReturn(self::ERROR_PAGE_CONTROLLER_CONTENT);
        GeneralUtility::addInstance(ErrorPageController::class, $errorPageControllerStub);
    }

    protected function setupRequestFactoryStub($response)
    {
        $requestFactoryStub = $this->getMockBuilder(RequestFactory::class)
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

    /**
     * @test
     */
    public function httpErrorOnFetchingUrlIsDetected()
    {
        $this->setupErrorPageControllerStub();

        $this->setupRequestFactoryStub(new Response($this->buildResponseBody('SERVER ERROR TEXT'), 500)); // anything but 200

        $result = $this->sut->fetchWithFallback('http://foo.bar/', '', '');
        $this->assertEquals(self::ERROR_PAGE_CONTROLLER_CONTENT, $result);
    }

    /**
     * @test
     */
    public function emptyContentOfFetchedUrlIsDetected()
    {
        $this->setupErrorPageControllerStub();

        $this->setupRequestFactoryStub(new Response()); // will return an empty string

        $result = $this->sut->fetchWithFallback('http://foo.bar/', '', '');
        $this->assertEquals(self::ERROR_PAGE_CONTROLLER_CONTENT, $result);
    }

    /**
     * @test
     */
    public function unusableContentOfFetchedUrlIsDetected()
    {
        $this->setupErrorPageControllerStub();

        $this->setupRequestFactoryStub(new Response($this->buildResponseBody(' <h1> </h1> ')));

        $result = $this->sut->fetchWithFallback('http://foo.bar/', '', '');
        $this->assertEquals(self::ERROR_PAGE_CONTROLLER_CONTENT, $result);
    }

    /**
     * @test
     */
    public function usableContentOfFetchedUrlIsReturned()
    {
        $errorPageContent = 'CUSTOM ERROR PAGE TEXT';
        $this->setupRequestFactoryStub(new Response($this->buildResponseBody($errorPageContent)));

        $result = $this->sut->fetchWithFallback('http://foo.bar/', '', '');
        $this->assertEquals($errorPageContent, $result);
    }
}
