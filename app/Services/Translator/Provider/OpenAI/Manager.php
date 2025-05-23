<?php declare(strict_types=1);

namespace App\Services\Translator\Provider\OpenAI;

use App\Exceptions\UnexpectedValueException;
use App\Services\Curl\Curl;
use App\Services\Translator\Provider\ProviderAbstract;

class Manager extends ProviderAbstract
{
    /**
     * @const string
     */
    protected const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    /**
     * @param array $config
     *
     * @return void
     */
    protected function config(array $config): void
    {
        if (empty($config['key'])) {
            throw new UnexpectedValueException('You must set an OpenAI Key');
        }

        $this->config = $config;
    }

    /**
     * @param string $from
     * @param string $to
     * @param array $strings
     *
     * @return array
     */
    public function array(string $from, string $to, array $strings): array
    {
        return $this->response($this->request($from, $to, $strings));
    }

    /**
     * @param string $from
     * @param string $to
     * @param array $strings
     *
     * @return array
     */
    protected function request(string $from, string $to, array $strings): array
    {
        return Curl::new()
            ->setMethod('POST')
            ->setUrl(static::ENDPOINT)
            ->setJson()
            ->setAuthorization($this->config['key'])
            ->setBody($this->requestBody($from, $to, $strings))
            ->send()
            ->getBody('array');
    }

    /**
     * @param string $from
     * @param string $to
     * @param array $strings
     *
     * @return array
     */
    protected function requestBody(string $from, string $to, array $strings): array
    {
        return [
            'model' => $this->config['model'],
            'messages' => $this->requestBodyMessages($from, $to, $strings),
            'temperature' => $this->config['temperature'],
        ];
    }

    /**
     * @param string $from
     * @param string $to
     * @param array $strings
     *
     * @return array
     */
    protected function requestBodyMessages(string $from, string $to, array $strings): array
    {
        return [$this->requestBodyMessagesSystem($from, $to), ...$this->requestBodyMessagesUser($strings)];
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return array
     */
    protected function requestBodyMessagesSystem(string $from, string $to): array
    {
        return [
            'role' => 'system',
            'content' => $this->requestBodyMessagesSystemContent($from, $to),
        ];
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return string
     */
    protected function requestBodyMessagesSystemContent(string $from, string $to): string
    {
        return trim(
            $this->requestBodyMessagesSystemContentDefault($from, $to)
            ."\n".$this->requestBodyMessagesSystemContentPrompt()
        );
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return string
     */
    protected function requestBodyMessagesSystemContentDefault(string $from, string $to): string
    {
        return trim(<<<END
                You are a professional JSON translation engine.
                You must translate the JSON values from "$from" language into "$to" language without explanation.
                You must not translate any word starting with ":" because is a binding key.
                You can only include a valid JSON in the response, you cannot include anything else. You also must not include json formatting tags, such as "```json".
            END);
    }

    /**
     * @return string
     */
    protected function requestBodyMessagesSystemContentPrompt(): string
    {
        if (empty($this->config['prompt'])) {
            return '';
        }

        $file = base_path($this->config['prompt']);

        if (is_file($file) === false) {
            return '';
        }

        return file_get_contents($file);
    }

    /**
     * @param array $strings
     *
     * @return array
     */
    protected function requestBodyMessagesUser(array $strings): array
    {
        return [
            [
                'role' => 'user',
                'content' => json_encode($strings),
            ],
        ];
    }

    /**
     * @param array $response
     *
     * @return array
     */
    protected function response(array $response): array
    {
        $output = json_decode($response['choices'][0]['message']['content'], true);

        if ($output === null) {
            throw new UnexpectedValueException(sprintf('Invalid Translation Response: %s', $response['choices'][0]['message']['content']));
        }

        return $output;
    }
}
