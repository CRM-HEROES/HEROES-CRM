'use strict';

/**
 * The help sheets of the agents (resources/agent-knowledge) against the real interface and server: a permission named in a
 * sheet is one the CRM really asks, a file named as a source exists, a route named exists, and every entry of the top bar
 * and of the settings is told somewhere. A sheet that drifts from the CRM fails here instead of misleading the agent.
 */

const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { pathToFileURL } = require('node:url');

const root = path.resolve(__dirname, '..', '..');
const sheetsDir = path.join(root, 'resources/agent-knowledge');
const sheets = fs.readdirSync(sheetsDir).filter((f) => f.endsWith('.md')).map((f) => ({ file: f, text: fs.readFileSync(path.join(sheetsDir, f), 'utf8') }));

function walk(dir, extensions, found = []) {
    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
        const full = path.join(dir, entry.name);
        if (entry.isDirectory()) {
            if (!['node_modules', 'vendor'].includes(entry.name)) walk(full, extensions, found);
        } else if (extensions.some((e) => entry.name.endsWith(e))) {
            found.push(full);
        }
    }

    return found;
}

/** "projectRoleUpdate" -> "all.project.role.update" */
function dotted(ability) {
    return 'all.' + (ability.match(/[A-Z]?[a-z0-9]+/g) || []).map((p) => p.toLowerCase()).join('.');
}

async function knownPermissions() {
    const known = new Set();

    // What the screen of the roles offers
    const tree = (await import(pathToFileURL(path.join(root, 'resources/js/utils/role-permissions.js')).href)).default;
    (function visit(node, prefix) {
        const name = prefix ? prefix + '.' + node.key : node.key;
        known.add(name);
        (node.sub || []).forEach((child) => visit(child, name));
    })(tree, '');

    // What the interface asks
    for (const file of walk(path.join(root, 'resources/js'), ['.vue', '.js'])) {
        for (const m of fs.readFileSync(file, 'utf8').matchAll(/can\(\s*['"](all[a-zA-Z0-9_.\-]*)['"]/g)) known.add(m[1]);
    }

    // What the server asks
    for (const file of walk(path.join(root, 'app'), ['.php'])) {
        const text = fs.readFileSync(file, 'utf8');
        for (const m of text.matchAll(/can\(\s*'([a-z][A-Za-z0-9]+)'/g)) known.add(dotted(m[1]));
        for (const m of text.matchAll(/'ability'\s*=>\s*'([a-z][A-Za-z0-9]+)'/g)) known.add(dotted(m[1]));
    }

    return known;
}

test('every permission named in a sheet is one the CRM really asks or offers', async () => {
    const known = await knownPermissions();
    const unknown = [];

    for (const { file, text } of sheets) {
        for (const m of text.matchAll(/`(all(?:\.[a-z0-9_-]+)*)`/g)) {
            // "all.project.user.affected" is told as "not a right to tick": the entry of the panel, not a permission
            if (!known.has(m[1]) && m[1] !== 'all.project.user.affected') unknown.push(`${file} : ${m[1]}`);
        }
    }

    assert.deepEqual([...new Set(unknown)], [], 'Droits cités par une fiche mais ni demandés ni proposés par le CRM');
});

test('every file of the interface named as a source in a sheet exists', () => {
    const missing = [];

    for (const { file, text } of sheets) {
        for (const m of text.matchAll(/`((?:components|router|lang|store|actions|apis|utils)\/[A-Za-z0-9_\/.-]+\.(?:vue|js|json))`/g)) {
            const candidates = [m[1], 'resources/js/' + m[1], 'resources/js/components/' + m[1].replace(/^components\//, '')];
            if (!candidates.some((c) => fs.existsSync(path.join(root, c)))) missing.push(`${file} : ${m[1]}`);
        }
    }

    assert.deepEqual([...new Set(missing)], [], 'Fichiers cités comme sources mais introuvables');
});

test('every route of the router named in a sheet exists', () => {
    const router = fs.readFileSync(path.join(root, 'resources/js/router/index.js'), 'utf8');
    const names = new Set([...router.matchAll(/name:\s*["']([^"']+)["']/g)].map((m) => m[1]));
    const missing = [];

    for (const { file, text } of sheets) {
        for (const m of text.matchAll(/`([a-z]+(?:-[a-z]+)*\.(?:show|index|edit|view))`/g)) {
            if (!names.has(m[1])) missing.push(`${file} : ${m[1]}`);
        }
    }

    assert.deepEqual([...new Set(missing)], [], 'Routes citées mais absentes du routeur');
});

test('every entry of the top bar and of the settings is told in at least one sheet', () => {
    const fr = JSON.parse(fs.readFileSync(path.join(root, 'resources/js/lang/fr.json'), 'utf8'));
    const everything = sheets.map((s) => s.text.toLowerCase()).join(' ');

    // Entries that are not a function of the CRM: a button to close, the copyright, the confirmations of a double factor
    const ignored = new Set(['header.close', 'setting.copyright.title', 'setting.copyright.commercial_questions', 'setting.copyright.technical_questions']);
    const ignoredStart = ['setting.google_auth.confirm'];
    const missing = [];

    for (const [section, component] of [['header', 'resources/js/components/layouts/Header.vue'], ['setting', 'resources/js/components/layouts/setting/Layout.vue']]) {
        const source = fs.readFileSync(path.join(root, component), 'utf8');

        for (const m of source.matchAll(new RegExp(`\\$t\\(\\s*['"]${section}\\.([a-zA-Z0-9_.]+?)['"]`, 'g'))) {
            const key = `${section}.${m[1]}`;
            if (ignored.has(key) || ignoredStart.some((p) => key.startsWith(p))) continue;

            let value = fr[section];
            for (const part of m[1].split('.')) value = value && typeof value === 'object' ? value[part] : undefined;
            if (value && typeof value === 'object') value = value.title || value.name || value.label;

            // A description under a title that is told counts as told with it: only the titles and names are looked for
            if (typeof value === 'string' && value.length > 2 && !key.endsWith('.description') && !everything.includes(value.toLowerCase())) {
                missing.push(`${key} : ${value}`);
            }
        }
    }

    assert.deepEqual([...new Set(missing)], [], 'Entrées de l\'interface qu\'aucune fiche ne mentionne');
});
