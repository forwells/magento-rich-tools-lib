<?php

namespace Tools\Database;

use Magento\Framework\App\DeploymentConfig;
class Db extends Platform\Pdo
{

    public function __construct(
        DeploymentConfig $ini
    )
    {
        $inc = $ini->get('db/connection/default');
        parent::__construct($inc['host'], $inc['username'], $inc['password'], $inc['dbname']);
    }
}