const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const filename = path.resolve(__dirname, '../../resources/js/components/line/OperatorConfigFields.vue');
const { descriptor } = parse(fs.readFileSync(filename, 'utf8'));
const { code } = transformSync(descriptor.script.content, {
    babelrc: false, configFile: false,
    plugins: ['@babel/plugin-transform-modules-commonjs'],
});

function operatorFields(handler = () => ({ data: { agents: [] } }), fixtures = {}) {
    const requests = [];
    const module = { exports: {} };
    const dependencies = {
        vuex: { mapGetters: () => ({}) },
        '@/store': { commit() {} },
        '@/actions/modal': { OPEN_MODAL: 'open' },
        '@/apis/project/line': {
            getCloudtalkAgents: async (...args) => {
                requests.push(args);
                return handler(...args);
            },
        },
        './KavkomDiagnostic.vue': {},
    };
    new Function('require', 'module', 'exports', code)(
        key => {
            if (!(key in dependencies)) throw new Error(`Unexpected dependency: ${key}`);
            return dependencies[key];
        }, module, module.exports
    );
    const component = module.exports.default;
    const vm = {
        ...component.data(), ...component.methods,
        project: { slug: 'test-project' },
        operator: 'cloudtalk',
        config: {
            api_key_id: 'key-id', api_key_secret: 'key-secret', agent_id: '12', numero: '',
        },
        fields: [],
        ...fixtures,
    };
    for (const [key, computed] of Object.entries(component.computed)) {
        const getter = typeof computed === 'function' ? computed : computed.get;
        Object.defineProperty(vm, key, { get: () => getter.call(vm), configurable: true });
    }
    const watch = (name, value, oldValue) => {
        const definition = component.watch[name];
        const handler = typeof definition === 'function' ? definition : definition.handler;
        return handler.call(vm, value, oldValue);
    };
    const verified = (agents = [
        { id: 12, label: 'Alice', default_number: '+33123456789' },
        { id: '23', label: 'Bob', default_number: '+442012345678' },
    ]) => {
        vm.cloudtalkAgents = agents;
        vm.cloudtalkCredentialsVerified = true;
        vm.cloudtalkCredentialsKey = vm.currentCloudtalkCredentialsKey;
    };
    return { vm, component, requests, watch, verified };
}

function deferred() {
    let resolve, reject;
    const promise = new Promise((success, failure) => { resolve = success; reject = failure; });
    return { promise, resolve, reject };
}

test('loading existing agents preserves the saved SMS number and requires manual confirmation', async () => {
    const agents = [{ id: 12, label: 'Alice', default_number: '+33123456789' }];
    const h = operatorFields(() => ({ data: { agents } }));
    h.vm.config.numero = '+33687654321';

    await h.vm.fetchInitialCloudtalkAgents();

    assert.deepEqual(h.requests, [[
        'test-project', { api_key_id: 'key-id', api_key_secret: 'key-secret' },
    ]]);
    assert.equal(h.vm.config.numero, '+33687654321');
    assert.equal(h.vm.cloudtalkAgentSmsNumber, '+33123456789');
    assert.equal(h.vm.cloudtalkSmsConfirmed, false);
});

test('selecting another agent proposes its number without filling it or carrying confirmation', () => {
    const h = operatorFields();
    h.verified();
    h.vm.config.agent_id = '23';
    h.watch('config.agent_id', '23', '12');

    assert.equal(h.vm.selectedCloudtalkAgent.label, 'Bob');
    assert.equal(h.vm.cloudtalkAgentSmsNumber, '+442012345678');
    assert.equal(h.vm.config.numero, '');
    assert.equal(h.vm.cloudtalkSmsConfirmed, false);
});

test('confirmation fills the proposed number and removing confirmation clears that automatic value', () => {
    const h = operatorFields();
    h.verified();

    h.vm.confirmCloudtalkSmsNumber(true);
    h.watch('config.numero', h.vm.config.numero, '');

    assert.equal(h.vm.config.numero, '+33123456789');
    assert.equal(h.vm.cloudtalkSmsConfirmed, true);
    assert.deepEqual(h.requests, []);

    h.vm.confirmCloudtalkSmsNumber(false);

    assert.equal(h.vm.config.numero, '');
    assert.equal(h.vm.cloudtalkSmsConfirmed, false);
});

test('changing agents clears the automatic number and requires another confirmation', () => {
    const h = operatorFields();
    h.verified();
    h.vm.confirmCloudtalkSmsNumber(true);

    h.vm.config.agent_id = 23;
    h.watch('config.agent_id', 23, '12');

    assert.equal(h.vm.config.numero, '');
    assert.equal(h.vm.cloudtalkSmsConfirmed, false);
    assert.equal(h.vm.cloudtalkAgentSmsNumber, '+442012345678');

    h.vm.confirmCloudtalkSmsNumber(true);
    assert.equal(h.vm.config.numero, '+442012345678');
    assert.equal(h.vm.cloudtalkSmsConfirmed, true);
});

