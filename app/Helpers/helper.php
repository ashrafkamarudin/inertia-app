<?php

use App\User;
use App\Workspace;
use Carbon\Carbon;
use App\BannedEmail;
use GuzzleHttp\Client;
use Carbon\CarbonInterval;
use GuzzleHttp\RequestOptions;
use Composer\Semver\Comparator;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\DomCrawler\Crawler;

if (!function_exists('serverGoToURL')) {
    function serverGoToURL($server)
    {
        if (!$server->connected) {
            return route('servers:installation', $server->id);
        } elseif ($server->changeIp) {
            return route('servers:changeip', $server->id);
        } elseif ($server->connected && !$server->online) {
            return route('servers:offline', $server->id);
        } elseif ($server->transferStatus == 'WAITING' || $server->transferStatus == 'TRANSFERRING') {
            return route('servers:transferring', $server->id);
        } elseif ($server->connected && $server->online) {
            return route('servers:show', $server->id);
        }
    }
}

if (!function_exists('active_link')) {
    function active_link($route, $active = 'active', $exact = false)
    {
        if ($route == null) {
            return null;
        }

        if ($exact) {
            return request()->url() === $route ? 'active' : null;
        }

        return strpos(request()->url(), $route) !== false ? 'active' : null;
    }
}

if (!function_exists('active_links')) {
    function active_links($routes, $active = 'active', $exact = false)
    {
        if ($routes == null) {
            return null;
        }

        if ($exact) {
            return in_array(request()->url(), $routes) ? 'active' : null;
        }

        foreach ($routes as $route) {
            if (strpos(request()->url(), $route) !== false) {
                return 'active';
            }

        }
    }
}

if (!function_exists('item_number')) {
    function item_number($model, $index)
    {
        return (($model->currentPage() - 1) * $model->perPage()) + ($index + 1);
    }
}

if (!function_exists('isEmailBanned')) {
    function isEmailBanned($email)
    {
        $emailHash = hash('sha512', $email);

        $bannedEmail = BannedEmail::where('email', $emailHash)->first();

        return $bannedEmail != null;
    }
}

if (!function_exists('mbToGb')) {
    function mbToGb($value)
    {
        return round($value / 1024, 2) . "GB";
    }
}

if (!function_exists('sizeToBytes')) {
    function sizeToBytes(string $from): ?int
    {
        $units  = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $number = substr($from, 0, -2);
        $suffix = strtoupper(substr($from, -2));

        //B or no suffix
        if (is_numeric(substr($suffix, 0, 1))) {
            return preg_replace('/[^\d]/', '', $from);
        }

        $exponent = array_flip($units)[$suffix] ?? null;
        if ($exponent === null) {
            return null;
        }

        return $number * (1024 ** $exponent);
    }
}

if (!function_exists('isMoreThan80')) {
    function isMoreThan80($value)
    {
        if ($value > 80) {
            return 'text-red-500';
        }
    }
}

if (!function_exists('isServerOwnerCan')) {
    function isServerOwnerCan($userID, $permission)
    {
        $user = User::find($userID);

        return $user->can($permission);
    }
}

if (!function_exists('canUserAddServer')) {
    function canUserAddServer(User $user)
    {
        if ($user->isNotA('trial', 'admin', 'sysadmin', 'finance', 'marketing', 'support', 'freeforever')) {
            $plan = app('SubscriptionModule')->setUser($user)->getPlanFor('server');

            $server_count = app('RunCloud.InternalSDK')
                ->service('server')
                ->get('/internal/resources/find/Server/count')
                ->payload([
                    RequestOptions::JSON => [
                        'where' => [
                            'user_id' => $user->id,
                        ],
                    ],
                ])
                ->execute();

            if ($server_count >= $plan->meta['numberOfServer']) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('cdn')) {
    function cdn($path, $manifestDirectory = null)
    {
        static $manifests = [];

        if (!starts_with($path, '/')) {
            $path = "/{$path}";
        }

        if ($manifestDirectory && !starts_with($manifestDirectory, '/')) {
            $manifestDirectory = "/{$manifestDirectory}";
        }

        if (file_exists(public_path($manifestDirectory . '/hot'))) {
            return new HtmlString("//localhost:8080{$path}");
        }

        $manifestPath = public_path($manifestDirectory . '/mix-manifest.json');

        if (!isset($manifests[$manifestPath])) {
            if (!file_exists($manifestPath)) {
                throw new Exception('The Mix manifest does not exist.');
            }

            $manifests[$manifestPath] = json_decode(file_get_contents($manifestPath), true);
        }

        $manifest = $manifests[$manifestPath];

        if (!isset($manifest[$path])) {
            throw new \Exception(
                "Unable to locate Mix file: {$path}. Please check your " .
                'webpack.mix.js output paths and try again.'
            );
        }

        $location = new HtmlString($manifestDirectory . $manifest[$path]);

        if (config('app.env') == 'production') {
            // return sprintf("https://cf1.cdn.manage.runcloud.io%s", $location);
            return sprintf(config('runcloud.cdn') . "%s", $location);
        } elseif (config('app.env') == 'staging') {
            // return sprintf("https://cf1.cdn.manage.runcloud.dev%s", $location);
            return sprintf(config('runcloud.cdn') . "%s", $location);
        }

        return sprintf("%s%s", config('app.url'), $location);
    }
}

if (!function_exists('getProgressBarColour')) {
    function getProgressBarColour($percentage)
    {
        if ($percentage <= 25) {
            return 'progress-bar-success';
        } elseif ($percentage <= 50) {
            return 'progress-bar-info';
        } elseif ($percentage <= 75) {
            return 'progress-bar-warning';
        } else {
            return 'progress-bar-danger';
        }
    }
}

if (!function_exists('getBrowser')) {
    function getBrowser()
    {
        $user_agent = request()->header('user-agent');

        $browser = "Unknown Browser";

        $browser_array = [
            '/msie/i'      => 'Internet Explorer',
            '/firefox/i'   => 'Firefox',
            '/safari/i'    => 'Safari',
            '/chrome/i'    => 'Chrome',
            '/edge/i'      => 'Edge',
            '/edg/i'       => 'Edge',
            '/opera/i'     => 'Opera',
            '/opr/i'       => 'Opera',
            '/netscape/i'  => 'Netscape',
            '/maxthon/i'   => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i'    => 'Handheld Browser',
        ];

        foreach ($browser_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $browser = $value;
            }
        }
        return $browser;
    }
}

if (!function_exists('getOS')) {
    function getOS()
    {
        $user_agent = request()->header('user-agent');

        $os_platform = "Unknown OS Platform";
        $os_array    = array(
            '/windows nt 10/i'      => 'Windows 10',
            '/windows nt 6.3/i'     => 'Windows 8.1',
            '/windows nt 6.2/i'     => 'Windows 8',
            '/windows nt 6.1/i'     => 'Windows 7',
            '/windows nt 6.0/i'     => 'Windows Vista',
            '/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     => 'Windows XP',
            '/windows xp/i'         => 'Windows XP',
            '/windows nt 5.0/i'     => 'Windows 2000',
            '/windows me/i'         => 'Windows ME',
            '/win98/i'              => 'Windows 98',
            '/win95/i'              => 'Windows 95',
            '/win16/i'              => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i'        => 'Mac OS 9',
            '/linux/i'              => 'Linux',
            '/ubuntu/i'             => 'Ubuntu',
            '/iphone/i'             => 'iPhone',
            '/ipod/i'               => 'iPod',
            '/ipad/i'               => 'iPad',
            '/android/i'            => 'Android',
            '/blackberry/i'         => 'BlackBerry',
            '/webos/i'              => 'Mobile',
        );
        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $os_platform = $value;
            }
        }
        return $os_platform;
    }
}

