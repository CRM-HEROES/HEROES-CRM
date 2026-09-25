<?php

namespace App\Jobs\Import;

// Linux filesystems are case-sensitive. The code imports the trait as
// SendsWelcomeSms, but the original file was named Sendswelcomesms.php.
// Include the canonical implementation from the legacy filename so both
// names resolve correctly during autoloading.
require_once __DIR__ . '/Sendswelcomesms.php';
