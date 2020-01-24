<?php namespace Mirjan\Oklogin;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    /**
     * @var array Plugin dependencies
     */
    public $require = ['RainLab.User', 'Flynsarmy.SocialLogin'];

    public function  register_flynsarmy_sociallogin_providers()
    {
        return [
            '\\Mirjan\\Oklogin\\SocialLoginProviders\\Ok' => [
                'label' => 'Ok',
                'alias' => 'Ok',
                'description' => 'Log in with ok.ru'
            ],
        ];
    }
}
