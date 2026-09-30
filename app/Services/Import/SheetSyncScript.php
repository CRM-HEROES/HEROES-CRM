<?php

namespace App\Services\Import;

use App\Models\Import;
use App\Support\ImportHeaderAliases;

/**
 * Builds the Google Apps Script a user pastes in their sheet
 * (Extensions > Apps Script) to push new leads to the CRM instantly.
 */
class SheetSyncScript
{
    /** Rows per HTTP request (the webhook accepts up to 500). */
    public const BATCH_SIZE = 200;

    /**
     * Daily safety net, only there to catch an event Google never delivered:
     * hour of the day (0 = midnight, in the script's time zone), null = disabled.
     * Google runs an "at hour" trigger at a random moment within that hour.
     */
    public const SAFETY_NET_HOUR = 0;

    public function webhookUrl(Import $import): string
    {
        // The CRM is served from the same origin as its API: use the host the
        // user is on, APP_URL being often left at its default.
        if (app()->runningInConsole() || !request()->getHost()) {
            $base = config('app.url') ?: url('/');
        } else {
            // Behind a tunnel / reverse proxy (ngrok, load balancer) the scheme
            // seen by PHP is http: trust the forwarded one.
            $scheme = request()->header('X-Forwarded-Proto') ?: request()->getScheme();
            $base = $scheme . '://' . request()->getHttpHost();
        }

        return rtrim($base, '/') . '/api/webservice/' . $import->id . '/sheet-sync';
    }

    /**
     * How the script recognises a lead: the sheet's "id" column when it has one
     * (Meta lead id), otherwise the values of the import's "MAJ" columns.
     */
    public function keySpec(Import $import): array
    {
        $headers = is_array($import->headers) ? $import->headers : [];
        $mapping = is_array($import->mapping) ? $import->mapping : [];

        $idNames = [];
        foreach ($headers as $header) {
            if (ImportHeaderAliases::normalize($header) === 'id') {
                $idNames = ['id'];
            }
        }

        $majSlugs = [];
        if (!empty($import->duplicates_fields) && $import->project) {
            $majSlugs = $import->project
                ->fields()
                ->whereIn('id', (array) $import->duplicates_fields)
                ->pluck('slug')
                ->all();
        }

        // No "MAJ" column chosen: the importer falls back on email / phones.
        $fallback = empty($majSlugs);
        $targets = $fallback ? ['email', 'phone_number', 'mobile_phone_number'] : $majSlugs;

        $groups = [];
        foreach ($mapping as $index => $attribute) {
            if (!is_string($attribute) || !isset($headers[$index])) {
                continue;
            }

            $slug = str_starts_with($attribute, 'meta->') ? substr($attribute, 6) : $attribute;
            if (!in_array($slug, $targets, true)) {
                continue;
            }

            $names = [ImportHeaderAliases::normalize($headers[$index])];
            if (!str_starts_with($attribute, 'meta->')) {
                $names = array_merge($names, ImportHeaderAliases::aliasesFor($slug));
            }

            $groups[$slug] = [
                'names' => array_values(array_unique(array_filter($names))),
                'type' => (str_contains($slug, 'phone') || str_contains($slug, 'mobile')) ? 'phone' : 'text',
            ];
        }

        return ['idNames' => $idNames, 'maj' => array_values($groups)];
    }

    public function config(Import $import): array
    {
        $import->makeVisible('token');

        return [
            'webhookUrl' => $this->webhookUrl($import),
            'token' => $import->token,
            'batchSize' => self::BATCH_SIZE,
            'safetyNetHour' => self::SAFETY_NET_HOUR,
            // Only these sheets/tabs are synced (empty = all of them).
            'sheets' => array_values((array) ($import->selected_sheets ?: [])),
            'key' => $this->keySpec($import),
        ];
    }

