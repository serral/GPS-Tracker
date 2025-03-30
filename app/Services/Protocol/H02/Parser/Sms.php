<?php declare(strict_types=1);

namespace App\Services\Protocol\H02\Parser;

use App\Services\Protocol\ParserAbstract;

class Sms extends ParserAbstract
{
    /**
     * @return array
     */
    public function resources(): array
    {
        if ($this->messageIsValid() === false) {
            return [];
        }

        $this->values = explode(',', substr($this->message, 1, -1));

        $this->addIfValid($this->resourceSms());

        return $this->resources;
    }

    /**
     * @return bool
     */
    public function messageIsValid(): bool
    {
        return (bool)preg_match($this->messageIsValidRegExp(), $this->message);
    }

    /**
     * @return string
     */
    protected function messageIsValidRegExp(): string
    {
        return '/^'
            .'\*[A-Z]{2},' // 0 - maker
            .'[0-9]+,'     // 1 - serial
            .'SMS,'        // 2 - type
            .'.*'          // 3 - payload
            .'$/';
    }

    /**
     * @return string
     */
    protected function maker(): string
    {
        return $this->values[0];
    }

    /**
     * @return string
     */
    protected function serial(): string
    {
        return $this->values[1];
    }

    /**
     * @return string
     */
    protected function type(): string
    {
        return $this->values[2];
    }

    /**
     * @return array
     */
    protected function payload(): array
    {
        return $this->cache[__FUNCTION__] ??= explode(',', $this->values[3]);
    }

    /**
     * @return string
     */
    protected function response(): string
    {
        return '*'.$this->maker().','.$this->serial().',V4,'.$this->type().','.date('YmdHis').'#';
    }
}
