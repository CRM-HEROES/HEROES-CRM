<?php

namespace App\Console\Commands\Scraping\Company\Infogreffe;

use App\Utils\Scraping\Scraper as ScrapingScraper;
use Illuminate\Console\Command;

class Scraper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scraping-infogreffe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrapping Infogreffe';

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
     * @return mixed
     */
    public function handle()
    {
        $scraper = new ScrapingScraper();
        if ($dom = $scraper->scrape("https://www.societe.com/cgi-bin/search?champs=sion")) {
            $parser = new Parser();
            $result = $parser->parse($dom);
            dd($result);
        } else {
            dd("NOK");
        }
    }
}
