<?php
# edit path to log files
$logsFolder = '/path_to_project/storage/logs/';

if ($date = $_GET['date']) {
    $logViewer = new LogViewer($logsFolder . $date);
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
    </style>
</head>
<body>
<button type="button" class="collapsible">All logs</button>
<div class="content">
    <?php
        if ($handle = opendir($logsFolder)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != '.' && $entry != '..' && $entry != '.gitignore') {
                    echo '<a href="?date=' . $entry . '">' . $entry . '</a><br>';
                }
            }

            closedir($handle);
        }
    ?>
</div>
<?php
    if (isset($date)) {
        ?>
        <ul>
            <li>Viewing log: <b> <?php echo $date; ?> </b></li>
        </ul>
        <?php
    }
?>
<ol id='log'>
    <?php
        if (isset($date) && isset($logViewer)) {
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
            endforeach;
        }
    ?>
</ol>
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
/*
 * Laravel logs viewer in single file
 *
 */

class LogViewer {

    public $rawLines;
    public $formattedLines = [];

    function __construct($logFilePath) {
        $this->rawLines = file($logFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->formatLines();
    }

    function formatLines() {
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

    function checkLevel($message) {
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
}
?>