if (!function_exists('carbon_range_generator')) {
    function carbon_range_generator(Carbon $from, Carbon $to, $rangeType, $inclusive = true)
    {
        if ($from->gt($to)) {
            return null;
        }

        if ($rangeType == 'hour') {
            $step = CarbonInterval::hour();
        } elseif ($rangeType == 'day') {
            $step = CarbonInterval::day();
        } elseif ($rangeType == 'month') {
            $step = CarbonInterval::month();
        } elseif ($rangeType == 'year') {
            $step = CarbonInterval::year();
        }

        $period = new DatePeriod($from, $step, $to);

        // Convert the DatePeriod into a plain array of Carbon objects
        $range = [];

        foreach ($period as $p) {
            $range[] = new Carbon($p);
        }

        return !empty($range) ? $range : null;
    }
}

if (!function_exists('paymentMethodCountries')) {
    function paymentMethodCountries()
    {
        return cache()->remember('paymentMethodCountries', 43800, function () {
            $data = [];
            foreach (countryList() as $country) {
                $data[$country['slug']] = $country['title'];
            }

            return collect($data);
        });
    }
}

if (!function_exists('getCountryData')) {
    function getCountryData()
    {
        $countries = [
            ['slug' => 'AF', 'title' => 'Afghanistan', 'native' => 'غانستان', 'dial_code' => '+93'],
            ['slug' => 'AX', 'title' => 'Åland Islands', 'native' => 'Åland', 'dial_code' => ''],
            ['slug' => 'AL', 'title' => 'Albania', 'native' => 'Shqipëri', 'dial_code' => '+355'],
            ['slug' => 'DZ', 'title' => 'Algeria', 'native' => 'الجزائر', 'dial_code' => '+213'],
            ['slug' => 'AS', 'title' => 'American Samoa', 'native' => '', 'dial_code' => '+1 684'],
            ['slug' => 'AD', 'title' => 'Andorra', 'native' => '', 'dial_code' => '+376'],
            ['slug' => 'AO', 'title' => 'Angola', 'native' => '', 'dial_code' => '+244'],
            ['slug' => 'AI', 'title' => 'Anguilla', 'native' => '', 'dial_code' => '+1 264'],
            ['slug' => 'AQ', 'title' => 'Antarctica', 'native' => '', 'dial_code' => ''],
            ['slug' => 'AG', 'title' => 'Antigua and Barbuda', 'native' => '', 'dial_code' => '+1 268'],
            ['slug' => 'AR', 'title' => 'Argentina', 'native' => '', 'dial_code' => '+54'],
            ['slug' => 'AM', 'title' => 'Armenia', 'native' => 'Հայաստան', 'dial_code' => '+374'],
            ['slug' => 'AW', 'title' => 'Aruba', 'native' => '', 'dial_code' => '+297'],
            ['slug' => 'SH', 'title' => 'Ascension Island', 'native' => '', 'dial_code' => '+290'],
            ['slug' => 'AU', 'title' => 'Australia', 'native' => '', 'dial_code' => '+61'],
            ['slug' => 'AT', 'title' => 'Austria', 'native' => 'Österreich', 'dial_code' => '+43'],
            ['slug' => 'AZ', 'title' => 'Azerbaijan', 'native' => 'Azərbaycan', 'dial_code' => '+994'],
            ['slug' => 'BS', 'title' => 'Bahamas', 'native' => '', 'dial_code' => '+1 242'],
            ['slug' => 'BH', 'title' => 'Bahrain', 'native' => 'البحرين', 'dial_code' => '+973'],
            ['slug' => 'BD', 'title' => 'Bangladesh', 'native' => 'বাংলাদেশ', 'dial_code' => '+880'],
            ['slug' => 'BB', 'title' => 'Barbados', 'native' => '', 'dial_code' => '+1 246'],
            ['slug' => 'BY', 'title' => 'Belarus', 'native' => 'Беларусь', 'dial_code' => '+375'],
            ['slug' => 'BE', 'title' => 'Belgium', 'native' => 'België', 'dial_code' => '+32'],
            ['slug' => 'BZ', 'title' => 'Belize', 'native' => '', 'dial_code' => '+501'],
            ['slug' => 'BJ', 'title' => 'Benin', 'native' => 'Bénin', 'dial_code' => '+229'],
            ['slug' => 'BM', 'title' => 'Bermuda', 'native' => '', 'dial_code' => '+1 441'],
            ['slug' => 'BT', 'title' => 'Bhutan', 'native' => 'འབྲུག', 'dial_code' => '+975'],
            ['slug' => 'BO', 'title' => 'Bolivia', 'native' => '', 'dial_code' => '+591'],
            ['slug' => 'BA', 'title' => 'Bosnia and Herzegovina', 'native' => 'Босна и Херцеговина', 'dial_code' => '+387'],
            ['slug' => 'BW', 'title' => 'Botswana', 'native' => '', 'dial_code' => '+267'],
            ['slug' => 'BV', 'title' => 'Bouvet Island', 'native' => '', 'dial_code' => ''],
            ['slug' => 'BR', 'title' => 'Brazil', 'native' => 'Brasil', 'dial_code' => '+55'],
            ['slug' => 'IO', 'title' => 'British Indian Ocean Territory', 'native' => '', 'dial_code' => '+246'],
            ['slug' => 'VG', 'title' => 'British Virgin Islands', 'native' => '', 'dial_code' => '+1 284'],
            ['slug' => 'BN', 'title' => 'Brunei', 'native' => '', 'dial_code' => '+673'],
            ['slug' => 'BG', 'title' => 'Bulgaria', 'native' => 'България', 'dial_code' => '+359'],
            ['slug' => 'BF', 'title' => 'Burkina Faso', 'native' => '', 'dial_code' => '+226'],
            ['slug' => 'BI', 'title' => 'Burundi', 'native' => 'Uburundi', 'dial_code' => '+257'],
            ['slug' => 'KH', 'title' => 'Cambodia', 'native' => 'កម្ពុជា', 'dial_code' => '+855'],
            ['slug' => 'CM', 'title' => 'Cameroon', 'native' => 'Cameroun', 'dial_code' => '+237'],
            ['slug' => 'CA', 'title' => 'Canada', 'native' => '', 'dial_code' => '+1'],
            ['slug' => 'IC', 'title' => 'Canary Islands', 'native' => 'islas Canarias', 'dial_code' => '+34'],
            ['slug' => 'CV', 'title' => 'Cape Verde', 'native' => 'Kabu Verdi', 'dial_code' => '+238'],
            ['slug' => 'BQ', 'title' => 'Caribbean Netherlands', 'native' => '', 'dial_code' => '+599'],
            ['slug' => 'KY', 'title' => 'Cayman Islands', 'native' => '', 'dial_code' => '+ 345'],
            ['slug' => 'CF', 'title' => 'Central African Republic', 'native' => 'République centrafricaine', 'dial_code' => '+236'],
            ['slug' => 'EA', 'title' => 'Ceuta and Melilla', 'native' => 'Ceuta y Melilla', 'dial_code' => '+34'],
            ['slug' => 'TD', 'title' => 'Chad', 'native' => 'Tchad', 'dial_code' => '+235'],
            ['slug' => 'CL', 'title' => 'Chile', 'native' => '', 'dial_code' => '+56'],
            ['slug' => 'CN', 'title' => 'China', 'native' => '中国', 'dial_code' => '+86'],
            ['slug' => 'CX', 'title' => 'Christmas Island', 'native' => '', 'dial_code' => '+61'],
            ['slug' => 'CP', 'title' => 'Clipperton Island', 'native' => '', 'dial_code' => '+689'],
            ['slug' => 'CC', 'title' => 'Cocos (Keeling) Islands', 'native' => 'Kepulauan Cocos (Keeling)', 'dial_code' => '+61'],
            ['slug' => 'CO', 'title' => 'Colombia', 'native' => '', 'dial_code' => '+57'],
            ['slug' => 'KM', 'title' => 'Comoros', 'native' => 'جزر القمر', 'dial_code' => '+269'],
            ['slug' => 'CD', 'title' => 'Congo (DRC)', 'native' => 'Jamhuri ya Kidemokrasia ya Kongo', 'dial_code' => '+243'],
            ['slug' => 'CG', 'title' => 'Congo (Republic)', 'native' => 'Congo-Brazzaville', 'dial_code' => '+242'],
            ['slug' => 'CK', 'title' => 'Cook Islands', 'native' => '', 'dial_code' => '+682'],
            ['slug' => 'CR', 'title' => 'Costa Rica', 'native' => '', 'dial_code' => '+506'],
            ['slug' => 'CI', 'title' => 'Côte d’Ivoire', 'native' => '', 'dial_code' => '+225'],
            ['slug' => 'HR', 'title' => 'Croatia', 'native' => 'Hrvatska', 'dial_code' => '+385'],
            ['slug' => 'CU', 'title' => 'Cuba', 'native' => '', 'dial_code' => '+53'],
            ['slug' => 'CW', 'title' => 'Curaçao', 'native' => '', 'dial_code' => '+599'],
            ['slug' => 'CY', 'title' => 'Cyprus', 'native' => 'Κύπρος', 'dial_code' => '+537'],
            ['slug' => 'CZ', 'title' => 'Czech Republic', 'native' => 'Česká republika', 'dial_code' => '+420'],
            ['slug' => 'DK', 'title' => 'Denmark', 'native' => 'Danmark', 'dial_code' => '+45'],
            ['slug' => 'DJ', 'title' => 'Djibouti', 'native' => '', 'dial_code' => '+253'],
            ['slug' => 'DM', 'title' => 'Dominica', 'native' => '', 'dial_code' => '+1 767'],
            ['slug' => 'DO', 'title' => 'Dominican Republic', 'native' => 'República Dominicana', 'dial_code' => '+1 849'],
            ['slug' => 'EC', 'title' => 'Ecuador', 'native' => '', 'dial_code' => '+593'],
            ['slug' => 'EG', 'title' => 'Egypt', 'native' => 'مصر', 'dial_code' => '+20'],
            ['slug' => 'SV', 'title' => 'El Salvador', 'native' => '', 'dial_code' => '+503'],
            ['slug' => 'GQ', 'title' => 'Equatorial Guinea', 'native' => 'Guinea Ecuatorial', 'dial_code' => '+240'],
            ['slug' => 'ER', 'title' => 'Eritrea', 'native' => '', 'dial_code' => '+291'],
            ['slug' => 'EE', 'title' => 'Estonia', 'native' => 'Eesti', 'dial_code' => '+372'],
            ['slug' => 'ET', 'title' => 'Ethiopia', 'native' => '', 'dial_code' => '+251'],
            ['slug' => 'FK', 'title' => 'Falkland Islands', 'native' => 'Islas Malvinas', 'dial_code' => '+500'],
            ['slug' => 'FO', 'title' => 'Faroe Islands', 'native' => 'Føroyar', 'dial_code' => '+298'],
            ['slug' => 'FJ', 'title' => 'Fiji', 'native' => '', 'dial_code' => '+679'],
            ['slug' => 'FI', 'title' => 'Finland', 'native' => 'Suomi', 'dial_code' => '+358'],
            ['slug' => 'FR', 'title' => 'France', 'native' => '', 'dial_code' => '+33'],
            ['slug' => 'GF', 'title' => 'French Guiana', 'native' => 'Guyane française', 'dial_code' => '+594'],
            ['slug' => 'PF', 'title' => 'French Polynesia', 'native' => 'Polynésie française', 'dial_code' => '+689'],
            ['slug' => 'TF', 'title' => 'French Southern Territories', 'native' => 'Terres australes françaises', 'dial_code' => '+262'],
            ['slug' => 'GA', 'title' => 'Gabon', 'native' => '', 'dial_code' => '+241'],
            ['slug' => 'GM', 'title' => 'Gambia', 'native' => '', 'dial_code' => '+220'],
            ['slug' => 'GE', 'title' => 'Georgia', 'native' => 'საქართველო', 'dial_code' => '+995'],
            ['slug' => 'DE', 'title' => 'Germany', 'native' => 'Deutschland', 'dial_code' => '+49'],
            ['slug' => 'GH', 'title' => 'Ghana', 'native' => 'Gaana', 'dial_code' => '+233'],
            ['slug' => 'GI', 'title' => 'Gibraltar', 'native' => '', 'dial_code' => '+350'],
            ['slug' => 'GR', 'title' => 'Greece', 'native' => 'Ελλάδα', 'dial_code' => '+30'],
            ['slug' => 'GL', 'title' => 'Greenland', 'native' => 'Kalaallit Nunaat', 'dial_code' => '+299'],
            ['slug' => 'GD', 'title' => 'Grenada', 'native' => '', 'dial_code' => '+1 473'],
            ['slug' => 'GP', 'title' => 'Guadeloupe', 'native' => '', 'dial_code' => '+590'],
            ['slug' => 'GU', 'title' => 'Guam', 'native' => '', 'dial_code' => '+1 671'],
            ['slug' => 'GT', 'title' => 'Guatemala', 'native' => '', 'dial_code' => '+502'],
            ['slug' => 'GG', 'title' => 'Guernsey', 'native' => '', 'dial_code' => '+44'],
            ['slug' => 'GN', 'title' => 'Guinea', 'native' => 'Guinée', 'dial_code' => '+224'],
            ['slug' => 'GW', 'title' => 'Guinea-Bissau', 'native' => 'Guiné Bissau', 'dial_code' => '+245'],
            ['slug' => 'GY', 'title' => 'Guyana', 'native' => '', 'dial_code' => '+595'],
            ['slug' => 'HT', 'title' => 'Haiti', 'native' => '', 'dial_code' => '+509'],
            ['slug' => 'HM', 'title' => 'Heard & McDonald Islands', 'native' => '', 'dial_code' => '+672'],
            ['slug' => 'HN', 'title' => 'Honduras', 'native' => '', 'dial_code' => '+504'],
            ['slug' => 'HK', 'title' => 'Hong Kong', 'native' => '香港', 'dial_code' => '+852'],
            ['slug' => 'HU', 'title' => 'Hungary', 'native' => 'Magyarország', 'dial_code' => '+36'],
            ['slug' => 'IS', 'title' => 'Iceland', 'native' => 'Ísland', 'dial_code' => '+354'],
            ['slug' => 'IN', 'title' => 'India', 'native' => 'भारत', 'dial_code' => '+91'],
            ['slug' => 'ID', 'title' => 'Indonesia', 'native' => '', 'dial_code' => '+62'],
            ['slug' => 'IR', 'title' => 'Iran', 'native' => 'ایران', 'dial_code' => '+98'],
            ['slug' => 'IQ', 'title' => 'Iraq', 'native' => 'العراق', 'dial_code' => '+964'],
            ['slug' => 'IE', 'title' => 'Ireland', 'native' => '', 'dial_code' => '+353'],
            ['slug' => 'IM', 'title' => 'Isle of Man', 'native' => '', 'dial_code' => '+44'],
            ['slug' => 'IL', 'title' => 'Israel', 'native' => 'ישראל', 'dial_code' => '+972'],
            ['slug' => 'IT', 'title' => 'Italy', 'native' => 'Italia', 'dial_code' => '+39'],
            ['slug' => 'JM', 'title' => 'Jamaica', 'native' => '', 'dial_code' => '+1 876'],
            ['slug' => 'JP', 'title' => 'Japan', 'native' => '日本', 'dial_code' => '+81'],
            ['slug' => 'JE', 'title' => 'Jersey', 'native' => '', 'dial_code' => '+44'],
            ['slug' => 'JO', 'title' => 'Jordan', 'native' => 'الأردن', 'dial_code' => '+962'],
            ['slug' => 'KZ', 'title' => 'Kazakhstan', 'native' => 'Казахстан', 'dial_code' => '+7 7'],
            ['slug' => 'KE', 'title' => 'Kenya', 'native' => '', 'dial_code' => '+254'],
            ['slug' => 'KI', 'title' => 'Kiribati', 'native' => '', 'dial_code' => '+686'],
            ['slug' => 'XK', 'title' => 'Kosovo', 'native' => 'Kosovë', 'dial_code' => '+383'],
            ['slug' => 'KW', 'title' => 'Kuwait', 'native' => 'الكويت', 'dial_code' => '+965'],
            ['slug' => 'KG', 'title' => 'Kyrgyzstan', 'native' => 'Кыргызстан', 'dial_code' => '+996'],
            ['slug' => 'LA', 'title' => 'Laos', 'native' => 'ລາວ', 'dial_code' => '+856'],
            ['slug' => 'LV', 'title' => 'Latvia', 'native' => 'Latvija', 'dial_code' => '+371'],
            ['slug' => 'LB', 'title' => 'Lebanon', 'native' => 'لبنان', 'dial_code' => '+961'],
            ['slug' => 'LS', 'title' => 'Lesotho', 'native' => '', 'dial_code' => '+266'],
            ['slug' => 'LR', 'title' => 'Liberia', 'native' => '', 'dial_code' => '+231'],
            ['slug' => 'LY', 'title' => 'Libya', 'native' => 'ليبيا', 'dial_code' => '+218'],
            ['slug' => 'LI', 'title' => 'Liechtenstein', 'native' => '', 'dial_code' => '+423'],
            ['slug' => 'LT', 'title' => 'Lithuania', 'native' => 'Lietuva', 'dial_code' => '+370'],
            ['slug' => 'LU', 'title' => 'Luxembourg', 'native' => '', 'dial_code' => '+352'],
            ['slug' => 'MO', 'title' => 'Macao', 'native' => '澳門', 'dial_code' => '+853'],
            ['slug' => 'MK', 'title' => 'Macedonia (FYROM)', 'native' => 'Македонија', 'dial_code' => '+389'],
            ['slug' => 'MG', 'title' => 'Madagascar', 'native' => 'Madagasikara', 'dial_code' => '+261'],
            ['slug' => 'MW', 'title' => 'Malawi', 'native' => '', 'dial_code' => '+265'],
            ['slug' => 'MY', 'title' => 'Malaysia', 'native' => '', 'dial_code' => '+60'],
            ['slug' => 'MV', 'title' => 'Maldives', 'native' => '', 'dial_code' => '+960'],
            ['slug' => 'ML', 'title' => 'Mali', 'native' => '', 'dial_code' => '+223'],
            ['slug' => 'MT', 'title' => 'Malta', 'native' => '', 'dial_code' => '+356'],
            ['slug' => 'MH', 'title' => 'Marshall Islands', 'native' => '', 'dial_code' => '+692'],
            ['slug' => 'MQ', 'title' => 'Martinique', 'native' => '', 'dial_code' => '+596'],
            ['slug' => 'MR', 'title' => 'Mauritania', 'native' => 'موريتانيا', 'dial_code' => '+222'],
            ['slug' => 'MU', 'title' => 'Mauritius', 'native' => 'Moris', 'dial_code' => '+230'],
            ['slug' => 'YT', 'title' => 'Mayotte', 'native' => '', 'dial_code' => '+262'],
            ['slug' => 'MX', 'title' => 'Mexico', 'native' => '', 'dial_code' => '+52'],
            ['slug' => 'FM', 'title' => 'Micronesia', 'native' => '', 'dial_code' => '+691'],
            ['slug' => 'MD', 'title' => 'Moldova', 'native' => 'Republica Moldova', 'dial_code' => '+373'],
            ['slug' => 'MC', 'title' => 'Monaco', 'native' => '', 'dial_code' => '+377'],
            ['slug' => 'MN', 'title' => 'Mongolia', 'native' => 'Монгол', 'dial_code' => '+976'],
            ['slug' => 'ME', 'title' => 'Montenegro', 'native' => 'Crna Gora', 'dial_code' => '+382'],
            ['slug' => 'MS', 'title' => 'Montserrat', 'native' => '', 'dial_code' => '+1 664'],
            ['slug' => 'MA', 'title' => 'Morocco', 'native' => 'المغرب', 'dial_code' => '+212'],
            ['slug' => 'MZ', 'title' => 'Mozambique', 'native' => 'Moçambique', 'dial_code' => '+258'],
            ['slug' => 'MM', 'title' => 'Myanmar (Burma)', 'native' => 'မြန်မာ', 'dial_code' => '+95'],
            ['slug' => 'NA', 'title' => 'Namibia', 'native' => 'Namibië', 'dial_code' => '+264'],
            ['slug' => 'NR', 'title' => 'Nauru', 'native' => '', 'dial_code' => '+674'],
            ['slug' => 'NP', 'title' => 'Nepal', 'native' => 'नेपाल', 'dial_code' => '+977'],
            ['slug' => 'NL', 'title' => 'Netherlands', 'native' => 'Nederland', 'dial_code' => '+31'],
            ['slug' => 'NC', 'title' => 'New Caledonia', 'native' => 'Nouvelle-Calédonie', 'dial_code' => '+687'],
            ['slug' => 'NZ', 'title' => 'New Zealand', 'native' => '', 'dial_code' => '+64'],
            ['slug' => 'NI', 'title' => 'Nicaragua', 'native' => '', 'dial_code' => '+505'],
            ['slug' => 'NE', 'title' => 'Niger', 'native' => 'Nijar', 'dial_code' => '+227'],
            ['slug' => 'NG', 'title' => 'Nigeria', 'native' => '', 'dial_code' => '+234'],
            ['slug' => 'NU', 'title' => 'Niue', 'native' => '', 'dial_code' => '+683'],
            ['slug' => 'NF', 'title' => 'Norfolk Island', 'native' => '', 'dial_code' => '+672'],
            ['slug' => 'MP', 'title' => 'Northern Mariana Islands', 'native' => '', 'dial_code' => '+1 670'],
            ['slug' => 'KP', 'title' => 'North Korea', 'native' => '조선 민주주의 인민 공화국', 'dial_code' => '+850'],
            ['slug' => 'NO', 'title' => 'Norway', 'native' => 'Norge', 'dial_code' => '+47'],
            ['slug' => 'OM', 'title' => 'Oman', 'native' => 'عُمان', 'dial_code' => '+968'],
            ['slug' => 'PK', 'title' => 'Pakistan', 'native' => 'پاکستان', 'dial_code' => '+92'],
            ['slug' => 'PW', 'title' => 'Palau', 'native' => '', 'dial_code' => '+680'],
            ['slug' => 'PS', 'title' => 'Palestine', 'native' => 'فلسطين', 'dial_code' => '+970'],
            ['slug' => 'PA', 'title' => 'Panama', 'native' => '', 'dial_code' => '+507'],
            ['slug' => 'PG', 'title' => 'Papua New Guinea', 'native' => '', 'dial_code' => '+675'],
            ['slug' => 'PY', 'title' => 'Paraguay', 'native' => '', 'dial_code' => '+595'],
            ['slug' => 'PE', 'title' => 'Peru', 'native' => 'Perú', 'dial_code' => '+51'],
            ['slug' => 'PH', 'title' => 'Philippines', 'native' => '', 'dial_code' => '+63'],
            ['slug' => 'PN', 'title' => 'Pitcairn Islands', 'native' => '', 'dial_code' => '+872'],
            ['slug' => 'PL', 'title' => 'Poland', 'native' => 'Polska', 'dial_code' => '+48'],
            ['slug' => 'PT', 'title' => 'Portugal', 'native' => '', 'dial_code' => '+351'],
            ['slug' => 'PR', 'title' => 'Puerto Rico', 'native' => '', 'dial_code' => '+1 939'],
            ['slug' => 'QA', 'title' => 'Qatar', 'native' => 'قطر', 'dial_code' => '+974'],
            ['slug' => 'RE', 'title' => 'Réunion', 'native' => 'La Réunion', 'dial_code' => '+262'],
            ['slug' => 'RO', 'title' => 'Romania', 'native' => 'România', 'dial_code' => '+40'],
            ['slug' => 'RU', 'title' => 'Russia', 'native' => 'Россия', 'dial_code' => '+7'],
            ['slug' => 'RW', 'title' => 'Rwanda', 'native' => '', 'dial_code' => '+250'],
            ['slug' => 'BL', 'title' => 'Saint Barthélemy', 'native' => 'Saint-Barthélemy', 'dial_code' => '+590'],
            ['slug' => 'SH', 'title' => 'Saint Helena', 'native' => '', 'dial_code' => '+290'],
            ['slug' => 'KN', 'title' => 'Saint Kitts and Nevis', 'native' => '', 'dial_code' => '+1 869'],
            ['slug' => 'LC', 'title' => 'Saint Lucia', 'native' => '', 'dial_code' => '+1 758'],
            ['slug' => 'MF', 'title' => 'Saint Martin', 'native' => '', 'dial_code' => '+590'],
            ['slug' => 'PM', 'title' => 'Saint Pierre and Miquelon', 'native' => 'Saint-Pierre-et-Miquelon', 'dial_code' => '+508'],
            ['slug' => 'WS', 'title' => 'Samoa', 'native' => '', 'dial_code' => '+685'],
            ['slug' => 'SM', 'title' => 'San Marino', 'native' => '', 'dial_code' => '+378'],
            ['slug' => 'ST', 'title' => 'São Tomé and Príncipe', 'native' => 'São Tomé e Príncipe', 'dial_code' => '+239'],
            ['slug' => 'SA', 'title' => 'Saudi Arabia', 'native' => 'المملكة العربية السعودية', 'dial_code' => '+966'],
            ['slug' => 'SN', 'title' => 'Senegal', 'native' => 'Sénégal', 'dial_code' => '+221'],
            ['slug' => 'RS', 'title' => 'Serbia', 'native' => 'Србија', 'dial_code' => '+381'],
            ['slug' => 'SC', 'title' => 'Seychelles', 'native' => '', 'dial_code' => '+248'],
            ['slug' => 'SL', 'title' => 'Sierra Leone', 'native' => '', 'dial_code' => '+232'],
            ['slug' => 'SG', 'title' => 'Singapore', 'native' => '', 'dial_code' => '+65'],
            ['slug' => 'SX', 'title' => 'Sint Maarten', 'native' => '', 'dial_code' => '+599'],
            ['slug' => 'SK', 'title' => 'Slovakia', 'native' => 'Slovensko', 'dial_code' => '+421'],
            ['slug' => 'SI', 'title' => 'Slovenia', 'native' => 'Slovenija', 'dial_code' => '+386'],
            ['slug' => 'SB', 'title' => 'Solomon Islands', 'native' => '', 'dial_code' => '+677'],
            ['slug' => 'SO', 'title' => 'Somalia', 'native' => 'Soomaaliya', 'dial_code' => '+252'],
            ['slug' => 'ZA', 'title' => 'South Africa', 'native' => '', 'dial_code' => '+27'],
            ['slug' => 'GS', 'title' => 'South Georgia & South Sandwich Islands', 'native' => '', 'dial_code' => '+500'],
            ['slug' => 'KR', 'title' => 'South Korea', 'native' => '대한민국', 'dial_code' => '+82'],
            ['slug' => 'SS', 'title' => 'South Sudan', 'native' => 'جنوب السودان', 'dial_code' => '+211'],
            ['slug' => 'ES', 'title' => 'Spain', 'native' => 'España', 'dial_code' => '+34'],
            ['slug' => 'LK', 'title' => 'Sri Lanka', 'native' => 'ශ්‍රී ලංකාව', 'dial_code' => '+94'],
            ['slug' => 'VC', 'title' => 'St. Vincent & Grenadines', 'native' => '', 'dial_code' => '+1 784'],
            ['slug' => 'SD', 'title' => 'Sudan', 'native' => 'السودان', 'dial_code' => '+249'],
            ['slug' => 'SR', 'title' => 'Suriname', 'native' => '', 'dial_code' => '+597'],
            ['slug' => 'SJ', 'title' => 'Svalbard and Jan Mayen', 'native' => 'Svalbard og Jan Mayen', 'dial_code' => '+47'],
            ['slug' => 'SZ', 'title' => 'Swaziland', 'native' => '', 'dial_code' => '+268'],
            ['slug' => 'SE', 'title' => 'Sweden', 'native' => 'Sverige', 'dial_code' => '+46'],
            ['slug' => 'CH', 'title' => 'Switzerland', 'native' => 'Schweiz', 'dial_code' => '+41'],
            ['slug' => 'SY', 'title' => 'Syria', 'native' => 'سوريا', 'dial_code' => '+963'],
            ['slug' => 'TW', 'title' => 'Taiwan', 'native' => '台灣', 'dial_code' => '+886'],
            ['slug' => 'TJ', 'title' => 'Tajikistan', 'native' => '', 'dial_code' => '+992'],
            ['slug' => 'TZ', 'title' => 'Tanzania', 'native' => '', 'dial_code' => '+255'],
            ['slug' => 'TH', 'title' => 'Thailand', 'native' => 'ไทย', 'dial_code' => '+66'],
            ['slug' => 'TL', 'title' => 'Timor-Leste', 'native' => '', 'dial_code' => '+670'],
            ['slug' => 'TG', 'title' => 'Togo', 'native' => '', 'dial_code' => '+228'],
            ['slug' => 'TK', 'title' => 'Tokelau', 'native' => '', 'dial_code' => '+690'],
            ['slug' => 'TO', 'title' => 'Tonga', 'native' => '', 'dial_code' => '+676'],
            ['slug' => 'TT', 'title' => 'Trinidad and Tobago', 'native' => '', 'dial_code' => '+1 868'],
            ['slug' => 'TA', 'title' => 'Tristan da Cunha', 'native' => '', 'dial_code' => '+290'],
            ['slug' => 'TN', 'title' => 'Tunisia', 'native' => 'تونس', 'dial_code' => '+216'],
            ['slug' => 'TR', 'title' => 'Turkey', 'native' => 'Türkiye', 'dial_code' => '+90'],
            ['slug' => 'TM', 'title' => 'Turkmenistan', 'native' => '', 'dial_code' => '+993'],
            ['slug' => 'TC', 'title' => 'Turks and Caicos Islands', 'native' => '', 'dial_code' => '+1 649'],
            ['slug' => 'TV', 'title' => 'Tuvalu', 'native' => '', 'dial_code' => '+688'],
            ['slug' => 'UM', 'title' => 'U.S. Outlying Islands', 'native' => '', 'dial_code' => '+246'],
            ['slug' => 'VI', 'title' => 'U.S. Virgin Islands', 'native' => '', 'dial_code' => '+1 340'],
            ['slug' => 'UG', 'title' => 'Uganda', 'native' => '', 'dial_code' => '+256'],
            ['slug' => 'UA', 'title' => 'Ukraine', 'native' => 'Україна', 'dial_code' => '+380'],
            ['slug' => 'AE', 'title' => 'United Arab Emirates', 'native' => 'الإمارات العربية المتحدة', 'dial_code' => '+971'],
            ['slug' => 'GB', 'title' => 'United Kingdom', 'native' => '', 'dial_code' => '+44'],
            ['slug' => 'US', 'title' => 'United States', 'native' => '', 'dial_code' => '+1'],
            ['slug' => 'UY', 'title' => 'Uruguay', 'native' => '', 'dial_code' => '+598'],
            ['slug' => 'UZ', 'title' => 'Uzbekistan', 'native' => 'Oʻzbekiston', 'dial_code' => '+998'],
            ['slug' => 'VU', 'title' => 'Vanuatu', 'native' => '', 'dial_code' => '+678'],
            ['slug' => 'VA', 'title' => 'Vatican City', 'native' => 'Città del Vaticano', 'dial_code' => '+379'],
            ['slug' => 'VE', 'title' => 'Venezuela', 'native' => '', 'dial_code' => '+58'],
            ['slug' => 'VN', 'title' => 'Vietnam', 'native' => 'Việt Nam', 'dial_code' => '+84'],
            ['slug' => 'WF', 'title' => 'Wallis and Futuna', 'native' => '', 'dial_code' => '+681'],
            ['slug' => 'EH', 'title' => 'Western Sahara', 'native' => 'الصحراء الغربية', 'dial_code' => '+212'],
            ['slug' => 'YE', 'title' => 'Yemen', 'native' => 'اليمن', 'dial_code' => '+967'],
            ['slug' => 'ZM', 'title' => 'Zambia', 'native' => '', 'dial_code' => '+260'],
            ['slug' => 'ZW', 'title' => 'Zimbabwe', 'native' => '', 'dial_code' => '+263'],
        ];

        return $countries;
    }
}

