<?php

namespace WHMCS\Module\Addon\Affili\Hooks;

class ClientAreaFooterOutputHook extends AbstractHook
{
    public function handle()
    {
        $script = '<script src="https://analytics.affili.ir/scripts/affili-v2.js" async></script>';
        $script .= '<script>window.affiliData = window.affiliData || [];function affili(){affiliData.push(arguments);} affili("create");</script>';

        return $script;
    }
}