<?php
/**
 * @file
 * Interface to enumerate and use dimension classes
 */

namespace local_analytics;

class dimensions {
    /**
     * The array of class instances.
     */
    static private $dimension_instances = null;

    /**
     * Find class instances and populate the array
     *
     * @return array of strings
     *   A list of the names of files containing plugins.
     */
    static public function enumerate_plugins()
    {
        $dir = dirname(__FILE__) . '/dimensions';

        $list_of_files = scandir($dir);
        foreach($list_of_files as $index => $entry) {
            if ($entry == '.' || $entry == '..' || substr($entry, -4) != '.php') {
                unset($list_of_files[$index]);
            }
        }

        return $list_of_files;
    }

    /**
     * Instantiate plugins and populate the array.
     *
     * @return array
     *   An array keys by plugin filename, with values being class instances.
     */
    static public function instantiate_plugins()
    {
        if (is_null(self::$dimension_instances)) {
            $list_of_files = self::enumerate_plugins();

            $plugins = array();

            foreach ($list_of_files as $index => $entry) {
                require_once(__DIR__ . '/dimensions/' . $entry);

                $class_name = substr($entry, 0, -4);

                // Check the expected class exists.
                if (!class_exists('\local\analytics\dimensions\\' . $class_name, FALSE)) {
                    debugging("Local Analytics: File ${entry} in the dimensions directory doesn't define a class named ${class_name}",
                        DEBUG_DEVELOPER);
                    continue;
                }

                $class_name = '\local\analytics\dimensions\\' . $class_name;
                $plugins[$class_name] = new $class_name;
            }

            self::$dimension_instances = $plugins;
        }

        return self::$dimension_instances;
    }

    /**
     * Get plugin options list.
     *
     * @return array
     *   An array of items for a select combo.
     */
    static public function setting_options()
    {
        static $result = null;

        if (is_null($result)) {
            $plugins = self::instantiate_plugins();

            $result = array('' => '');

            foreach ($plugins as $file => $plugin) {
                $lang_string = get_string($plugin::$name, 'local_analytics');
                $result[$plugin::$name] = $lang_string;
            }
        }

        return $result;

    }

}