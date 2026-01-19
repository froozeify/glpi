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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
            $working_dir = $this->getPluginDirectory();
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

        if (!is_dir($locales_dir)) {
            return;
        }

        $files = glob($locales_dir . '/*.po');
        if (empty($files)) {
            return;
        }

        // Check msgfmt
        $process = new Process(['msgfmt', '--version']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException('msgfmt not found!');
        }

        foreach ($files as $file) {
            $mo = preg_replace('/\.po$/', '.mo', $file);
            $basename = basename($file);

            $proc = new Process(['msgfmt', $file, '-o', $mo]);
            $proc->run();

            if (!$proc->isSuccessful()) {
                $this->io->writeln(" <error>Failed to compile $basename</error>");
            } else {
                $this->io->writeln(" <info>Compiled $basename</info>");
            }
        }
    }
}
