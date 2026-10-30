const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const root = path.resolve(__dirname, '../..');
const lang = JSON.parse(fs.readFileSync(path.join(root, 'resources/js/lang/fr.json'), 'utf8')).workflow;

const tip = (() => {
    const { code } = transformSync(parse(fs.readFileSync(path.join(root, 'resources/js/components/workflow/HelpTip.vue'), 'utf8')).descriptor.script.content, {
        babelrc: false,
        configFile: false,
        plugins: ['@babel/plugin-transform-modules-commonjs'],
    });
    const module_ = { exports: {} };
    new Function('module', 'exports', code)(module_, module_.exports);
    return module_.exports.default;
})();

const content = (props, messages = {}) => tip.computed.content.call({ ...props, $te: (k) => k in messages, $t: (k) => messages[k] });

test('the bubble shows the text given, or the translation of the key, or nothing', () => {
    assert.equal(content({ text: 'Hello', k: '' }), 'Hello');
    assert.equal(content({ text: '', k: 'a.b' }, { 'a.b': 'Translated' }), 'Translated');
    assert.equal(content({ text: '', k: 'a.unknown' }), '');
    assert.equal(content({ text: '', k: '' }), '');
});

test('every tip key used by the screens exists in French', () => {
    const used = new Set();
    const dir = path.join(root, 'resources/js/components/workflow');

    for (const file of fs.readdirSync(dir).filter((f) => f.endsWith('.vue'))) {
        for (const m of fs.readFileSync(path.join(dir, file), 'utf8').matchAll(/<help-tip[^>]*?\sk="(workflow\.[a-z_.]+)"/g)) used.add(m[1]);
    }

    assert.ok(used.size > 10);
    for (const key of used) {
        const value = key.split('.').slice(1).reduce((o, part) => (o ? o[part] : undefined), lang);
        assert.equal(typeof value, 'string', key);
    }

    // The keys built from a status or a button
    for (const status of ['draft', 'active', 'paused', 'archived']) assert.ok(lang.tip['status_' + status], status);
    for (const name of ['turn_on', 'publish_now', 'activate', 'publish']) assert.ok(lang.tip[name], name);
});