if (!function_exists('getDialCodes')) {
    function getDialCodes()
    {
        return cache()->remember('getDialCodes', 43800, function () {
            $data = [];
            foreach (getCountryData() as $country) {
                if ($country['dial_code'] != '') {
                    $data[$country['slug']] = sprintf("%s (%s)", $country['slug'], $country['dial_code']);
                }

            }
            ksort($data);

            return collect($data);
        });
    }
}

if (!function_exists('countryList')) {
    function countryList()
    {
        return cache()->remember('countryList', 43800, function () {
            return collect(getCountryData())->map(function ($item, $key) {
                $item['originalTitle'] = $item['title'];
                if ($item['native'] != '') {
                    $item['title'] = sprintf("%s (%s)", $item['title'], $item['native']);
                }
                unset($item['native']);
                return $item;
            });
        });
    }
}

if (!function_exists('getWebServer')) {
    function getWebServer($serverId)
    {
        return cache()->remember('webserverbject:' . $serverId, Carbon::now()->addHour(), function () use ($serverId) {
            return app('RunCloud.InternalSDK')
                ->service('server')
                ->get('/internal/resources/find/Server/pluck')
                ->payload([
                    \GuzzleHttp\RequestOptions::JSON => [
                        'where' => [
                            'id' => $serverId,
                        ],
                        'pluck' => ['webServerType'],
                    ],
                ])
                ->execute()[0];
        });
    }
}

