<?php

namespace Tools\Helper;

use Tools\Base;

class Common extends Base
{
    public function helper($className = "")
    {
        return $this->manager->get($className);
    }
}