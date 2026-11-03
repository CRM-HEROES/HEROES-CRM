<?php

namespace App\Agents\Entities;

use App\Agents\AgentExecutionContext;
use App\Filters\ProspectFilters;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\Interaction;
use App\Models\Order;
use App\Models\Prospect;
use App\Models\Sms;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

/**
 * The only door through which an agent reads the records of the CRM.
 *
 * It must be called inside AgentExecutionContext::run(): the queries are the
 * ones of the CRM models, so the global scopes (ProspectScope, OrderScope,
 * CalendarScope...) apply with the rights of the owner, exactly as they do for
 * the same user in the interface. On top of that:
 *
 *  - the project is the one of the context, never a parameter;
 *  - the rows of calls, SMS, appointments and orders are restricted to the
 *    prospects (and calendars) the owner can see;
 *  - only the fields and filters of EntityRegistry exist, and personal data
 *    such as e-mail or phone is returned only when asked for;
 *  - the size of a page is bounded (agents.max_rows).
 */
class ReadGateway
{
    protected const TEXT_COLUMNS = ['first_name', 'last_name', 'company_name', 'email', 'phone_number', 'mobile_phone_number', 'city'];

    public function __construct(protected EntityRegistry $entities)
    {
    }

    /**
     * @param  array{filters?: array, fields?: string[], sort?: array, limit?: int, offset?: int}  $params
     * @throws AgentReadException when the request is refused or invalid
     */
    public function search(AgentExecutionContext $context, string $entity, array $params = []): array
    {
        $spec = $this->entities->get($entity)
            ?? throw new AgentReadException("Entité inconnue : $entity. Entités disponibles : " . implode(', ', $this->entities->keys()) . '.');

        $this->authorize($context, $spec);

        $filters = $this->checkFilters($entity, $spec, $params['filters'] ?? []);
        $fields = $this->checkFields($entity, $spec, $params['fields'] ?? null);
        [$sortField, $direction] = $this->checkSort($entity, $spec, $params['sort'] ?? null);
        $limit = max(1, min((int) ($params['limit'] ?? 20), (int) config('agents.max_rows', 50)));
        $offset = max(0, (int) ($params['offset'] ?? 0));

        $query = match ($entity) {
            'prospect' => $this->prospects($context, $filters),
            'user' => $this->users($context, $filters),
            'call' => $this->calls($context, $filters),
            'sms' => $this->sms($context, $filters),
            'appointment' => $this->appointments($context, $filters),
            'order' => $this->orders($context, $filters),
            'email' => $this->emails($context, $filters),
            'workflow' => $this->workflows($context, $filters),
            'campaign' => $this->campaigns($context, $filters),
        };

        $total = (clone $query)->count();

        $this->sort($entity, $query, $sortField, $direction);

        $models = $query->offset($offset)->limit($limit)->get();
        $rows = $this->format($entity, $models, $fields, $context);

        $returned = count($rows);

        return [
            'entity' => $entity,
            'total' => $total,
            'returned' => $returned,
            'offset' => $offset,
            'has_more' => $offset + $returned < $total,
            'next_offset' => $offset + $returned < $total ? $offset + $returned : null,
            'rows' => $rows,
        ];
    }

    /**
     * The prospects an action would reach, from filters, for the write tools:
     * the same filters and the same visibility as search(), but only the ids.
     *
     * @param  array  $filters  filters of the "prospect" entity
     * @return array{ids: int[], total: int, processed: int, truncated: bool}
     *         total: matching and visible; processed: among them, the ones the CRM
     *         locks against edits; ids: at most $max of the others (when $editable)
     */
    public function selectProspects(AgentExecutionContext $context, array $filters, int $max, bool $editable): array
    {
        $spec = $this->entities->get('prospect');
        $query = $this->prospects($context, $this->checkFilters('prospect', $spec, $filters));

        $total = (clone $query)->count();
        $processed = (clone $query)->whereNotNull('prospects.processed_at')->count();

        if ($editable) {
            $query->whereNull('prospects.processed_at');
        }

        $ids = $query->orderBy('prospects.id')->limit($max + 1)->pluck('prospects.id')->map(fn ($id) => (int) $id)->all();
        $truncated = count($ids) > $max;

        return ['ids' => array_slice($ids, 0, $max), 'total' => $total, 'processed' => $processed, 'truncated' => $truncated];
    }

