"use strict";

// G.711 is deliberately kept dependency-free: FreeSWITCH usually supplies L16
// to mod_audio_stream, but these functions make the codec boundary explicit
// and testable for installations that fork PCMA/PCMU directly.
function muLawToPcm(value) {
    value = ~value & 0xff;
    const magnitude = ((value & 0x0f) << 3) + 0x84;
    return (value & 0x80 ? -1 : 1) * ((magnitude << ((value & 0x70) >> 4)) - 0x84);
}

function aLawToPcm(value) {
    value ^= 0x55;
    let magnitude = (value & 0x0f) << 4;
    const segment = (value & 0x70) >> 4;
    if (segment === 0) magnitude += 8;
    else if (segment === 1) magnitude += 0x108;
    else magnitude = (magnitude + 0x108) << (segment - 1);
    return (value & 0x80) ? magnitude : -magnitude;
}

function decodeG711(input, codec = "pcma") {
    const output = Buffer.allocUnsafe(input.length * 2);
    for (let i = 0; i < input.length; i += 1) output.writeInt16LE(codec === "pcmu" ? muLawToPcm(input[i]) : aLawToPcm(input[i]), i * 2);
    return output;
}

// Linear interpolation preserves timing and is adequate for speech; media
// deployments can instead let FreeSWITCH's resampler perform this conversion.
function resamplePcm16le(input, sourceRate, targetRate) {
    if (sourceRate === targetRate) return Buffer.from(input);
    const sourceSamples = input.length / 2;
    const targetSamples = Math.max(1, Math.round(sourceSamples * targetRate / sourceRate));
    const output = Buffer.allocUnsafe(targetSamples * 2);
    for (let i = 0; i < targetSamples; i += 1) {
        const position = i * sourceRate / targetRate;
        const left = Math.min(Math.floor(position), sourceSamples - 1);
        const right = Math.min(left + 1, sourceSamples - 1);
        const fraction = position - left;
        const sample = Math.round(input.readInt16LE(left * 2) * (1 - fraction) + input.readInt16LE(right * 2) * fraction);
        output.writeInt16LE(Math.max(-32768, Math.min(32767, sample)), i * 2);
    }
    return output;
}

module.exports = { decodeG711, resamplePcm16le };
