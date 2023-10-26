<?php

declare(strict_types=1);

namespace Mautic\CoreBundle\Service;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\UserBundle\Entity\User;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\Security\Core\User\UserInterface;

class ExportLogger
{
    public const LEAD_EXPORT   = 'lead.exports';
    public const REPORT_EXPORT = 'report.exports';

    protected Logger $logger;

    protected string $logPath;

    protected string $logFileName;

    protected int $maxFiles;

    /**
     * @throws \Exception
     */
    public function __construct(CoreParametersHelper $coreParametersHelper)
    {
        $this->logPath     = (string) $coreParametersHelper->get('log_exports_path');
        $this->logFileName = (string) $coreParametersHelper->get('log_file_exports_name');
        $this->maxFiles    = (int) $coreParametersHelper->get('max_log_exports_files');
        $this->logger      = new Logger($this->getLoggerName());
        $this->registerHandlers();
    }

    public function getLoggerName(): string
    {
        return 'logger_exports';
    }

    public function getFileName(): string
    {
        return $this->logFileName ?? 'exports_prod.php';
    }

    public function getLogPath(): string
    {
        return $this->logPath ?? '%kernel.root_dir%/var/logs/exports';
    }

    public function getMaxFiles(): int
    {
        return $this->maxFiles ?? 7;
    }

    /**
     * @param array<mixed> $args
     */
    public function loggerInfo(?UserInterface $user, string $type, array $args): void
    {
        $msg = ($user instanceof User) ?
            'User #'.$user->getId().'_'.crc32($user->getEmail()).' '.$type.' exported with params: '
            : 'User #'.$user->getUserIdentifier().' '.$type.' exported with params: ';

        $this->logger->info($msg, $args);
    }

    /**
     * Register logger handlers.
     *
     * @throws \Exception
     */
    private function registerHandlers(): void
    {
        $this->logger->pushHandler(new RotatingFileHandler(
            $this->getLogPath().'/'.$this->getFileName(),
            $this->getMaxFiles(),
            Logger::INFO
        ));
    }
}
