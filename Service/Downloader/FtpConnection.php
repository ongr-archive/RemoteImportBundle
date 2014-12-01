<?php

namespace ONGR\RemoteImportBundle\Service\Downloader;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Downloads file contents over ftp.
 */
class FtpConnection implements LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $connection;

    /**
     * @var resource
     */
    protected $ftp;

    /**
     * Constructor.
     *
     * @param array $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if ($this->ftp) {
            if ($this->ftpClose()) {
                $this->logger && $this->logger->info(
                    'FTP connection: ftp_close()',
                    [$this->connection['host'], 'success']
                );
            } else {
                $this->logger && $this->logger->info(
                    'FTP connection: ftp_close()',
                    [$this->connection['host'], 'failure']
                );
            }
        }
    }

    /**
     * Downloads file.
     *
     * @param string $remote
     * @param string $local
     *
     * @return bool
     */
    public function downloadFile($remote, $local)
    {
        $ret = false;

        if (!$this->ftp) {
            $this->ftp = $this->setConnection();
        }

        if ($this->ftp) {
            $ret = $this->ftpGet($remote, $local);
        }

        return $ret;
    }

    /**
     * @return mixed
     */
    protected function setConnection()
    {
        $conn = $this->ftpConnect();

        if ($conn != false) {
            $this->logger && $this->logger->info(
                'FTP connection: ftp_connect()',
                [$this->connection['host'], 'success']
            );

            if ($this->ftpLogin($conn)) {
                $this->logger && $this->logger->info(
                    'FTP connection: ftp_login()',
                    [$this->connection['user'], 'success']
                );

                if ($this->ftpPasv($conn)) {
                    $this->logger && $this->logger->info(
                        'FTP connection: ftp_pasv()',
                        ['success']
                    );
                } else {
                    $this->logger && $this->logger->info(
                        'FTP connection: ftp_pasv()',
                        ['failure']
                    );
                }
            } else {
                $this->logger && $this->logger->info(
                    'FTP connection: ftp_login()',
                    [$this->connection['user'], 'failure']
                );
            }
        } else {
            $this->logger && $this->logger->info(
                'FTP connection: ftp_connect()',
                [$this->connection['host'], 'failure']
            );
        }

        return $conn;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Closes ftp connection.
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    protected function ftpClose()
    {
        return ftp_close($this->ftp);
    }

    /**
     * Connects to ftp server.
     *
     * @return resource
     *
     * @codeCoverageIgnore
     */
    protected function ftpConnect()
    {
        return ftp_connect($this->connection['host']);
    }

    /**
     * Logins with given connection settings.
     *
     * @param resource $connection
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    protected function ftpLogin($connection)
    {
        return ftp_login($connection, $this->connection['user'], $this->connection['pass']);
    }

    /**
     * Tries to use passive mode.
     *
     * @param resource $connection
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    protected function ftpPasv($connection)
    {
        return ftp_pasv($connection, true);
    }

    /**
     * Gets a file from remote to local in binary more.
     *
     * @param string $remote
     * @param string $local
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    protected function ftpGet($remote, $local)
    {
        return ftp_get($this->ftp, $local, $remote, FTP_BINARY);
    }
}