if (!function_exists('displayWebServerType')) {
    function displayWebServerType($webServerType)
    {
        $definedWebServers = [
            'nginx' => 'NGINX',
            'ols'   => 'OpenLiteSpeed',
        ];
        return $definedWebServers[$webServerType];
    }
}

if (!function_exists('getLatestAgentVersion')) {
    function getLatestAgentVersion($web_server_type, $force = false)
    {
        $cache_key = sprintf('os_agent_version:%s:%s', $web_server_type, config('app.env'));

        if ($force) {
            Cache::forget($cache_key);
        }

        return Cache::remember($cache_key, 60 * 72, function () use ($web_server_type) {
            $base_url   = config('app.env') == 'production' ? 'https://release.runcloud.io' : 'https://release.runcloud.dev';
            $agent_type = $web_server_type == 'ols' ? '-lsws' : '';

            $repo_url = sprintf('%s/pool/main/r/runcloud-agent%s/', $base_url, $agent_type);

            $response = (new Client())->get($repo_url);

            $html_content = $response->getBody()->getContents();

            $crawler = (new Crawler($html_content))->filterXPath('//body//a');

            $os_agent_version = [];
            $regex_pattern    = sprintf('/runcloud-agent%s_(.*)-\d+\+(.*)\+(\d+)_amd64/', $agent_type);

            foreach ($crawler as $domElement) {
                if (preg_match_all($regex_pattern, $domElement->nodeValue, $matches)) {
                    $os_version    = $matches[2][0];
                    $agent_version = "{$matches[1][0]}.{$matches[3][0]}";

                    if (!isset($os_agent_version[$os_version])) {
                        $os_agent_version[$os_version] = $agent_version;
                    } elseif (Comparator::greaterThan($agent_version, $os_agent_version[$os_version])) {
                        $os_agent_version[$os_version] = $agent_version;
                    }
                }
            }

            return $os_agent_version;
        });
    }
}

