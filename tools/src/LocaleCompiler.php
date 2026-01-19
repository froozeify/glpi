<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2026 Teclib' and contributors.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

namespace Glpi\Tools;

/**
 * Utility class for compiling locale files.
 * This class has no external dependencies and can be used before Composer autoloading is available.
 * Used in bin/console for example.
 */
final class LocaleCompiler
{
    /**
     * Compile MO files from PO files.
     *
     * @param string        $locales_dir     Path to the locales directory
     * @param callable|null $output_callback Optional callback for output: function(string $message, bool $is_error)
     *
     * @throws \RuntimeException If msgfmt is not found
     */
    public static function compileLocales(string $locales_dir, ?callable $output_callback = null): void
    {
        if (!is_dir($locales_dir)) {
            return;
        }

        $files = glob($locales_dir . '/*.po');
        if (empty($files)) {
            return;
        }

        // Check msgfmt
        exec('msgfmt --version 2>&1', $output, $return_code);
        if ($return_code !== 0) {
            throw new \RuntimeException('msgfmt not found!');
        }

        foreach ($files as $file) {
            $mo = preg_replace('/\.po$/', '.mo', $file);
            $basename = basename($file);

            exec(sprintf('msgfmt %s -o %s 2>&1', escapeshellarg($file), escapeshellarg($mo)), $cmd_output, $exit_code);

            if ($output_callback !== null) {
                if ($exit_code !== 0) {
                    $output_callback("Failed to compile $basename", true);
                } else {
                    $output_callback("Compiled $basename", false);
                }
            }
        }
    }
}
