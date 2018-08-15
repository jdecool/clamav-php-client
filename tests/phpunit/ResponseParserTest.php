<?php

declare(strict_types=1);

namespace JDecool\ClamAV\Tests;

use JDecool\ClamAV\Analysis\Analysis;
use JDecool\ClamAV\Analysis\AnalysisResult;
use JDecool\ClamAV\Exception\Exception;
use JDecool\ClamAV\Exception\InvalidDeamonResponse;
use JDecool\ClamAV\ResponseParser;
use PHPUnit\Framework\TestCase;

class ResponseParserTest extends TestCase
{
    /**
     * @dataProvider cleanFileResult
     */
    public function testParseCleanFileResult(string $clamavResponse)
    {
        $result = ResponseParser::parse($clamavResponse);
        $this->assertInstanceOf(Analysis::class, $result);
        $this->assertSame(1, $result->count());

        $analysis = current($result->all());
        $this->assertInstanceOf(AnalysisResult::class, $analysis);
        $this->assertSame('/path/foo.bar', $analysis->getFilename());
        $this->assertSame(AnalysisResult::OK, $analysis->getStatus());
        $this->assertTrue($analysis->isClean());
        $this->assertFalse($analysis->isInfected());
        $this->assertFalse($analysis->isError());
        $this->assertNull($analysis->getMessage());
    }

    /**
     * @dataProvider infectedFileResult
     */
    public function testParseInfectedFileResult(string $clamavResponse)
    {
        $result = ResponseParser::parse($clamavResponse);
        $this->assertInstanceOf(Analysis::class, $result);
        $this->assertSame(1, $result->count());

        $analysis = current($result->all());
        $this->assertInstanceOf(AnalysisResult::class, $analysis);
        $this->assertSame('/path/foo.bar', $analysis->getFilename());
        $this->assertSame(AnalysisResult::INFECTED, $analysis->getStatus());
        $this->assertTrue($analysis->isInfected());
        $this->assertFalse($analysis->isClean());
        $this->assertFalse($analysis->isError());
        $this->assertSame('Eicar-Test-Signature', $analysis->getMessage());
    }

    /**
     * @dataProvider errorFileResult
     */
    public function testParseErrorFileResult(string $clamavResponse)
    {
        $result = ResponseParser::parse($clamavResponse);
        $this->assertInstanceOf(Analysis::class, $result);
        $this->assertSame(1, $result->count());

        $analysis = current($result->all());
        $this->assertInstanceOf(AnalysisResult::class, $analysis);
        $this->assertSame('/path/foo.bar', $analysis->getFilename());
        $this->assertSame(AnalysisResult::ERROR, $analysis->getStatus());
        $this->assertTrue($analysis->isError());
        $this->assertFalse($analysis->isClean());
        $this->assertFalse($analysis->isInfected());
        $this->assertSame('lstat() failed: No such file or directory.', $analysis->getMessage());
    }

    public function testParseMultilineResult()
    {
        $result = ResponseParser::parse(<<<RESPONSE
/app/docker-clamav/fixtures/foo.txt: Eicar-Test-Signature FOUND
/app/bar.txt: OK
/app/failed.txt: lstat() failed: No such file or directory. ERROR
RESPONSE
);
        $this->assertInstanceOf(Analysis::class, $result);
        $this->assertSame(3, $result->count());

        $analysis1 = $result->get('/app/docker-clamav/fixtures/foo.txt');
        $this->assertInstanceOf(AnalysisResult::class, $analysis1);
        $this->assertSame('/app/docker-clamav/fixtures/foo.txt', $analysis1->getFilename());
        $this->assertSame(AnalysisResult::INFECTED, $analysis1->getStatus());
        $this->assertTrue($analysis1->isInfected());
        $this->assertFalse($analysis1->isClean());
        $this->assertFalse($analysis1->isError());
        $this->assertSame('Eicar-Test-Signature', $analysis1->getMessage());
        
        $analysis2 = $result->get('/app/bar.txt');
        $this->assertInstanceOf(AnalysisResult::class, $analysis2);
        $this->assertSame('/app/bar.txt', $analysis2->getFilename());
        $this->assertSame(AnalysisResult::OK, $analysis2->getStatus());
        $this->assertTrue($analysis2->isClean());
        $this->assertFalse($analysis2->isInfected());
        $this->assertFalse($analysis2->isError());
        $this->assertNull($analysis2->getMessage());
        
        $analysis3 = $result->get('/app/failed.txt');
        $this->assertInstanceOf(AnalysisResult::class, $analysis3);
        $this->assertSame('/app/failed.txt', $analysis3->getFilename());
        $this->assertSame(AnalysisResult::ERROR, $analysis3->getStatus());
        $this->assertTrue($analysis3->isError());
        $this->assertFalse($analysis3->isClean());
        $this->assertFalse($analysis3->isInfected());
        $this->assertSame('lstat() failed: No such file or directory.', $analysis3->getMessage());
    }

    /**
     * @dataProvider invalidDeamonResponse
     */
    public function testExceptionThrowOnInvalidDeamonResponse(string $deamonResponse)
    {
        try {
            $result = ResponseParser::parse($deamonResponse);
            $this->fail('An exception should be throw');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidDeamonResponse::class, $e);
            $this->assertSame($deamonResponse, $e->getContent());
            $this->assertCount(1, $e->getErrors());
            $this->assertContains($deamonResponse, $e->getErrors());
        }
    }

    public function testExceptionThrowOnInvalidMultilineDeamonResponse()
    {
        $deamonResponse = <<<RESPONSE
/app/docker-clamav/fixtures/foo.txt: Eicar-Test-Signature FOUND
/app/bar.txt: OK
/path/foo.bar: lstat() failed: No such file or directory. UNKNOW
Eicar-Test-Signature FOUND
/app/failed.txt: lstat() failed: No such file or directory. ERROR
RESPONSE;

        try {
            $result = ResponseParser::parse($deamonResponse);
            $this->fail('An exception should be throw');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidDeamonResponse::class, $e);
            $this->assertSame($deamonResponse, $e->getContent());
            $this->assertCount(2, $e->getErrors());
            $this->assertContains('/path/foo.bar: lstat() failed: No such file or directory. UNKNOW', $e->getErrors());
            $this->assertContains('Eicar-Test-Signature FOUND', $e->getErrors());
        }
    }

    public function cleanFileResult(): array
    {
        return [
            ['/path/foo.bar: OK'],
            ['1: /path/foo.bar: OK'],
        ];
    }

    public function infectedFileResult(): array
    {
        return [
            ['/path/foo.bar: Eicar-Test-Signature FOUND'],
            ['1: /path/foo.bar: Eicar-Test-Signature FOUND'],
        ];
    }

    public function errorFileResult(): array
    {
        return [
            ['/path/foo.bar: lstat() failed: No such file or directory. ERROR'],
            ['1: /path/foo.bar: lstat() failed: No such file or directory. ERROR'],
        ];
    }

    public function invalidDeamonResponse(): array
    {
        return [
            ['/path/foo.bar: lstat() failed: No such file or directory. UNKNOW'], // unknow status
            ['Eicar-Test-Signature FOUND'], // missing filename
        ];
    }
}
