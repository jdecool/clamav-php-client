<?php

declare(strict_types=1);

namespace JDecool\ClamAV;

use JDecool\ClamAV\Analysis\Analysis;
use JDecool\ClamAV\Analysis\AnalysisResult;
use JDecool\ClamAV\Exception\InvalidDeamonResponse;

class ResponseParser
{
    /**
     * Parse response string from ClamAV deamon.
     *
     * There are two response format that depend on the execution context of the command (if it is in a session or not).
     *
     * In session context: `1: /path/file: Eicar-Test-Signature FOUND`
     * -> where: - `1`: corresponding to the command ID in the session
     *           - `/path/file`: analysed file path
     *           - `Eicar-Test-Signature`: facultative message from ClamAV
     *           - `FOUND`: file status
     *
     * In single command context:  `/path/file: Eicar-Test-Signature FOUND`
     * -> where: - `/path/file`: analysed file path
     *           - `Eicar-Test-Signature`: facultative message from ClamAV
     *           - `FOUND`: file status
     *
     * List of available status:
     * - `OK`
     * - `FOUND`
     * - `ERROR`
     */
    public static function parseLine(string $line): AnalysisResult
    {
        $parts = [];
        preg_match('/^\d?:? ?(\S*): (.*)? ?(OK|FOUND|ERROR)$/', $line, $parts);

        if (4 !== count($parts)) {
            throw new InvalidDeamonResponse($line);
        }

        $filename = $parts[1];
        $message = '' !== $parts[2] ? $parts[2] : null;
        $status = $parts[3];

        return new AnalysisResult($filename, $status, $message);
    }

    public static function parse(string $content): Analysis
    {
        $results = [];
        $errors = [];

        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            try {
                $results[] = self::parseLine($line);
            } catch (InvalidDeamonResponse $e) {
                $errors[] = $e->getContent();
            }
        }

        if (!empty($errors)) {
            throw InvalidDeamonResponse::createMultilineInvalidResponse($content, $errors);
        }

        return new Analysis($results);
    }
}
