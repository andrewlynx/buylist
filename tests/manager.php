<?php

use App\Kernel;

require __DIR__.'/bootstrap.php';
$kernel = new Kernel('test', true);
$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
