# Laravel log viewer

## How to use
- Copy `log-viewer.php` file to your place where you want to run (example `public` folder)
- Change line 8 to your project logs folder path:
```
$logsFolder = '/path_to_project/storage/logs/';
```
- Access link `https://your.domain/log-viewer.php`

*Note*: the `LOG_CHANNEL` is set to `daily` instead of `stack` in your .env file is the best. (`APP_LOG=daily` for Laravel 5.5 and below)
## Preview
![](https://raw.githubusercontent.com/thaont540/laravel-log-viewer/master/demo1.png)
