<?php

namespace GrumphpDrupalCheck;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Drupal check task.
 */
class DrupalCheck extends AbstractExternalTask
{

  /**
   * @param ContextInterface $context
   *
   * @return bool
   */
  #[\Override]
  public function canRunInContext(ContextInterface $context): bool
  {
      return $context instanceof GitPreCommitContext || $context instanceof RunContext;
  }

  /**
   * {@inheritdoc}
   */
  #[\Override]
  public static function getConfigurableOptions(): ConfigOptionsResolver
  {
      $resolver = new OptionsResolver();
      $resolver->setDefaults([
        'drupal_root' => '',    // Path to Drupal root.
        'format' => 'table',    // Formatter to use: raw, table, checkstyle, json, or junit [default: "table"]
        'deprecations' => TRUE, // Check for deprecations
        'analysis' => TRUE,     // Check code analysis
        'style' => FALSE,       // Check code style
        'php8' => FALSE,        // Set PHPStan phpVersion for 8.1 (Drupal 10 requirement)
        'memory_limit' => '',   // Memory limit for analysis
        'exclude_dir' => [],    // Directories to exclude. Separate multiple directories with a comma, no spaces.
        'verbose' => 0          // Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
      ]);

      $resolver->addAllowedTypes('drupal_root', ['string']);
      $resolver->addAllowedTypes('format', ['string']);
      $resolver->addAllowedTypes('deprecations', ['bool']);
      $resolver->addAllowedTypes('analysis', ['bool']);
      $resolver->addAllowedTypes('style', ['bool']);
      $resolver->addAllowedTypes('php8', ['bool']);
      $resolver->addAllowedTypes('memory_limit', ['string']);
      $resolver->addAllowedTypes('exclude_dir', ['array']);
      $resolver->addAllowedTypes('verbose', ['int']);

      return ConfigOptionsResolver::fromOptionsResolver($resolver);
  }

  /**
   * {@inheritdoc}
   */
  #[\Override]
  public function run(ContextInterface $context): TaskResultInterface
  {
    $config = $this->getConfig();
    $options = $config->getOptions();

    /** @var \GrumPHP\Collection\FilesCollection $files */
    $files = $context->getFiles();
    $triggered_by = [
      'php',
      'inc',
      'module',
      'install',
      'profile',
      'theme',
    ];
    $files = $files->extensions($triggered_by);
    if (0 === count($files)) {
        return TaskResult::createSkipped($this, $context);
    }
    $arguments = $this->processBuilder->createArgumentsForCommand(('drupal-check'));
    $arguments->add('--no-progress');
    $arguments->addOptionalArgument('--drupal-root=%s', $options['drupal_root']);
    $arguments->addOptionalArgument('--format=%s', $options['format']);
    !$options['deprecations'] ?: $arguments->add('--deprecations');
    !$options['analysis'] ?: $arguments->add('--analysis');
    !$options['style'] ?: $arguments->add('--style');
    !$options['php8'] ?: $arguments->add('--php8');
    $arguments->addOptionalArgument('--memory-limit=%s', $options['memory_limit']);
    $arguments->addOptionalArgument('--exclude-dir=%s', implode(',', $options['exclude_dir']));
    if ($options['verbose'] === 1) {
        $arguments->add('-v');
    } else if ($options['verbose'] === 2) {
        $arguments->add('-vv');
    } else if ($options['verbose'] === 3) {
        $arguments->add('-vvv');
    };
    $arguments->addFiles($files);
    $process = $this->processBuilder->buildProcess($arguments);
    $process->run();
    if (!$process->isSuccessful()) {
        $output = $this->formatter->format($process);
        return TaskResult::createFailed($this, $context, $output);
    }
    return TaskResult::createPassed($this, $context);
  }

}
