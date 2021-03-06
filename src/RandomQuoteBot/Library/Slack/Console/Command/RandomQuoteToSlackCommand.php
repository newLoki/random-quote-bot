<?php
namespace RandomQuoteBot\Library\Slack\Console\Command;

use RandomQuoteBot\Library\Slack\SlackConfig;
use RandomQuoteBot\RandomQuoteFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RandomQuoteToSlackCommand extends Command
{
    const ARGUMENT_CHANNEL = 'channel';
    const ARGUMENT_QUOTE_TYPE = 'quote_type';
    const ARGUMENT_CONFIG = 'config';
    
    /**
     * sets the description to appear in console list of commands
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('quote:send-random-quote-to-slack')
            ->setDescription('Send Random Axel Stoll Quote to Slack channel. usage: command <configName> <quoteType> <channelName>')
            ->addArgument(
                self::ARGUMENT_CONFIG,
                InputArgument::REQUIRED,
                'what config to load, requires a <config>.yml in the config/ folder'
            )
            ->addArgument(
                self::ARGUMENT_CHANNEL,
                InputArgument::REQUIRED,
                'what channel to quote to send to'
            )
            ->addArgument(
                self::ARGUMENT_QUOTE_TYPE,
                InputArgument::OPTIONAL,
                'what quote type to load, if none is given, a random quote is selected'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $input->getArgument(self::ARGUMENT_CONFIG);
        $quoteType = $input->getArgument(self::ARGUMENT_QUOTE_TYPE, null);
        $channel = $input->getArgument(self::ARGUMENT_CHANNEL);

        $fileName = APP_ROOT . 'config/' . $config . '.yml';;

        if ($quoteType) {
            $quote = RandomQuoteFactory::createRandomQuoteByName($quoteType, new SlackConfig($fileName));
        } else {
            $quote = RandomQuoteFactory::createRandomQuote(new SlackConfig($fileName));
        }

        $response = $quote->sendQuote('#'. $channel);
        if ($this->isSuccess($response)) {
            echo "quote send" . PHP_EOL;
        } else {
            echo "FAILED sending quote" . PHP_EOL . PHP_EOL;
            var_dump($response);
            echo PHP_EOL;
        }
    }

    /**
     * @param \Frlnc\Slack\Contracts\Http\Response $response
     * @return bool
     */
    protected function isSuccess(\Frlnc\Slack\Contracts\Http\Response $response)
    {
        $body = $response->getBody();
        if (isset($body['ok']) && $body['ok'] == true) {
            return true;
        }

        return false;
    }
}
