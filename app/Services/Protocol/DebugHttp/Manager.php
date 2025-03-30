<?php declare(strict_types=1);

namespace App\Services\Protocol\DebugHttp;

use App\Services\Protocol\ProtocolAbstract;
use App\Services\Server\Http\Server;

class Manager extends ProtocolAbstract
{
    /**
     * @return string
     */
    public function code(): string
    {
        return 'debug-http';
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'Debug HTTP';
    }

    /**
     * @param int $port
     *
     * @return \App\Services\Server\Http\Server
     */
    public function server(int $port): Server
    {
        return Server::new($port);
    }

    /**
     * @return array
     */
    protected function parsers(): array
    {
        return [];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     *
     * @param string $message
     * @param array $data = []
     *
     * @return array
     */
    public function resources(string $message, array $data = []): array
    {
        return [];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     *
     * @param string $message
     *
     * @return array
     */
    public function messages(string $message): array
    {
        return [];
    }
}
