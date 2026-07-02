<?php

namespace App\Console\Commands\Scraping\Company\PageJaune;

use App\Utils\Scraping\Scraper as ScrapingScraper;
use Illuminate\Console\Command;

class Scraper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scraping-pagejaune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrapping Page Jaune';

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
        if ($dom = $scraper->scrape("https://www.pagesjaunes.fr/annuaire/chercherlespros?quoiqui=a&ou=Paris%20%2875%29&quoiQuiInterprete=a&page=2")) {
            $parser = new Parser();
            $result = $parser->parse($dom);
            dd($result);
        } else {
            dd("NOK");
        }
    }
}