if (!function_exists('decoupleAgentVersion')) {
    function decoupleAgentVersion($agentVersion): array
    {
        $versions = [
            'agent' => '1.0.0.0',
            'os'    => 'Ubuntu 16.04',
            'osKey' => 'ubuntu16.04',
        ];

        if (preg_match_all('/(.*)-\d+\+(.*)\+(\d+)/', $agentVersion, $matches)) {
            $versions['agent'] = "{$matches[1][0]}.{$matches[3][0]}";
            $versions['os']    = str_replace('ubuntu', 'Ubuntu ', $matches[2][0]);
            $versions['osKey'] = $matches[2][0];
        }

        return $versions;
    }
}

if (!function_exists('isLatestAgentVersion')) {
    function isLatestAgentVersion($agentVersion, $upstreamVersion): bool
    {
        $versions = decoupleAgentVersion($agentVersion);

        if (!isset($upstreamVersion[$versions['osKey']])) {
            return false;
        }

        return Comparator::greaterThanOrEqualTo($versions['agent'], $upstreamVersion[$versions['osKey']]);
    }
}

if (!function_exists('displayAmountWithCurrency')) {
    function displayAmountWithCurrency($price)
    {
        $sign = '';

        if ($price < 0) {
            $sign = '-';
            $price *= -1;
        }

        return sprintf('%s$%s', $sign, number_format($price, 2));
    }
}