    public function build(Import $import): string
    {
        return str_replace(
            '__CONFIG__',
            json_encode($this->config($import), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            self::TEMPLATE
        );
    }

    private const TEMPLATE = <<<'JS'
/**
 * HEROES CRM - synchronisation instantanee Google Sheets -> CRM
 *
 * 1. Extensions > Apps Script, collez ce script (remplacez tout le contenu).
 * 2. Choisissez la fonction "setup" dans la liste, cliquez sur Executer, autorisez l'acces.
 *
 * Les lignes deja presentes au moment de "setup" ne sont pas envoyees (elles
 * viennent de l'import classique). Seules les NOUVELLES lignes sont envoyees,
 * par lots, directement au CRM.
 *
 * - "testWebhook" verifie que le CRM est joignable (affiche l'URL et la reponse).
 * - "setup" ne doit etre lance qu'UNE fois : il considere comme deja importees
 *   toutes les lignes presentes a ce moment-la. Une ligne ajoutee avant un
 *   nouveau "setup" ne serait donc jamais envoyee.
 * - "backfill" renvoie TOUTES les lignes de la feuille (le CRM ignore les
 *   leads qu'il possede deja et ne cree que les manquants).
 */
var CONFIG = __CONFIG__;
var REGISTRY_SHEET = '_crm_sync';

// ---------------------------------------------------------------- triggers

/** Installed trigger: fires for user edits AND for rows added by an API / connector. */
function onSheetChange(e) {
  PropertiesService.getScriptProperties().setProperty('dirty', String(Date.now()));
  run_(false);
}

/** Daily safety net only (an event Google did not deliver). */
function safetyNet() {
  run_(true);
}

function setup() {
  removeTriggers_();
  ScriptApp.newTrigger('onSheetChange').forSpreadsheet(SpreadsheetApp.getActive()).onChange().create();
  if (CONFIG.safetyNetHour !== null) {
    // once a day, at midnight (Google picks the exact minute within that hour)
    ScriptApp.newTrigger('safetyNet').timeBased().atHour(CONFIG.safetyNetHour).everyDays(1).create();
  }
  var marked = mark_(allRows_().map(function (r) { return r.key; }));
  Logger.log('Synchronisation installee. ' + marked + ' ligne(s) nouvelle(s) considerees comme deja importees (utilisez backfill pour les envoyer).');
  try {
    testWebhook();
  } catch (e) {
    Logger.log('ATTENTION : le CRM est injoignable (' + e.message + '). Verifiez l\'URL ci-dessus (ngrok / serveur).');
  }
}

/** Checks the connection to the CRM without sending any lead. */
function testWebhook() {
  var body = JSON.stringify({ batch_id: 'test', sheet: 'test', headers: [], rows: [] });
  var res = UrlFetchApp.fetch(CONFIG.webhookUrl, {
    method: 'post', contentType: 'application/json', payload: body,
    headers: { 'X-Signature': hmac_(body), 'ngrok-skip-browser-warning': '1' }, muteHttpExceptions: true
  });
  var code = res.getResponseCode();
  Logger.log('URL du CRM : ' + CONFIG.webhookUrl);
  Logger.log('Reponse du CRM : HTTP ' + code + ' ' + res.getContentText().substring(0, 200) + (code === 202 ? '  => OK' : '  => PROBLEME'));
  return code;
}

function backfill() {
  var lock = LockService.getScriptLock();
  lock.waitLock(30000);
  try { syncOnce_(true); } finally { lock.releaseLock(); }
}

function removeTriggers_() {
  // This project only contains this script: remove every trigger, including
  // those left by an older version whose function no longer exists (they
  // would fail on every change).
  ScriptApp.getProjectTriggers().forEach(function (t) { ScriptApp.deleteTrigger(t); });
}

// -------------------------------------------------------------- main loop

function run_(force) {
  var props = PropertiesService.getScriptProperties();
  var lock = LockService.getScriptLock();
  // One pass at a time. An event that cannot get the lock leaves the "dirty"
  // mark: the running pass loops again before it exits.
  if (!lock.tryLock(25000)) return;
  try {
    if (!force && Number(props.getProperty('dirty') || 0) <= Number(props.getProperty('processed') || 0)) return;
    var guard = 0, start;
    do {
      start = Date.now();
      syncOnce_();
      props.setProperty('processed', String(start));
      guard++;
    } while (Number(props.getProperty('dirty') || 0) > start && guard < 20);
  } finally {
    lock.releaseLock();
  }
}

/** Sends every row whose key has not been acknowledged by the CRM yet. */
function syncOnce_(ignoreRegistry) {
  var known = ignoreRegistry ? {} : registry_();
  var groups = {};
  allRows_().forEach(function (r) {
    if (known[r.key]) return;
    (groups[r.sheet] = groups[r.sheet] || { headers: r.headers, rows: [] }).rows.push(r);
  });

  var requests = [], meta = [];
  Object.keys(groups).forEach(function (sheet) {
    var g = groups[sheet];
    for (var i = 0; i < g.rows.length; i += CONFIG.batchSize) {
      var chunk = g.rows.slice(i, i + CONFIG.batchSize);
      var body = JSON.stringify({
        batch_id: Utilities.getUuid(),
        sheet: sheet,
        headers: g.headers,
        rows: chunk.map(function (r) { return { key: r.key, values: r.values }; })
      });
      requests.push({
        url: CONFIG.webhookUrl, method: 'post', contentType: 'application/json',
        payload: body, headers: { 'X-Signature': hmac_(body), 'ngrok-skip-browser-warning': '1' }, muteHttpExceptions: true
      });
      meta.push(chunk);
    }
  });

  // Several lots at once, 10 requests in parallel at most.
  for (var s = 0; s < requests.length; s += 10) {
    var responses;
    try {
      responses = UrlFetchApp.fetchAll(requests.slice(s, s + 10));
    } catch (e) {
      Logger.log('Envoi impossible vers ' + CONFIG.webhookUrl + ' : ' + e.message + ' (les lignes seront renvoyees)');
      throw e;
    }
    responses.forEach(function (res, j) {
      var code = res.getResponseCode();
      if (code >= 200 && code < 300) {
        // Acknowledged = safely stored by the CRM: only then is the row forgotten.
        mark_(meta[s + j].map(function (r) { return r.key; }));
      } else {
        Logger.log('CRM: HTTP ' + code + ' ' + res.getContentText().substring(0, 300) + ' (les lignes seront renvoyees)');
      }
    });
  }
}

// ------------------------------------------------------------------ rows

function allRows_() {
  var out = [];
  SpreadsheetApp.getActive().getSheets().forEach(function (sh) {
    var name = sh.getName();
    if (name === REGISTRY_SHEET) return;
    if (CONFIG.sheets.length && CONFIG.sheets.indexOf(name) < 0) return;
    if (sh.getLastRow() < 2) return;

    var values = sh.getDataRange().getValues();
    var headers = values[0].map(function (h) { return String(h); });
    var normHeaders = headers.map(norm_);
    var tz = Session.getScriptTimeZone();

    for (var i = 1; i < values.length; i++) {
      var row = values[i].map(function (v) {
        if (v instanceof Date) return Utilities.formatDate(v, tz, "yyyy-MM-dd'T'HH:mm:ssXXX");
        return v === null ? '' : v;
      });
      var key = key_(row, normHeaders);
      if (key) out.push({ sheet: name, headers: headers, values: row, key: key });
    }
  });
  return out;
}

/** Lead identifier: the sheet "id" column, otherwise the "MAJ" columns. Empty = not a lead (yet). */
function key_(row, normHeaders) {
  var id = pick_(row, normHeaders, CONFIG.key.idNames);
  if (id !== '') return 'id:' + id;

  var parts = [], any = false;
  CONFIG.key.maj.forEach(function (group) {
    var v = pick_(row, normHeaders, group.names);
    if (group.type === 'phone') v = v.replace(/\D+/g, '').slice(-9);
    else v = v.toLowerCase();
    if (v !== '') any = true;
    parts.push(v);
  });
  return any ? 'maj:' + parts.join('|') : '';
}

function pick_(row, normHeaders, names) {
  for (var n = 0; n < names.length; n++) {
    var idx = normHeaders.indexOf(names[n]);
    if (idx >= 0 && row[idx] !== '' && row[idx] !== null) return String(row[idx]).trim();
  }
  return '';
}

function norm_(s) {
  return String(s).normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase()
    .replace(/[^a-z0-9]+/g, ' ').trim();
}

function hmac_(body) {
  // Sign the UTF-8 BYTES. With a string argument Apps Script turns every
  // non-ASCII character (e, a with accent...) into "?" before signing, which
  // never matches the body actually sent.
  var sig = Utilities.computeHmacSha256Signature(
    Utilities.newBlob(body).getBytes(),
    Utilities.newBlob(CONFIG.token).getBytes()
  );
  return sig.map(function (b) {
    return ('0' + (b & 0xFF).toString(16)).slice(-2);
  }).join('');
}

// -------------------------------------------- registry of acknowledged keys

function registrySheet_() {
  var ss = SpreadsheetApp.getActive();
  var sh = ss.getSheetByName(REGISTRY_SHEET);
  if (!sh) {
    sh = ss.insertSheet(REGISTRY_SHEET);
    sh.getRange('A:A').setNumberFormat('@');
    sh.hideSheet();
  }
  return sh;
}

function registry_() {
  var sh = registrySheet_(), set = {}, last = sh.getLastRow();
  if (last > 0) sh.getRange(1, 1, last, 1).getValues().forEach(function (r) { set[r[0]] = true; });
  return set;
}

function mark_(keys) {
  if (!keys.length) return 0;
  var sh = registrySheet_(), now = new Date(), known = registry_(), seen = {};
  var fresh = keys.filter(function (k) {
    if (known[k] || seen[k]) return false;
    seen[k] = true;
    return true;
  });
  if (fresh.length) {
    sh.getRange(sh.getLastRow() + 1, 1, fresh.length, 2)
      .setValues(fresh.map(function (k) { return [k, now]; }));
  }
  return fresh.length;
}
JS;
}
