<?php
class CSV_Redirects
{

    private $uri;
    private $host;
    private $targeted_host;
    private $file_path;
    private $filename;
    private $redirects = [];


    function __construct($filename)
    {
        $this->filename = $filename;
        $this->file_path = __DIR__ . '/' . $this->filename;
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    }


    function run()
    {
        if (!$this->checkFile()) {
            echo 'Error! The file `' . $this->filename . '` does not exist.';
            return;
        }

        $this->doRedirect();

    }


    private function doRedirect()
    {
        $this->getRedirects();

        if (array_key_exists($this->host . $this->uri, $this->redirects)) {

            $location = $this->redirects[$this->host . $this->uri];

            header("Location: $location", true);

        } else {

            header("Location:  $this->targeted_host", true);
        }
    }


    private function getRedirects()
    {
        $handle = fopen($this->file_path, 'r');

        $i = true;
        while (false !== ($data = fgetcsv($handle))) {

            if ($i) {
                $parse_url = parse_url($data[1]);
                $this->targeted_host = $parse_url['scheme'].'://'.$parse_url['host'];
                $i = false;
            }

            // Redirects
            $this->redirects[$data[0]] = $data[1];
        }


        fclose($handle);

    }


    private function checkFile()
    {
        $mime_types = array(
            'application/csv',
            'application/excel',
            'application/ms-excel',
            'application/x-excel',
            'application/vnd.ms-excel',
            'application/vnd.msexcel',
            'application/octet-stream',
            'application/data',
            'application/x-csv',
            'application/txt',
            'plain/text',
            'text/anytext',
            'text/csv',
            'text/x-csv',
            'text/plain',
            'text/comma-separated-values'
        );

        if (!is_readable($this->file_path)) {
            return false;
        }

        if (in_array(mime_content_type($this->file_path), $mime_types)) {
            return $this->file_path;
        } else {
            return false;
        }
    }


    private function debug()
    {
        $args = func_get_args();

        foreach ($args as $argName => $argValue) {
            print_r('<br/>=== ' . $argName . ' ===<br/>');
            print_r('<pre class="te-st">');
            print_r((is_string($argValue) ? htmlentities($argValue) : $argValue));
            print_r('</pre>');
            print_r('<br/>');
        }

    }
}