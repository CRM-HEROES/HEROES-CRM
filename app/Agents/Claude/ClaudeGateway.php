<?php

namespace App\Agents\Claude;

/**
 * The only door to the Claude API. The engine talks to this interface, so the
 * tests run it with a scripted gateway and the SDK stays in one class.
 *
 * Request
 *  model       string
 *  system      list of {text: string, cache?: bool}  (cache: put a cache breakpoint after it)
 *  tools       list of {name, description, schema}   (JSON Schema, additionalProperties: false)
 *  messages    list of {role, content}               (content as stored: blocks of the API)
 *  max_tokens  int
 *  effort      string                                (low | medium | high | xhigh | max)
 */
interface ClaudeGateway
{
    /**
     * @throws ClaudeException
     */
    public function send(array $request): ClaudeResponse;
}
