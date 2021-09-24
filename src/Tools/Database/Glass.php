<?php

namespace Tools\Database;

use Magento\Framework\App\DeploymentConfig;
class Glass extends Platform\Pdo
{

    public function __construct(
        DeploymentConfig $ini
    )
    {
        $inc = $ini->get('glassdb/connection');
        parent::__construct($inc['host'], $inc['username'], $inc['password'], $inc['dbname']);
    }
}