<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — a one-off serialisation of a plain config doesn't need a Memento.
 *
 * Memento earns its keep when:
 *   - the originator has private state that callers shouldn't touch directly,
 *   - state needs to be saved AND restored later (undo/redo, autosave),
 *   - the caretaker should hold the snapshot opaquely.
 *
 * Dumping a config to disk and walking away is none of those. JSON is
 * fine. There is no originator with private invariants, no restore
 * step, no caretaker. Adding a `Memento` here would be ceremony for
 * the sake of a pattern name.
 */

$config = ['db' => 'mysql://...', 'mail' => 'smtp://...'];
$json = json_encode($config, JSON_THROW_ON_ERROR);

pdp_assert_eq('{"db":"mysql:\/\/...","mail":"smtp:\/\/..."}', $json, 'json is enough');

// roundtrip is also trivial
$restored = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
pdp_assert_eq($config, $restored, 'roundtrip works without ceremony');

pdp_done('Memento was the wrong answer here — see the comment block.');
