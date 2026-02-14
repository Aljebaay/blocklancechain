<?php
declare(strict_types=1);

namespace App\Runtime;

final class RuntimeContext
{
    /** @var array<string,mixed> */
    private array $globals;

    /**
     * @param array<string,mixed> $globals
     */
    private function __construct(array $globals)
    {
        $this->globals = $globals;
    }

    /**
     * @param array<int,string> $keys
     */
    public static function fromGlobals(array $keys = []): self
    {
        if ($keys === []) {
            return new self($GLOBALS);
        }

        $scoped = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $GLOBALS)) {
                $scoped[$key] = $GLOBALS[$key];
            }
        }

        return new self($scoped);
    }

    /**
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        if (array_key_exists($name, $this->globals)) {
            return $this->globals[$name];
        }

        return $default;
    }

    /**
     * @return array<string,mixed>
     */
    public function all(): array
    {
        return $this->globals;
    }
}
