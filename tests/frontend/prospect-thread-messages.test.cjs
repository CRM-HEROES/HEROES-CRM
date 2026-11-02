const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const sourcePath = path.resolve(
    __dirname,
    '../../resources/js/components/prospect/profile/blocs/message/Bloc.vue'
);
const descriptor = parse(fs.readFileSync(sourcePath, 'utf8')).descriptor;
const compiled = transformSync(descriptor.script.content, {
    babelrc: false,
    configFile: false,
    plugins: ['@babel/plugin-transform-modules-commonjs'],
}).code;

function loadComponent(messageService) {
    const module = { exports: {} };
    const actions = new Proxy(
        {},
        {
            get: (_, key) => key,
        }
    );

    new Function('require', 'module', 'exports', compiled)(
        (specifier) => {
            if (specifier === '@/apis/project/prospect/message') {
                return messageService;
            }
            if (specifier === 'vuex') {
                return {
                    mapGetters: () => ({}),
                };
            }
            if (specifier.startsWith('@/actions/')) {
                return actions;
            }
            if (specifier === '@/store') {
                return {};
            }
            return {};
        },
        module,
        module.exports
    );

    return module.exports.default;
}

test('message form renders before the newest-first conversation list', () => {
    const formIndex = descriptor.template.content.indexOf('<form');
    const listIndex = descriptor.template.content.indexOf(
        'v-for="(m, i) in messages"'
    );

    assert.ok(formIndex >= 0);
    assert.ok(listIndex > formIndex);
});

test('fetchMessages sorts newest messages first', async () => {
    const component = loadComponent({
        get: async () => ({
            data: [
                { id: 1, created_at: '2026-11-02T07:32:30.000000Z' },
                { id: 3, created_at: '2026-11-02T07:32:46.000000Z' },
                { id: 2, created_at: '2026-11-02T07:32:36.000000Z' },
            ],
        }),
    });
    const context = {
        project: { slug: 'test2' },
        prospect: { id: 37711 },
        thread: { id: 4 },
        fetchingMessages: false,
        messages: [],
    };

    await component.methods.fetchMessages.call(context);

    assert.deepEqual(
        context.messages.map((message) => message.id),
        [3, 2, 1]
    );
    assert.equal(context.fetchingMessages, false);
});
