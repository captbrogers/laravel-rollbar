<?php

namespace Captbrogers\Rollbar;

use Illuminate\Foundation\Application;
use Monolog\Logger as Monolog;
use Psr\Log\AbstractLogger;

use Exception;
use InvalidArgumentException;
use Rollbar\RollbarLogger;
use Rollbar\Payload\Level;

class RollbarLogHandler extends AbstractLogger
{
    /**
     * The rollbar client instance.
     *
     * @var \Rollbar\RollbarLogger
     */
    protected $rollbar;

    /**
     * The Laravel application.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The minimum log level at which messages are sent to Rollbar.
     *
     * @var string
     */
    protected $level;

    /**
     * The Log levels.
     *
     * @var array
     */
    protected $levels = [
        'debug'     => Level::DEBUG,
        'info'      => Level::INFO,
        'notice'    => Level::NOTICE,
        'warning'   => Level::WARNING,
        'error'     => Level::ERROR,
        'critical'  => Level::CRITICAL,
        'alert'     => Level::ALERT,
        'emergency' => Level::EMERGENCY,
        'none'      => 1000,
    ];

    /**
     * Constructor.
     */
    public function __construct(RollbarLogger $rollbar, Application $app, $level = 'debug')
    {
        $this->rollbar = $rollbar;

        $this->app = $app;

        $this->level = $this->parseLevel($level ?: 'debug');
    }

    /**
     * Log a message to Rollbar.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = [])
    {
        // Check if we want to log this message.
        if ($this->parseLevel($level) < $this->level) {
            return;
        }

        $context = $this->addContext($context);

        $this->rollbar->log($level, $message, $context);
    }

    /**
     * Add Laravel specific information to the context.
     *
     * @param array $context
     */
    protected function addContext(array $context = [])
    {
        // Add session data.
        if ($session = $this->app->session->all()) {
            if (empty($this->rollbar->person) or ! is_array($this->rollbar->person)) {
                $this->rollbar->person = [];
            }

            // Merge person context.
            if (isset($context['person']) and is_array($context['person'])) {
                $this->rollbar->person = $context['person'];
                unset($context['person']);
            }

            // Add user session information.
            if (isset($this->rollbar->person['session'])) {
                $this->rollbar->person['session'] = array_merge($session, $this->rollbar->person['session']);
            } else {
                $this->rollbar->person['session'] = $session;
            }

            // User session id as user id if not set.
            if (! isset($this->rollbar->person['id'])) {
                $this->rollbar->person['id'] = $this->app->session->getId();
            }
        }

        return $context;
    }

    /**
     * Parse the string level into a Monolog constant.
     *
     * @param  string  $level
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    protected function parseLevel($level)
    {
        if (isset($this->levels[$level])) {
            return $this->levels[$level];
        }

        throw new InvalidArgumentException('Invalid log level: ' . $level);
    }
}
