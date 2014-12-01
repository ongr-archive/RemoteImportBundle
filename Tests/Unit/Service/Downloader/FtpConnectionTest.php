<?php

namespace ONGR\RemoteImportBundle\Tests\Unit\Service\Downloader;

use ONGR\RemoteImportBundle\Service\Downloader\FtpConnection;
use Psr\Log\NullLogger;
use Symfony\Component\HttpKernel\Tests\Logger;

/**
 * Test for FtpConnection.
 */
class FtpConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Returns FtpConnection mock.
     *
     * @return FtpConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFtpConnectionMock()
    {
        $connectionSettings = [
            'host' => '127.0.0.1:200',
            'user' => 'root',
            'pass' => 'root',
        ];

        /** @var FtpConnection|\PHPUnit_Framework_MockObject_MockObject $ftpConnection */
        $ftpConnection = $this->getMockBuilder('ONGR\RemoteImportBundle\Service\Downloader\FtpConnection')
            ->setConstructorArgs([$connectionSettings])
            ->setMethods(['ftpClose', 'ftpConnect', 'ftpLogin', 'ftpPasv', 'ftpGet'])
            ->getMock();

        return $ftpConnection;
    }

    /**
     * Test if everything is called in the correct order.
     */
    public function testDownloadFile()
    {
        $ftpConnection = $this->getFtpConnectionMock();
        $ftpConnection->setLogger(new NullLogger());

        $ftpConnection->expects($this->at(0))->method('ftpConnect')->willReturn(true);
        $ftpConnection->expects($this->at(1))->method('ftpLogin')->willReturn(true);
        $ftpConnection->expects($this->at(2))->method('ftpPasv')->willReturn(true);
        $ftpConnection->expects($this->at(3))->method('ftpGet')->willReturn(true);
        $ftpConnection->expects($this->at(4))->method('ftpClose')->willReturn(true);

        $ftpConnection->downloadFile('testDownload', 'localDownload');
        $ftpConnection->__destruct();
    }

    /**
     * Data provider for testErrors.
     *
     * @return array
     */
    public function dataErrors()
    {
        // Case #0 connect.
        $passes = [];
        $failure = [0 => 'ftpConnect'];
        $expectedLog = 'FTP connection: ftp_connect()';

        $out[] = [$passes, $failure, $expectedLog];

        // Case #1 login.
        $passes = [0 => 'ftpConnect'];
        $failure = [1 => 'ftpLogin'];
        $expectedLog = 'FTP connection: ftp_login()';

        $out[] = [$passes, $failure, $expectedLog];

        // Case #2 pasv.
        $passes = [0 => 'ftpConnect', 1 => 'ftpLogin'];
        $failure = [2 => 'ftpPasv'];
        $expectedLog = 'FTP connection: ftp_pasv()';

        $out[] = [$passes, $failure, $expectedLog];

        // Case #3 close.
        $passes = [0 => 'ftpConnect', 1 => 'ftpLogin', 2 => 'ftpPasv'];
        $failure = [3 => 'ftpClose'];
        $expectedLog = 'FTP connection: ftp_close()';

        $out[] = [$passes, $failure, $expectedLog];

        return $out;
    }

    /**
     * Test if errors are logged if something fails.
     *
     * @param array  $passes
     * @param array  $failure
     * @param string $expectedLog
     *
     * @dataProvider dataErrors()
     */
    public function testErrors(array $passes, array $failure, $expectedLog)
    {
        $logger = new Logger();

        $ftpConnection = $this->getFtpConnectionMock();
        $ftpConnection->setLogger($logger);

        foreach ($passes as $at => $method) {
            $ftpConnection->expects($this->at($at))->method($method)->willReturn(true);
        }

        foreach ($failure as $at => $method) {
            $ftpConnection->expects($this->at($at))->method($method)->willReturn(false);
        }

        $ftpConnection->downloadFile('testDownload', 'localDownload');
        $ftpConnection->__destruct();
        $this->assertTrue($this->arrayContains($expectedLog, $logger->getLogs()));
    }

    /**
     * Checks if array contains a string recursively.
     *
     * @param mixed $needle
     * @param array $haystack
     *
     * @return bool
     */
    protected function arrayContains($needle, $haystack)
    {
        foreach ($haystack as $value) {
            if ($needle === $value || (is_array($value) && $this->arrayContains($needle, $value) !== false)) {
                return true;
            }
        }

        return false;
    }
}