test('editing the SMS number manually removes provenance and preserves the manual value', () => {
    const h = operatorFields();
    h.verified();
    h.vm.confirmCloudtalkSmsNumber(true);
    const automaticNumber = h.vm.config.numero;
    h.vm.config.numero = '+33687654321';
    h.watch('config.numero', h.vm.config.numero, automaticNumber);

    assert.equal(h.vm.cloudtalkSmsConfirmed, false);
    assert.equal(h.vm.autoFilledCloudtalkSmsNumber, '');
    h.vm.confirmCloudtalkSmsNumber(false);
    assert.equal(h.vm.config.numero, '+33687654321');

    h.vm.config.agent_id = '23';
    h.watch('config.agent_id', '23', '12');
    assert.equal(h.vm.config.numero, '+33687654321');
});

test('clearing confirmation preserves a changed manual value even before its watcher runs', () => {
    const h = operatorFields();
    h.verified();
    h.vm.confirmCloudtalkSmsNumber(true);
    h.vm.config.numero = '+33687654321';

    h.vm.clearCloudtalkSmsConfirmation();

    assert.equal(h.vm.config.numero, '+33687654321');
    assert.equal(h.vm.cloudtalkSmsConfirmed, false);
});

test('missing and invalid agent phone numbers cannot be confirmed', () => {
    for (const default_number of [undefined, null, '', '12345', '0033123456789', '+not-a-number', '+01234567', '+1234567890123456']) {
        const h = operatorFields();
        h.verified([{ id: 12, label: 'Alice', default_number }]);
        h.vm.config.numero = '+33687654321';

        h.vm.confirmCloudtalkSmsNumber(true);

        assert.equal(h.vm.cloudtalkAgentSmsNumber, '', String(default_number));
        assert.equal(h.vm.config.numero, '+33687654321', String(default_number));
        assert.equal(h.vm.cloudtalkSmsConfirmed, false);
    }
});

test('an unverified or obsolete credential set cannot supply a number', () => {
    const h = operatorFields();
    h.verified();
    h.vm.cloudtalkCredentialsVerified = false;
    h.vm.confirmCloudtalkSmsNumber(true);
    assert.equal(h.vm.cloudtalkAgentSmsNumber, '');
    assert.equal(h.vm.config.numero, '');

    h.vm.cloudtalkCredentialsVerified = true;
    h.vm.config.api_key_secret = 'different-secret';
    h.vm.confirmCloudtalkSmsNumber(true);
    assert.equal(h.vm.cloudtalkAgentSmsNumber, '');
    assert.equal(h.vm.config.numero, '');
    assert.equal(h.vm.cloudtalkSmsConfirmed, false);
});

test('other operators never propose or apply a CloudTalk agent number', () => {
    for (const operator of ['telephone', 'aircall', 'ringover', 'kavkom']) {
        const h = operatorFields(undefined, { operator });
        h.verified();
        h.vm.config.numero = '+33687654321';

        h.vm.confirmCloudtalkSmsNumber(true);

        assert.equal(h.vm.cloudtalkAgentSmsNumber, '');
        assert.equal(h.vm.config.numero, '+33687654321');
        assert.equal(h.vm.cloudtalkSmsConfirmed, false);
        assert.deepEqual(h.requests, []);
    }
});

test('changing credentials clears the automatic value and resets confirmation', () => {
    const h = operatorFields();
    h.verified();
    h.vm.confirmCloudtalkSmsNumber(true);
    h.vm.config.api_key_secret = 'new-secret';

    h.watch('config.api_key_secret', 'new-secret', 'key-secret');

    assert.equal(h.vm.config.numero, '');
    assert.equal(h.vm.cloudtalkSmsConfirmed, false);
    assert.equal(h.vm.cloudtalkCredentialsVerified, false);
    assert.deepEqual(h.vm.cloudtalkAgents, []);
});

test('replacing configuration forgets provenance without clearing the new configuration number', () => {
    const h = operatorFields();
    h.verified();
    h.vm.confirmCloudtalkSmsNumber(true);
    const previousConfig = h.vm.config;
    h.vm.config = { ...previousConfig, agent_id: '23' };
    h.vm.fetchInitialCloudtalkAgents = async () => {};

    h.watch('config', h.vm.config, previousConfig);

    assert.equal(h.vm.config.numero, '+33123456789');
    assert.equal(h.vm.autoFilledCloudtalkSmsNumber, '');
    assert.equal(h.vm.cloudtalkSmsConfirmed, false);
});

test('switching operator forgets provenance without clearing its number', () => {
    const h = operatorFields();
    h.verified();
    h.vm.confirmCloudtalkSmsNumber(true);
    h.vm.operator = 'ringover';

    h.watch('operator', 'ringover', 'cloudtalk');

    assert.equal(h.vm.config.numero, '+33123456789');
    assert.equal(h.vm.autoFilledCloudtalkSmsNumber, '');
    assert.equal(h.vm.cloudtalkSmsConfirmed, false);
});

