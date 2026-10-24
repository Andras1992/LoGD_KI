<?php

// PDO-backed compatibility layer for the original db_* API.
// This keeps legacy game pages working while they are migrated gradually.

$dbqueriesthishit = 0;
$dbtimethishit = 0;
$dbLastStatement = null;
$dbLastError = '';

// Temporary compatibility for legacy pages that relied on magic_quotes_gpc.
// Remove this shim only after their SQL has been converted to bound parameters.
function db_apply_legacy_quotes(&$value)
{
    if (is_array($value)) {
        foreach ($value as &$item) {
            db_apply_legacy_quotes($item);
        }
        unset($item);
    } elseif (is_string($value)) {
        $value = addslashes($value);
    }
}

foreach (array('_GET', '_POST', '_COOKIE') as $inputName) {
    if (isset($GLOBALS[$inputName])) {
        db_apply_legacy_quotes($GLOBALS[$inputName]);
    }
}

$HTTP_GET_VARS =& $_GET;
$HTTP_POST_VARS =& $_POST;
$HTTP_COOKIE_VARS =& $_COOKIE;

// Request values still pass through the temporary magic-quotes shim above.
// Remove that one compatibility layer before binding them with PDO.
function db_unquote_legacy_input($value)
{
    if (is_string($value)) {
        return stripslashes($value);
    }

    return is_scalar($value) || $value === null ? $value : '';
}

function db_query($sql)
{
    global $pdo, $dbqueriesthishit, $dbtimethishit, $dbLastStatement, $dbLastError;

    $dbqueriesthishit++;
    $start = microtime(true);

    try {
        $dbLastStatement = $pdo->query($sql);
        $dbLastError = '';
        return $dbLastStatement;
    } catch (PDOException $exception) {
        $dbLastStatement = null;
        $dbLastError = $exception->getMessage();
        throw $exception;
    } finally {
        $dbtimethishit += microtime(true) - $start;
    }
}

/**
 * Execute a prepared statement while preserving the legacy db_* result API.
 * Values must be supplied separately from the SQL string.
 */
function db_query_params($sql, $params = array())
{
    global $pdo, $dbqueriesthishit, $dbtimethishit, $dbLastStatement, $dbLastError;

    $dbqueriesthishit++;
    $start = microtime(true);

    try {
        $statement = $pdo->prepare($sql);
        $statement->execute($params);
        $dbLastStatement = $statement;
        $dbLastError = '';
        return $statement;
    } catch (PDOException $exception) {
        $dbLastStatement = null;
        $dbLastError = $exception->getMessage();
        throw $exception;
    } finally {
        $dbtimethishit += microtime(true) - $start;
    }
}

function db_insert_id($link = false)
{
    global $pdo;
    return $pdo->lastInsertId();
}

function db_error($link = false)
{
    global $pdo, $dbLastError;

    if ($dbLastError !== '') {
        return $dbLastError;
    }

    if (isset($pdo) && $pdo instanceof PDO) {
        $error = $pdo->errorInfo();
        return isset($error[2]) ? $error[2] : '';
    }

    return '';
}

function db_fetch_assoc($result)
{
    global $dbtimethishit;
    $start = microtime(true);
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $dbtimethishit += microtime(true) - $start;
    return $row;
}

function db_num_rows($result)
{
    global $dbtimethishit;
    $start = microtime(true);
    $count = $result->rowCount();
    $dbtimethishit += microtime(true) - $start;
    return $count;
}

function db_affected_rows($link = false)
{
    global $dbLastStatement;
    return $dbLastStatement instanceof PDOStatement ? $dbLastStatement->rowCount() : 0;
}

function db_pconnect($host, $user, $pass, $database = '', $charset = 'utf8mb4')
{
    global $pdo, $dbLastError;

    $dsn = 'mysql:host=' . $host;
    if ($database !== '') {
        $dsn .= ';dbname=' . $database;
    }
    if ($charset !== '') {
        $dsn .= ';charset=' . $charset;
    }

    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_EMULATE_PREPARES => false,
    );

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        $dbLastError = '';
        return $pdo;
    } catch (PDOException $exception) {
        $dbLastError = $exception->getMessage();
        throw $exception;
    }
}

function db_select_db($dbname)
{
    global $pdo;
    $pdo->exec('USE `' . str_replace('`', '``', $dbname) . '`');
    return true;
}

function db_free_result($result)
{
    if ($result instanceof PDOStatement) {
        return $result->closeCursor();
    }
    return false;
}
?>
