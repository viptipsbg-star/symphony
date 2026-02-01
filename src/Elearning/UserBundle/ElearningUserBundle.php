<?php

namespace Elearning\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ElearningUserBundle extends Bundle
{

    public function getParent() {
        return "SonataUserBundle";
    }
}
