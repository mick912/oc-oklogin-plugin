<?php namespace Mirjan\Oklogin\SocialLoginProviders;

use Backend\Widgets\Form;
use Mirjan\Oklogin\Classes\OkProvider;
use Flynsarmy\SocialLogin\SocialLoginProviders\SocialLoginProviderBase;
use URL;

class Ok extends SocialLoginProviderBase
{
    use \October\Rain\Support\Traits\Singleton;

    protected $driver = 'Ok';
    protected $adapter;
    protected $callback;

    /**
     * Initialize the singleton free from constructor parameters.
     */
    protected function init()
    {
        parent::init();
        $this->callback = URL::route('flynsarmy_sociallogin_provider_callback', ['Ok'], true);
    }

    public function getAdapter()
    {
        if ( !$this->adapter )
        {
            // Instantiate adapter using the configuration from our settings page
            $providers = $this->settings->get('providers', []);

            $this->adapter = new OkProvider([
                'callback' => $this->callback,

                'keys' => [
                    'key'     => @$providers['Ok']['client_id'],
                    'secret' => @$providers['Ok']['client_secret'],
                    'public' => @$providers['Ok']['client_public'],
                ],

                'debug_mode' => config('app.debug', false),
                'debug_file' => storage_path('logs/flynsarmy.sociallogin.'.basename(__FILE__).'.log'),
            ]);
        }

        return $this->adapter;
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
        if ($this->getAdapter()->isConnected() )
            return \Redirect::to($this->callback);

        $this->getAdapter()->authenticate();
    }

    /**
     * Handles redirecting off to the login provider
     *
     * @return array ['token' => array $token, 'profile' => \Hybridauth\User\Profile]
     */
    public function handleProviderCallback()
    {
        $this->getAdapter()->authenticate();

        $token = $this->getAdapter()->getAccessToken();
        $profile = $this->getAdapter()->getUserProfile();

        // Don't cache anything or successive logins to different accounts
        // will keep logging in to the first account
        $this->getAdapter()->disconnect();

        return [
            'token' => $token,
            'profile' => $profile
        ];
    }
}