test('an obsolete successful agent request cannot overwrite a newer request or stop its loading state', async () => {
    const oldRequest = deferred(), newRequest = deferred();
    let calls = 0;
    const h = operatorFields(() => (++calls === 1 ? oldRequest.promise : newRequest.promise));
    const oldPending = h.vm.fetchCloudtalkAgents();
    h.vm.config.api_key_secret = 'new-secret';
    h.watch('config.api_key_secret', 'new-secret', 'key-secret');
    assert.equal(h.vm.fetchingCloudtalkAgents, false);
    const newPending = h.vm.fetchCloudtalkAgents();

    oldRequest.resolve({ data: { agents: [{ id: 12, label: 'Obsolete', default_number: '+33123456789' }] } });
    await oldPending;

    assert.equal(h.vm.fetchingCloudtalkAgents, true);
    assert.deepEqual(h.vm.cloudtalkAgents, []);
    assert.equal(h.vm.cloudtalkCredentialsVerified, false);

    newRequest.resolve({ data: { agents: [{ id: 23, label: 'Current', default_number: '+442012345678' }] } });
    await newPending;

    assert.equal(h.vm.fetchingCloudtalkAgents, false);
    assert.equal(h.vm.cloudtalkAgents[0].label, 'Current');
    assert.equal(h.vm.cloudtalkCredentialsKey, h.vm.currentCloudtalkCredentialsKey);
    assert.equal(h.vm.config.numero, '');
});

test('an obsolete failed request cannot clear a new account agent or show its error', async () => {
    const oldRequest = deferred();
    let calls = 0;
    const h = operatorFields(() => ++calls === 1 ? oldRequest.promise : {
        data: { agents: [{ id: 23, label: 'Current', default_number: '+442012345678' }] },
    });
    const oldPending = h.vm.fetchCloudtalkAgents();
    h.vm.config.api_key_id = 'new-id';
    h.watch('config.api_key_id', 'new-id', 'key-id');
    await h.vm.fetchCloudtalkAgents();
    h.vm.config.agent_id = '23';
    const currentMessage = h.vm.cloudtalkMessage;

    oldRequest.reject(new Error('Obsolete credentials failed'));
    await oldPending;

    assert.equal(h.vm.cloudtalkAgents[0].label, 'Current');
    assert.equal(h.vm.config.agent_id, '23');
    assert.equal(h.vm.cloudtalkCredentialsVerified, true);
    assert.equal(h.vm.cloudtalkMessage, currentMessage);
});

test('a current agent request error preserves the manually entered SMS number', async () => {
    const h = operatorFields(() => { throw { response: { data: { message: 'Identifiants invalides' } } }; });
    h.vm.config.numero = '+33687654321';

    const loaded = await h.vm.fetchCloudtalkAgents();

    assert.equal(loaded, false);
    assert.equal(h.vm.config.numero, '+33687654321');
    assert.equal(h.vm.cloudtalkMessage, 'Identifiants invalides');
    assert.equal(h.vm.cloudtalkMessageType, 'error');
    assert.equal(h.vm.cloudtalkCredentialsVerified, false);
    assert.equal(h.vm.fetchingCloudtalkAgents, false);
});

test('replacing configuration invalidates a pending response even with the same credentials', async () => {
    const response = deferred();
    const h = operatorFields(() => response.promise);
    const pending = h.vm.fetchCloudtalkAgents();
    const previousConfig = h.vm.config;
    h.vm.config = { ...previousConfig, agent_id: '23', numero: '+33687654321' };
    h.vm.fetchInitialCloudtalkAgents = async () => {};
    h.watch('config', h.vm.config, previousConfig);

    response.resolve({ data: { agents: [{ id: 12, label: 'Old configuration' }] } });
    await pending;

    assert.equal(h.vm.config.agent_id, '23');
    assert.equal(h.vm.config.numero, '+33687654321');
    assert.deepEqual(h.vm.cloudtalkAgents, []);
    assert.equal(h.vm.cloudtalkCredentialsVerified, false);
});

test('unmounting invalidates pending agent requests', async () => {
    const response = deferred();
    const h = operatorFields(() => response.promise);
    const pending = h.vm.fetchCloudtalkAgents();

    h.component.beforeUnmount.call(h.vm);
    response.resolve({ data: { agents: [{ id: 12, label: 'After unmount', default_number: '+33123456789' }] } });
    await pending;

    assert.deepEqual(h.vm.cloudtalkAgents, []);
    assert.equal(h.vm.cloudtalkCredentialsVerified, false);
    assert.equal(h.vm.config.numero, '');
    assert.equal(h.vm.cloudtalkMessage, '');
});
