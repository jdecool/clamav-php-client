<?php

declare(strict_types=1);

namespace JDecool\ClamAV\Analysis;

class Analysis
{
    private $data;

    /**
     * @param AnalysisResult[] $data
     */
    public function __construct(array $data = [])
    {
        $this->data = [];

        foreach ($data as $item) {
            $this->addAnalysisResult($item);
        }
    }

    public function addResult(string $filename, string $status, string $message = null): void
    {
        $this->addAnalysisResult(new AnalysisResult($filename, $status, $message));
    }

    public function addAnalysisResult(AnalysisResult $result): void
    {
        $this->data[$result->getFilename()] = $result;
    }

    /**
     * @return AnalysisResult[]
     */
    public function all(): array
    {
        return $this->data;
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function get(string $file): ?AnalysisResult
    {
        return $this->data[$file] ?? null;
    }
}
