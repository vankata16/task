<?php

require __DIR__ . '/vendor/autoload.php';

$calculateCommissions = new CalculateCommissions($argv[1]);

$commissions = $calculateCommissions->calculate();

echo $commissions;

exit();
