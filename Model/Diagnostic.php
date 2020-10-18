<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model;

use Groove\Hubshoply\Model\Diagnostic\DiagnosticPool;
use Groove\Hubshoply\Model\Diagnostic\DiagnosticResultInterface;

class Diagnostic
{

    /**
     * @var bool
     */
    private $skipDependencyCheckFlag = false;

    /**
     * @var DiagnosticPool
     */
    private $diagnosticPool;

    public function __construct(DiagnosticPool $diagnosticPool)
    {
        $this->diagnosticPool = $diagnosticPool;
    }

    public function setSkipDependencyCheckFlag(bool $flag)
    {
        $this->skipDependencyCheckFlag = $flag;

        return $this;
    }

    public function getSkipDependencyCheckFlag(): bool
    {
        return $this->skipDependencyCheckFlag;
    }

    /**
     * @param array       $types
     * @param string|null $storeId
     *
     * @return array
     */
    public function run(array $types = [], string $storeId = null): array
    {
        $result = [];
        if (!$types) {
            $types = $this->diagnosticPool->getNames();
        }
        foreach ($types as $type) {
            $diagnostic = $this->diagnosticPool->getByName($type);
            $result[] = $diagnostic->run($storeId);
        }

        return $result;
    }
}
