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

namespace Glpi\Tools\Command;

use Glpi\Tools\LocaleCompiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class CompileLocalesCommand extends AbstractCommand
{
    #[Override]
    protected function isPluginOptionAvailable(): bool
    {
        return true;
    }

    protected function configure(): void
    {
        parent::configure();
        $this->setName('tools:compile_locales');
        $this->setDescription('Compile MO files from PO files.');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->isPluginCommand()) {
            $working_dir = $this->getDevPluginDirectory();
        } else {
            $working_dir = dirname(__DIR__, 3); // glpi
        }

        $this->compile($working_dir);

        return Command::SUCCESS;
    }

    private function compile(string $dir): void
    {
        $locales_dir = $dir . '/locales';
        $this->io->section("Compiling MO files");
        if ($this->output->isVerbose()) {
            $this->output->writeln(" <question>Locales dir: $locales_dir</question>");
        }

        /** @var ConsoleSectionOutput $section */
        $section = $this->output->section();
        $section_messages = [];

        $output_callback = function (string $message, bool $is_error = false) use ($section, &$section_messages) {
            $section_messages[] = $is_error
                ? " <error>$message</error>"
                : " <info>$message</info>";
            $section->overwrite(implode("\n", $section_messages));
        };

        LocaleCompiler::compileLocales($locales_dir, $output_callback);
        $this->io->newLine();
    }
}