if (!function_exists('phpVersionDisplay')) {
    function phpVersionDisplay($version)
    {
        $trim = implode('.', str_split(preg_replace(['/php/', '/rc/', '/ls/'], '', $version)));

        return $trim;
    }
}

if (!function_exists('paypalCreditSelection')) {
    function paypalCreditSelection()
    {
        return [
            8        => '$8.00',
            10       => '$10.00',
            15       => '$15.00',
            45       => '$45.00',
            50       => '$50.00',
            100      => '$100.00',
            150      => '$150.00',
            300      => '$300.00',
            450      => '$450.00',
            500      => '$500.00',
            1000     => '$1000.00',
            'custom' => 'Custom Amount',
        ];
    }
}

if (!function_exists('groupLogByDays')) {
    function groupLogByDays($logs)
    {
        return collect($logs)->sortByDesc('created_at')->groupBy(function ($log) {
            return \Carbon\Carbon::parse($log->created_at)->toDateString();
        })->mapWithKeys(function ($logs, $date) {

            $date = \Carbon\Carbon::parse($date);
            if ($date->isToday() || $date->isYesterday()) {
                $date = $date->isToday() ? 'today' : 'yesterday';
            }

            return [($date instanceof \Carbon\Carbon ? $date->format('M d, Y') : $date) => $logs];
        });
    }
}

