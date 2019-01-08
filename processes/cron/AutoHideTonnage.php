<?php

require_once __DIR__ . '/../tonnage/LoadDateAutoHide.php';

echo "Trying to autohide tonnage at: " . date('m/d/Y H:i:s') . "\n";
$l = LoadDateAutoHide();
