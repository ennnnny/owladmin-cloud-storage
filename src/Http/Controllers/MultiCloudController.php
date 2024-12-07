<?php

namespace Slowlyo\MultiCloud\Http\Controllers;

use Slowlyo\OwlAdmin\Controllers\AdminController;
use Slowlyo\OwlAdmin\Renderers\Form;

class MultiCloudController extends AdminController
{
    public function detail(): Form
    {
        return $this->baseDetail()->body([]);
    }
}