if (!function_exists('convertToBytes')) {
    function convertToBytes(string $from): ?int
    {
        $units  = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $number = substr($from, 0, -2);
        $suffix = strtoupper(substr($from, -2));

        //B or no suffix
        if (is_numeric(substr($suffix, 0, 1))) {
            return preg_replace('/[^\d]/', '', $from);
        }

        $exponent = array_flip($units)[$suffix] ?? null;
        if ($exponent === null) {
            return null;
        }

        return $number * (1024 ** $exponent);
    }
}

if (!function_exists('getGeoRecordFromIpAddress')) {
    function getGeoRecordFromIpAddress($ipAddress): array
    {
        $GEORecord = app('GeoIPService')->getRecordFromIP($ipAddress);

        if ($GEORecord != null && $GEORecord->country != null && $GEORecord->country->names != null) {
            return [
                'country'          => $GEORecord->country->names['en'],
                'country_iso_code' => $GEORecord->country->isoCode,
                'subdivision'      => count($GEORecord->subdivisions) > 0 ? $GEORecord->subdivisions[0]->names['en'] : 'Unknown',
            ];
        }

        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false) {
            return [
                'country'          => 'Private',
                'subdivision'      => 'Private',
                'country_iso_code' => 'N/A',
            ];
        }

        return [
            'country'          => 'Unknown',
            'subdivision'      => 'Unknown',
            'country_iso_code' => 'N/A',
        ];
    }
}

if (!function_exists('findLongestWordCount')) {
    function findLongestWordCount($string): string
    {
        $words = explode(' ', $string);
        usort($words, function ($a, $b) {return strlen($b) - strlen($a);});
        return strlen($words[0]); //returns length of the longest word in arg ($string)
    }
}

if (!function_exists('getArtisanCommands')) {
    function getArtisanCommands(): array
    {
        return [
            'clear-compiled',
            'up',
            'down',
            'cache:clear',
            'cache:forget',
            'config:cache',
            'config:clear',
            'key:generate',
            'migrate',
            'optimize:clear',
            'package:discover',
            'queue:flush',
            'queue:forget',
            'queue:restart',
            'queue:retry',
            'route:cache',
            'route:clear',
            'view:clear',
            'view:cache',
        ];
    }
}

if (!function_exists('laravelOctaneRequirement')) {
    function laravelOctaneRequirement()
    {
        return [
            'php'                => 'PHP 8 and above',
            'webApplicationType' => 'Web application type Laravel',
            'webServerType'      => 'Web server type NGINX',
        ];
    }
}

if (!function_exists('displayWebappType')) {
    function displayWebappType($type)
    {
        $webApp = [
            "concrete5"        => "Concrete5",
            "drupal"           => "Drupal",
            "grav"             => "Grav Core",
            "gravadmin"        => "Grav Core Admin",
            "joomla"           => "Joomla",
            "mybb"             => "MyBB",
            "octobercms"       => "OctoberCMS",
            "phpbb"            => "phpBB",
            "phpmyadmin"       => "PHPMyAdmin",
            "piwik"            => "Matomo (Piwik)",
            "prestashop"       => "PrestaShop",
            "wordpress"        => "WordPress",
            "Empty"            => "Custom",
            "github"           => "GitHub",
            "bitbucket"        => "BitBucket",
            "gitlab"           => "GitLab",
            "selfhostedgitlab" => "Self-hosted GitLab",
            "laravel"          => "Laravel",
        ];
        return $webApp[$type] ?? $type;
    }
}

if (!function_exists('getIpAddressCountry')) {
    function getIpAddressCountry($ipAddress): string
    {
        $GEORecord = app('GeoIPService')->getRecordFromIP($ipAddress);

        if ($GEORecord != null && $GEORecord->country != null && $GEORecord->country->names != null) {
            return $GEORecord->country->names["en"];
        }

        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false) {
            return 'Private';
        }

        return 'Unknown';
    }
}


if (!function_exists('canWorkspaceUser')) {
    function canWorkspaceUser(array $roles = [])
    {
        $workspace = auth()->user();
        $user = auth()->guard('workspace-user')->user();
        $allowedRoles = array_merge(['owner'], $roles);
        $role = cache()
            ->tags(sprintf("workspace:%s", $workspace->id))
            ->remember(sprintf('workspace:%s|user:%s|middleware-authorization', $workspace->id, $user->id),
                60,
                function () use ($allowedRoles, $workspace, $user) {
                    $workspace = Workspace::find($workspace->id);
                    return app('WorkspaceModule')->setWorkspace($workspace)->members()->getRole($user);
                });

        if ($role == null || !in_array($role->slug, $allowedRoles)) return false;

        return true;
    }
}

if (!function_exists('workspaceAudit')) {
    function workspaceAudit(string $event, array $model = [], array $changes)
    {
        $workspaceUser = auth()->guard('workspace-user')->user();

        if ($workspaceUser != null) {
            app('RunCloud.Audit')
                ->on(auth()->user()->id)
                ->doing($event)
                ->model($model)
                ->performedBy([
                    'id'   => $workspaceUser->id,
                    'name' => $workspaceUser->name,
                ])
                ->changes($changes)
                ->send(true);
        }
    }
}

