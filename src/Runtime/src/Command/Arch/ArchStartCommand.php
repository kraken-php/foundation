<?php

namespace Kraken\Runtime\Command\Arch;

use Dazzle\Channel\ChannelInterface;
use Dazzle\Channel\Extra\Request;
use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;
use Dazzle\Throwable\Exception\Runtime\RejectionException;
use Dazzle\Promise\Promise;
use Kraken\Runtime\RuntimeCommand;

class ArchStartCommand extends Command implements CommandInterface
{
    /**
     * ChannelInterface
     */
    protected $channel;

    /**
     * @override
     * @inheritDoc
     */
    protected function construct()
    {
        $this->channel = $this->runtime->getCore()->make('Kraken\Runtime\Service\ChannelInternal');
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function destruct()
    {
        unset($this->channel);
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        $runtime = $this->runtime;
        $channel = $this->channel;
        $promise = $this->runtime->start();

        return $promise
            ->then(
                function() use($runtime) {
                    return $runtime->getManager()->getRuntimes();
                }
            )
            ->then(
                function($children) use($channel) {
                    $promises = [];

                    foreach ($children as $childAlias)
                    {
                        $req = $this->createRequest(
                            $channel,
                            $childAlias,
                            new RuntimeCommand('arch:start')
                        );

                        $promises[] = $req->call();
                    }

                    return Promise::all($promises);
                }
            )
            ->then(
                function() {
                    return 'Part of architecture has been started.';
                },
                function() {
                    throw new RejectionException('Part of architecture could not be started.');
                }
            )
        ;
    }

    /**
     * Create Request.
     *
     * @param ChannelInterface $channel
     * @param string $receiver
     * @param string $command
     * @return Request
     */
    protected function createRequest(ChannelInterface $channel, $receiver, $command)
    {
        return new Request($channel, $receiver, $command);
    }
}
