<?php

declare(strict_types=1);
namespace Zoosper\Core\Console\BuiltIn;
use Zoosper\Core\Console\ConsoleCommandInterface; use Zoosper\Core\Console\ConsoleOutput; use Zoosper\Core\Release\ReleaseCheck;
final readonly class ReleaseCheckCommand implements ConsoleCommandInterface
{
 public function __construct(private ReleaseCheck $check) {} public function name(): string{return 'release:check';} public function description(): string{return 'Run alpha release-readiness checks.';}
 public function run(array $args, ConsoleOutput $output): int { $failed=0; foreach($this->check->run() as $r){$output->writeln(($r->passed?'[PASS]':'[FAIL]')." {$r->name}: {$r->message}"); if(!$r->passed){$failed++;}} $output->writeln($failed===0?'Alpha release checks passed.':"Alpha release checks failed: {$failed} blocker(s)."); return $failed===0?0:1; }
}
