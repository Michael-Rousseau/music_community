<?php
namespace App\Controllers;

class Controller {
    protected function render($view, $data = [], $layout = 'layouts/base') {
        // extract data for use in views
        extract($data);

        // check if htmx request
        $isHtmxRequest = isset($_SERVER['HTTP_HX_REQUEST']) && $_SERVER['HTTP_HX_REQUEST'] === 'true';

        // get view content
        $viewFile = __DIR__ . "/../Views/$view.php";
        if (!file_exists($viewFile)) {
            die("View '$view' not found!");
        }

        // capture view output
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // if htmx request, return content only
        if ($isHtmxRequest) {
            echo $content;
            return;
        }

        // otherwise wrap in layout
        if ($layout !== false) {
            $layoutFile = __DIR__ . "/../Views/$layout.php";
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $content; // fallback if layout missing
            }
        } else {
            echo $content;
        }
    }
    
    protected function redirect($url) {
        header("Location: $url");
        exit();
    }
}
