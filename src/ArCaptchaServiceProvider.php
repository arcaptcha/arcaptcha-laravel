<?php

namespace Mohammadv184\ArCaptcha\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Mohammadv184\ArCaptcha\ArCaptcha;

class ArCaptchaServiceProvider extends ServiceProvider
{
    /**
     * service provider register method.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/arcaptcha.php',
            'arcaptcha'
        );

        $this->app->singleton('arcaptcha', function () {
            $config = config('arcaptcha');

            return new ArCaptcha(
                $config['site_key'],
                $config['secret_key'],
                ['verify_exception_value' => $config['verify_exception_value']]
            );
        });
    }

    /**
     *service provider boot method.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/arcaptcha.php' => config_path('arcaptcha.php')
        ], 'config');

        $this->addValidationRule();

        $this->addBladeDirective();
    }

    /**
     * Add Validation Rule.
     */
    protected function addValidationRule(): void
    {
        Validator::extendImplicit('arcaptcha', function ($attribute, $value, $parameters) {
            if (is_null($value)) {
                return false;
            }
            return app('arcaptcha')->verify($value);
        }, trans('validation.arcaptcha'));
    }

    /**
     * add blade Directive.
     */
    protected function addBladeDirective(): void
    {
        Blade::directive('arcaptchaScript', function () {
            return '<?php echo ArCaptcha::getScript()?>';
        });
        Blade::directive('arcaptchaWidget', function ($options) {
            return "<?php echo ArCaptcha::getWidget({$options})?>";
        });
    }
}
