<?php

/**
 * Shared renderer for application views.
 *
 * @package ProyectoBase
 * @subpackage App\Core
 * @author Jandres25
 * @version 1.0
 */

namespace App\Core;

use InvalidArgumentException;

class ViewRenderer
{
    /**
     * Project root absolute path.
     *
     * @var string
     */
    private $projectRoot;

    public function __construct(?string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?? dirname(__DIR__, 2);
    }

    /**
     * Renders a view with optional shared layout wrappers.
     *
     * @param string $view
     * @param array  $data
     * @param array  $options
     * @return void
     */
    public function render(string $view, array $data = [], array $options = []): void
    {
        $viewFile = $this->resolveViewPath($view);
        if (!file_exists($viewFile)) {
            throw new InvalidArgumentException("View file not found: {$viewFile}");
        }

        extract($data, EXTR_SKIP);

        $withLayout     = $options['with_layout'] ?? true;
        $includeSession = $options['include_session'] ?? true;
        $includeMessage = $options['include_messages'] ?? true;

        if ($withLayout) {
            $layoutPath = $this->projectRoot . '/views/layouts';

            if ($includeSession) {
                require_once $layoutPath . '/session.php';
            }

            require_once $this->projectRoot . '/app/config/config.php';
            include $layoutPath . '/header.php';
            include $viewFile;

            if ($includeMessage) {
                include $layoutPath . '/messages.php';
            }

            include $layoutPath . '/footer.php';
            return;
        }

        include $viewFile;
    }

    /**
     * Resolves a view reference to an absolute file path.
     *
     * @param string $view
     * @return string
     */
    private function resolveViewPath(string $view): string
    {
        if (str_starts_with($view, $this->projectRoot)) {
            return $view;
        }

        if (str_starts_with($view, 'views/')) {
            return $this->projectRoot . '/' . $view;
        }

        $relativeView = str_ends_with($view, '.php') ? $view : $view . '.php';
        return $this->projectRoot . '/views/' . $relativeView;
    }
}
