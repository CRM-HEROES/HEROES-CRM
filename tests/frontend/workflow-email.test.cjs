const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { transformSync } = require('@babel/core');

const { code } = transformSync(fs.readFileSync(path.resolve(__dirname, '../../resources/js/utils/workflow-email.js'), 'utf8'), {
    babelrc: false,
    configFile: false,
    plugins: ['@babel/plugin-transform-modules-commonjs'],
});
const module_ = { exports: {} };
new Function('module', 'exports', code)(module_, module_.exports);
const e = module_.exports;

test('the preview shows an example in place of each variable, and leaves the unknown ones', () => {
    assert.equal(e.withSamples('Bonjour {{prospect.firstname}} {{ prospect.lastname }}'), 'Bonjour Marie Dupont');
    assert.equal(e.withSamples('{{prospect.nope}} {{nothing.here}}'), '{{prospect.nope}} {{nothing.here}}');
    assert.equal(e.withSamples('Chez {{project.name}}', 'Heroes <CRM>'), 'Chez Heroes &lt;CRM&gt;');
    assert.equal(e.withSamples(null), '');
});

test('a link or an image address is a web address, never a script', () => {
    assert.equal(e.safeUrl('https://heroes.test/page'), 'https://heroes.test/page');
    assert.equal(e.safeUrl('mailto:contact@heroes.test'), 'mailto:contact@heroes.test');
    assert.equal(e.safeUrl('www.heroes.test'), 'https://www.heroes.test');
    assert.equal(e.safeUrl('javascript:alert(1)'), null);
    assert.equal(e.safeUrl('  JaVaScRiPt:alert(1)'), null);
    assert.equal(e.safeUrl('data:text/html;base64,AAAA'), null);
    assert.equal(e.safeUrl('', {}), null);
    // An image is on the web only
    assert.equal(e.safeUrl('https://heroes.test/a.png', { image: true }), 'https://heroes.test/a.png');
    assert.equal(e.safeUrl('mailto:a@b.fr', { image: true }), null);
    assert.equal(e.safeUrl('heroes.test/a.png', { image: true }), null);
});

test('an empty editor is not a content, an image or a word is', () => {
    assert.equal(e.hasContent(''), false);
    assert.equal(e.hasContent('<p><br></p>'), false);
    assert.equal(e.hasContent('<div>&nbsp;</div>'), false);
    assert.equal(e.hasContent('<p>Bonjour</p>'), true);
    assert.equal(e.hasContent('<img src="https://heroes.test/a.png">'), true);
});

test('the text shown in the preview is escaped', () => {
    assert.equal(e.escapeHtml('<b>"x" & y</b>'), '&lt;b&gt;&quot;x&quot; &amp; y&lt;/b&gt;');
});

test('the preview of the email runs in a frame without any right', () => {
    const source = fs.readFileSync(path.resolve(__dirname, '../../resources/js/components/workflow/EmailEditor.vue'), 'utf8');

    assert.ok(/<iframe[^>]*class="hc-wfe-mail-frame"[^>]*sandbox=""/s.test(source));
    assert.ok(!source.includes('v-html'), 'the HTML of the email is never put in the page of the CRM');
});

test('the design by default is the one of the server, key for key', () => {
    const design = e.defaultDesign('Heroes');

    assert.deepEqual(Object.keys(design).sort(), [
        'accent_color', 'background_color', 'brand_label', 'brand_name', 'category', 'closing', 'font', 'header_color', 'logo', 'rights',
        'sent_by', 'show_footer', 'show_signature', 'show_title', 'signature_name', 'style', 'text_color',
    ]);
    assert.equal(design.header_color, '#163b52');
    assert.equal(design.signature_name, 'Heroes');
    assert.equal(design.brand_label, ''); // no brand of the CRM on the emails of a project
    assert.equal(e.defaultDesign().sent_by, '');
});

test('every colour, font and style of the editor is a value the server accepts', () => {
    const colour = /^#[0-9a-f]{6}$/i;

    for (const palette of e.PALETTES) {
        assert.match(palette.header_color, colour);
        assert.match(palette.accent_color, colour);
    }

    assert.deepEqual(e.FONTS, ['arial', 'verdana', 'tahoma', 'trebuchet', 'georgia', 'times']);
    assert.deepEqual(e.STYLES, ['branded', 'simple']);
});
