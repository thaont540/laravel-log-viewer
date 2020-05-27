<?php

/*
 * Laravel logs viewer in single file
 *
 */

class SimpleLogViewer {

    public $rawLines;
    public $formattedLines = [];

    function __construct($logFilePath) {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $this->rawLines = file($logFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->formatLines();
    }

    function formatLines(){
        for ($index = 0; $index < count($this->rawLines); $index++) {
            $line = $this->rawLines[$index];
            $this->formattedLines[$index] = [];
            $level = strpos($line, 'ERROR') !== false ? 'error' : (strpos($line, 'DEBUG') !== false ? 'debug' : '');
            $this->formattedLines[$index]['level'] = $level;
            $this->formattedLines[$index]['message'] = $line;
        }
    }

}

$logsFolder = '/var/www/path_to_project/storage/logs/';

if ($handle = opendir($logsFolder)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != '.' && $entry != '..') {
            echo '<a href="/log-view.php?date=' . $entry . '">' . $entry . '</a><br>';
        }
    }

    closedir($handle);
}


$date = $_GET['date'];
if ($date) {
    $simpleLogViewer = new SimpleLogViewer($logsFolder . $date);
}
?>
<!doctype html>
<html>
<head>
    <title>Logs Viewer</title>
    <meta charset='utf-8'>
    <!--<meta http-equiv='refresh' content='5'>-->
    <style>
        body {
            font:1em/1.2 normal Arial, sans-serif;
        }
        ol li {
            padding:6px;
            border-bottom:1px solid #ccc;
        }
        .debug {
            color: orange;
        }
        .error {
            color: red;
        }
        .warn {
            color: yellow;
        }
        .time {
            font-size:0.8em;
        }
    </style>
</head>
<body>
<ol id='log'>
    <?php if (isset($date) && isset($simpleLogViewer)) { ?>
        <?php foreach($simpleLogViewer->formattedLines as $line): ?>
            <li>
                <span class='message <?php echo $line['level']; ?> level'><?php echo $line['message']; ?></span>
            </li>
        <?php endforeach; ?>
    <?php } ?>
</ol>
</body>
</html>
