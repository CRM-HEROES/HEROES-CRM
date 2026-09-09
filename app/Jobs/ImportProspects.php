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
use App\Models\User;
use App\Services\ProspectAutoAssignment;
use App\Support\ImportHeaderAliases;
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
use Illuminate\Support\Facades\Notification;
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
    protected $existingPhones = [];
    protected $existingMobiles = [];
    protected $suspiciousRowsCount = 0;
    protected $flaggedDuplicatesCount = 0;
    protected $duplicateGroupCache = [];
    protected $incremental = false;
    protected $acceptedProspectIndexes = [];

    /**
     * Create a new job instance.
     *
     * @param  bool  $incremental  When true (auto-sync of a Google Sheets
     *   import): don't wipe and recreate the import's own prospects on
     *   every run — instead recognise them as "already imported" so a row
        *   already present (matched by email/phone/mobile against a prospect this
        *   same import already created on a previous sync) is simply skipped,
     *   and only genuinely new rows are inserted. False (default) preserves
     *   the existing "start fresh from this file" behaviour used by
     *   manual/file imports and the "Re importer" button.
     *
    *   Either way, a row matching any existing prospect is ignored so the
    *   database remains authoritative and no duplicate is created.
     *
     * @return void
     */
    public function __construct($import, bool $incremental = false)
    {
        $this->import = $import;
        $this->incremental = $incremental;
        $this->date = Carbon::now()->format('Y-m-d H:i:s');
        $this->categories = $this->getCategories();
        $this->threads = $this->getThreads();
        $this->calendars = $this->getCalendars();

        $this->columnToFieldHandlers = $this->getColumnToFieldHandlers();
        $this->prospectRelationsHandlers = $this->getProspectRelationsHandlers();

        $this->mapping = $this->getImportMappingFields();

        $this->emptyProspect = $this->newProspect();

        // Contact details already present in the database (to avoid duplicates)
        $this->existingEmails = $this->getExistingEmails();
        $this->existingPhones = $this->getExistingPhones();
        $this->existingMobiles = $this->getExistingMobiles();
    }

    /**
     * Return the CRM field that must receive a standard Meta/Google Sheets
     * column.  This is deliberately applied again by the worker: an import
     * may have been created with an older automatic mapping before it is
     * started (or restarted).
     *
     * Delegates to ImportHeaderAliases, the single shared alias list also
     * used by ImportObserver::autoMapping() when a mapping is first
     * generated — kept in one place so the two never drift apart.
     */
    protected function getKnownDefaultField($header): ?string
    {
        return ImportHeaderAliases::resolve($header);
    }

    /**
     * Normalize a header for comparison: strip accents/case/punctuation so
     * that "E-mail", "e_mail" and "E Mail" are recognised as the same header.
     */
    protected function normalizeHeaderText($header): string
    {
        return ImportHeaderAliases::normalize($header);
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

            // Remove previous imported prospects. Skipped in incremental
            // mode (auto-sync): the import's own prospects stay in place
            // and are recognised via existingEmails/existingMobiles below,
            // so re-running never duplicates them and any manual edits
            // made to them in the CRM between two syncs aren't wiped out.
            if (!$this->incremental) {
                $this->removePreviousImportProspects();
            }


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
                $this->normalizeProspectPhones($prospect);

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

                // Skip rows that repeat an earlier row within this same
                // sync run (e.g. the same lead pasted twice in the source
                // spreadsheet) — not a mismatch with the database, just an
                // accidental repeat in this one file, so only the first
                // occurrence is kept.
                if ($this->isRepeatedWithinFile($prospect)) {
                    continue;
                }

                // Compare the row to prospects already present in the
                // database (by email, phone, or mobile number). The database is
                // authoritative: never create an incoming copy of an
                // existing prospect, regardless of which import owns it.
                $duplicate = $this->findExistingDuplicate($prospect);

                if ($duplicate) {
                    // This applies both to the same import during an
                    // incremental Google Sheets sync and to prospects owned
                    // by another import or created manually. Keep the
                    // existing database record authoritative and ignore the
                    // incoming row completely.
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
        // Mark import as finished.
        // rows_count reflects the import's current total prospect count
        // rather than just $rowsCount (rows freshly inserted this run):
        // in incremental mode a sync that finds nothing new would
        // otherwise report "0" even though the import still owns its
        // previously-synced prospects. In non-incremental mode this is
        // equivalent to $rowsCount anyway, since previous rows were wiped
        // before this run started.
        $this->import->update([
            'rows_count' => DB::table('prospects')
                ->where('import_id', $this->import->id)
                ->whereNull('deleted_at')
                ->count(),
            'is_processing' => 0,
            'processed_at' => Carbon::now(),
        ]);

        // Notifier SMS de bienvenue :
        // envoyé une fois l'import terminé — les prospects flagués comme
        // doublons (cf. findExistingDuplicate ci-dessus) sont exclus de cet
        // envoi par sendWelcomeSms() elle-même (ils existent déjà en base).
        $this->sendWelcomeSms($this->import);

        ImportFinished::dispatch($this->import->refresh());

        Log::info('ImportProspects: finished import', [
            'import_id' => $this->import->id,
            'project_id' => $this->import->project_id,
            'rows_count' => $rowsCount,
            'suspicious_rows_count' => $this->suspiciousRowsCount,
            'flagged_duplicates_count' => $this->flaggedDuplicatesCount,
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
            // Exclude this import's own prospects, UNLESS running
            // incrementally: in the normal (non-incremental) mode they are
            // about to be deleted and recreated by
            // removePreviousImportProspects(), so treating them as
            // "already existing" would make every row of a re-processed
            // import look like a duplicate of itself, silently wiping the
            // import's data instead of recreating it. In incremental mode
            // nothing gets deleted, so they must be included here instead —
            // that's what lets a re-synced row be recognised as already
            // imported (skipped/merged) rather than duplicated. Prospects
            // with no import_id (created manually) always count as
            // "existing" either way, hence the whereNull branch.
            ->when(!$this->incremental, function ($q) {
                $q->where(function ($q) {
                    $q->whereNull('import_id')->orWhere('import_id', '<>', $this->import->id);
                });
            })
            ->whereNull('deleted_at')
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->select('id', 'email', 'import_id')
            ->orderBy('id')
            ->chunk(5000, function ($rows) use (&$emails) {
                foreach ($rows as $p) {
                    $emails[strtolower(trim($p->email))] = ['id' => $p->id, 'import_id' => $p->import_id];
                }
            });

        return $emails;
    }

    /**
     * Preload phone numbers already present in the project so they cannot be
     * recreated by a file import or an incremental Google Sheets sync.
     */
    protected function getExistingPhones()
    {
        $phones = [];

        DB::table('prospects')
            ->where('project_id', $this->import->project_id)
            ->when(!$this->incremental, function ($q) {
                $q->where(function ($q) {
                    $q->whereNull('import_id')->orWhere('import_id', '<>', $this->import->id);
                });
            })
            ->whereNull('deleted_at')
            ->whereNotNull('phone_number')
            ->where('phone_number', '<>', '')
            ->select('id', 'phone_number', 'import_id')
            ->orderBy('id')
            ->chunk(5000, function ($rows) use (&$phones) {
                foreach ($rows as $p) {
                    $key = $this->normalizePhone($p->phone_number);
                    if ($key !== '') {
                        $phones[$key] = ['id' => $p->id, 'import_id' => $p->import_id];
                    }
                }
            });

        return $phones;
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
            // See getExistingEmails() for why this import's own prospects
            // are excluded here only outside incremental mode.
            ->when(!$this->incremental, function ($q) {
                $q->where(function ($q) {
                    $q->whereNull('import_id')->orWhere('import_id', '<>', $this->import->id);
                });
            })
            ->whereNull('deleted_at')
            ->whereNotNull('mobile_phone_number')
            ->where('mobile_phone_number', '<>', '')
            ->select('id', 'mobile_phone_number', 'import_id')
            ->orderBy('id')
            ->chunk(5000, function ($rows) use (&$mobiles) {
                foreach ($rows as $p) {
                    $key = $this->normalizePhone($p->mobile_phone_number);
                    if ($key !== '') {
                        $mobiles[$key] = ['id' => $p->id, 'import_id' => $p->import_id];
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
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        // Lead exports may prefix phone values with "p:" or "tel:".
        $value = preg_replace('/^(?:p:|tel:)\s*/i', '', $value);
        $value = preg_replace('/\s*(?:ext|x|poste)\.?\s*\d+$/i', '', $value);

        if (class_exists('libphonenumber\\PhoneNumberUtil')) {
            $util = \libphonenumber\PhoneNumberUtil::getInstance();

            try {
                $number = $util->parse($value, 'ZZ');

                if ($util->isPossibleNumber($number) && $util->isValidNumber($number)) {
                    return $util->format($number, \libphonenumber\PhoneNumberFormat::E164);
                }
            } catch (\Throwable $exception) {
                // Fall through to a conservative key when the number is
                // malformed or its country cannot be inferred safely.
            }
        }

        $digits = preg_replace('/\D+/', '', $value);

        // Keep an explicit international prefix even when the optional
        // phone metadata package is unavailable. Local numbers remain
        // intentionally unresolved because their country is unknown.
        if (str_starts_with($value, '+')) {
            return '+' . $digits;
        }

        if (str_starts_with($digits, '00')) {
            return '+' . substr($digits, 2);
        }

        return $digits;
    }

    /**
     * Store the same international representation that is used for matching.
     */
    protected function normalizeProspectPhones(array &$prospect): void
    {
        foreach (['phone_number', 'mobile_phone_number'] as $field) {
            if (!empty($prospect[$field])) {
                $prospect[$field] = $this->normalizePhone($prospect[$field]);
            }
        }
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
     * Rows repeated within this same sync/import run (e.g. the same lead
     * pasted twice in the source spreadsheet) — not a comparison against
     * the database, just an accidental repeat in this one file, so only
     * the first occurrence is kept. Tracks seen values as it goes.
     */
    protected function isRepeatedWithinFile($prospect)
    {
        // Check email
        if (!empty($prospect['email'])) {
            $email = strtolower(trim($prospect['email']));
            if (in_array($email, $this->seenDuplicates['email'])) {
                return true;
            }
            $this->seenDuplicates['email'][] = $email;
        }

        // Phone and mobile share the same duplicate pool. Build the current
        // row's values first so phone_number and mobile_phone_number cannot
        // falsely match each other within that same row.
        $phoneValues = [];
        foreach (['phone_number', 'mobile_phone_number'] as $field) {
            if (!empty($prospect[$field])) {
                $phone = $this->normalizePhone($prospect[$field]);
                if ($phone !== '') {
                    $phoneValues[$phone] = true;
                }
            }
        }

        foreach (array_keys($phoneValues) as $phone) {
            if (
                in_array($phone, $this->seenDuplicates['phone'])
                || in_array($phone, $this->seenDuplicates['mobile'])
            ) {
                return true;
            }
        }

        foreach (array_keys($phoneValues) as $phone) {
            $this->seenDuplicates['phone'][] = $phone;
            $this->seenDuplicates['mobile'][] = $phone;
        }

        return false;
    }

    /**
     * Compares the row being imported to prospects already present in the
    * database (email, phone, or mobile match) — this is the actual duplicate
    * check against existing CRM data.
     *
     * Returns null when nothing matches. Otherwise returns the id of the
     * matching existing prospect, which field(s) matched, and whether that
     * existing prospect belongs to this very import — meaning the "match"
     * is simply this same lead reappearing on a later sync, not a
     * duplicate to flag (see the caller in handle()).
     */
    protected function findExistingDuplicate($prospect)
    {
        $existing = null;
        $matchedFields = [];

        if (!empty($prospect['email'])) {
            $email = strtolower(trim($prospect['email']));
            if (isset($this->existingEmails[$email])) {
                $existing = $this->existingEmails[$email];
                $matchedFields[] = 'email';
            }
        }

        if (!empty($prospect['phone_number'])) {
            $phone = $this->normalizePhone($prospect['phone_number']);
            $phoneMatch = $this->existingPhones[$phone]
                ?? $this->existingMobiles[$phone]
                ?? null;

            if ($phoneMatch) {

                if ($existing === null) {
                    $existing = $phoneMatch;
                }

                if ($existing['id'] === $phoneMatch['id']) {
                    $matchedFields[] = 'phone_number';
                }
            }
        }

        if (!empty($prospect['mobile_phone_number'])) {
            $mobile = $this->normalizePhone($prospect['mobile_phone_number']);
            $mobileMatch = $this->existingMobiles[$mobile]
                ?? $this->existingPhones[$mobile]
                ?? null;

            if ($mobileMatch) {

                if ($existing === null) {
                    $existing = $mobileMatch;
                }

                // Only credit the mobile match to duplicate_fields if it
                // points at the same existing prospect as the email match
                // (the rare case where email and mobile match two
                // different existing prospects is left as an email-only
                // match against the first one found).
                if ($existing['id'] === $mobileMatch['id']) {
                    $matchedFields[] = 'mobile_phone_number';
                }
            }
        }

        if ($existing === null) {
            return null;
        }

        return [
            'id' => $existing['id'],
            'fields' => $matchedFields,
            'sameImport' => (int) $existing['import_id'] === (int) $this->import->id,
        ];
    }

    /**
     * Links a row that duplicates an already-existing prospect into that
     * prospect's duplicate cluster, WITHOUT touching any of the existing
     * prospect's business data (name, email, phone, meta) — the existing
     * record stays fully authoritative. Only the bookkeeping columns that
     * drive the "duplicates first" list ordering (duplicate_group_id) and
     * per-cell highlighting (duplicate_fields) are updated, mirroring the
     * convention already used by App\Services\ProspectDuplicateChecker for
     * manually created/edited prospects: the group id is the lowest id in
     * the cluster, i.e. the existing prospect's own id the first time it
     * is flagged (it is always older/lower than the row being inserted).
     *
     * @return int the duplicate_group_id to store on the new row
     */
    protected function linkDuplicateGroup($existingId, array $matchedFields)
    {
        $firstTimeThisRun = !isset($this->duplicateGroupCache[$existingId]);

        if ($firstTimeThisRun) {
            $existing = DB::table('prospects')->where('id', $existingId)->first(['duplicate_group_id', 'duplicate_fields']);

            $this->duplicateGroupCache[$existingId] = [
                'group_id' => ($existing && $existing->duplicate_group_id) ? (int) $existing->duplicate_group_id : $existingId,
                'fields' => $existing ? (json_decode($existing->duplicate_fields ?: '[]', true) ?: []) : [],
            ];
        }

        $cache = &$this->duplicateGroupCache[$existingId];
        $mergedFields = array_values(array_unique(array_merge($cache['fields'], $matchedFields)));

        if ($firstTimeThisRun || $mergedFields != $cache['fields']) {
            DB::table('prospects')->where('id', $existingId)->update([
                'duplicate_group_id' => $cache['group_id'],
                'duplicate_fields' => json_encode($mergedFields),
            ]);
        }

        $cache['fields'] = $mergedFields;

        return $cache['group_id'];
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
            'meta'               => [],
            'import_id'          => $this->import->id,
            'project_id'         => $this->import->project_id,
            'creator_id'         => $this->import->creator_id,
            // Default to "not a duplicate" — set by findExistingDuplicate()
            // / linkDuplicateGroup() below when the row matches a prospect
            // already present in the database. Declared here (rather than
            // only when a duplicate is found) so every row in a batch
            // insert has the exact same set of columns — DB::table()
            // ->insert() aligns values positionally per row, so rows with
            // different keys would silently corrupt the batch.
            'duplicate_id'       => null,
            'duplicate_group_id' => null,
            'duplicate_fields'   => null,
            'created_at'         => $this->date,
            'updated_at'         => $this->date,
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
        $lockName = 'heroes-crm-prospect-project-' . $this->import->project_id;
        $lockAcquired = false;

        try {
            // The in-memory check above is not enough when two imports for
            // the same project run at the same time. Serialize the final
            // check and insert at database level so the first committed row
            // always wins and the later row is ignored.
            if (DB::getDriverName() === 'mysql') {
                $lock = DB::selectOne('SELECT GET_LOCK(?, 60) AS acquired', [$lockName]);
                $lockAcquired = (int) ($lock->acquired ?? 0) === 1;

                if (!$lockAcquired) {
                    throw new \RuntimeException('Unable to acquire the prospect deduplication lock.');
                }
            }

            // Refresh under the lock: another import may have inserted a
            // matching prospect after this job loaded its initial indexes.
            $this->existingEmails = $this->getExistingEmails();
            $this->existingPhones = $this->getExistingPhones();
            $this->existingMobiles = $this->getExistingMobiles();

            $accepted = [];
            $this->acceptedProspectIndexes = [];

            foreach ($prospects as $index => $prospect) {
                if ($this->findExistingDuplicate($prospect)) {
                    continue;
                }

                $accepted[$index] = $prospect;
                $this->acceptedProspectIndexes[] = $index;
            }

            if (empty($accepted)) {
                return [];
            }

            DB::table('prospects')->insert(array_values($accepted));

            // Get prospects ids
            $prospectsIds = DB::table('prospects')
                ->where('import_id', $this->import->id)
                ->orderBy('id', 'desc')
                ->limit(count($accepted))
                ->get(['id'])
                ->toArray();

            // Retrieve only ids
            $prospectsIds = array_map(function($data) {
                return $data->id;
            }, $prospectsIds);

            // Reverse array because it was ordered by id desc above.
            return array_reverse($prospectsIds);
        } finally {
            if ($lockAcquired) {
                DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockName]);
            }
        }
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
            $prospect['duplicate_fields'] = $prospect['duplicate_fields'] !== null
                ? json_encode($prospect['duplicate_fields'])
                : null;

            foreach ($this->prospectRelationsHandlers as $key => $handler) {
                unset($prospect[$key]);
            }

            return $prospect;
        }, $prospects);

        // Create prospects
        $prospectsIds = $this->createProspects($prospects);

        if (empty($prospectsIds)) {
            return;
        }

        if (count($this->acceptedProspectIndexes) !== count($prospects)) {
            $acceptedIndexes = array_flip($this->acceptedProspectIndexes);

            foreach ($prospectsItems as $key => $items) {
                $prospectsItems[$key] = array_values(array_intersect_key($items, $acceptedIndexes));
            }
        }

        // Create prospects associated items
        foreach ($this->prospectRelationsHandlers as $key => $handler) {
            $handler->handle($this->import, $prospectsIds, $prospectsItems[$key], $this->date);
        }

        // Import relations
        $this->handleProspectsImportLabels($prospectsIds);
        // RÉACTIVÉ (demande client, 2026-09-04) : un utilisateur coché dans
        // "Utilisateurs affectés" doit recevoir TOUS les leads de l'import,
        // pas une part répartie équitablement — chaque prospect est donc
        // attaché à chacun des utilisateurs sélectionnés ici, avant même
        // que ProspectAutoAssignment ne s'exécute en fin de job. Comme ce
        // dernier ne traite que les prospects sans utilisateur
        // (Prospect::doesntHave('users')), un import avec des "Utilisateurs
        // affectés" non vides n'a donc plus rien à répartir : la
        // répartition équitable (least-loaded) reste inchangée pour les
        // pools "Rôles effectués" et "Groupes utilisateurs effectués", qui
        // ne passent pas par ici.
        $this->handleProspectsImportUsers($prospectsIds);
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
     * Notify the import's creator, plus every user who actually received
     * a prospect from this import — they are the ones who need to know
     * new leads landed in their queue, not just whoever launched the
     * import.
     */
    protected function notifyImportFinished()
    {
        // When redis works,
        // please uncomment this code
        // ImportFinished::dispatch($this->import);

        // and comment this other one
        $recipients = collect();

        if ($this->import->creator) {
            $recipients->push($this->import->creator);
        }

        $assignedUserIds = DB::table('prospect_user')
            ->join('prospects', 'prospects.id', '=', 'prospect_user.prospect_id')
            ->where('prospects.import_id', $this->import->id)
            ->distinct()
            ->pluck('prospect_user.user_id');

        if ($assignedUserIds->isNotEmpty()) {
            $recipients = $recipients->merge(User::whereIn('id', $assignedUserIds)->get());
        }

        $recipients = $recipients->unique('id');

        if ($recipients->isEmpty()) {
            return;
        }

        try {
            Notification::send($recipients, new \App\Notifications\ImportFinished($this->import));
        } catch (\Exception $e) {
            Log::warning('ImportProspects: failed to notify import finished', [
                'import_id' => $this->import->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
