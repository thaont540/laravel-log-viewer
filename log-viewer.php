<?php
/*
 * Laravel logs viewer in single file
 *
 */

# edit path to log files
$logsFolder = '/path_to_project/storage/logs/';
$logViewer = new LogViewer($logsFolder);

if ($date = $_GET['date']) {
    $logViewer->read($date);
}

if ($file = $_GET['download']) {
    $logViewer->download($file);
}
?>
<!doctype html>
<html>
<head>
    <title>Logs Viewer</title>
    <meta charset='utf-8'>
    <!--<meta http-equiv='refresh' content='5'>-->
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font:1em/1.2 normal Arial, sans-serif;
            padding: 10px;
        }
        .message {
            display: list-item;
            padding: 6px;
            border-bottom: 1px solid #ccc;
        }
        .debug {
            color: orange;
        }
        .error {
            color: red;
        }
        .warning {
            color: yellow;
        }
        .info {
            color: deepskyblue;
        }
        .notice {
            color: #0E9A00;
        }
        .critical {
            color: #f70503;
        }
        .alert {
            color: #ff00ff;
        }
        .emergency {
            color: #ec4844;
        }
        .collapsible {
            background-color: #6f706f;
            color: white;
            cursor: pointer;
            padding: 18px;
            width: 100%;
            border: none;
            text-align: left;
            outline: none;
            font-size: 15px;
            margin-bottom: 20px;
        }
        .active, .collapsible:hover {
            background-color: #555;
        }
        .content {
            padding: 0 18px;
            display: none;
            overflow: hidden;
            background-color: #f1f1f1;
        }
        .header {
            padding: 30px;
            text-align: center;
            background: white;
        }
        .header h1 {
            font-size: 50px;
        }
        .leftcolumn {
            float: left;
            width: 75%;
        }
        .rightcolumn {
            float: left;
            width: 25%;
            padding-left: 20px;
        }
        .card {
            background-color: white;
            padding: 20px;
            margin-top: 20px;
        }
        .row:after {
            content: "";
            display: table;
            clear: both;
        }
        @media screen and (max-width: 800px) {
            .leftcolumn, .rightcolumn {
                width: 100%;
                padding: 0;
            }
        }
        @media screen and (max-width: 400px) {
            .topnav a {
                float: none;
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Simple Laravel Logs viewer</h1>
    <p>Show your log by simple way.</p>
</div>
<div class="row">
    <?php if (isset($date) && $logViewer->formattedLines) { ?>
        <div class="leftcolumn">
            <div class="card">
                <?php
                foreach($logViewer->formattedLines as $index => $line):
                    if ($line['level'] != '') {
                        if ($index != 0) {
                            echo '</div>';
                        }

                        echo '<button type="button" class="collapsible ' . $line['level'] . '">' . substr($line['message'], 0, 250) . '...</button>';
                        echo '<div class="content">';
                        echo '<span class="message level">' . $line['message'] . '</span>';
                    } else {
                        echo '<span class="message level">' . $line['message'] . '</span>';
                    }

                    if ($index == count($logViewer->formattedLines) - 1) {
                        echo '</div>';
                    }
                endforeach;
                ?>
            </div>
        </div>
        <div class="rightcolumn">
            <div class="card">
                <?php
                if (isset($date)) {
                    echo '<h2>Viewing file: <a href="?download=' . $date . '" target="_blank">' . $date . '</a></h2>';
                }
                ?>
            </div>
            <div class="card">
                <button type="button" class="collapsible">Show all logs</button>
                <div class="content">
                    <ul>
                        <?php
                        foreach ($logViewer->logs() as $log) {
                            echo '<li><a href="?date=' . $log . '">' . $log . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div>
            <h2>Start from here</h2>
            <ul>
                <?php
                foreach ($logViewer->logs() as $log) {
                    echo '<li><a href="?date=' . $log . '">' . $log . '</a></li>';
                }
                ?>
            </ul>
        </div>
    <?php } ?>
</div>
<div class="footer">
    By <a href="https://github.com/thaont540" target="_blank">ThaoNT</a>
</div>
</body>
<script>
    var coll = document.getElementsByClassName('collapsible');
    var i;

    for (i = 0; i < coll.length; i++) {
        coll[i].addEventListener('click', function() {
            this.classList.toggle('active');
            var content = this.nextElementSibling;
            if (content.style.display === 'block') {
                content.style.display = 'none';
            } else {
                content.style.display = 'block';
            }
        });
    }
</script>
</html>

<?php

class LogViewer
{
    public $rawLines;
    public $formattedLines = [];
    public $logsFolder;

    function __construct($logsFolder)
    {
        $this->logsFolder = $logsFolder;
    }

    function read($file)
    {
        $fileName = realpath($this->logsFolder . $file);
        if ($fileName === false || strncmp($fileName, $this->logsFolder, strlen($this->logsFolder)) !== 0) {
            die('Missing file or not in the expected location');
        }

        if (file_exists($this->logsFolder . $file)) {
            $this->rawLines = file($this->logsFolder . $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $this->formatLines();
        } else {
            die('Error: File not found.');
        }
    }

    function formatLines()
    {
        for ($index = 0; $index < count($this->rawLines); $index++) {
            $line = $this->rawLines[$index];
            $this->formattedLines[$index] = [];
            $level = $this->checkLevel($line);
            $this->formattedLines[$index]['level'] = $level;
            $this->formattedLines[$index]['message'] = str_replace(
                strtoupper($level),
                '<span class="' . $level . '">' . strtoupper($level) . '</span>',
                $line
            );
        }
    }

    function checkLevel($message)
    {
        $levels = [
            'emergency',
            'alert',
            'critical',
            'error',
            'warning',
            'notice',
            'info',
            'debug',
        ];

        foreach ($levels as $level) {
            if (strpos(strtolower($message), '.' . $level) !== FALSE) {
                return $level;
            }
        }

        return '';
    }

    function download($file)
    {
        $attachmentLocation = $this->logsFolder . $file;
        if (file_exists($attachmentLocation)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
            header('Cache-Control: public');
            header('Content-Type: text/plain');
            header('Content-Transfer-Encoding: Binary');
            header('Content-Length:' . filesize($attachmentLocation));
            header('Content-Disposition: attachment; filename=' . $file);
            readfile($attachmentLocation);
            die($file . ' downloaded.');
        } else {
            die('Error: File not found.');
        }
    }

    function logs()
    {
        $logs = [];
        $ignore = [
            '.',
            '..',
            '.gitignore',
        ];
        if ($handle = opendir($this->logsFolder)) {
            while (false !== ($entry = readdir($handle))) {
                if (!in_array($entry, $ignore)) {
                    $logs[] = $entry;
                }
            }

            closedir($handle);
        }

        return $logs;
    }
}
?>
