<?php

namespace App\Providers;

use App\Models\Accept;
use App\Models\Bidding;
use App\Models\Contact;
use App\Models\EnqueteAnswer;
use App\Models\EnqueteItem;
use App\Models\File;
use App\Models\MailTemplate;
use App\Models\Paper;
use App\Models\RevConflict;
use App\Models\Review;
use App\Models\Score;
use App\Models\Setting;
use App\Models\Submit;
use App\Models\User;
use App\Observers\FileObserver;
use App\Observers\GenericObserver;
use App\Observers\PaperObserver;
use App\Observers\ScoreObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     * 送信しようとしている情報は保護されません
     * https://zakkuri.life/laravel-chrome-security/
     */
    public function boot(\Illuminate\Routing\UrlGenerator $url): void
    {
        User::observe(UserObserver::class);
        Paper::observe(PaperObserver::class);
        File::observe(FileObserver::class);
        Score::observe(ScoreObserver::class);
        Review::observe(GenericObserver::class);
        RevConflict::observe(GenericObserver::class);
        EnqueteAnswer::observe(GenericObserver::class);
        EnqueteItem::observe(GenericObserver::class);
        MailTemplate::observe(GenericObserver::class);
        Accept::observe(GenericObserver::class);
        Bidding::observe(GenericObserver::class);
        Setting::observe(GenericObserver::class);
        Contact::observe(GenericObserver::class);
        Submit::observe(GenericObserver::class);

        if (PHP_OS == "Linux") { // 本番環境なら
            URL::forceScheme('https');
        }
        if (config('app.env') == "production"){ // App::environment(['production'])) {
            URL::forceScheme('https');
        }
        // Schema::defaultStringLength(191);
        // App::setLocale('ja');
    }
}
