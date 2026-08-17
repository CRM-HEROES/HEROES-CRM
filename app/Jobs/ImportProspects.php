<?php

namespace App\Jobs;

use App\Jobs\Import\ImportColumnToField\CalendarField;
use App\Jobs\Import\ImportColumnToField\DefaultField;
use App\Jobs\Import\ImportColumnToField\EventField;
use App\Jobs\Import\ImportColumnToField\GroupField;
use App\Jobs\Import\ImportColumnToField\InteractionField;
use App\Jobs\Import\ImportColumnToField\LabelField;
use App\Jobs\Import\ImportColumnToField\LinkField;
use App\Jobs\Import\ImportColumnToField\MetaField;
use App\Jobs\Import\ImportColumnToField\OrderField;
use App\Jobs\Import\ImportColumnToField\SmsField;
use App\Jobs\Import\ImportColumnToField\MessageField;
use App\Jobs\Import\ImportColumnToField\UserField;
use App\Jobs\Import\ImportColumnToField\UserRepository;

use App\Jobs\Import\ProspectItemsHandler\EventsHandler;
use App\Jobs\Import\ProspectItemsHandler\GroupsHandler;
use App\Jobs\Import\ProspectItemsHandler\InteractionsHandler;
use App\Jobs\Import\ProspectItemsHandler\LabelsHandler;
use App\Jobs\Import\ProspectItemsHandler\LinksHandler;
use App\Jobs\Import\ProspectItemsHandler\MessagesHandler;
use App\Jobs\Import\ProspectItemsHandler\OrdersHandler;
use App\Jobs\Import\ProspectItemsHandler\SmsHandler;
use App\Jobs\Import\ProspectItemsHandler\UsersHandler;
use App\Events\ImportFinished;
use App\Models\Import;
use App\Services\ProspectAutoAssignment;
// The trait file is named Sendswelcomesms.php. Keep the import spelling in
// sync with the file for case-sensitive production filesystems.
use App\Jobs\Import\Sendswelcomesms as SendsWelcomeSms;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportProspects implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SendsWelcomeSms;

    const MAPPING_FIELD_DEFAULT = 0;
    const MAPPING_FIELD_META = 1;
    const MAPPING_FIELD_LABEL = 2;
    const MAPPING_FIELD_EVENT = 3;
    const MAPPING_FIELD_MESSAGE = 4;
    const MAPPING_FIELD_ORDER = 5;
    const MAPPING_FIELD_SMS = 6;
    const MAPPING_FIELD_INTERACTION = 7;
    const MAPPING_FIELD_LINK = 8;
    const MAPPING_FIELD_USER = 9;
    const MAPPING_FIELD_GROUP = 10;
    const MAPPING_FIELD_CALENDAR = 11;

    protected $import;
    protected $limit = 500;
    protected $date;
    protected $createdDate;
    protected $categories;
    protected $threads;
    protected $calendars;
    protected $columnToFieldHandlers;
    protected $prospectRelationsHandlers;
    protected $mapping;
    protected $emptyProspect;
    protected $seenDuplicates = ['email' => [], 'phone' => [], 'mobile' => []];
    protected $existingEmails = [];
    protected $existingMobiles = [];
    protected $suspiciousRowsCount = 0;
    protected $updatedDuplicatesCount = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($import)
    {
        $this->import = $import;
        $this->date = Carbon::now()->format('Y-m-d H:i:s');
        $this->categories = $this->getCategories();
        $this->threads = $this->getThreads();
        $this->calendars = $this->getCalendars();

        $this->columnToFieldHandlers = $this->getColumnToFieldHandlers();
        $this->prospectRelationsHandlers = $this->getProspectRelationsHandlers();

        $this->mapping = $this->getImportMappingFields();

        $this->emptyProspect = $this->newProspect();

        // Emails / mobiles déjà présents en base (pour éviter les répétitions)
        $this->existingEmails = $this->getExistingEmails();
        $this->existingMobiles = $this->getExistingMobiles();
    }

    /**
     * Return the CRM field that must receive a standard Meta/Google Sheets
     * column.  This is deliberately applied again by the worker: an import
     * may have been created with an older automatic mapping before it is
     * started (or restarted).
     */
    protected function getKnownDefaultField($header): ?string
    {
        $header = $this->normalizeHeaderText($header);

        return [
            'email' => 'email',
            'e mail' => 'email',
            'mail' => 'email',
            'full name' => 'full_name',
            'nom complet' => 'full_name',
            'name' => 'full_name',
            'phone number' => 'mobile_phone_number',
            'numero de telephone' => 'mobile_phone_number',
            'telephone' => 'mobile_phone_number',
            'telephone portable' => 'mobile_phone_number',
            'mobile' => 'mobile_phone_number',
            'phone' => 'mobile_phone_number',
            'created time' => 'created_at',
            'created at' => 'created_at',
            'date de creation' => 'created_at',
        ][$header] ?? null;
    }

    /**
     * Normalize a header for comparison: strip accents/case/punctuation so
     * that "E-mail", "e_mail" and "E Mail" are recognised as the same header.
     */
    protected function normalizeHeaderText($header): string
    {
        $header = Str::ascii((string) $header);
        $header = str_replace(['\\_', '_'], ' ', $header);
        $header = preg_replace('/[^a-zA-Z0-9]+/', ' ', $header);

        return strtolower(trim(preg_replace('/\s+/', ' ', $header)));
    }

    /**
     * A spreadsheet's column meaning: the CRM field it is already known to
     * map to, or its normalized text when it isn't a recognised alias.
     * Comparing this between two headers tells us whether they refer to the
     * same column even if worded differently (e.g. "email" / "e-mail").
     */
    protected function resolveHeaderAlias($header): string
    {
        return $this->getKnownDefaultField($header) ?? $this->normalizeHeaderText($header);
    }

    /**
     * Build a map from a sheet's own column index to the column index of
     * the import's reference headers (taken from the first sheet). Needed
     * because a workbook can contain several tabs whose columns are not in
     * the same order, or are missing/adding a few, e.g. one Google Sheets
     * tab per campaign. Columns that cannot be matched to a reference
     * header are dropped rather than corrupting other fields by shifting
     * columns.
     */
    protected function buildSheetColumnMap(array $sheetHeader): array
    {
        $masterHeaders = $this->import->headers ?: [];

        if (empty($masterHeaders)) {
            // No reference headers to align to: keep the sheet's own order.
            return array_combine(array_keys($sheetHeader), array_keys($sheetHeader));
        }

        $masterAliases = array_map(function ($header) {
            return $this->resolveHeaderAlias($header);
        }, $masterHeaders);

        $columnMap = [];

        foreach ($sheetHeader as $sheetIndex => $header) {
            $masterIndex = array_search($this->resolveHeaderAlias($header), $masterAliases, true);

            if ($masterIndex !== false) {
                $columnMap[$sheetIndex] = $masterIndex;
            }
        }

        return $columnMap;
    }

    /**
     * Reorder a sheet's row so its values sit at the same column index as
     * the import's reference headers, using the map built by
     * buildSheetColumnMap(). Unmatched source columns are dropped.
     */
    protected function remapRowToMasterColumns(array $row, array $columnMap): array
    {
        $remapped = [];

        foreach ($columnMap as $sheetIndex => $masterIndex) {
            $remapped[$masterIndex] = $row[$sheetIndex] ?? null;
        }

        return $remapped;
    }

    /**
     * Repair automatic mappings generated by older versions of the importer.
     * Without this, a restarted import keeps `meta->full_name`, `meta->email`
     * and `meta->phone_number` even though the source columns are recognised.
     */
    protected function synchronizeKnownDefaultMappings(): void
    {
        $mapping = $this->import->mapping ?: [];
        $changed = false;

        foreach ($this->import->headers ?: [] as $index => $header) {
            $field = $this->getKnownDefaultField($header);
            if ($field && ($mapping[$index] ?? null) !== $field) {
                $mapping[$index] = $field;
                $changed = true;
            }
        }

        if ($changed) {
            $this->import->update(['mapping' => $mapping]);
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 8 queue workers listen on the same "imports" queue, and Redis'
        // retry_after (90s) is shorter than a large import can take to
        // process. When that happens, Redis considers the job lost and
        // hands the *same* import to a second, idle worker while the first
        // one is still running — the second run's removePreviousImportProspects()
        // then deletes the prospects the first run just created (and already
        // assigned), and its own re-insert sees them all as duplicates
        // (existingEmails/existingMobiles were preloaded from the first
        // run's data), so it ends up importing nothing. This lock ensures
        // only one worker processes a given import at a time; a duplicate
        // run releases immediately instead of racing the first one.
        // Fall back to the default cache store when Redis isn't available
        // (e.g. local/dev environments without the Redis extension), so a
        // missing Redis setup doesn't crash the import outright.
        try {
            $store = Cache::store('redis');
            $store->get('import-processing-probe');
        } catch (\Throwable $e) {
            Log::warning('ImportProspects: redis cache store unavailable, falling back to default store for the lock', [
                'import_id' => $this->import->id,
                'message' => $e->getMessage(),
            ]);
            $store = Cache::store();
        }

        $lock = $store->lock('import-processing-' . $this->import->id, 3600);

        if (!$lock->get()) {
            Log::warning('ImportProspects: import already being processed by another worker, skipping duplicate run', [
                'import_id' => $this->import->id,
            ]);
            return;
        }

        try {
            $this->processImport();
        } finally {
            $lock->release();
        }
    }

    protected function processImport()
    {
        Log::info('ImportProspects: starting import', [
            'import_id' => $this->import->id,
            'project_id' => $this->import->project_id,
        ]);

        try {
            // Keep imports created before the Meta Lead Ads mapping fix from
            // storing contact data only in the JSON meta column.
            $this->synchronizeKnownDefaultMappings();
            $this->mapping = $this->getImportMappingFields();

            // check if we should stop the import
            if ($this->checkImportStopped()) {
                return;
            }

            $this->import->update(['processing_at' => Carbon::now()]);

            // Remove previous imported prospects
            $this->removePreviousImportProspects();


            // Total count of imported prospects
            $rowsCount = 0;
            // Temporary prospects array
            $prospects = [];


        // WE USE "BOX SPOUT" FOR THE FILE READING
        // BECAUSE IT SEEMS TO BE MORE EFFICIENT
        // FOR BIG FILES


        // File path
        $filepath = storage_path('app/imports/' . $this->import->path);


        // Choose reader type
        // depending on the type of file to import
        $reader = $this->getFileReader($filepath);


        // Open the file using the Memory Spout reader
        $reader->open($filepath);

        // SHEET LOOP
        // Loop through all sheets in the workbook so Google Sheets
        // imports can import every tab in the document, unless the user
        // restricted the import to a subset of sheets.
        foreach ($reader->getSheetIterator() as $sheet) {

            if (
                !empty($this->import->selected_sheets)
                && !in_array($sheet->getName(), $this->import->selected_sheets, true)
            ) {
                continue;
            }

            // Indicate the first row
            // as the header of the file
            $isHeaderRow = true;
            $headerRow = null;
            $columnMap = [];

            // ROW LOOP
            // Loop through the sheet rows
            foreach ($sheet->getRowIterator() as $r) {

                // Skip the header row
                if ($isHeaderRow) {
                    $isHeaderRow = false;
                    $headerRow = $this->getCellsValues($r);
                    // Different tabs of the same Google Sheets document can
                    // have columns in a different order, missing, or extra
                    // ones — align this sheet's columns to the reference
                    // headers instead of assuming the same column index
                    // means the same field on every sheet.
                    $columnMap = $this->buildSheetColumnMap($headerRow);
                    continue;
                }

                // Get cells values in current row
                $row = $this->getCellsValues($r);

                // Skip rows that repeat the header (common when several
                // exports have been pasted one after another in the same
                // spreadsheet) — otherwise the header values leak into the
                // data (e.g. the literal text "created_time" ends up in the
                // created_at column) and crash the whole insert batch.
                if ($row === $headerRow) {
                    continue;
                }

                // Realign this sheet's row to the reference column order.
                $row = $this->remapRowToMasterColumns($row, $columnMap);

                // Convert import row to prospect data
                $prospect = $this->importRowToProspect($row, $rowsCount);

                // Skip rows whose email/phone don't look like an email/phone
                // at all (columns shifted in the source spreadsheet) instead
                // of importing garbled data into the wrong fields.
                if ($this->isSuspiciousProspect($prospect)) {
                    ++$this->suspiciousRowsCount;

                    Log::warning('ImportProspects: suspicious row skipped, columns look shifted in the source spreadsheet', [
                        'import_id' => $this->import->id,
                        'sheet' => $sheet->getName(),
                        'row' => $row,
                    ]);

                    continue;
                }

                // Skip rows with neither an email nor a phone number (e.g.
                // manually pasted rows in the source spreadsheet that only
                // contain a name) — without a way to contact them, they are
                // not usable prospects and would otherwise pollute the CRM
                // with empty contacts.
                if ($this->hasNoContactInfo($prospect)) {
                    continue;
                }

                // Skip if prospect is a duplicate (by email or phone) —
                // but still refresh the existing prospect's meta answers
                // (e.g. the recontact date) instead of silently dropping
                // the new data, see updateExistingDuplicateProspect().
                if ($this->isDuplicateProspect($prospect)) {
                    if ($this->updateExistingDuplicateProspect($prospect)) {
                        ++$this->updatedDuplicatesCount;
                    }

                    continue;
                }

                // Add prospect to the array of prospects to create
                $prospects[] = $prospect;

                // Rows count
                ++$rowsCount;

                // Every 100 prospects,
                // check if we should stop the import
                if ($rowsCount % 100 == 0 && $this->checkImportStopped()) {
                    break;
                }

                // Create prospects
                // For optimization,
                // we create several prospects at the same time
                if ($rowsCount % $this->limit == 0) {
                    $this->handleProspects($prospects);
                    $prospects = [];
                }
            }

        }

        // Create remaining prospects
        if (count($prospects) > 0) {
            $this->handleProspects($prospects);
        }

        // Assign automatically any imported prospects
        // that were not assigned during import relation handling.
        $automaticAssignments = app(ProspectAutoAssignment::class)
            ->assignUnassignedProspects(null, $this->import->id);

        Log::info('ImportProspects: automatic assignment after import', [
            'import_id' => $this->import->id,
            'assigned_count' => $automaticAssignments,
        ]);

        // Close the reader
        $reader->close();

        // Check prospects from the import
        // which latitude and longitude
        // values are given
        $this->checkValidAddress();

        // Ensure that the first leads added
        // are the ones that were created last
        // $this->reverseCreatedAt();

        // Update import infos
        // Mark import as finished
        $this->import->update([
            'rows_count' => $rowsCount,
            'is_processing' => 0,
            'processed_at' => Carbon::now(),
        ]);

        // Notifier SMS de bienvenue :
        // envoyé une fois l'import terminé et les doublons
        // écartés (cf. isDuplicateProspect ci-dessus).
        $this->sendWelcomeSms($this->import);

        ImportFinished::dispatch($this->import->refresh());

        Log::info('ImportProspects: finished import', [
            'import_id' => $this->import->id,
            'project_id' => $this->import->project_id,
            'rows_count' => $rowsCount,
            'suspicious_rows_count' => $this->suspiciousRowsCount,
            'updated_duplicates_count' => $this->updatedDuplicatesCount,
        ]);

        // Send notification to the import's creator
        // that import has been finished
        $this->notifyImportFinished();
    } catch (\Throwable $exception) {
        Log::error('ImportProspects: import failed during handle', [
            'import_id' => $this->import->id,
            'project_id' => $this->import->project_id,
            'message' => $exception->getMessage(),
        ]);

        throw $exception;
    }
}

    public function failed(\Throwable $exception)
    {
        Log::error('ImportProspects: import failed', [
            'import_id' => $this->import->id,
            'project_id' => $this->import->project_id,
            'message' => $exception->getMessage(),
        ]);

        $this->import->update([
            'is_processing' => 0,
            'processed_at' => Carbon::now(),
        ]);
    }

    /**
     * Remove previous imported prospect
     */
    protected function removePreviousImportProspects()
    {
        DB::table('prospects')
            ->where('import_id', $this->import->id)
            ->delete();
    }

    /**
     * Get list of labels categories
     * with the associated labels
     */
    protected function getCategories()
    {
        return $this->import->project
            ->categories()
            ->select('id')
            ->with('labels')
            ->get();
    }

    /**
     * Get list of threads
     */
    protected function getThreads()
    {
        return $this->import->project
            ->threads()
            ->select('id')
            ->get();
    }

    /**
     * Get list of calendars
     */
    protected function getCalendars()
    {
        return $this->import->project
            ->calendars()
            ->select('id')
            ->get();
    }

    /**
     *
     */
    protected function getColumnToFieldHandlers()
    {
        $userRepository = new UserRepository($this->import->project);

        return [
            ImportProspects::MAPPING_FIELD_DEFAULT     => new DefaultField(), // 0.71
            ImportProspects::MAPPING_FIELD_EVENT       => new EventField($this->import->project, $userRepository), // 0.84
            ImportProspects::MAPPING_FIELD_GROUP       => new GroupField($this->import->project), // 0.70
            ImportProspects::MAPPING_FIELD_INTERACTION => new InteractionField($userRepository), // 0.71
            ImportProspects::MAPPING_FIELD_LABEL       => new LabelField(), // 1.06
            ImportProspects::MAPPING_FIELD_LINK        => new LinkField(), // 0.71
            ImportProspects::MAPPING_FIELD_MESSAGE     => new MessageField($userRepository), // 0.73
            ImportProspects::MAPPING_FIELD_META        => new MetaField(), // 0.68
            ImportProspects::MAPPING_FIELD_ORDER       => new OrderField($this->import->project, $userRepository), // 0.80
            ImportProspects::MAPPING_FIELD_SMS         => new SmsField($userRepository), // 0.74
            ImportProspects::MAPPING_FIELD_USER        => new UserField($userRepository), // 1.49
            ImportProspects::MAPPING_FIELD_CALENDAR    => new CalendarField($this->import->creator_id),
        ];
    }

    /**
     *
     */
    protected function getProspectRelationsHandlers()
    {
        return [
            'events'       => new EventsHandler(),
            'groups'       => new GroupsHandler(),
            'interactions' => new InteractionsHandler(),
            'labels'       => new LabelsHandler(),
            'links'        => new LinksHandler(),
            'messages'     => new MessagesHandler(),
            'orders'       => new OrdersHandler(),
            'sms'          => new SmsHandler(),
            'users'        => new UsersHandler(),
        ];
    }

    /**
     * Get import mapping fields
     */
    protected function getImportMappingFields()
    {
        $mapping = array_map(function($attribute, $index) {

                // 1. Not mapped column
                if (is_null($attribute)) {
                    return null;
                }

                // 2. Meta field
                if (Str::startsWith($attribute, 'meta->')) {
                    $fieldSlug = str_replace('meta->', '', $attribute);

                    return [
                        'type'  => ImportProspects::MAPPING_FIELD_META,
                        'field' => empty($fieldSlug) ? $this->createNewField($index)->slug : $fieldSlug
                    ];
                }

                // 3. Category labels
                if (Str::startsWith($attribute, 'category->')) {
                    $categoryId = str_replace('category->', '', $attribute);

                    if (empty($categoryId)) {
                        $category = $this->createNewCategory($index);
                    } else {
                        // Check if category exists
                        $category = $this->categories->find($categoryId);
                        if (!$category) {
                            // else, do not map the column
                            return null;
                        }
                    }

                    return [
                        'type'  => ImportProspects::MAPPING_FIELD_LABEL,
                        'field' => $category
                    ];
                }

                // 4. Events
                if ($attribute == 'events') {
                    return [
                        'type'  => ImportProspects::MAPPING_FIELD_EVENT,
                        'field' => null
                    ];
                }

                // 5. Threads
                if (Str::startsWith($attribute, 'thread->')) {
                    $threadId = str_replace('thread->', '', $attribute);

                    if (empty($threadId)) {
                        $thread = $this->createNewThread($index);
                    } else {
                        // Check if thread exists
                        $thread = $this->threads->find($threadId);
                        if (!$thread) {
                            // else, do not map the column
                            return null;
                        }
                    }

                    return [
                        'type'  => ImportProspects::MAPPING_FIELD_MESSAGE,
                        'field' => $thread
                    ];
                }

                // 6. Orders
                if ($attribute == 'orders') {
                    return [
                        'type'  => ImportProspects::MAPPING_FIELD_ORDER,
                        'field' => null
                    ];
                }

                // 7. SMS
                if ($attribute == 'sms') {
                    return [
                        'type'  => ImportProspects::MAPPING_FIELD_SMS,
                        'field' => null
                    ];
                }

                // 8. Interactions
                if ($attribute == 'interactions') {
                    return [
                        'type'  => ImportProspects::MAPPING_FIELD_INTERACTION,
                        'field' => null
                    ];
                }

                // 9. Links
                if ($attribute == 'links') {
                    return [
                        'type'  => ImportProspects::MAPPING_FIELD_LINK,
                        'field' => null
                    ];
                }

                // 10. Users
                if ($attribute == 'users') {
                    return [
                        'type'  => ImportProspects::MAPPING_FIELD_USER,
                        'field' => null
                    ];
                }

                // 11. Users
                if ($attribute == 'groups') {
                    return [
                        'type'  => ImportProspects::MAPPING_FIELD_GROUP,
                        'field' => null
                    ];
                }

                // 13. Events
                if (Str::startsWith($attribute, 'calendar->')) {
                    $calendarId = str_replace('calendar->', '', $attribute);

                    if (empty($calendarId)) {
                        $calendar = $this->createNewThread($index);
                    } else {
                        // Check if thread exists
                        $calendar = $this->calendars->find($calendarId);
                        if (!$calendar) {
                            // else, do not map the column
                            return null;
                        }
                    }

                    return [
                        'type'  => ImportProspects::MAPPING_FIELD_CALENDAR,
                        'field' => $calendar
                    ];
                }

                // 12. Default field
                return [
                    'type'  => ImportProspects::MAPPING_FIELD_DEFAULT,
                    'field' => $attribute
                ];
            }, $this->import->mapping, array_keys($this->import->mapping));

        for ($i = 0, $count = count($mapping); $i < $count; ++$i) {
            if (!$mapping[$i] || !isset($this->columnToFieldHandlers[$mapping[$i]['type']])) {
                unset($mapping[$i]);
            }
        }

        return $mapping;
    }

    /**
     * Choose reader type
     * depending on the type of file to import
     */
    protected function getFileReader($filepath)
    {

        // Get file extension
        $pathinfoExtension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        // CSV
        if ($pathinfoExtension == 'csv') {
            $reader = ReaderEntityFactory::createCSVReader();

            if ($this->import->field_delimiter) {
                $reader->setFieldDelimiter($this->normalizeCsvDelimiter($this->import->field_delimiter));
            }

            if ($this->import->field_enclosure) {
                $reader->setFieldEnclosure($this->import->field_enclosure);
            }

            return $reader;
        }

        // ODS
        if ($pathinfoExtension == 'ods') {
            return ReaderEntityFactory::createODSReader();
        }

        // XLSX
        return ReaderEntityFactory::createXLSXReader();
    }

    /**
     * Normalize CSV delimiter values to actual parser delimiters.
     */
    protected function normalizeCsvDelimiter(string $delimiter): string
    {
        return $delimiter === 'tab' ? "\t" : $delimiter;
    }

    /**
     * Précharge les emails des prospects déjà présents en base
     * (même projet) afin de ne pas ré-importer une personne existante.
     */
    protected function getExistingEmails()
    {
        $emails = [];

        DB::table('prospects')
            ->where('project_id', $this->import->project_id)
            // Exclude this import's own prospects: they are about to be
            // deleted and recreated by removePreviousImportProspects(), so
            // treating them as "already existing" would make every row of
            // a re-processed import look like a duplicate of itself,
            // silently wiping the import's data instead of recreating it.
            // Prospects with no import_id (created manually) must still
            // count as "existing", hence the whereNull branch.
            ->where(function ($q) {
                $q->whereNull('import_id')->orWhere('import_id', '<>', $this->import->id);
            })
            ->whereNull('deleted_at')
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->select('id', 'email')
            ->orderBy('id')
            ->chunk(5000, function ($rows) use (&$emails) {
                foreach ($rows as $p) {
                    $emails[strtolower(trim($p->email))] = $p->id;
                }
            });

        return $emails;
    }

    /**
     * Précharge les numéros mobiles des prospects déjà présents en base
     * (même projet) afin de ne pas ré-importer une personne existante.
     */
    protected function getExistingMobiles()
    {
        $mobiles = [];

        DB::table('prospects')
            ->where('project_id', $this->import->project_id)
            // See getExistingEmails() for why this import's own (about to
            // be recreated) prospects must be excluded here.
            ->where(function ($q) {
                $q->whereNull('import_id')->orWhere('import_id', '<>', $this->import->id);
            })
            ->whereNull('deleted_at')
            ->whereNotNull('mobile_phone_number')
            ->where('mobile_phone_number', '<>', '')
            ->select('id', 'mobile_phone_number')
            ->orderBy('id')
            ->chunk(5000, function ($rows) use (&$mobiles) {
                foreach ($rows as $p) {
                    $key = $this->normalizePhone($p->mobile_phone_number);
                    if ($key !== '') {
                        $mobiles[$key] = $p->id;
                    }
                }
            });

        return $mobiles;
    }

    /**
     * Normalise un numéro de téléphone : ne garde que les chiffres
     * (ex: "06 18 41 66 33" et "0618416633" deviennent identiques).
     */
    protected function normalizePhone($value)
    {
        return preg_replace('/\D+/', '', (string) $value);
    }

    /**
     * A prospect with no email and no phone number cannot be contacted,
     * so it is not imported.
     */
    protected function hasNoContactInfo($prospect)
    {
        return empty($prospect['email'])
            && empty($prospect['phone_number'])
            && empty($prospect['mobile_phone_number']);
    }

    /**
     * A source spreadsheet row whose cells got shifted (e.g. a lead that
     * skipped one question, so the automation feeding the sheet wrote the
     * next answer one column too early) ends up with a name in the email
     * field, or a phone number in the name field. Importing that as-is
     * would silently corrupt the prospect instead of erroring out, so
     * these rows are flagged and skipped for manual review instead.
     */
    protected function isSuspiciousProspect($prospect)
    {
        if (!empty($prospect['email']) && !str_contains($prospect['email'], '@')) {
            return true;
        }

        // Count actual digits regardless of formatting (spaces, dots,
        // dashes are common in phone numbers, e.g. "06 66 34 59 06"), and
        // only flag values too short to be a real phone number at all.
        foreach (['mobile_phone_number', 'phone_number'] as $field) {
            if (!empty($prospect[$field]) && strlen($this->normalizePhone($prospect[$field])) < 6) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if prospect is a duplicate by email or phone number
     * Tracks seen values to prevent duplicates within the import
     */
    protected function isDuplicateProspect($prospect)
    {
        // Email déjà présent en base → on ignore la ligne
        if (!empty($prospect['email'])
            && isset($this->existingEmails[strtolower(trim($prospect['email']))])) {
            return true;
        }

        // Mobile déjà présent en base → on ignore la ligne
        if (!empty($prospect['mobile_phone_number'])) {
            $mobile = $this->normalizePhone($prospect['mobile_phone_number']);
            if ($mobile !== '' && isset($this->existingMobiles[$mobile])) {
                return true;
            }
        }

        // Check email
        if (!empty($prospect['email'])) {
            $email = strtolower(trim($prospect['email']));
            if (in_array($email, $this->seenDuplicates['email'])) {
                return true; // Duplicate found
            }
            $this->seenDuplicates['email'][] = $email;
        }

        // Check phone_number
        if (!empty($prospect['phone_number'])) {
            $phone = trim($prospect['phone_number']);
            if (in_array($phone, $this->seenDuplicates['phone'])) {
                return true; // Duplicate found
            }
            $this->seenDuplicates['phone'][] = $phone;
        }

        // Check mobile_phone_number
        if (!empty($prospect['mobile_phone_number'])) {
            $mobile = trim($prospect['mobile_phone_number']);
            if (in_array($mobile, $this->seenDuplicates['mobile'])) {
                return true; // Duplicate found
            }
            $this->seenDuplicates['mobile'][] = $mobile;
        }

        return false; // Not a duplicate
    }

    /**
     * A lead that already exists in the CRM (matched by email or mobile)
     * is never re-created, but re-importing it must still refresh its
     * answers — e.g. "quand souhaitez-vous être recontacté" — otherwise a
     * corrected/updated sheet silently stops updating known leads, which
     * has caused missed appointments in production. Only meta fields are
     * merged in (never email/phone/name) to avoid the identity fields
     * themselves being touched, and suspicious (likely column-shifted)
     * rows are never merged in, to avoid corrupting an already-good
     * existing prospect with garbled data.
     */
    protected function updateExistingDuplicateProspect($prospect)
    {
        if ($this->isSuspiciousProspect($prospect)) {
            return false;
        }

        $existingId = null;

        if (!empty($prospect['email'])) {
            $existingId = $this->existingEmails[strtolower(trim($prospect['email']))] ?? null;
        }

        if (!$existingId && !empty($prospect['mobile_phone_number'])) {
            $mobile = $this->normalizePhone($prospect['mobile_phone_number']);
            $existingId = $mobile !== '' ? ($this->existingMobiles[$mobile] ?? null) : null;
        }

        if (!$existingId) {
            return false;
        }

        $newMeta = array_filter($prospect['meta'] ?? [], function ($value) {
            return $value !== null && $value !== '';
        });

        if (empty($newMeta)) {
            return false;
        }

        $existing = DB::table('prospects')->where('id', $existingId)->first(['meta']);

        if (!$existing) {
            return false;
        }

        $existingMeta = json_decode($existing->meta ?: '{}', true) ?: [];
        $mergedMeta = array_merge($existingMeta, $newMeta);

        if ($mergedMeta == $existingMeta) {
            return false;
        }

        DB::table('prospects')->where('id', $existingId)->update([
            'meta' => json_encode($mergedMeta),
            'updated_at' => $this->date,
        ]);

        return true;
    }

    /**
     * Check if import has been stopped
     *
     * @return import stopped
     */
    protected function checkImportStopped()
    {
        // Refresh the import from database
        $this->import->refresh();

        // Check if we should stop the import
        return $this->import->is_processing == 0;
    }

    /**
     * Create new field
     *
     * @param  {string}  $name name of the new field
     */
    protected function createNewField($index)
    {
        $field = $this->import->project
            ->fields()
            ->create(['name' => $this->import->headers[$index], 'meta' => true]);

        $field->refresh();

        $mapping = $this->import->mapping;
        $mapping[$index] = 'meta->' . $field->slug;
        $this->import->update([
            'mapping' => $mapping
        ]);

        return $field;
    }

    /**
     * Create new category
     *
     * @param  {string}  $name name of the new category
     */
    protected function createNewCategory($index)
    {
        $category = $this->import->project
            ->categories()
            ->create(['name' => $this->import->headers[$index]]);

        $mapping = $this->import->mapping;
        $mapping[$index] = 'category->' . $category->id;
        $this->import->update([
            'mapping' => $mapping
        ]);

        $category->load('labels');
        $this->categories->push($category);

        return $category;
    }

    /**
     * Create new thread
     *
     * @param  {string}  $name name of the new thread
     */
    protected function createNewThread($index)
    {
        $thread = $this->import->project
            ->threads()
            ->create(['name' => $this->import->headers[$index]]);

        $mapping = $this->import->mapping;
        $mapping[$index] = 'thread->' . $thread->id;
        $this->import->update([
            'mapping' => $mapping
        ]);

        $this->threads->push($thread);

        return $thread;
    }

    /**
     * Get values from row cells
     *
     * @param  $r
     */
    protected function getCellsValues(&$r)
    {
        $row = [];

        foreach ($r->getCells() as $cell) {
            $row[] = $cell->getValue();
        }

        return $row;
    }

    /**
     * Create empty new prospect
     */
    protected function newProspect()
    {
        $prospect = [
            'meta'           => [],
            'import_id'      => $this->import->id,
            'project_id'     => $this->import->project_id,
            'creator_id'     => $this->import->creator_id,
            'created_at'     => $this->date,
            'updated_at'     => $this->date,
        ];

        foreach ($this->prospectRelationsHandlers as $key => $handler) {
            $prospect[$key] = [];
        }

        return $prospect;
    }

    /**
     * Map import row with prospect
     *
     * @param  {array}  $row import row
     *
     * @return  {array}  row converted to prospect
     */
    protected function importRowToProspect(&$row, $rowsCount)
    {
        // Prospect to create
        $prospect = $this->emptyProspect;
        $prospect['created_at'] = Carbon::createFromFormat('Y-m-d H:i:s', $prospect['created_at'])->subMilliseconds($rowsCount)->format('Y-m-d H:i:s.v');

        // COLUMN LOOP
        // Loop through the import mapping
        foreach ($this->mapping as $index => $mapping) {
            $this->columnToFieldHandlers[$mapping['type']]->handle(
                $prospect,
                $mapping['field'],
                // cell value
                isset($row[$index]) && $row[$index] != 'null' ?
                    $row[$index] :
                    null
            );
        }

        return $prospect;
    }

    /**
     * Create many prospects
     * in the given array
     *
     * @param  {array}  $prospects list of prospects to create
     */
    protected function createProspects(&$prospects)
    {
        // Create prospects into DB
        DB::table('prospects')->insert($prospects);

        // Get prospects ids
        $prospectsIds = DB::table('prospects')
            ->where('import_id', $this->import->id)
            ->orderBy('id', 'desc')
            ->limit(count($prospects))
            ->get(['id'])
            ->toArray();

        // Retrieve only ids
        $prospectsIds = array_map(function($data) {
            return $data->id;
        }, $prospectsIds);

        // Reverse array
        // because it was ordered by id desc
        // in previous query
        return array_reverse($prospectsIds);
    }

    /**
     * Associate the import labels to each prospect in the import
     *
     * @param  {array}  $prospectsIds list of prospects ids
     */
    protected function handleProspectsImportLabels(&$prospectsIds)
    {
        if (!$this->import->labels) {
            return;
        }

        $data = [];

        foreach ($this->import->labels as $labelId) {
            foreach ($prospectsIds as $prospectId) {
                $data[] = [
                    'prospect_id' => $prospectId,
                    'label_id'    => $labelId,
                    'creator_id'  => $this->import->creator_id,
                    'created_at'  => $this->date,
                    'updated_at'  => $this->date,
                ];
            }
        }

        DB::table('prospect_label')->insert($data);
    }

    /**
     * Associate the import users to each prospect in the import
     * Only assign to prospects that don't already have users assigned.
     *
     * @param  {array}  $prospectsIds list of prospects ids
     */
    protected function handleProspectsImportUsers(&$prospectsIds)
    {
        if (!$this->import->users) {
            return;
        }

        // Get prospects that already have users assigned
        $assignedProspectIds = DB::table('prospect_user')
            ->whereIn('prospect_id', $prospectsIds)
            ->distinct('prospect_id')
            ->pluck('prospect_id')
            ->toArray();

        // Filter out prospects that already have users
        $unassignedProspectIds = array_filter($prospectsIds, function($id) use ($assignedProspectIds) {
            return !in_array($id, $assignedProspectIds);
        });

        if (empty($unassignedProspectIds)) {
            return; // All prospects already have users assigned
        }

        $data = [];

        foreach ($this->import->users as $userId) {
            foreach ($unassignedProspectIds as $prospectId) {
                $data[] = [
                    'prospect_id' => $prospectId,
                    'user_id'     => $userId,
                    'creator_id'     => $this->import->creator_id,
                    'created_at'  => $this->date,
                    'updated_at'  => $this->date,
                ];
            }
        }

        if (!empty($data)) {
            DB::table('prospect_user')->insert($data);
        }
    }

    /**
     * Associate the import groups to each prospect in the import
     *
     * @param  {array}  $prospectsIds list of prospects ids
     */
    protected function handleProspectsImportGroups(&$prospectsIds)
    {
        if (!$this->import->groups) {
            return;
        }

        $data = [];

        foreach ($this->import->groups as $groupId) {
            foreach ($prospectsIds as $prospectId) {
                $data[] = [
                    'prospect_id' => $prospectId,
                    'group_id'    => $groupId,
                    'created_at'  => $this->date,
                    'updated_at'  => $this->date,
                ];
            }
        }

        DB::table('prospect_group')->insert($data);
    }

    /**
     *
     */
    protected function handleProspects(&$prospects)
    {
        $prospectsItems = [];

        // Get prospects associated items
        foreach ($this->prospectRelationsHandlers as $key => $handler) {
            $prospectsItems[$key] = array_map(function($prospect) use($key) {
                return $prospect[$key];
            }, $prospects);
        }

        // Get prospects data
        $prospects = array_map(function($prospect) {
            $prospect['meta'] = json_encode($prospect['meta']);

            foreach ($this->prospectRelationsHandlers as $key => $handler) {
                unset($prospect[$key]);
            }

            return $prospect;
        }, $prospects);

        // Create prospects
        $prospectsIds = $this->createProspects($prospects);

        // Create prospects associated items
        foreach ($this->prospectRelationsHandlers as $key => $handler) {
            $handler->handle($this->import, $prospectsIds, $prospectsItems[$key], $this->date);
        }

        // Import relations
        $this->handleProspectsImportLabels($prospectsIds);
//        $this->handleProspectsImportUsers($prospectsIds);
        $this->handleProspectsImportGroups($prospectsIds);
    }

    /**
     * Check prospect valid address
     */
    protected function checkValidAddress()
    {
        DB::table("prospects")
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('import_id', $this->import->id)
            ->update(['valid_address' => 1]);
    }

    /**
     * Check prospect valid address
     */
    protected function reverseCreatedAt()
    {
        $firstProspect = DB::table("prospects")
            ->where('import_id', $this->import->id)
            ->first(['id']);

        DB::statement("UPDATE prospects SET created_at = TIMESTAMPADD(MILLISECOND, " . $firstProspect->id . " - id, created_at)");
    }

    /**
     * Notify the import's creator
     * that import has been finished
     */
    protected function notifyImportFinished()
    {
        // When redis works,
        // please uncomment this code
        // ImportFinished::dispatch($this->import);

        // and comment this other one
        if ($this->import->creator) {
            try {
              $this
                ->import
                ->creator
                ->notify(
                    new \App\Notifications\ImportFinished($this->import)
                );
            } catch(\Exception $e) {
            }
        }
    }
}
