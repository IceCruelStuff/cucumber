<?php

namespace cucumber\log;

use cucumber\Cucumber;
use cucumber\event\CEvent;
use cucumber\utils\ds\Stack;

final class LogManager
{

    /** @var Cucumber */
    private $plugin;

    /** @var string */
    private $dir;

    /** @var Stack<Logger> */
    private $loggers;

    /** @var string */
    private $global_template;

    /** @var string */
    private $time_format;

    public function __construct(Cucumber $plugin)
    {
        $this->plugin = $plugin;
        $this->dir = $this->plugin->getConfig()->getNested('log.path') ?? 'log/';
        $this->loggers = new Stack;
        $messages = $this->plugin->messages;
        $this->global_template = $messages->getNested('log.templates.global');
        $this->time_format = $messages->getNested('log.time-format') ?? 'Y-m-d\TH:i:s';
    }

    public function getDirectory(): string
    {
        return $this->plugin->getDataFolder() . $this->dir;
    }

    public function log(string $message): void
    {
        foreach ($this->loggers as $logger) {
            $logger->log($message);
        }
    }

    /**
     * Pushes a logger to the top of the logger stack
     * @param Logger $logger
     * @return LogManager For chaining
     */
    public function addLogger(Logger $logger): self
    {
        $this->loggers->push($logger);
        return $this;
    }

    /**
     * Creates a log message for the given event
     * Uses the template for the event type
     * specified in messages.yml and populates
     * it with the data in CEvent::getData()
     * @param CEvent $ev
     * @return string
     */
    public function formatEventMessage(CEvent $ev): string
    {
        $data = $ev->getData();
        $type = $ev->getType();
        return $this->plugin->getMessageFactory()->format(
            $this->global_template,
            $this->generateGlobalTemplateData($ev->getTemplate(), $type, $data)
        );
    }

    /**
     * Generates the data for the global template
     * @param string $template The event message template
     * @param string $type
     * @param array $data
     * @return array
     */
    private function generateGlobalTemplateData(string $template, string $type, array $data): array
    {
        return [
            'time' => date($this->time_format),
            'type' => $type,
            '...' => $this->plugin->getMessageFactory()->format($template, $data)
        ];
    }

}