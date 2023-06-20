<?php

namespace SLLH\ComposerVersionsCheck;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\RootPackageInterface;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Repository\RepositoryManager;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class VersionsCheckPlugin implements PluginInterface, EventSubscriberInterface
{
    private Composer $composer;
    private IOInterface $io;
    private VersionsCheck $versionsCheck;
    private bool $preferLowest;
    private array $options = [];

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->versionsCheck = new VersionsCheck();
        $this->options = $this->resolveOptions();
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PluginEvents::COMMAND => [
                ['command'],
            ],
            ScriptEvents::POST_UPDATE_CMD => [
                ['postUpdate', -100],
            ],
        ];
    }

    public function command(CommandEvent $event): void
    {
        $input = $event->getInput();
        $this->preferLowest = $input->hasOption('prefer-lowest') && $input->getOption('prefer-lowest');
    }

    public function postUpdate(Event $event): void
    {
        if ($this->preferLowest) {
            return;
        }

        $this->checkVersions($this->composer->getRepositoryManager(), $this->composer->getPackage());
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    private function resolveOptions(): array
    {
        $pluginConfig = $this->composer->getConfig()
            ? $this->composer->getConfig()->get('sllh-composer-versions-check')
            : null;

        $options = [
            'show-links' => false,
        ];

        if ($pluginConfig === null) {
            return $options;
        }

        $options['show-links'] = $pluginConfig['show-links'] ?? $options['show-links'];

        return $options;
    }

    private function checkVersions(RepositoryManager $repositoryManager, RootPackageInterface $rootPackage): void
    {
        foreach ($repositoryManager->getRepositories() as $repository) {
            $this->versionsCheck->checkPackages(
                $repository,
                $repositoryManager->getLocalRepository(),
                $rootPackage
            );
        }

        $this->io->write($this->versionsCheck->getOutput($this->options['show-links']), false);
    }
}
