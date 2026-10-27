const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse, compileTemplate } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const filename = path.resolve(__dirname, '../../resources/js/components/stat-builder/renderers/TableView.vue');
const { descriptor } = parse(fs.readFileSync(filename, 'utf8'));
const { code } = transformSync(descriptor.script.content, {
    babelrc: false,
    configFile: false,
    plugins: ['@babel/plugin-transform-modules-commonjs'],
});

function deferred() {
    let resolve;
    const promise = new Promise(done => { resolve = done; });
    return { promise, resolve };
}

function response(status = 200, body = new Blob(['audio'], { type: 'audio/wav' })) {
    return { status, ok: status >= 200 && status < 300, blob: async () => body };
}

function table(fetchHandler = async () => response(), playHandler = () => Promise.resolve()) {
    const requests = [], audios = [], objects = [], revoked = [];
    class AudioStub {
        constructor(url) {
            this.url = url;
            this.events = {};
            this.paused = false;
            audios.push(this);
        }
        addEventListener(event, handler) { this.events[event] = handler; }
        play() { return playHandler(this); }
        pause() { this.paused = true; }
    }
    const moduleObject = { exports: {} };
    new Function('require', 'module', 'exports', 'fetch', 'Audio', 'URL', 'AbortController', code)(
        () => ({ formatNumber: String, formatValue: String, formatDateTime: String }),
        moduleObject,
        moduleObject.exports,
        (url, options) => {
            requests.push({ url, options });
            return fetchHandler(url, options);
        },
        AudioStub,
        {
            createObjectURL(blob) {
                const url = `blob:recording-${objects.length + 1}`;
                objects.push({ url, blob });
                return url;
            },
            revokeObjectURL: url => revoked.push(url),
        },
        AbortController
    );
    const component = moduleObject.exports.default;
    const vm = { ...component.methods, ...component.data() };
    return { vm, component, requests, audios, objects, revoked };
}

test('the table template compiles with the loading and unavailable recording states', () => {
    const result = compileTemplate({ source: descriptor.template.content, filename, id: 'dashboard-recordings' });
    assert.deepEqual(result.errors, []);
});

test('204 and empty media mark a recording unavailable without creating an audio player', async () => {
    for (const result of [response(204), response(200, new Blob())]) {
        const h = table(async () => result);
        await h.vm.togglePlay('/recording');
        assert.equal(h.vm.unavailableAudio['/recording'], true);
        assert.equal(h.audios.length, 0);
        assert.equal(h.vm.loadingAudio, null);
        assert.equal(h.vm.audioError, null);
    }
});

test('available media uses session cookies and releases its blob when playback ends', async () => {
    const h = table();
    await h.vm.togglePlay('/recording');
    assert.equal(h.requests[0].options.credentials, 'same-origin');
    assert.equal(h.vm.playing, '/recording');
    assert.equal(h.vm.loadingAudio, null);
    assert.equal(h.audios[0].url, h.objects[0].url);
    h.audios[0].events.ended();
    assert.equal(h.audios[0].paused, true);
    assert.deepEqual(h.revoked, [h.objects[0].url]);
    assert.equal(h.vm.audio, null);
    assert.equal(h.vm.playing, null);
});

test('a refreshed dashboard can offer recordings that became available after an earlier empty result', async () => {
    const h = table(async () => response(204));
    await h.vm.togglePlay('/recording');
    assert.equal(h.vm.unavailableAudio['/recording'], true);
    h.component.watch.data.call(h.vm);
    assert.deepEqual(h.vm.unavailableAudio, {});
});

test('a transient HTTP error remains retryable and a successful retry clears the error', async () => {
    let attempts = 0;
    const h = table(async () => response(++attempts === 1 ? 503 : 200));
    await h.vm.togglePlay('/recording');
    assert.ok(h.vm.audioError);
    assert.equal(h.vm.unavailableAudio['/recording'], undefined);
    await h.vm.togglePlay('/recording');
    assert.equal(h.vm.playing, '/recording');
    assert.equal(h.vm.audioError, null);
});

test('switching recordings aborts the first fetch and ignores a late response', async () => {
    const first = deferred();
    const h = table(url => url === '/first' ? first.promise : Promise.resolve(response()));
    const oldRequest = h.vm.togglePlay('/first');
    await h.vm.togglePlay('/second');
    assert.equal(h.requests[0].options.signal.aborted, true);
    first.resolve(response());
    await oldRequest;
    assert.equal(h.audios.length, 1);
    assert.equal(h.vm.playing, '/second');
    assert.equal(h.vm.audioError, null);
});

test('canceling during blob loading prevents the old media from starting', async () => {
    const blob = deferred();
    const h = table(async () => ({ ...response(), blob: () => blob.promise }));
    const pending = h.vm.togglePlay('/recording');
    await Promise.resolve();
    await Promise.resolve();
    await h.vm.togglePlay('/recording');
    assert.equal(h.requests[0].options.signal.aborted, true);
    blob.resolve(new Blob(['audio']));
    await pending;
    assert.equal(h.audios.length, 0);
    assert.equal(h.vm.loadingAudio, null);
    assert.equal(h.vm.playing, null);
});

test('stopping pending playback keeps a late play promise from restoring the playing state', async () => {
    const playing = deferred();
    const started = deferred();
    const h = table(undefined, () => { started.resolve(); return playing.promise; });
    const pending = h.vm.togglePlay('/recording');
    await started.promise;
    h.vm.stop();
    playing.resolve();
    await pending;
    assert.equal(h.vm.playing, null);
    assert.equal(h.audios[0].paused, true);
    assert.deepEqual(h.revoked, [h.objects[0].url]);
});

test('unmounting stops playback and releases its object URL', async () => {
    const h = table();
    await h.vm.togglePlay('/recording');
    h.component.beforeUnmount.call(h.vm);
    assert.equal(h.audios[0].paused, true);
    assert.deepEqual(h.revoked, [h.objects[0].url]);
    assert.equal(h.vm.audio, null);
});

test('decoding errors show an inline error and release the failed media', async () => {
    const h = table();
    await h.vm.togglePlay('/recording');
    h.audios[0].events.error();
    assert.ok(h.vm.audioError);
    assert.equal(h.vm.playing, null);
    assert.deepEqual(h.revoked, [h.objects[0].url]);
});
