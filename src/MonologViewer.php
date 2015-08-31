<?php
namespace Jtclark;

class MonologViewer {
    protected $settings = [];

    public function __construct(array $settings)
    {
        if (empty($settings['user'])) {
            throw new \Exception('user must be set');
        }
        if (empty($settings['pass'])) {
            throw new \Exception('password must be set');
        }
        if (empty($settings['path'])) {
            throw new \Exception('Log path must be set');
        }
        $this->settings = $settings;
    }


    public function authenticate()
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'You must be authorized to access this page.';
            exit;
        }

        if ($_SERVER['PHP_AUTH_USER'] != $this->settings['user'] || $_SERVER['PHP_AUTH_PW'] != $this->settings['pass']) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Invalid credentials';
            exit;
        }
    }

    /**
     * Number of lines to return, if null, entire file will be read
     * @param int|null $lines
     */
    public function render($lines = 100)
    {
        $logPath = $this->settings['path'];

        // make sure log path actually exists
        if (!file_exists($logPath)) {
            die('Log file does not exist');
        }

        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/templates');
        $twig = new \Twig_Environment($loader, [
            'debug' => true,
        ]);

        $twig->addExtension(new \Twig_Extension_Debug());
        $twig->addExtension(new \Twig_Extensions_Extension_Date());

        $twig->addFilter(new \Twig_SimpleFilter('alertIconClass', function ($string) {
            switch ($string) {
                case 'ERROR':
                case 'CRITICAL':
                    return 'fa-exclamation-circle';
                    break;
                case 'WARNING':
                    return 'fa-exclamation-triangle';
                    break;
                case 'INFO':
                case 'DEBUG':
                    return 'fa-info-circle';
                    break;
            }
            return '';
        }));

        $twig->addFilter(new \Twig_SimpleFilter('alertClass', function ($string) {
            switch ($string) {
                case 'ERROR':
                case 'CRITICAL':
                    return 'alert-danger';
                    break;
                case 'WARNING':
                    return 'alert-warning';
                    break;
                case 'INFO':
                case 'DEBUG':
                    return 'alert-info';
                    break;
            }
            return '';
        }));

        if ($lines === null) {
            $lines = array_reverse(explode("\n", file_get_contents($logPath)));
        } else {
            $lines = array_reverse(explode("\n", $this->tail($logPath, $lines)));
        }
        foreach ($lines as $line) {
            $json = json_decode($line, true);
            if ($json === null) {
                die('Could not read log line: ' . $line);
            }
            $template = !empty($this->settings['template']) ? $this->settings['template'] : 'log.twig';
            echo $twig->render($template, $json);
        }
    }

    /**
     * Read X lines using a dynamic buffer (more efficient for all file sizes)
     *
     * @author Lorenzo Stanco
     * @url https://gist.github.com/lorenzos/1711e81a9162320fde20
     *
     * @param $filepath
     * @param int $lines
     * @param bool|true $adaptive
     * @return bool|string
     */
    public function tail($filepath, $lines = 100, $adaptive = true)
    {

        // Open file
        $f = @fopen($filepath, "rb");
        if ($f === false) return false;

        // Sets buffer size
        if (!$adaptive) $buffer = 4096;
        else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));

        // Jump to last character
        fseek($f, -1, SEEK_END);

        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n") $lines -= 1;

        // Start reading
        $output = '';
        $chunk = '';

        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {

            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);

            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);

            // Read a chunk and prepend it to our output
            $output = ($chunk = fread($f, $seek)) . $output;

            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);

            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");

        }

        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {

            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);

        }

        // Close file and return
        fclose($f);
        return trim($output);

    }
}