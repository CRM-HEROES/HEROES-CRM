const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { transformSync } = require('@babel/core');

const filename = path.resolve(__dirname, '../../resources/js/utils/workflow-format.js');
const { code } = transformSync(fs.readFileSync(filename, 'utf8'), {
    babelrc: false,
    configFile: false,
    plugins: ['@babel/plugin-transform-modules-commonjs'],
});
const module_ = { exports: {} };
new Function('module', 'exports', code)(module_, module_.exports);
const f = module_.exports;

test('a duration is shown in the largest units that matter', () => {
    assert.equal(f.duration(null), '-');
    assert.equal(f.duration(undefined), '-');
    assert.equal(f.duration(0), '0 s');
    assert.equal(f.duration(45), '45 s');
    assert.equal(f.duration(60), '1 min');
    assert.equal(f.duration(90), '1 min 30 s');
    assert.equal(f.duration(7200), '2 h');
    assert.equal(f.duration(5400), '1 h 30 min');
    assert.equal(f.duration(-5), '0 s');
});

test('a date that is missing or invalid is shown as a dash', () => {
    assert.equal(f.dateTime(null), '-');
    assert.equal(f.dateTime(''), '-');
    assert.equal(f.dateTime('not a date'), '-');
    assert.match(f.dateTime('2026-10-30T10:30:00Z'), /2026/);
});

test('every outcome of a step has a sign', () => {
    for (const status of ['completed', 'failed', 'skipped', 'running', 'waiting']) {
        assert.notEqual(f.stepIcon(status), '·', status);
    }
    assert.equal(f.stepIcon('unknown'), '·');
});

test('only an execution that is not being handled can be cancelled', () => {
    assert.equal(f.cancellable({ status: 'waiting' }), true);
    assert.equal(f.cancellable({ status: 'pending' }), true);
    for (const status of ['running', 'completed', 'failed', 'cancelled', 'skipped']) {
        assert.equal(f.cancellable({ status }), false, status);
    }
});

test('an execution tells what it waits for', () => {
    assert.equal(f.waitingKind({ status: 'waiting', waiting_event: 'sms_received' }), 'waiting_event');
    assert.equal(f.waitingKind({ status: 'waiting', waiting_event: null }), 'waiting_until');
    assert.equal(f.waitingKind({ status: 'completed', waiting_event: null }), null);
});
