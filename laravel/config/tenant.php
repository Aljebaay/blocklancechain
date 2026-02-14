<?php

return [
    'enabled' => filter_var(env('TENANT_MODE', false), FILTER_VALIDATE_BOOLEAN),
    'path_prefix' => env('TENANT_PATH_PREFIX', '/t'),
    // placeholder for future tenant table/column config
];
