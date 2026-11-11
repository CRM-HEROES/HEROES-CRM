const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const filename = path.resolve(
    __dirname,
    '../../resources/js/components/prospect/table/header-cell/FindReplace.vue'
);
const script = parse(fs.readFileSync(filename, 'utf8')).descriptor.script.content;
const compiled = transformSync(script, {
    babelrc: false,
    configFile: false,
    plugins: ['@babel/plugin-transform-modules-commonjs'],
}).code;
const store = { dispatch: async () => {} };
const module = { exports: {} };

new Function('require', 'module', 'exports', compiled)(specifier => {
    if (specifier === 'vuex') {
        return {
            mapGetters: names => Object.fromEntries(
                names.map(name => [name, function () { return this[name]; }])
            ),
        };
    }
    if (specifier === '@/store') return store;
    if (specifier === '@/apis/project/prospect') return { default: {} };
    if (specifier === '@/actions/project/prospect') {
        return { FETCH_PROSPECTS: 'fetchProspects' };
    }
    throw new Error(`Unexpected dependency: ${specifier}`);
}, module, module.exports);

const component = module.exports.default;

function instance({ search, replace = 'X', caseSensitive = true, sample }) {
    const vm = {
        ...component.data(),
        ...component.methods,
        search,
        replace,
        caseSensitive,
        result: { samples: [sample] },
    };
    return vm;
}

test('finds every repeated occurrence in a single field and queues them in order', () => {
    const sample = { id: 7, before: '12ahjh12ajlj12al', after: '' };
    const vm = instance({ search: '12a', sample });

    assert.deepEqual(vm.findOccurrences(sample.before).map(match => match.index), [0, 6, 12]);
    vm.startReview();
    assert.equal(vm.reviewQueue.length, 3);
    assert.equal(vm.isCurrentOccurrence(sample, 0), true);

    vm.skipOccurrence();
    assert.equal(vm.isCurrentOccurrence(sample, 1), true);
});

test('replacing one occurrence advances to the next original occurrence', async () => {
    const sample = { id: 7, before: '12a-12a-12a', after: '' };
    const vm = instance({ search: '12a', sample });
    vm.startReview();
    vm.run = async (mode, params) => {
        assert.equal(mode, 'replace_occurrence');
        assert.deepEqual(params, {
            prospect_ids: [7],
            occurrence_index: 0,
            expected_value: '12a-12a-12a',
        });
        return { samples: [{ after: 'X-12a-12a' }] };
    };
    vm.advanceReview = component.methods.advanceReview.bind(vm);

    await vm.replaceOccurrence(sample, 0);

    assert.equal(sample.before, 'X-12a-12a');
    assert.equal(vm.reviewQueue[1].occurrenceIndex, 0);
    assert.equal(vm.isCurrentOccurrence(sample, 0), true);
});

test('numeric searches allow whitespace between digits like the server', () => {
    const sample = { id: 9, before: '12 3 / 123', after: '' };
    const vm = instance({ search: '123', sample });

    assert.deepEqual(vm.findOccurrences(sample.before).map(match => match.text), ['12 3', '123']);
});
