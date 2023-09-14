<?php

namespace Tests\Enjoys\AssetsCollector;

use Psr\Log\LoggerInterface;

class ArrayLogger implements LoggerInterface
{

    private array $log = [];

    public function emergency($message, array $context = array()): void
    {
        $this->log['emergency'][] = [
            $message,
            $context
        ];
    }

    public function alert($message, array $context = array()): void
    {
        $this->log['alert'][] = [
            $message,
            $context
        ];
    }

    public function critical($message, array $context = array()): void
    {
        $this->log['critical'][] = [
            $message,
            $context
        ];
    }

    public function error($message, array $context = array()): void
    {
        $this->log['error'][] = [
            $message,
            $context
        ];
    }

    public function warning($message, array $context = array()): void
    {
        $this->log['warning'][] = [
            $message,
            $context
        ];
    }

    public function notice($message, array $context = array()): void
    {
        $this->log['notice'][] = [
            $message,
            $context
        ];
    }

    public function info($message, array $context = array()): void
    {
        $this->log['info'][] = [
            $message,
            $context
        ];
    }

    public function debug($message, array $context = array()): void
    {
        $this->log['debug'][] = [
            $message,
            $context
        ];
    }

    public function log($level, $message, array $context = array()): void
    {
        $this->log[$level][] = [
            $message,
            $context
        ];
    }


    public function getLog($level = null): array
    {
        if ($level === null) {
            return $this->log;
        }
        return $this->log[$level] ?? [];
    }
}
