<?php

namespace WHMCS\Module\Addon\Affili\Hooks;

use WHMCS\Module\Addon\Affili\Helper;

abstract class AbstractHook
{
    protected array $vars;
    protected Helper $helper;

    public function __construct(array $vars = [])
    {
        $this->vars = $vars;
        $this->helper = Helper::make();
    }

    public static function make($vars)
    {
        return (new static($vars));
    }

    public static function render(array $vars = [])
    {
        return static::make($vars)->handle();
    }

    abstract public function handle();

    protected function getVar(string $key, $default = null)
    {
        return $this->vars[$key] ?? $default;
    }
}