if (!function_exists('parseAudit')) {
    function parseAudit($audit): object
    {
        $replaceModelName = [
            'User'                      => 'Workspace',
            'WebApplicationSetting'     => 'WebApplicationSettings',
            'WebApplicationSecurity'    => 'SSL/TLS',
            'TransferServer'            => 'ServerTransfer',
            'ServerUser'                => 'SystemUser',
            'WhitelistIpNotification'   => 'WhitelistIPNotification',
            'CanvasSetting'             => 'CanvasSettings',
            'Api'                       => 'API',
            'ApiRestriction'            => 'APIRestriction',
            'SshCredential'             => 'SSHCredential',
            'Vault'                     => 'SSHKeyVault'
        ];

        $template = [
            'created'             => '<strong>%s</strong> has added %s: <strong>%s</strong>',
            'created_'            => '<strong>%s</strong> has added %s',
            'updated'             => '<strong>%s</strong> has edited the %s to %s',
            'deleted'             => '<strong>%s</strong> has removed %s: <strong>%s</strong>',
            'deleted_'            => '<strong>%s</strong> has removed %s',
            'invited'             => '<strong>%s</strong> has invited <strong>%s</strong> into the workspace as: <strong>%s</strong>',
            'transferred'         => '<strong>%s</strong> has transferred the workspace ownership to <strong>%s</strong> and self-assigned as: <strong>%s</strong>',
            'rename'              => '<strong>%s</strong> has renamed a file/folder from %s to <strong>%s</strong>',
            'zip'                 => '<strong>%s</strong> has created a zip folder: <strong>%s</strong>',
            'unzip'               => '<strong>%s</strong> has extracted <strong>%s</strong> into folder: <strong>%s</strong>',
        ];

        $identifiers = [
            'Workspace'                 => 'name', // good
            'WorkspaceMember'           => 'user', // good
            'API'                       => 'label', // good
            'APIRestriction'            => 'ipAddress', // good
            'Company'                   => 'companyName', // good
            'Subscription'              => 'subscription_id', 
            'ServerTransfer'            => 'server_id', // good
            'PaymentMethod'             => 'identifier', // good
            'AtomicProject'             => 'name', 
            'AtomicScript'              => 'name', // good
            'CronJob'                   => 'label', // good
            'PaymentMethod'             => 'identifier', // good
            'Database'                  => 'name', // assign/revoke missing
            'DatabaseUser'              => 'username', // change password
            'Domain'                    => 'name', // good
            'SSL/TLS'                   => 'method', // good
            'Firewall'                  => 'port', // good
            'ScriptInstaller'           => 'name', // good
            'SystemUser'                => 'username', // good
            'SSHCredential'             => 'label', // good
            'Supervisor'                => 'label', // good
            'VersionControl'            => 'repository',
            'WebApplication'            => 'name', // good
            'Canvas'                    => 'name', // good
            'CanvasItem'                => 'name', // good
            'SSHKeyVault'               => 'label', // good
            'DNSManager'               => 'name', // good
        ];

         /** No identifiers
         * Atomic Symlink
         * SSH Config
         * Web Application Firewall
         * Web Application Firewall Rule
         * Web Application Run Cache
         * Web Application Security
         * Web Application Settings
         * Canvas Settings
         * File Manager
         */
        
        // Remove 'App/', replace undesired model names & other prep work
        $audit->model = str_replace('App\\', '', $audit->model);
        if (array_key_exists($audit->model, $replaceModelName)) $audit->model = $replaceModelName[$audit->model];

        $changes = json_decode($audit->changes);
        $keys = array_keys(get_object_vars($changes));
        $modelName = preg_replace('/(?<=[a-z])[A-Z]|[A-Z](?=[a-z])/', ' $0', $audit->model);
        
        // Parse changes into defined string templates
        switch ($audit->event) {
            case 'rename':
                $audit->toString = sprintf($template[$audit->event], $audit->performed_by, optional($changes->rename)->oldName, optional($changes->rename)->newName);
                break;
            case 'invited':
                $audit->toString = sprintf($template[$audit->event], $audit->performed_by, $changes->user, $changes->role);
                break;
            case 'transferred':
                $audit->toString = sprintf($template[$audit->event], $audit->performed_by, $changes->user, $changes->role);
                break;
            case 'zip':
                $audit->toString = sprintf($template[$audit->event], $audit->performed_by, optional($changes)->destination);
                break;
            case 'unzip':
                $audit->toString = sprintf($template[$audit->event], $audit->performed_by, optional($changes)->zipFolder, optional($changes)->destination);
                break;
            case 'updated':
                if ($audit->model == 'WorkspaceMember') {
                    $audit->toString = sprintf('<strong>%s</strong> has assigned the role for %s as: <strong>%s</strong>', $audit->performed_by, $changes->user, $changes->role);
                    break;
                }
                if ($audit->model == 'DNSManager') {
                    $audit->toString = sprintf($template[$audit->event], $audit->performed_by, $changes->entity, $changes->name);
                    break;
                }
                // permission
                if ($audit->model == 'FileManager' && isset($changes->permission)) {
                    $audit->toString = sprintf('<strong>%s</strong> has edited the permission(s) for file: <strong>%s</strong>', $audit->performed_by,  implode(', ', optional($changes)->names));
                    break;
                } else if ($audit->model == 'FileManager') {
                // edit file
                    $audit->toString = sprintf('<strong>%s</strong> has edited the file content: <strong>%s</strong>', $audit->performed_by,  $changes->rootFolder . $changes->file);
                    break;
                }
                if (count($keys) <= 3) $audit->toString = sprintf($template[$audit->event], $audit->performed_by, '<code class="px-1 py-0.5 bg-gray-50 text-black text-sm rounded ">' . implode(', ', $keys) . '</code>', $modelName);
                else $audit->toString = sprintf($template[$audit->event], $audit->performed_by, '<strong>' . count($keys) . ' fields </strong>', $modelName);
                break;
            default:
                if ($audit->model == 'FileManager' && $audit->event == 'created') {
                    $audit->toString = sprintf($template[$audit->event], $audit->performed_by, optional($changes->create)->type, optional($changes->create)->name);
                    break;
                }
                if ($audit->model == 'FileManager' && $audit->event == 'deleted') {
                    $audit->toString = sprintf($template[$audit->event], $audit->performed_by, 'file/folder', implode(', ', optional($changes)->names));
                    break;
                }
                if ($audit->model == 'DNSManager') {
                    $audit->toString = sprintf($template[$audit->event], $audit->performed_by, $changes->entity, $changes->name);
                    break;
                }

                if (!array_key_exists($audit->model, $identifiers)) $audit->toString = sprintf($template[$audit->event . '_'], $audit->performed_by, $modelName);
                else $audit->toString = sprintf($template[$audit->event], $audit->performed_by, $modelName, $changes->{ $identifiers[$audit->model] });
                break;
        }
        
        return $audit;

    }
}

if (!function_exists('isLoginAsWorkspace')) {
    function isLoginAsWorkspace()
    {
        // need to check db query here. if single call, no problem.
        // else, we need to hunt every code that use this lines and put it in a cache.
        return auth()->user()->isWorkspace();
    }
}

if (!function_exists('isLoginAsUser')) {
    function isLoginAsUser()
    {
        // need to check db query here. if single call, no problem.
        // else, we need to hunt every code that use this lines and put it in a cache.
        return auth()->user()->isUser();
    }
}
