const test = require("node:test");
const assert = require("node:assert/strict");
const { decodeG711, resamplePcm16le } = require("../audio-codec");
test("G.711 reference silence and 8kHz to 16kHz PCM timing", () => {
    const alawSilence = Buffer.from([0xd5, 0xd5, 0xd5, 0xd5]);
    const pcm8k = decodeG711(alawSilence, "pcma");
    // A-law's digital-silence reference code expands to the standard 8-LSB
    // quantisation offset, not a mathematically exact zero.
    assert.ok([...pcm8k].every((byte, index) => index % 2 ? byte === 0 : byte === 8));
    assert.equal(resamplePcm16le(pcm8k, 8000, 16000).length, 16);
    assert.equal(resamplePcm16le(pcm8k, 8000, 24000).length, 24);
});
