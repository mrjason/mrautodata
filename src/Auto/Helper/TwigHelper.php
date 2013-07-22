<?php

namespace Auto\Helper;

use \Symfony\Component\Console\Helper\Helper;

/**
 *
 * @package Helper
 * @subpackage Twig
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 * @author Mark Nielsen <mark@moodlerooms.com>
 */
class TwigHelper extends Helper {
    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    function getName() {
        return 'twig';
    }

    /**
     * @param string $skeletonDir Path to skeleton directory (Templates)
     * @param string $template The relative path to $skeletonDir to render
     * @param string $target The target for the rendered product
     * @param array $parameters Parameters used by $template
     * @throws \InvalidArgumentException
     */
    public function renderFile($skeletonDir, $template, $target, $parameters)
    {
        if (file_exists($target)) {
            throw new \InvalidArgumentException(sprintf('Target file already exists: %s', $target));
        }
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem($skeletonDir), array(
            'debug'            => true,
            'cache'            => false,
            'strict_variables' => true,
            'Autoescape'       => false,
        ));

        file_put_contents($target, $twig->render($template, $parameters));
    }
}