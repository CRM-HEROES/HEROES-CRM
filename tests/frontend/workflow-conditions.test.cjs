const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { transformSync } = require('@babel/core');

const filename = path.resolve(__dirname, '../../resources/js/utils/workflow-conditions.js');
const { code } = transformSync(fs.readFileSync(filename, 'utf8'), {
    babelrc: false,
    configFile: false,
    plugins: ['@babel/plugin-transform-modules-commonjs'],
});
const module_ = { exports: {} };
new Function('module', 'exports', code)(module_, module_.exports);
const c = module_.exports;

const catalog = {
    conditions: {
        filters: {
            prospect: [
                { name: 'incomingCommunications', type: 'boolean' },
                { name: 'createdDaysAgo', type: 'number' },
                { name: 'withUsers', type: 'users' },
                { name: 'withLabels', type: 'labels' },
            ],
            order: [],
        },
    },
};

test('a new criterion starts with a value of the right type', () => {
    assert.deepEqual(c.newFilterItem(catalog, 'prospect', 'incomingCommunications'), { filters: { incomingCommunications: true } });
    assert.deepEqual(c.newFilterItem(catalog, 'prospect', 'createdDaysAgo'), { filters: { createdDaysAgo: 1 } });
    assert.deepEqual(c.newFilterItem(catalog, 'prospect', 'withUsers'), { filters: { withUsers: [] } });
    assert.equal(c.newFilterItem(catalog, 'prospect', 'nope'), null);
    assert.equal(c.newFilterItem(catalog, 'order', 'processed'), null);
});

test('two new criteria never share the same list', () => {
    const a = c.newFilterItem(catalog, 'prospect', 'withUsers');
    const b = c.newFilterItem(catalog, 'prospect', 'withUsers');

    a.filters.withUsers.push(3);

    assert.deepEqual(b.filters.withUsers, []);
});

test('the criteria of an unknown subject are none', () => {
    assert.deepEqual(c.filtersFor(catalog, 'event'), []);
    assert.deepEqual(c.filtersFor({ conditions: {} }, 'prospect'), []);
});

test('the checks start with usable values', () => {
    assert.deepEqual(c.newCheckItem('work_hours'), { check: 'work_hours', days: [1, 2, 3, 4, 5], from: '08:00', to: '17:00' });
    assert.deepEqual(c.newCheckItem('date_range'), { check: 'date_range', from: null, to: null });
});

test('a trigger that watches a filter starts with its first criterion', () => {
    assert.deepEqual(c.triggerConfig(catalog, 'prospect.filter_entered'), {
        trigger: 'prospect.filter_entered',
        op: 'and',
        items: [{ filters: { incomingCommunications: true } }],
    });
    assert.equal(c.triggerConfig(catalog, 'prospect.filter_left').items.length, 1);
});

test('the other triggers start with nothing else', () => {
    assert.deepEqual(c.triggerConfig(catalog, 'prospect.created'), { trigger: 'prospect.created' });
    assert.deepEqual(c.triggerConfig(catalog, 'event.starting_soon'), { trigger: 'event.starting_soon' });
});
