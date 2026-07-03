<?php
namespace verbb\navigation\migrations\plugins;

class MigrationLine
{
    // Public Methods
    // =========================================================================

    public function __construct(
        public string $level,
        public string $message,
        public int $depth = 0,
        public array $context = [],
    ) {}
}
