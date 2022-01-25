<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateGEOIDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runcloud:updategeoipdb';
    private $licenseKey  = "mSmeIAiniU4nxZyM";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update geoip database. GEOIP DATABASE IS NOT TRACKED INSIDE GIT!!';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // check md5
        $client = new Client;
        $res    = $client->request('GET', sprintf('https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz.md5', $this->licenseKey));
        $newMD5 = $res->getBody();

        // if md5 tak sama or tak exists, then download geolite db
        $oldMD5Exists = File::exists(storage_path('files/geoip/GeoLite2-City.mmdb-md5'));

        if (!$oldMD5Exists) {
            File::put(storage_path('files/geoip/GeoLite2-City.mmdb-md5'), $newMD5);
            $this->download();
        } else {
            $oldMD5 = File::get(storage_path('files/geoip/GeoLite2-City.mmdb-md5'));
            if ($oldMD5 != $newMD5) {
                $this->download();
                File::put(storage_path('files/geoip/GeoLite2-City.mmdb-md5'), $newMD5);
            }
        }

        // cleanup after everything
        $this->cleanup();
    }

    private function download()
    {
        // download and letak dalam storage path
        try {
            $client = new Client;
            $client->request('GET',
                sprintf('https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz', $this->licenseKey),
                [
                    'sink' => storage_path('files/geoip/GeoLite2-City.tar.gz'),
                ]
            );

            $this->tar();
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
        }
    }

    private function tar()
    {
        // tarkan and rename to GeoLite2-City.mmdb-tmp
        try {
            $phar = new \PharData(storage_path('files/geoip/GeoLite2-City.tar.gz'));
            $phar->extractTo(
                storage_path('files/geoip/extracted'),
                null,
                true
            ); // extract all files

            $extractedFile = storage_path(
                sprintf('files/geoip/extracted/%s/GeoLite2-City.mmdb', $phar->getBasename())
            );

            $this->copy($extractedFile);
        } catch (Exception $e) {
            app('sentry')->captureException($e);
        }
    }

    private function copy($newFile)
    {
        // remove GeoLite2-City.mmdb-old
        File::delete(storage_path('files/geoip/GeoLite2-City.mmdb-old'));

        try {
            // rename GeoLite2-City.mmdb-data to GeoLite2-City.mmdb-old
            File::move(storage_path('files/geoip/GeoLite2-City.mmdb-data'), storage_path('files/geoip/GeoLite2-City.mmdb-old'));
        } catch (\Exception $e) {}

        // rename GeoLite2-City.mmdb-tmp to GeoLite2-City.mmdb-data
        File::move($newFile, storage_path('files/geoip/GeoLite2-City.mmdb-data'));
    }

    private function cleanup()
    {
        File::deleteDirectory(storage_path('files/geoip/extracted'));
        File::delete(storage_path('files/geoip/GeoLite2-City.tar.gz'));
    }
}
