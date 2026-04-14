<?php

/**
 * Base controller for app-layer controllers.
 *
 * @package ProyectoBase
 * @subpackage App\Core
 * @author Jandres25
 * @version 1.0
 */

namespace App\Core;

abstract class BaseController
{
    /**
     * Shared view renderer.
     *
     * @var ViewRenderer
     */
    protected $viewRenderer;

    public function __construct(?ViewRenderer $viewRenderer = null)
    {
        $this->viewRenderer = $viewRenderer ?? new ViewRenderer();
    }

    /**
     * Renders a view with optional layout options.
     *
     * @param string $view
     * @param array  $data
     * @param array  $options
     * @return void
     */
    protected function render(string $view, array $data = [], array $options = []): void
    {
        $this->viewRenderer->render($view, $data, $options);
    }

    /**
     * Outputs a JSON response and ends execution.
     *
     * @param array $payload
     * @param int   $statusCode
     * @return void
     */
    protected function json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload);
        exit;
    }
}
