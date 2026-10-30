const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse, compileTemplate } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const source = fs.readFileSync(path.resolve(__dirname, '../../resources/js/components/workflow/FieldInput.vue'), 'utf8');
const descriptor = parse(source).descriptor;

test('the template compiles, and the warning does not cut the chain of inputs', () => {
    const { errors, code } = compileTemplate({ source: descriptor.template.content, id: 'x', filename: 'FieldInput.vue' });

    assert.deepEqual(errors, []);

    // The warning is after the last "else": a v-if in the middle would give the
    // text input to every type of field, next to the right one
    const template = descriptor.template.content;
    assert.ok(template.indexOf('v-if="emptyList && type') > template.indexOf('<input v-else type="text"'));
});

test('ticking an item adds it, ticking it again removes it, others are kept', () => {
    const { code } = transformSync(descriptor.script.content, {
        babelrc: false,
        configFile: false,
        plugins: ['@babel/plugin-transform-modules-commonjs'],
    });
    const stubs = { vuex: { mapGetters: () => ({}) }, '@/store': {}, '@/apis/project/workflow': {}, '@/apis/project/label': {} };
    const module_ = { exports: {} };
    new Function('module', 'exports', 'require', code)(module_, module_.exports, (id) => stubs[id] || {});
    const component = module_.exports.default;

    const emitted = [];
    const vm = { modelValue: [3], $emit: (e, v) => emitted.push(v) };
    for (const [name, method] of Object.entries(component.methods)) vm[name] = method.bind(vm);

    vm.toggleId(5);
    vm.modelValue = [3, 5];
    vm.toggleId(3);

    assert.deepEqual(emitted, [[3, 5], [5]]);
    assert.equal(vm.isChosen(5), true);
    assert.equal(vm.isChosen(9), false);
});
