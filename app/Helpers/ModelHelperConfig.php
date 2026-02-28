<?php

namespace App\Helpers;

class ModelHelperConfig
{
    public $config = [
        'files' => [],
        'uuid' => false,
        'invoice_no' => false,
        'slug' => null,
        'username' => null,
    ];

    public static function create()
    {
        return new static();
    }

    public function files(array $fileAttributes)
    {
        $this->config['files'] = $fileAttributes;
        return $this;
    }

    public function generateUuid(bool $generate = true)
    {
        $this->config['uuid'] = $generate;
        return $this;
    }

    public function generateInvoice(bool $generate = true)
    {
        $this->config['invoice_no'] = $generate;
        return $this;
    }

    public function generateSlug(?string $sourceAttribute = null)
    {
        $this->config['slug'] = $sourceAttribute;
        return $this;
    }

    public function generateUserName(string|array|null $sourceAttribute = null)
    {
        $this->config['username'] = $sourceAttribute;
        return $this;
    }

    public function generate(string $attribute, string|array|null $sourceAttribute = null)
    {
        $this->config[$attribute] = $sourceAttribute;
        return $this;
    }

    public function from(string|array|null $sourceAttribute = null)
    {
        $lastAttribute = array_key_last($this->config);
        $this->config[$lastAttribute] = $sourceAttribute;
        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
