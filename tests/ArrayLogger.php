<?php

namespace Tests\Enjoys\AssetsCollector;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class ArrayLogger implements LoggerInterface
{

    private array $log = [];

    public function emergency($message, array $context = array()): void
    {
        $this->log[LogLevel::EMERGENCY][] = [
            $message,
            $context
        ];
    }

    public function alert($message, array $context = array()): void
    {
        $this->log[LogLevel::ALERT][] = [
            $message,
            $context
        ];
    }

    public function critical($message, array $context = array()): void
    {
        $this->log[LogLevel::CRITICAL][] = [
            $message,
            $context
        ];
    }

    public function error($message, array $context = array()): void
    {
        $this->log[LogLevel::ERROR][] = [
            $message,
            $context
        ];
    }

    public function warning($message, array $context = array()): void
    {
        $this->log[LogLevel::WARNING][] = [
            $message,
            $context
        ];
    }

    public function notice($message, array $context = array()): void
    {
        $this->log[LogLevel::NOTICE][] = [
            $message,
            $context
        ];
    }

    public function info($message, array $context = array()): void
    {
        $this->log[LogLevel::INFO][] = [
            $message,
            $context
        ];
    }

    public function debug($message, array $context = array()): void
    {
        $this->log[LogLevel::DEBUG][] = [
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

    public function clear(): void
    {
        $this->log = [];
    }
}
