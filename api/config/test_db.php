<?php
$db = require __DIR__ . '/local.db.php';
// test database! Important not to run tests on production or development databases
$db['dsn'] = 'mysql:host=localhost;dbname=prorabapitest';

return $db;