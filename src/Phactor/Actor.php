<?php
namespace Phactor\Phactor;

use SuperClosure\Serializer;
use Symfony\Component\Process\PhpProcess;

/**
 * Class Actor
 * @package Phactor\Phactor
 * Represents an actor model
 * @see https://en.wikipedia.org/wiki/Actor_model
 */
class Actor
{
    protected $id;
    protected $state;
    protected $handler;

    /**
     * Constructor
     * @param integer  $id An unique id of an actor, should be free tcp-port in current implementation
     * @param callable $handler a function that takes $message and $state
     * @param array    $state initial Actor's state
     */
    public function __construct($id, Callable $handler, array $state = [])
    {
        $this->id      = $id;
        $this->handler = $handler;
        $this->state   = $state;
    }

    /**
     * Runs an infinite loop of fetching and handling messages
     */
    public function run()
    {
        $this->state['mailbox'] = new Mailbox($this->id);
        while (true) {
            while ($connection = $this->state['mailbox']->fetchMessage()) {
                $this->handle(stream_get_contents($connection));
            }
        }
    }

    /**
     * Responsible for proper handler invoking and updating Actor's state
     * @param string $message
     */
    protected function handle($message)
    {
        $this->state = $this->handler->__invoke($message, $this->state);
    }

    /**
     * Initiates new actor in a new PHP process
     * @param integer  $id An unique id of an actor, should be free tcp-port in current implementation
     * @param callable $handler
     * @return PhpProcess
     */
    public static function createAndRun($id, Callable $handler)
    {
        $serializedHandler = base64_encode((new Serializer())->serialize($handler));
        $autoloadPath      = Utils::getAutoloadPath();
        $process           = new PhpProcess(<<<EOF
    <?php
        require '$autoloadPath';
        \Phactor\Phactor\Actor::initializeChild($id, '$serializedHandler');
    ?>
EOF
        );
        if (null === $process->getCommandLine()) $process->setPhpBinary(PHP_BINARY); // workaround for portable windows php
        $process->start();
        return $process;
    }

    public static function initializeChild($id, $serializedHandler)
    {
        $handler = (new Serializer())->unserialize(base64_decode($serializedHandler));
        (new Actor($id, $handler))->run();
    }

    /**
     * Sends a message to specified address
     * @param integer $address
     * @param string  $message
     * @see http://php.net/manual/en/function.stream-socket-client.php
     */
    public static function sendMessage($address, $message)
    {
        for (; ;) {
            $socket = @stream_socket_client(Mailbox::buildFullAddress($address));
            if ($socket) break;
            echo 'Reconnecting to ' . $address . PHP_EOL;
            sleep(3);
        }
        fwrite($socket, $message);
        fclose($socket);
    }

}