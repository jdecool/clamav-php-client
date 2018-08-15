<?php

declare(strict_types=1);

namespace JDecool\ClamAV\Tests;

use Exception;
use JDecool\ClamAV\Analysis\Analysis;
use JDecool\ClamAV\Analysis\AnalysisResult;
use JDecool\ClamAV\Client;
use JDecool\ClamAV\Exception\ConnectionError;
use JDecool\ClamAV\Exception\ReloadingError;
use JDecool\ClamAV\Socket\Socket;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testPing()
    {
        $socket = $this->createMock(Socket::class);
        $socket->expects($this->once())
            ->method('send')
            ->with("nPING\n")
            ->willReturn('PONG');

        try {
            $instance = new Client($socket);
            $instance->ping();
        } catch (Exception $e) {
            $this->fail();
        }
    }

    public function testPingFail()
    {
        $socket = $this->createMock(Socket::class);
        $socket->expects($this->once())
            ->method('send')
            ->with("nPING\n")
            ->willReturn('');

        $instance = new Client($socket);

        $this->expectException(ConnectionError::class);

        $instance->ping();
    }

    public function testVersion()
    {
        $version = 'ClamAV 0.100.0/24833/Sat Aug 11 16:45:12 201';

        $socket = $this->createMock(Socket::class);
        $socket->expects($this->once())
            ->method('send')
            ->with("nVERSION\n")
            ->willReturn($version);

        $instance = new Client($socket);

        $this->assertSame($version, $instance->version());
    }

    public function testVersionFail()
    {
        $socket = $this->createMock(Socket::class);
        $socket->expects($this->once())
            ->method('send')
            ->with("nVERSION\n")
            ->willReturn('');

        $instance = new Client($socket);

        $this->expectException(ConnectionError::class);

        $instance->version();
    }

    public function testReload()
    {
        $socket = $this->createMock(Socket::class);
        $socket->expects($this->once())
            ->method('send')
            ->with("nRELOAD\n")
            ->willReturn('RELOADING');

        try {
            $instance = new Client($socket);
            $instance->reload();
        } catch (Exception $e) {
            $this->fail();
        }
    }

    public function testReloadFail()
    {
        $socket = $this->createMock(Socket::class);
        $socket->expects($this->once())
            ->method('send')
            ->with("nRELOAD\n")
            ->willReturn('');

        $instance = new Client($socket);

        $this->expectException(ReloadingError::class);

        $instance->reload();
    }

    public function testShutdown()
    {
        $socket = $this->createMock(Socket::class);
        $socket->expects($this->once())
            ->method('send')
            ->with("nSHUTDOWN\n")
            ->willReturn('');

        try {
            $instance = new Client($socket);
            $instance->shutdown();
        } catch (Exception $e) {
            $this->fail();
        }
    }

    public function testScanCleanFile()
    {
        $file = '/path/foo.txt';

        $socket = $this->createMock(Socket::class);
        $socket->expects($this->once())
            ->method('send')
            ->with("nSCAN $file\n")
            ->willReturn("$file: OK");

        $instance = new Client($socket);

        $analysis = $instance->scan($file);
        $this->assertInstanceOf(Analysis::class, $analysis);
        $this->assertSame(1, $analysis->count());

        $fileResult = $analysis->get($file);
        $this->assertNotNull($file);
        $this->assertSame($file, $fileResult->getFilename());
        $this->assertSame(AnalysisResult::OK, $fileResult->getStatus());
        $this->assertTrue($fileResult->isClean());
        $this->assertFalse($fileResult->isError());
        $this->assertFalse($fileResult->isInfected());
        $this->assertNull($fileResult->getMessage());
    }

    public function testScanInfectedFile()
    {
        $file = '/path/foo.txt';

        $socket = $this->createMock(Socket::class);
        $socket->expects($this->once())
            ->method('send')
            ->with("nSCAN $file\n")
            ->willReturn("$file: Eicar-Test-Signature FOUND");

        $instance = new Client($socket);

        $analysis = $instance->scan($file);
        $this->assertInstanceOf(Analysis::class, $analysis);
        $this->assertSame(1, $analysis->count());

        $fileResult = $analysis->get($file);
        $this->assertNotNull($file);
        $this->assertSame($file, $fileResult->getFilename());
        $this->assertSame(AnalysisResult::INFECTED, $fileResult->getStatus());
        $this->assertTrue($fileResult->isInfected());
        $this->assertFalse($fileResult->isClean());
        $this->assertFalse($fileResult->isError());
        $this->assertSame('Eicar-Test-Signature', $fileResult->getMessage());
    }

    public function testScanFileWithError()
    {
        $file = '/path/foo.txt';

        $socket = $this->createMock(Socket::class);
        $socket->expects($this->once())
            ->method('send')
            ->with("nSCAN $file\n")
            ->willReturn("$file: lstat() failed: No such file or directory. ERROR");

        $instance = new Client($socket);

        $analysis = $instance->scan($file);
        $this->assertInstanceOf(Analysis::class, $analysis);
        $this->assertSame(1, $analysis->count());

        $fileResult = $analysis->get($file);
        $this->assertNotNull($file);
        $this->assertSame($file, $fileResult->getFilename());
        $this->assertSame(AnalysisResult::ERROR, $fileResult->getStatus());
        $this->assertTrue($fileResult->isError());
        $this->assertFalse($fileResult->isInfected());
        $this->assertFalse($fileResult->isClean());
        $this->assertSame('lstat() failed: No such file or directory.', $fileResult->getMessage());
    }

    public function testContScanInfectedFile()
    {
        $file = '/path/foo.txt';

        $socket = $this->createMock(Socket::class);
        $socket->expects($this->once())
            ->method('send')
            ->with("nCONTSCAN $file\n")
            ->willReturn("$file: Eicar-Test-Signature FOUND");

        $instance = new Client($socket);

        $analysis = $instance->contScan($file);
        $this->assertInstanceOf(Analysis::class, $analysis);
        $this->assertSame(1, $analysis->count());

        $fileResult = $analysis->get($file);
        $this->assertNotNull($file);
        $this->assertSame($file, $fileResult->getFilename());
        $this->assertSame(AnalysisResult::INFECTED, $fileResult->getStatus());
        $this->assertTrue($fileResult->isInfected());
        $this->assertFalse($fileResult->isClean());
        $this->assertFalse($fileResult->isError());
        $this->assertSame('Eicar-Test-Signature', $fileResult->getMessage());
    }

    public function testMultiScanInfectedFile()
    {
        $file = '/path/foo.txt';

        $socket = $this->createMock(Socket::class);
        $socket->expects($this->once())
            ->method('send')
            ->with("nMULTISCAN $file\n")
            ->willReturn("$file: Eicar-Test-Signature FOUND");

        $instance = new Client($socket);

        $analysis = $instance->multiscan($file);
        $this->assertInstanceOf(Analysis::class, $analysis);
        $this->assertSame(1, $analysis->count());

        $fileResult = $analysis->get($file);
        $this->assertNotNull($file);
        $this->assertSame($file, $fileResult->getFilename());
        $this->assertSame(AnalysisResult::INFECTED, $fileResult->getStatus());
        $this->assertTrue($fileResult->isInfected());
        $this->assertFalse($fileResult->isClean());
        $this->assertFalse($fileResult->isError());
        $this->assertSame('Eicar-Test-Signature', $fileResult->getMessage());
    }

    public function testAllMatchScanInfectedFile()
    {
        $file = '/path/foo.txt';

        $socket = $this->createMock(Socket::class);
        $socket->expects($this->once())
            ->method('send')
            ->with("nALLMATCHSCAN $file\n")
            ->willReturn("$file: Eicar-Test-Signature FOUND");

        $instance = new Client($socket);

        $analysis = $instance->allMatchScan($file);
        $this->assertInstanceOf(Analysis::class, $analysis);
        $this->assertSame(1, $analysis->count());

        $fileResult = $analysis->get($file);
        $this->assertNotNull($file);
        $this->assertSame($file, $fileResult->getFilename());
        $this->assertSame(AnalysisResult::INFECTED, $fileResult->getStatus());
        $this->assertTrue($fileResult->isInfected());
        $this->assertFalse($fileResult->isClean());
        $this->assertFalse($fileResult->isError());
        $this->assertSame('Eicar-Test-Signature', $fileResult->getMessage());
    }
}
