<?php namespace Mirjan\Oklogin\SocialLoginProviders;

use Backend\Widgets\Form;
use Mirjan\Oklogin\Classes\OkProvider;
use Flynsarmy\SocialLogin\SocialLoginProviders\SocialLoginProviderBase;
use Socialite;
use JhaoDa\SocialiteProviders\Odnoklassniki\Provider;
use URL;

class Ok extends SocialLoginProviderBase
{
    use \October\Rain\Support\Traits\Singleton;

    protected $driver = 'Ok';

    /**
     * Initialize the singleton free from constructor parameters.
     */
    protected function init()
    {
        parent::init();

        // Socialite uses config files for credentials but we want to pass from
        // our settings page - so override the login method for this provider
        Socialite::extend($this->driver, function ($app) {
            $providers = \Flynsarmy\SocialLogin\Models\Settings::instance()->get('providers', []);
            $providers['Ok']['redirect'] = URL::route('flynsarmy_sociallogin_provider_callback', ['Ok'], true);
            $provider = Socialite::buildProvider(
                OkProvider::class, (array)@$providers['Ok']
            );
            $provider->setCustomConfig((array)@$providers['Ok']);
            return $provider;
        });
    }

    public function isEnabled()
    {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Ok']['enabled']);
    }

    public function isEnabledForBackend()
    {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Ok']['enabledForBackend']);
    }

    public function extendSettingsForm(Form $form)
    {
        $form->addFields([
            'noop' => [
                'type' => 'partial',
                'path' => '$/mirjan/oklogin/partials/backend/forms/settings/_ok_info.htm',
                'tab' => 'Ok',
            ],

            'providers[Ok][enabled]' => [
                'label' => 'Enabled on frontend?',
                'type' => 'checkbox',
                'comment' => 'Can frontend users log in with ok?',
                'default' => 'true',
                'span' => 'left',
                'tab' => 'Ok',
            ],

            'providers[Ok][enabledForBackend]' => [
                'label' => 'Enabled on backend?',
                'type' => 'checkbox',
                'comment' => 'Can administrators log into the backend with ok?',
                'default' => 'false',
                'span' => 'right',
                'tab' => 'Ok',
            ],

            'providers[Ok][client_id]' => [
                'label' => 'App ID',
                'type' => 'text',
                'tab' => 'Ok',
            ],

            'providers[Ok][client_public]' => [
                'label' => 'Public Key',
                'type' => 'text',
                'tab' => 'Ok',
            ],
            'providers[Ok][client_secret]' => [
                'label' => 'Private Key',
                'type' => 'text',
                'tab' => 'Ok',
            ],
        ], 'primary');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider()
    {
        return Socialite::driver($this->driver)->scopes(['email'])->redirect();
    }

    /**
     * Handles redirecting off to the login provider
     *
     * @return array
     */
    public function handleProviderCallback()
    {
        $user = Socialite::driver($this->driver)->user();

        return (array)$user;
    }
}