    /**
     * Of these ids, the prospects of the project the owner can see.
     *
     * @param  int[]  $ids
     * @return array{ids: int[], unknown: int, processed: int}
     */
    public function visibleProspectIds(AgentExecutionContext $context, array $ids, bool $editable): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $rows = Prospect::query()->where('prospects.project_id', $context->project->id)
            ->whereIn('prospects.id', $ids)->get(['prospects.id', 'prospects.processed_at']);

        $processed = $rows->whereNotNull('processed_at')->count();
        $found = $editable ? $rows->whereNull('processed_at') : $rows;

        return ['ids' => $found->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all(), 'unknown' => count($ids) - $rows->count(), 'processed' => $processed];
    }

    // Checks

    protected function authorize(AgentExecutionContext $context, array $spec): void
    {
        if ($spec['ability'] !== null && !auth()->user()->can($spec['ability'], $context->project)) {
            throw new AgentReadException("L'utilisateur propriétaire de l'agent n'a pas le droit \"{$spec['ability']}\" dans ce projet : {$spec['label']} n'est pas consultable.", 'PERMISSION_DENIED');
        }
    }

    /**
     * @return array<string, mixed> the filters, with their values checked
     */
    protected function checkFilters(string $entity, array $spec, array $filters): array
    {
        $checked = [];

        foreach ($filters as $name => $value) {
            // The same keys come back as null when a schema lists them all
            if ($value === null) {
                continue;
            }

            if (!isset($spec['filters'][$name])) {
                throw new AgentReadException("Filtre inconnu pour $entity : $name. Filtres possibles : " . implode(', ', array_keys($spec['filters'])) . '.');
            }

            $definition = $spec['filters'][$name];

            $checked[$name] = match ($definition['type']) {
                'integer' => is_int($value) || (is_string($value) && ctype_digit($value))
                    ? (int) $value
                    : throw new AgentReadException("Le filtre $name doit être un entier."),
                'boolean' => is_bool($value)
                    ? $value
                    : throw new AgentReadException("Le filtre $name doit être true ou false."),
                'date' => is_string($value)
                    ? $value
                    : throw new AgentReadException("Le filtre $name doit être une date AAAA-MM-JJ."),
                default => is_string($value) && trim($value) !== '' && mb_strlen($value) <= 100
                    ? trim($value)
                    : throw new AgentReadException("Le filtre $name doit être un texte non vide de 100 caractères au plus."),
            };

            if (isset($definition['enum']) && !in_array($checked[$name], $definition['enum'], true)) {
                throw new AgentReadException("Le filtre $name doit valoir l'une de ces valeurs : " . implode(', ', $definition['enum']) . '.');
            }
        }

        return $checked;
    }

    /**
     * @return string[]
     */
    protected function checkFields(string $entity, array $spec, ?array $fields): array
    {
        if (!$fields) {
            return $this->entities->defaultFields($entity);
        }

        $unknown = array_diff($fields, array_keys($spec['fields']));

        if ($unknown) {
            throw new AgentReadException("Champ inconnu pour $entity : " . implode(', ', $unknown) . '. Champs possibles : ' . implode(', ', array_keys($spec['fields'])) . '.');
        }

        // The identifier always comes back: it is what links the rows together
        return array_values(array_unique(array_merge(['id'], $fields)));
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function checkSort(string $entity, array $spec, ?array $sort): array
    {
        $field = $sort['field'] ?? $spec['default_sort'][0];
        $direction = strtolower($sort['direction'] ?? $spec['default_sort'][1]);

        if (!in_array($field, $spec['sorts'], true)) {
            throw new AgentReadException("Tri impossible sur $field pour $entity. Tris possibles : " . implode(', ', $spec['sorts']) . '.');
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            throw new AgentReadException('Le sens du tri doit être asc ou desc.');
        }

        return [$field, $direction];
    }

    // Queries

    /**
     * The prospects of the project the owner can see (the global scope of
     * Prospect applies). Used to restrict everything that hangs on a prospect.
     */
    protected function visibleProspects(AgentExecutionContext $context): Builder
    {
        return Prospect::query()->where('prospects.project_id', $context->project->id)->select('prospects.id');
    }

    protected function prospects(AgentExecutionContext $context, array $filters): Builder
    {
        $query = Prospect::query()->where('prospects.project_id', $context->project->id);

        if (isset($filters['query'])) {
            foreach (preg_split('/\s+/', $filters['query'], -1, PREG_SPLIT_NO_EMPTY) as $token) {
                $like = '%' . addcslashes($token, '%_\\') . '%';

                // Every word must be found, in any of the columns
                $query->where(function (Builder $query) use ($like) {
                    foreach (self::TEXT_COLUMNS as $column) {
                        $query->orWhere("prospects.$column", 'like', $like);
                    }
                });
            }
        }

        $this->dateRange($query, 'prospects.created_at', $filters['created_after'] ?? null, $filters['created_before'] ?? null);
        $this->dateRange($query, 'prospects.updated_at', $filters['updated_after'] ?? null, $filters['updated_before'] ?? null);

        foreach (['city', 'country'] as $column) {
            if (isset($filters[$column])) {
                $query->where("prospects.$column", $filters[$column]);
            }
        }

        if (isset($filters['created_by_user_id'])) {
            $query->where('prospects.creator_id', $filters['created_by_user_id']);
        }

        if (isset($filters['is_duplicate'])) {
            $filters['is_duplicate'] ? $query->whereNotNull('prospects.duplicate_id') : $query->whereNull('prospects.duplicate_id');
        }

        if (isset($filters['without_label_id'])) {
            $query->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('prospect_label')
                ->whereColumn('prospect_label.prospect_id', 'prospects.id')
                ->where('prospect_label.label_id', $filters['without_label_id'])
                ->whereNull('prospect_label.deleted_at'));
        }

        if (isset($filters['never_contacted']) || isset($filters['not_contacted_since_days'])) {
            $this->notContacted($query, isset($filters['not_contacted_since_days']) && !isset($filters['never_contacted'])
                ? Carbon::now()->subDays(max(0, (int) $filters['not_contacted_since_days']))->format('Y-m-d H:i:s')
                : null);
        }

        // The filters of the CRM itself, so that "processed", "group" or "label"
        // mean what they mean in the interface
        $legacy = [];
        isset($filters['processed']) && $legacy['processed'] = $filters['processed'];
        isset($filters['assigned_user_id']) && $legacy['withUsers'] = [$filters['assigned_user_id']];
        isset($filters['group_id']) && $legacy['withGroups'] = [$filters['group_id']];
        isset($filters['label_id']) && $legacy['withLabels'] = [$filters['label_id']];

        if ($legacy) {
            $prospectFilters = new ProspectFilters();
            $prospectFilters->addFilters($legacy);
            $prospectFilters->apply($query);
        }

        return $query;
    }

    /**
     * A relation, not a builder: it keeps the join with the project and the
     * choice of columns (users.* and not the columns of the link table, which
     * has a last_activity of its own).
     */
    protected function users(AgentExecutionContext $context, array $filters): Builder|Relation
    {
        // Administrators see the whole team, the others the users they may assign
        $query = $context->project->users()->forCurrentUser();

        if (isset($filters['query'])) {
            foreach (preg_split('/\s+/', $filters['query'], -1, PREG_SPLIT_NO_EMPTY) as $token) {
                $like = '%' . addcslashes($token, '%_\\') . '%';

                $query->where(function ($query) use ($like) {
                    $query->orWhere('users.name', 'like', $like)
                        ->orWhere('users.last_name', 'like', $like)
                        ->orWhere('users.email', 'like', $like);
                });
            }
        }

        return $query;
    }

    protected function calls(AgentExecutionContext $context, array $filters): Builder
    {
        $date = 'COALESCE(interactions.started_at, interactions.created_at)';
        $query = Interaction::query()->whereIn('interactions.prospect_id', $this->visibleProspects($context));

        isset($filters['prospect_id']) && $query->where('interactions.prospect_id', $filters['prospect_id']);
        isset($filters['user_id']) && $query->where('interactions.creator_id', $filters['user_id']);
        isset($filters['status']) && $query->where('interactions.status', $filters['status']);
        isset($filters['source']) && $query->where('interactions.source', $filters['source']);

        if (isset($filters['direction'])) {
            $query->where('interactions.from_user', $filters['direction'] === 'outgoing' ? 1 : 0);
        }

        $this->dateRange($query, $date, $filters['started_after'] ?? null, $filters['started_before'] ?? null, true);

        return $query;
    }

    protected function sms(AgentExecutionContext $context, array $filters): Builder
    {
        $date = 'COALESCE(sms.sent_at, sms.created_at)';
        $query = Sms::query()->whereIn('sms.prospect_id', $this->visibleProspects($context));

        isset($filters['prospect_id']) && $query->where('sms.prospect_id', $filters['prospect_id']);
        isset($filters['user_id']) && $query->where('sms.creator_id', $filters['user_id']);
        isset($filters['source']) && $query->where('sms.source', $filters['source']);

        if (isset($filters['direction'])) {
            $query->where('sms.from_user', $filters['direction'] === 'sent' ? 1 : 0);
        }

        $this->dateRange($query, $date, $filters['sent_after'] ?? null, $filters['sent_before'] ?? null, true);

        return $query;
    }

    protected function appointments(AgentExecutionContext $context, array $filters): Builder
    {
        $query = Event::query()
            ->join('calendars', 'calendars.id', '=', 'events.calendar_id')
            // The calendars of the project the owner can see (CalendarScope applies)
            ->whereIn('events.calendar_id', Calendar::query()->where('calendars.project_id', $context->project->id)->select('calendars.id'))
            // A task has no prospect; an appointment is shown only if its prospect is visible
            ->where(function (Builder $query) use ($context) {
                $query->whereNull('events.prospect_id')
                    ->orWhereIn('events.prospect_id', $this->visibleProspects($context));
            })
            ->select('events.*', 'calendars.name as calendar_name');

        isset($filters['calendar_id']) && $query->where('events.calendar_id', $filters['calendar_id']);
        isset($filters['prospect_id']) && $query->where('events.prospect_id', $filters['prospect_id']);
        isset($filters['user_id']) && $query->where('events.user_id', $filters['user_id']);
        isset($filters['done']) && $query->where('events.done', $filters['done'] ? 1 : 0);
        isset($filters['confirmed']) && $query->where('events.confirmed', $filters['confirmed'] ? 1 : 0);

        $this->dateRange($query, 'events.started_at', $filters['starts_after'] ?? null, $filters['starts_before'] ?? null);

        return $query;
    }

    protected function orders(AgentExecutionContext $context, array $filters): Builder
    {
        $query = Order::query()
            ->whereIn('orders.prospect_id', $this->visibleProspects($context))
            ->select('orders.*')
            ->addSelect(['status_name' => DB::table('order_status')->select('name')->whereColumn('order_status.id', 'orders.status_id')->limit(1)]);

        isset($filters['prospect_id']) && $query->where('orders.prospect_id', $filters['prospect_id']);
        isset($filters['user_id']) && $query->where('orders.creator_id', $filters['user_id']);

        if (isset($filters['status'])) {
            $query->whereIn('orders.status_id', DB::table('order_status')
                ->where('project_id', $context->project->id)
                ->where('name', $filters['status'])
                ->select('id'));
        }

        $this->dateRange($query, 'orders.created_at', $filters['created_after'] ?? null, $filters['created_before'] ?? null);

        return $query;
    }

    /**
     * Prospects without any outgoing contact (call, SMS or e-mail sent by a
     * user), after $since, or ever when $since is null. Incoming calls, SMS
     * and mails are not a contact made by the team.
     */
    protected function notContacted(Builder $query, ?string $since): void
    {
        $outgoing = [
            ['interactions', 'COALESCE(interactions.started_at, interactions.created_at)', ''],
            ['sms', 'COALESCE(sms.sent_at, sms.created_at)', ''],
            ['messages', 'messages.created_at', 'and messages.deleted_at is null'],
        ];

        foreach ($outgoing as [$table, $date, $extra]) {
            $query->whereNotExists(function ($q) use ($table, $date, $extra, $since) {
                $q->select(DB::raw(1))->from($table)
                    ->whereColumn("$table.prospect_id", 'prospects.id')
                    ->where("$table.from_user", 1);

                if ($extra) {
                    $q->whereNull("$table.deleted_at");
                }

                if ($since !== null) {
                    $q->whereRaw("$date >= ?", [$since]);
                }
            });
        }
    }

    protected function emails(AgentExecutionContext $context, array $filters): Builder
    {
        $query = \App\Models\Message::query()
            ->join('threads', 'threads.id', '=', 'messages.thread_id')
            ->where('threads.project_id', $context->project->id)
            // The threads the owner can reach (the scope of Thread applies)
            ->whereIn('messages.thread_id', \App\Models\Thread::query()->where('threads.project_id', $context->project->id)->select('threads.id'))
            ->whereIn('messages.prospect_id', $this->visibleProspects($context))
            ->select('messages.*', 'threads.name as thread_name');

        isset($filters['prospect_id']) && $query->where('messages.prospect_id', $filters['prospect_id']);
        isset($filters['thread_id']) && $query->where('messages.thread_id', $filters['thread_id']);
        isset($filters['user_id']) && $query->where('messages.creator_id', $filters['user_id']);

        if (isset($filters['direction'])) {
            $query->where('messages.from_user', $filters['direction'] === 'sent' ? 1 : 0);
        }

        if (isset($filters['query'])) {
            $query->where('messages.plain_text', 'like', '%' . addcslashes($filters['query'], '%_\\') . '%');
        }

        $this->dateRange($query, 'messages.created_at', $filters['created_after'] ?? null, $filters['created_before'] ?? null);

        return $query;
    }

    protected function workflows(AgentExecutionContext $context, array $filters): Builder
    {
        $query = \App\Models\Workflow::query()->where('workflows.project_id', $context->project->id);

        isset($filters['status']) && $query->where('workflows.status', $filters['status']);
        isset($filters['query']) && $query->where('workflows.name', 'like', '%' . addcslashes($filters['query'], '%_\\') . '%');

        return $query;
    }

    protected function campaigns(AgentExecutionContext $context, array $filters): Builder
    {
        $query = \App\Models\Campaign::query()->where('campaigns.project_id', $context->project->id);

        isset($filters['active']) && $query->where('campaigns.active', $filters['active'] ? 1 : 0);
        isset($filters['query']) && $query->where('campaigns.name', 'like', '%' . addcslashes($filters['query'], '%_\\') . '%');

        return $query;
    }

    // Sort

    protected function sort(string $entity, Builder|Relation $query, string $field, string $direction): void
    {
        $column = match ($entity) {
            'prospect' => "prospects.$field",
            'user' => "users.$field",
            'call' => $field === 'started_at' ? 'COALESCE(interactions.started_at, interactions.created_at)' : "interactions.$field",
            'sms' => $field === 'sent_at' ? 'COALESCE(sms.sent_at, sms.created_at)' : "sms.$field",
            'appointment' => "events.$field",
            'order' => "orders.$field",
            'email' => "messages.$field",
            'workflow' => "workflows.$field",
            'campaign' => "campaigns.$field",
        };

        $primary = match ($entity) {
            'prospect' => 'prospects.id',
            'user' => 'users.id',
            'call' => 'interactions.id',
            'sms' => 'sms.id',
            'appointment' => 'events.id',
            'order' => 'orders.id',
            'email' => 'messages.id',
            'workflow' => 'workflows.id',
            'campaign' => 'campaigns.id',
        };

        // The id breaks the ties, so that the pages never overlap
        $query->orderByRaw("$column $direction")->orderBy($primary, $direction);
    }

    // Dates

    protected function dateRange(Builder $query, string $column, ?string $after, ?string $before, bool $raw = false): void
    {
        if ($after !== null) {
            $raw
                ? $query->whereRaw("$column >= ?", [$this->bound($after, false)])
                : $query->where($column, '>=', $this->bound($after, false));
        }

        if ($before !== null) {
            $raw
                ? $query->whereRaw("$column <= ?", [$this->bound($before, true)])
                : $query->where($column, '<=', $this->bound($before, true));
        }
    }

    /**
     * A date without time covers the whole day: start of day to start a range,
     * end of day to end it.
     */
    protected function bound(string $value, bool $end): string
    {
        try {
            $date = Carbon::parse($value);
        } catch (\Throwable) {
            throw new AgentReadException("Date invalide : \"$value\". Format attendu : AAAA-MM-JJ.");
        }

        if (!preg_match('/\d{1,2}:\d{2}/', $value)) {
            $date = $end ? $date->endOfDay() : $date->startOfDay();
        }

        return $date->format('Y-m-d H:i:s');
    }

    // Rows

    /**
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $models
     * @param  string[]  $fields
     */
    protected function format(string $entity, $models, array $fields, AgentExecutionContext $context): array
    {
        $roles = $entity === 'user' && in_array('roles', $fields, true)
            ? $this->rolesOf($models->pluck('id')->all(), $context->project->id)
            : [];

        $rows = $models->map(function ($model) use ($entity, $fields, $roles) {
            $row = [];

            foreach ($fields as $field) {
                $row[$field] = $this->value($entity, $model, $field, $roles);
            }

            return $row;
        })->all();

        return $this->withUserNames($rows);
    }

    protected function value(string $entity, $model, string $field, array $roles): mixed
    {
        $raw = fn (string $attribute) => $model->getRawOriginal($attribute);
        $iso = fn (?string $value) => $value ? Carbon::parse($value)->toIso8601String() : null;

        return match ($entity . '.' . $field) {
            'user.roles' => $roles[$model->id] ?? '',
            'call.user_id', 'sms.user_id', 'order.user_id', 'email.user_id' => $raw('creator_id') !== null ? (int) $raw('creator_id') : null,
            'prospect.created_by_user_id' => $raw('creator_id') !== null ? (int) $raw('creator_id') : null,
            'call.direction' => $raw('from_user') ? 'outgoing' : 'incoming',
            'email.direction' => $raw('from_user') ? 'sent' : 'received',
            'email.thread' => $model->getAttribute('thread_name'),
            'email.sent' => (bool) $raw('sent'),
            'email.preview' => mb_substr(trim((string) preg_replace('/\s+/', ' ', $raw('plain_text') ?: strip_tags((string) $raw('body')))), 0, 300),
            'sms.direction' => $raw('from_user') ? 'sent' : 'received',
            'call.started_at' => $iso($raw('started_at') ?? $raw('created_at')),
            'sms.sent_at' => $iso($raw('sent_at') ?? $raw('created_at')),
            'call.duration_seconds' => $raw('started_at') && $raw('ended_at')
                ? max(0, Carbon::parse($raw('started_at'))->diffInSeconds(Carbon::parse($raw('ended_at')), false))
                : null,
            'sms.message' => mb_substr((string) $raw('message'), 0, 300),
            'appointment.calendar' => $model->getAttribute('calendar_name'),
            'appointment.done', 'appointment.confirmed', 'campaign.active', 'campaign.error' => (bool) $raw($field),
            'order.status' => $model->getAttribute('status_name'),
            default => $this->plain($model, $field, $raw, $iso),
        };
    }

    protected function plain($model, string $field, \Closure $raw, \Closure $iso): mixed
    {
        $value = $raw($field);

        if (str_ends_with($field, '_at') || $field === 'last_activity') {
            return $iso($value);
        }

        if (in_array($field, ['id', 'prospect_id', 'user_id', 'number'], true)) {
            return $value === null ? null : (int) $value;
        }

        return $value;
    }

    /**
     * Next to every user id, the name of the user: the model reads names, not
     * numbers.
     */
    protected function withUserNames(array $rows): array
    {
        $ids = [];

        foreach ($rows as $row) {
            foreach (['user_id', 'created_by_user_id'] as $key) {
                if (!empty($row[$key])) {
                    $ids[] = $row[$key];
                }
            }
        }

        if (!$ids) {
            return $rows;
        }

        $names = User::query()->whereIn('id', array_unique($ids))->get(['id', 'name', 'last_name'])
            ->mapWithKeys(fn ($user) => [$user->id => trim($user->name . ' ' . $user->last_name)]);

        return array_map(function ($row) use ($names) {
            foreach (['user_id' => 'user', 'created_by_user_id' => 'created_by'] as $key => $label) {
                if (array_key_exists($key, $row) && !empty($row[$key])) {
                    $row[$label] = $names[$row[$key]] ?? null;
                }
            }

            return $row;
        }, $rows);
    }

    /**
     * @return array<int, string>
     */
    protected function rolesOf(array $userIds, int $projectId): array
    {
        return DB::table('model_has_roles as m')
            ->join('roles as r', 'r.id', '=', 'm.role_id')
            ->where('m.project_id', $projectId)
            ->whereIn('m.model_id', $userIds)
            ->get(['m.model_id', 'r.name'])
            ->groupBy('model_id')
            ->map(fn ($group) => $group->pluck('name')->implode(', '))
            ->all();
    }
}
