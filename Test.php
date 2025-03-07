<?php
// torture.php - tortue a Connect Four web service by performing
// various simple tests, esp. error handling.
//
// To run this script on your local PHP server to test your class webaccount,
// you need to enable OpenSSL in your PHP server:
//
// a. Navigate to your PHP installation directory.
// b. Copy the file php.ini-development and rename it to php.ini
// c. Open the php.ini file in a text editor to locate and uncomment
//    the extension=openssl line by removing the ;, so it appears as:
//       extension=openssl
// d. Addionally, set the extension_dir to the appropriate directory
//    if your PHP installation directory isnâ€™t C:\php. For example:
//       extension_dir="C:\Software\phpx64\ext"

// set to the base address (URL) of your Connect Four web service
$home = "https://www.cs.utep.edu/cheon/cs3360/project/c4/";
//$home = "http://localhost:8000/";
//$home = "https://cssrvlab01.utep.edu/Classes/cs3360Cheon/<user-name>";

$strategies = array(); // strategies supported by the web service under test
$numOfSlots = 7;  // number of slots supported by the service under test

runTests();

/** Test info: {"width":7,"height":6,"strategies":["Smart","Random"]}. */
function testInfo() {
    global $strategies;
    global $numOfSlots;
    $TAG = "I1";
    $response = visitInfo();
    if ($response) {
        $info = json_decode($response);
        if ($info != null) {
            $width = property($info, 'width');
            $numOfSlots = $width;
            assertTrue(isSet($width) && $width >= 7, "$TAG-1");
            $height = property($info, 'height');
            assertTrue(isSet($height) && $height >= 6, "$TAG-2");
            $strategies = property($info, 'strategies');
            assertTrue(isSet($strategies) && is_array($strategies)
                && sizeof($strategies) >= 2, "$TAG-3");
            return;
        }
    }
    fail("$TAG-4");
}

/** helper to retrieve a property of an object, or null. */
function property($obj, $property) {
    if (is_object($obj) && property_exists($obj, $property)) {
        return $obj->{$property};
    }
    return null;
}

/** Test: all strategies. Must be called after testInfo(). */
function testNew1() {
    $TAG = "N1";
    global $strategies;
    if (!is_array($strategies)) {
        assertTrue(false, "$TAG-1");
        return;
    }
    assertTrue(sizeof($strategies) > 0, "$TAG-2");
    foreach ($strategies as $s) {
        $response = visitNew($s);
        checkNewResponse($response, true, "$TAG-3");
    }
}

/** Test: strategy not specified. */
function testNew2() {
    $response = visitNew();
    checkNewResponse($response, false, "N2");
}

/** Test: unknown strategy. */
function testNew3() {
    $response = visitNew('Strategy' . uniqid());
    checkNewResponse($response, false, "N3");
}

/** Test: no pid specified. */
function testPlay1() {
    $response = visitPlay();
    //var_dump($response);
    checkPlayResponse($response, false, "P1");
}

/** Test: no move specified. */
function testPlay2() {
    $response = visitPlay(createGame());
    //var_dump($response);
    checkPlayResponse($response, false, "P2");
}

/** Test: unknown pid. */
function testPlay3() {
    $response = visitPlay('pid-' . uniqid(), "1");
    //var_dump($response);
    checkPlayResponse($response, false, "P3");
}

/** Test: invalid slot. */
function testPlay4() {
    $response = visitPlay(createGame(), "-1");
    //var_dump($response);
    checkPlayResponse($response, false, "P4");
}

/** Test: invalid slot. */
function testPlay5() {
    global $numOfSlots;
    $response = visitPlay(createGame(), $numOfSlots);
    //var_dump($response);
    checkPlayResponse($response, false, "P5");
}

/** Test: valid slot. */
function testPlay6() {
    $response = visitPlay(createGame(), "0");
    //var_dump($response);
    checkPlayResponse($response, true, "P6");
}

/** Test: valid slot */
function testPlay7() {
    global $numOfSlots;
    $response = visitPlay(createGame(), $numOfSlots - 1);
    checkPlayResponse($response, true, "P7");
}

/** Test: play response */
function testPlay8() {
    $TAG = "P8";
    $response = visitPlay(createGame(), "3");
    $json = json_decode($response);
    $ackMove = property($json, 'ack_move');
    assertTrue(isSet($ackMove), "$TAG-1");
    $slot = property($ackMove, 'slot');
    assertTrue(isSet($slot) && $slot == 3, "$TAG-2");
    $isWin = property($ackMove, 'isWin');
    assertTrue(isSet($isWin) && !$isWin, "$TAG-3");
    $isDraw = property($ackMove, 'isDraw');
    assertTrue(isSet($isDraw) && !$isDraw, "$TAG-4");
    $row = property($ackMove, 'row');
    assertTrue(isSet($row) && is_array($row) && empty($row), "$TAG-5");
}

