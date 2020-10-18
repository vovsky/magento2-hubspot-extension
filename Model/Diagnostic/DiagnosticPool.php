<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\Diagnostic;

use InvalidArgumentException;

class DiagnosticPool
{
    /**
     * @var DiagnosticInterface[]
     */
    private $tests;

    /**
     * DiagnosticPool constructor.
     *
     * @param DiagnosticInterface[] $tests
     */
    public function __construct(array $tests = [])
    {
        foreach ($tests as $test) {
            if (!$test instanceof DiagnosticInterface) {
                throw new InvalidArgumentException(
                    sprintf('Test must be an instance of %s', DiagnosticInterface::class)
                );
            }
        }
        $this->tests = $tests;
    }

    /**
     * @param string $name
     *
     * @return DiagnosticInterface|null
     */
    public function getByName(string $name): ?DiagnosticInterface
    {
        return isset($this->tests[$name]) ? $this->tests[$name] : null;
    }

    /**
     * @return array
     */
    public function getNames(): array
    {
        return array_keys($this->tests);
    }
}
