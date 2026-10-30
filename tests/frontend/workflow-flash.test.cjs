const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');

// The pop-ups of the app take { title, body }: a bare string gives an empty one
test('every pop-up of the workflow screens has a title and a body', () => {
    const dir = path.resolve(__dirname, '../../resources/js/components/workflow');

    for (const file of fs.readdirSync(dir).filter((f) => f.endsWith('.vue'))) {
        const calls = fs.readFileSync(path.join(dir, file), 'utf8').match(/\bflash(?:Error|Warning|Info)?\([^)]/g) || [];

        for (const call of calls) {
            assert.ok(call.endsWith('{'), `${file}: ${call}`);
        }
    }
});