/** Test: play response */
function testPlay9() {
    global $numOfSlots;

    $TAG = "P9";
    $response = visitPlay(createGame(), "3");
    $json = json_decode($response);
    $move = property($json, 'move');
    assertTrue(isSet($move), "$TAG-1");
    $slot = property($move, 'slot');
    assertTrue(isSet($slot) && $slot >= 0 && $slot < $numOfSlots, "$TAG-2");
    $isWin = property($move, 'isWin');
    assertTrue(isSet($isWin) && !$isWin, "$TAG-3");
    $isDraw = property($move, 'isDraw');
    assertTrue(isSet($isDraw) && !$isDraw, "$TAG-4");
    $row = property($move, 'row');
    assertTrue(isSet($row) && is_array($row) && empty($row), "$TAG-5");
}

/** Test: partial game - place several discs. */
function testPlay10() {
    global $numOfSlots;

    $TAG = "P10";
    $pid = createGame();
    for ($i = 0; $i < 3; $i++) {
        // pick an arbitray slot
        $slot = rand(0, $numOfSlots - 1);
        $response = visitPlay($pid, $slot);
        checkPlayResponse($response, true, $TAG);
    }
}

/** Test: concurrent games. */
function testPlay11() {
    $TAG = "P11";
    $g1 = createGame();
    play($g1, "1", true, "$TAG-1");
    $g2 = createGame();
    play($g2, "1", true, "$TAG-2");
    assertTrue($g1 != $g2, "$TAG-3"); // differed play Ids.
}

//- helper methods

function cs_file_get_contents($url) {
    // To fix an issue with https on CS server
    // $response = @file_get_contents($url);
    $arrContextOptions = [
        "ssl" => ["verify_peer" => false, "verify_peer_name" => false]
    ];
    $response = @file_get_contents($url, false,
        stream_context_create($arrContextOptions));
    return $response;
}

function visitInfo() {
    global $home;
    return cs_file_get_contents($home . "/info/index.php");
}

function visitNew($strategy = null) {
    global $home;
    $query = '';
    if (!is_null($strategy)) {
        $query = '?strategy=' . $strategy;
    }
    return cs_file_get_contents($home . "/new/index.php" . $query);
}

function checkNewResponse($response, $expected, $msg) {
    if ($response) {
        $json = json_decode($response);
        if ($json != null) {
            $r = property($json, 'response');
            assertTrue(isSet($r) && $r == $expected, $msg);
            if ($expected) {
                $pid = property($json, 'pid');
                assertTrue(isSet($pid), $msg);
            }
            return;
        }
    }
    fail($msg);
}

function createGame() {
    global $strategies;
    if (!is_array($strategies)) {
        assertTrue(false, "G-1");
        return;
    }
    $strategy = "Random";
    if (count($strategies) > 0) {
        $strategy = $strategies[0];
    }
    $response = visitNew($strategy);
    $json = json_decode($response);
    return property($json, 'pid');
}

function play($pid = null, $move = null, $ok = null, $tag = null) {
    $response = visitPlay($pid, $move);
    checkPlayResponse($response, $ok, $tag);
}

function visitPlay($pid = null, $move = null) {
    global $home;
    $query = '';
    if (!is_null($pid)) {
        $query = '?pid=' . $pid;
    }
    if (!is_null($move)) {
        $query = $query . (strlen($query) > 0 ? '&' : '?');
        $query = $query . 'move=' . $move;
    }
    return cs_file_get_contents($home . "/play/index.php" . $query);
}

function checkPlayResponse($response, $expected, $msg) {
    if ($response) {
        $json = json_decode($response);
        if ($json != null) {
            $r = property($json, 'response');
            assertTrue(isSet($r) && $r == $expected, $msg);
            if ($expected) {
                $ack = property($json, 'ack_move');
                assertTrue(isSet($ack), $msg);
            }
            return;
        }
    }
    fail($msg);
}

//---------------------------------------------------------------------
// Simple testing framework
//---------------------------------------------------------------------

/** Run all user-defined functions named 'test*'. */
function runTests($webOut = false) {
    $count = 0;
    $prefix = "test";
    $functions = get_defined_functions ();
    $names = $functions ['user'];
    foreach ($names as $name)  {
        if (substr($name, 0, strlen($prefix)) === $prefix) {
            $count ++;
            echo ".";
            call_user_func($name);
        }
    }
    summary($count, fail('', false), $webOut);
}


function assertTrue($expr, $msg) {
    if (!$expr) {
        fail($msg);
    }
}

function fail($msg, $report = true) {
    static $count = 0;
    static $tested = array();

    if ($report) {
        $prefix = explode('-', $msg);
        $testId = $prefix[0];  // e.g., P1 from P1-1
        if (!in_array($testId, $tested)) {
            $tested[] = $testId;
            $count++;
            echo "F($msg)";
        }
    }
    return $count;
}

function summary($total, $failure, $webout) {
    $sucess = $total - $failure;
    echo "\n";
    if ($webout) {
        echo "<br/>";
    }
    echo "Failed/Total: $failure/$total (passed: $sucess)\n";
}

?>